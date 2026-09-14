<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WebStaffOrderController extends Controller
{
    public function update(Request $request, Order $order, OrderStatusService $statuses): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:preparing,ready,completed,rejected'],
            'rejection_reason' => ['required_if:status,rejected', 'nullable', 'string', 'max:1000'],
        ]);

        $statuses->transition(
            $request->user(),
            $order,
            $data['status'],
            $data['rejection_reason'] ?? null,
        );

        $messages = [
            'preparing' => 'Đã nhận đơn và chuyển sang trạng thái đang chuẩn bị.',
            'ready' => 'Đơn hàng đã sẵn sàng để khách nhận.',
            'completed' => 'Đã hoàn tất đơn hàng.',
            'rejected' => 'Đã từ chối đơn, lưu lý do và hoàn tiền cho khách hàng.',
        ];

        return redirect()->route('staff.dashboard')->with('status', $messages[$data['status']]);
    }
}
