<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderStatusService;
use App\Services\OrderWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('items.food', 'payment', 'user:id,name,email');
        if ($request->user()->role === 'customer') {
            $query->where('user_id', $request->user()->id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return $query->latest()->paginate(15);
    }

    public function store(Request $request, OrderWorkflowService $workflow)
    {
        $data = $request->validate([
            'pickup_slot' => ['required', 'date_format:Y-m-d H:i', 'after:now'],
            'items' => ['nullable', 'array', 'min:1'],
            'items.*.food_id' => ['required', 'integer', 'distinct', 'exists:foods,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);
        $order = $workflow->create($request->user(), $data['pickup_slot'], $data['items'] ?? null);

        return response()->json($order->load('items.food'), 201);
    }

    public function show(Order $order)
    {
        $this->authorizeOrder($order);

        return $order->load('items.food', 'payment', 'user:id,name,email');
    }

    public function update(Request $request, Order $order)
    {
        $this->authorizeOrder($order);
        $data = $request->validate(['pickup_slot' => ['required', 'date_format:Y-m-d H:i', 'after:now']]);
        DB::transaction(function () use ($order, $data) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedOrder->status === 'pending_payment', 422, 'Chỉ được sửa đơn đang chờ thanh toán.');
            $lockedOrder->update($data);
        });

        return $order->fresh();
    }

    public function destroy(Request $request, Order $order, OrderWorkflowService $workflow)
    {
        $this->authorizeOrder($order);
        $workflow->cancel($request->user(), $order);

        return response()->json(['message' => 'Đã hủy đơn hàng.']);
    }

    public function status(Request $request, Order $order, OrderStatusService $statuses)
    {
        $data = $request->validate(['status' => ['required', 'in:preparing,ready,completed,rejected'], 'rejection_reason' => ['required_if:status,rejected', 'nullable', 'string', 'max:1000']]);
        $order = $statuses->transition($request->user(), $order, $data['status'], $data['rejection_reason'] ?? null);

        return $order;
    }

    private function authorizeOrder(Order $order): void
    {
        abort_if(auth()->user()->role === 'customer' && auth()->id() !== $order->user_id, 403, 'Bạn không có quyền xem đơn này.');
    }
}
