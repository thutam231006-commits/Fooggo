<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route($request->user()->role.'.dashboard');
    }

    public function customer(Request $request): View
    {
        $orders = Order::query()
            ->with('items.food', 'user:id,name,email')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->limit(20)
            ->get();

        return view('dashboards.customer', [
            'orders' => $orders,
            'foodCount' => Food::where('is_available', true)->count(),
            'cartQuantity' => $request->user()->cart?->items()->sum('quantity') ?? 0,
        ]);
    }

    public function staff(): View
    {
        $orders = Order::query()
            ->with('items.food', 'user:id,name,email')
            ->whereIn('status', ['pending_payment', 'paid', 'preparing', 'ready'])
            ->orderBy('pickup_slot')
            ->get();

        return view('dashboards.staff', [
            'orders' => $orders,
            'pendingPaymentCount' => $orders->where('status', 'pending_payment')->count(),
            'waitingCount' => $orders->where('status', 'paid')->count(),
            'preparingCount' => $orders->where('status', 'preparing')->count(),
            'readyCount' => $orders->where('status', 'ready')->count(),
        ]);
    }

    public function admin(): View
    {
        return view('dashboards.admin', [
            'orders' => Order::with('user:id,name,email')->latest()->limit(20)->get(),
            'userCount' => User::count(),
            'customerCount' => User::where('role', 'customer')->count(),
            'staffCount' => User::where('role', 'staff')->count(),
            'foodCount' => Food::count(),
            'unavailableFoodCount' => Food::where('is_available', false)->orWhere('stock', 0)->count(),
            'revenue' => Order::where('status', 'completed')->sum('total'),
            'completedOrderCount' => Order::where('status', 'completed')->count(),
        ]);
    }
}
