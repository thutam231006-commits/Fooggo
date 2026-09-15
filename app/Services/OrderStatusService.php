<?php

namespace App\Services;

use App\Models\Food;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderStatusService
{
    public function transition(User $actor, Order $order, string $status, ?string $rejectionReason = null): Order
    {
        abort_unless(in_array($actor->role, ['staff', 'admin'], true), 403);

        return DB::transaction(function () use ($actor, $order, $rejectionReason, $status) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $transitions = [
                'paid' => ['preparing', 'rejected'],
                'preparing' => ['ready'],
                'ready' => ['completed'],
            ];

            if (! in_array($status, $transitions[$lockedOrder->status] ?? [], true)) {
                throw ValidationException::withMessages(['status' => 'Chuyển trạng thái đơn hàng không hợp lệ.']);
            }

            if ($status === 'rejected') {
                if (blank($rejectionReason)) {
                    throw ValidationException::withMessages(['rejection_reason' => 'Vui lòng nhập lý do từ chối đơn hàng.']);
                }

                $lockedOrder->load('items');
                foreach ($lockedOrder->items as $item) {
                    Food::whereKey($item->food_id)->increment('stock', $item->quantity);
                }

                if ($lockedOrder->payment_method === 'cash_on_delivery') {
                    $lockedOrder->payment()->update(['status' => 'cancelled']);
                } else {
                    $payment = $lockedOrder->payment()->lockForUpdate()->first();
                    if (! $payment || $payment->status !== 'successful' || (string) $payment->amount !== (string) $lockedOrder->total) {
                        throw ValidationException::withMessages(['status' => 'Giao dịch không hợp lệ để hoàn tiền.']);
                    }

                    User::whereKey($lockedOrder->user_id)
                        ->lockForUpdate()
                        ->firstOrFail()
                        ->increment('wallet_balance', $payment->amount);

                    $updated = $lockedOrder->payment()->update([
                        'status' => 'refunded',
                        'refunded_at' => now(),
                    ]);

                    if ($updated !== 1) {
                        throw ValidationException::withMessages(['status' => 'Không tìm thấy giao dịch để hoàn tiền.']);
                    }
                }
            }

            if ($status === 'completed' && $lockedOrder->payment_method === 'cash_on_delivery') {
                $lockedOrder->payment()->update(['status' => 'successful', 'paid_at' => now()]);
            }

            $lockedOrder->update([
                'status' => $status,
                'rejection_reason' => $status === 'rejected' ? trim($rejectionReason) : null,
                'processed_by' => $actor->id,
                'prepared_at' => $status === 'preparing' ? now() : $lockedOrder->prepared_at,
                'ready_at' => $status === 'ready' ? now() : $lockedOrder->ready_at,
                'completed_at' => $status === 'completed' ? now() : $lockedOrder->completed_at,
            ]);

            return $lockedOrder->fresh()->load('items.food', 'payment', 'processedBy:id,name');
        });
    }
}
