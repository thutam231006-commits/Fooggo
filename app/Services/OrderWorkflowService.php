<?php

namespace App\Services;

use App\Models\Food;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderWorkflowService
{
    public function create(User $customer, string $pickupSlot, ?array $items = null): Order
    {
        $cart = $customer->cart()->first();
        $items ??= $cart?->items()->get(['food_id', 'quantity'])->toArray() ?? [];

        if ($items === []) {
            throw ValidationException::withMessages(['cart' => 'Giỏ hàng đang trống.']);
        }

        return DB::transaction(function () use ($cart, $customer, $pickupSlot, $items) {
            $total = 0;
            $rows = [];

            foreach ($items as $item) {
                $food = Food::whereKey($item['food_id'])->lockForUpdate()->first();

                if (! $food || ! $food->is_available || $food->stock === 0) {
                    throw ValidationException::withMessages([
                        'cart' => ($food?->name ?? 'Sản phẩm').' - Hết sản phẩm. Vui lòng xóa món khỏi giỏ hàng.',
                    ]);
                }

                if ($food->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'cart' => "{$food->name} chỉ còn {$food->stock} phần. Vui lòng giảm số lượng trong giỏ.",
                    ]);
                }

                $subtotal = (float) $food->price * $item['quantity'];
                $total += $subtotal;
                $rows[] = compact('food', 'subtotal') + ['quantity' => $item['quantity']];
            }

            $order = Order::create([
                'user_id' => $customer->id,
                'ordered_at' => now(),
                'pickup_slot' => $pickupSlot,
                'payment_expires_at' => now()->addMinutes(30)->min(Carbon::parse($pickupSlot)),
                'total' => $total,
                'status' => 'pending_payment',
            ]);

            foreach ($rows as $row) {
                $order->items()->create([
                    'food_id' => $row['food']->id,
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['food']->price,
                    'subtotal' => $row['subtotal'],
                ]);
                $row['food']->decrement('stock', $row['quantity']);
            }

            $cart?->items()->delete();

            return $order->load('items.food');
        });
    }

    public function pay(User $customer, Order $order): Order
    {
        if ($order->user_id !== $customer->id) {
            abort(403, 'Bạn không có quyền thanh toán đơn này.');
        }

        $result = DB::transaction(function () use ($customer, $order) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (in_array($lockedOrder->status, ['paid', 'preparing', 'ready', 'completed'], true) && $lockedOrder->payment()->where('status', 'successful')->exists()) {
                return $lockedOrder->fresh()->load('payment', 'items.food');
            }

            if ($lockedOrder->status !== 'pending_payment' || $lockedOrder->payment()->exists()) {
                throw ValidationException::withMessages(['payment' => 'Đơn hàng không thể thanh toán.']);
            }

            $deadline = $lockedOrder->payment_expires_at ?? $lockedOrder->ordered_at->copy()->addMinutes(30);
            if (! $deadline->isFuture() || ! Carbon::parse($lockedOrder->pickup_slot)->isFuture()) {
                $lockedOrder->load('items');
                foreach ($lockedOrder->items as $item) {
                    Food::whereKey($item->food_id)->increment('stock', $item->quantity);
                }
                $lockedOrder->update(['status' => 'cancelled']);

                return null;
            }

            $lockedCustomer = User::whereKey($customer->id)->lockForUpdate()->firstOrFail();

            if ($lockedCustomer->wallet_balance < $lockedOrder->total) {
                throw ValidationException::withMessages(['payment' => 'Số dư Ví FoodGo không đủ.']);
            }

            $lockedCustomer->decrement('wallet_balance', $lockedOrder->total);
            $lockedOrder->payment()->create([
                'transaction_code' => 'FG-'.now()->format('YmdHis').'-'.Str::upper(Str::random(8)),
                'method' => 'foodgo_wallet',
                'amount' => $lockedOrder->total,
                'status' => 'successful',
                'paid_at' => now(),
            ]);
            $lockedOrder->update(['status' => 'paid', 'payment_expires_at' => null]);

            return $lockedOrder->fresh()->load('payment', 'items.food');
        });

        if (! $result) {
            throw ValidationException::withMessages(['payment' => 'Đơn hàng đã hết thời gian giữ món. Tồn kho đã được hoàn lại, vui lòng đặt đơn mới.']);
        }

        return $result;
    }

    public function expireReservations(): int
    {
        $count = 0;
        Order::where('status', 'pending_payment')->orderBy('id')->chunkById(100, function ($orders) use (&$count) {
            foreach ($orders as $order) {
                $count += DB::transaction(function () use ($order) {
                    $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
                    $deadline = $locked->payment_expires_at ?? $locked->ordered_at->copy()->addMinutes(30);
                    if ($locked->status !== 'pending_payment' || ($deadline->isFuture() && Carbon::parse($locked->pickup_slot)->isFuture())) {
                        return 0;
                    }
                    foreach ($locked->items as $item) {
                        Food::whereKey($item->food_id)->increment('stock', $item->quantity);
                    }
                    $locked->update(['status' => 'cancelled']);

                    return 1;
                });
            }
        });

        return $count;
    }

    public function cancel(User $customer, Order $order): Order
    {
        if ($order->user_id !== $customer->id) {
            abort(403, 'Bạn không có quyền hủy đơn này.');
        }

        return DB::transaction(function () use ($order) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status !== 'pending_payment') {
                throw ValidationException::withMessages(['order' => 'Chỉ được hủy đơn đang chờ thanh toán.']);
            }

            $lockedOrder->load('items');
            foreach ($lockedOrder->items as $item) {
                Food::whereKey($item->food_id)->increment('stock', $item->quantity);
            }

            $lockedOrder->update(['status' => 'cancelled']);

            return $lockedOrder->fresh()->load('items.food');
        });
    }
}
