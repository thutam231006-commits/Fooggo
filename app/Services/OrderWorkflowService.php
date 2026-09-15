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
    public function __construct(private readonly FoodCustomizationService $customizations) {}

    public function create(User $customer, string $pickupSlot, ?array $items = null, string $fulfillmentType = 'dine_in', string $paymentMethod = 'foodgo_wallet'): Order
    {
        if (! in_array($paymentMethod, ['foodgo_wallet', 'cash_on_delivery'], true)) {
            throw ValidationException::withMessages(['payment_method' => 'Phuong thuc thanh toan khong hop le.']);
        }

        $cart = $customer->cart()->first();
        $items ??= $cart?->items()->get(['food_id', 'quantity', 'options', 'unit_price'])->toArray() ?? [];

        if ($items === []) {
            throw ValidationException::withMessages(['cart' => 'Giỏ hàng đang trống.']);
        }

        return DB::transaction(function () use ($cart, $customer, $pickupSlot, $items, $fulfillmentType, $paymentMethod) {
            $total = 0;
            $rows = [];
            $requestedQuantities = [];

            foreach ($items as $item) {
                $food = Food::whereKey($item['food_id'])->lockForUpdate()->first();

                $requestedQuantities[$item['food_id']] = ($requestedQuantities[$item['food_id']] ?? 0) + $item['quantity'];

                if (! $food || ! $food->is_available || $food->stock < $requestedQuantities[$item['food_id']]) {
                    throw ValidationException::withMessages([
                        'cart' => 'Một món trong giỏ đã hết hoặc không còn đủ số lượng. Vui lòng cập nhật giỏ hàng.',
                    ]);
                }

                $options = $this->customizations->normalize($item['options'] ?? $item);
                $unitPrice = $this->customizations->unitPrice($food, $options);
                $subtotal = $unitPrice * $item['quantity'];
                $total += $subtotal;
                $rows[] = compact('food', 'subtotal', 'unitPrice', 'options') + ['quantity' => $item['quantity']];
            }

            $serviceFee = $fulfillmentType === 'takeaway' ? collect($rows)->sum(fn ($row) => $row['quantity'] * 2000) : 0;
            $total += $serviceFee;

            $order = Order::create([
                'user_id' => $customer->id,
                'ordered_at' => now(),
                'pickup_slot' => $pickupSlot,
                'fulfillment_type' => $fulfillmentType,
                'payment_method' => $paymentMethod,
                'service_fee' => $serviceFee,
                'total' => $total,
                'status' => $paymentMethod === 'cash_on_delivery' ? 'paid' : 'pending_payment',
                'expires_at' => $paymentMethod === 'cash_on_delivery' ? null : now()->addMinutes(15),
            ]);

            $order->update(['pickup_code' => 'A-'.str_pad((string) $order->id, 3, '0', STR_PAD_LEFT)]);

            if ($paymentMethod === 'cash_on_delivery') {
                $order->payment()->create([
                    'transaction_code' => 'COD-'.now()->format('YmdHis').'-'.Str::upper(Str::random(8)),
                    'method' => 'cash_on_delivery',
                    'amount' => $total,
                    'status' => 'pending',
                ]);
            }

            foreach ($rows as $row) {
                $order->items()->create([
                    'food_id' => $row['food']->id,
                    'quantity' => $row['quantity'],
                    'options' => $row['options'],
                    'unit_price' => $row['unitPrice'],
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

        $expired = false;
        $paidOrder = DB::transaction(function () use ($customer, $order, &$expired) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status !== 'pending_payment' || $lockedOrder->payment()->exists()) {
                throw ValidationException::withMessages(['payment' => 'Đơn hàng không thể thanh toán hoặc đã được thanh toán.']);
            }

            if ($lockedOrder->expires_at?->isPast()) {
                $this->releaseInventory($lockedOrder);
                $lockedOrder->update(['status' => 'expired']);
                $expired = true;

                return null;
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

        if ($expired) {
            throw ValidationException::withMessages(['payment' => 'Đơn hàng đã hết thời gian thanh toán. Tồn kho đã được hoàn lại.']);
        }

        return $paidOrder;
    }

    public function cancel(User $customer, Order $order): Order
    {
        if ($order->user_id !== $customer->id) {
            abort(403, 'Bạn không có quyền hủy đơn này.');
        }

        return DB::transaction(function () use ($order) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! in_array($lockedOrder->status, ['pending_payment', 'paid'], true)) {
                throw ValidationException::withMessages(['order' => 'Đơn đã được bếp tiếp nhận nên không thể hủy.']);
            }

            $this->releaseInventory($lockedOrder);

            if ($lockedOrder->status === 'paid') {
                $payment = $lockedOrder->payment;

                if ($payment?->method === 'foodgo_wallet' && $payment->status === 'successful') {
                    User::whereKey($lockedOrder->user_id)->lockForUpdate()->firstOrFail()->increment('wallet_balance', $lockedOrder->total);
                    $payment->update(['status' => 'refunded', 'refunded_at' => now()]);
                } elseif ($payment?->method === 'cash_on_delivery') {
                    $payment->update(['status' => 'cancelled']);
                }
            }

            $lockedOrder->update(['status' => 'cancelled']);

            return $lockedOrder->fresh()->load('items.food');
        });
    }

    public function expirePendingOrders(): int
    {
        $expired = 0;
        Order::where('status', 'pending_payment')->where('expires_at', '<=', now())->pluck('id')->each(function ($orderId) use (&$expired) {
            DB::transaction(function () use ($orderId, &$expired) {
                $order = Order::whereKey($orderId)->lockForUpdate()->first();
                if (! $order || $order->status !== 'pending_payment' || ! $order->expires_at?->isPast()) {
                    return;
                }
                $this->releaseInventory($order);
                $order->update(['status' => 'expired']);
                $expired++;
            });
        });

        return $expired;
    }

    private function releaseInventory(Order $order): void
    {
        $order->loadMissing('items');
        foreach ($order->items as $item) {
            Food::whereKey($item->food_id)->increment('stock', $item->quantity);
        }
    }
}
