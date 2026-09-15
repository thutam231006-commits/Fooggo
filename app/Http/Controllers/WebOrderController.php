<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderWorkflowService;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class WebOrderController extends Controller
{
    public function store(Request $request, OrderWorkflowService $workflow): RedirectResponse
    {
        $data = $request->validate([
            'pickup_slot' => ['required', 'date_format:Y-m-d\TH:i', 'after:now'],
            'fulfillment_type' => ['nullable', 'in:dine_in,takeaway'],
            'payment_method' => ['nullable', 'in:foodgo_wallet,cash_on_delivery'],
        ]);
        $pickupSlot = Carbon::createFromFormat('Y-m-d\TH:i', $data['pickup_slot'])->format('Y-m-d H:i');
        $order = $workflow->create(
            $request->user(),
            $pickupSlot,
            fulfillmentType: $data['fulfillment_type'] ?? 'dine_in',
            paymentMethod: $data['payment_method'] ?? 'foodgo_wallet',
        );

        if ($order->payment_method === 'cash_on_delivery') {
            return redirect()->route('web.orders.show', $order)->with('status', 'Don hang da tao. Thanh toan khi nhan mon tai quay.');
        }

        return redirect()->route('web.orders.show', $order)->with('status', 'Đơn hàng đã được tạo. Vui lòng thanh toán.');
    }

    public function show(Request $request, Order $order, OrderWorkflowService $workflow, QrCodeService $qrCodes): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if ($order->status === 'pending_payment' && $order->expires_at?->isPast()) {
            $workflow->expirePendingOrders();
            $order->refresh();
        }

        $order->load('items.food', 'payment', 'reviews');

        $verificationUrl = URL::temporarySignedRoute(
            'pickup.verify',
            Carbon::parse($order->pickup_slot)->addHours(4),
            ['order' => $order],
        );

        return view('orders.show', [
            'order' => $order,
            'reviewsByFood' => $order->reviews->keyBy('food_id'),
            'qrCodeDataUri' => $qrCodes->dataUri($verificationUrl),
            'verificationUrl' => $verificationUrl,
        ]);
    }

    public function verifyPickup(Order $order): View
    {
        return view('orders.verify', ['order' => $order]);
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

        return redirect($route)->with('status', 'Đã hủy đơn hàng, hoàn lại tồn kho và hoàn tiền nếu đã thanh toán.');
    }
}
