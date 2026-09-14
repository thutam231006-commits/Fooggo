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

                User::whereKey($lockedOrder->user_id)
                    ->lockForUpdate()
                    ->firstOrFail()
                    ->increment('wallet_balance', $lockedOrder->total);

                $updated = $lockedOrder->payment()->update([
                    'status' => 'refunded',
                    'refunded_at' => now(),
                ]);

                if ($updated !== 1) {
                    throw ValidationException::withMessages(['status' => 'Không tìm thấy giao dịch để hoàn tiền.']);
                }
            }

            $lockedOrder->update([
                'status' => $status,
                'rejection_reason' => $status === 'rejected' ? trim($rejectionReason) : null,
                'processed_by' => $actor->id,
            ]);

            return $lockedOrder->fresh()->load('items.food', 'payment', 'processedBy:id,name');
        });
    }
}
