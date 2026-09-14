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

                if (! $food || ! $food->is_available || $food->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'cart' => 'Một món trong giỏ đã hết hoặc không còn đủ số lượng. Vui lòng cập nhật giỏ hàng.',
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

        return DB::transaction(function () use ($customer, $order) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status !== 'pending_payment' || $lockedOrder->payment()->exists()) {
                throw ValidationException::withMessages(['payment' => 'Đơn hàng không thể thanh toán hoặc đã được thanh toán.']);
            }

            if (Carbon::parse($lockedOrder->pickup_slot)->isPast()) {
                throw ValidationException::withMessages(['payment' => 'Khung giờ nhận món đã qua. Vui lòng hủy đơn và đặt lại.']);
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
            $lockedOrder->update(['status' => 'paid']);

            return $lockedOrder->fresh()->load('payment', 'items.food');
        });
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
