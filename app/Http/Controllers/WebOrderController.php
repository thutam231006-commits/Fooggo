<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class WebOrderController extends Controller
{
    public function store(Request $request, OrderWorkflowService $workflow): RedirectResponse
    {
        $data = $request->validate([
            'pickup_slot' => ['required', 'date_format:Y-m-d\TH:i', 'after:now'],
        ]);
        $pickupSlot = Carbon::createFromFormat('Y-m-d\TH:i', $data['pickup_slot'])->format('Y-m-d H:i');
        $order = $workflow->create($request->user(), $pickupSlot);

        return redirect()->route('web.orders.show', $order)->with('status', 'Đơn hàng đã được tạo. Vui lòng thanh toán.');
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load('items.food', 'payment', 'reviews');

        return view('orders.show', [
            'order' => $order,
            'reviewsByFood' => $order->reviews->keyBy('food_id'),
        ]);
    }

    public function pay(Request $request, Order $order, OrderWorkflowService $workflow): RedirectResponse
    {
        $workflow->pay($request->user(), $order);

        $route = $request->input('redirect_to') === 'dashboard' ? route('customer.dashboard') : route('web.orders.show', $order);

        return redirect($route)->with('status', 'Thanh toán thành công. Đơn hàng đang chờ căn tin xác nhận.');
    }

    public function cancel(Request $request, Order $order, OrderWorkflowService $workflow): RedirectResponse
    {
        $workflow->cancel($request->user(), $order);

        $route = $request->input('redirect_to') === 'dashboard' ? route('customer.dashboard') : route('web.orders.show', $order);

        return redirect($route)->with('status', 'Đã hủy đơn hàng và hoàn lại tồn kho.');
    }
}
