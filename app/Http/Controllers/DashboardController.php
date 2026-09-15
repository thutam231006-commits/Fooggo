<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route($request->user()->role === 'customer' ? 'home' : $request->user()->role.'.dashboard');
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

    public function staff(Request $request): View
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:pending_payment,paid,preparing,ready'],
        ]);

        $activeOrders = Order::query()
            ->with('items.food', 'user:id,name,email')
            ->whereIn('status', ['pending_payment', 'paid', 'preparing', 'ready'])
            ->get();

        $orders = Order::query()
            ->with('items.food', 'user:id,name,email')
            ->whereIn('status', ['pending_payment', 'paid', 'preparing', 'ready'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%'.$request->string('search')->trim().'%';
                $query->where(function ($query) use ($term) {
                    $query->where('pickup_code', 'like', $term)
                        ->orWhereHas('user', fn ($query) => $query->where('name', 'like', $term)->orWhere('email', 'like', $term))
                        ->orWhereHas('items.food', fn ($query) => $query->where('name', 'like', $term));
                });
            })
            ->orderBy('pickup_slot')
            ->get();

        $completedToday = Order::query()
            ->with('items.food', 'user:id,name,email')
            ->where('status', 'completed')
            ->whereDate('ordered_at', today())
            ->latest('completed_at')
            ->get();
        $preparationDurations = $completedToday
            ->filter(fn ($order) => $order->prepared_at && $order->ready_at)
            ->map(fn ($order) => $order->prepared_at->diffInMinutes($order->ready_at));

        return view('dashboards.staff', [
            'orders' => $orders,
            'pendingPaymentCount' => $activeOrders->where('status', 'pending_payment')->count(),
            'waitingCount' => $activeOrders->where('status', 'paid')->count(),
            'preparingCount' => $activeOrders->where('status', 'preparing')->count(),
            'readyCount' => $activeOrders->where('status', 'ready')->count(),
            'activeOrderCount' => $activeOrders->count(),
            'completedTodayCount' => $completedToday->count(),
            'completedTodayPortions' => $completedToday->sum(fn ($order) => $order->items->sum('quantity')),
            'averagePreparationMinutes' => $preparationDurations->isNotEmpty() ? round($preparationDurations->avg()) : null,
            'recentCompletedOrders' => $completedToday->take(5),
            'lowStockFoods' => Food::where('is_available', true)->where('stock', '<=', 10)->orderBy('stock')->limit(5)->get(),
        ]);
    }

    public function admin(): View
    {
        $activeOrders = Order::whereIn('status', ['paid', 'preparing', 'ready'])->count();
        $bestSellers = OrderItem::query()
            ->selectRaw('food_id, SUM(quantity) as quantity')
            ->with('food:id,name,category,price,image_url')
            ->groupBy('food_id')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get();

        return view('dashboards.admin', [
            'orders' => Order::with('user:id,name,email')->latest()->limit(20)->get(),
            'userCount' => User::count(),
            'customerCount' => User::where('role', 'customer')->count(),
            'staffCount' => User::where('role', 'staff')->count(),
            'foodCount' => Food::count(),
            'unavailableFoodCount' => Food::where('is_available', false)->orWhere('stock', 0)->count(),
            'revenue' => Order::where('status', 'completed')->sum('total'),
            'completedOrderCount' => Order::where('status', 'completed')->count(),
            'activeOrders' => $activeOrders,
            'lowStockFoods' => Food::where('is_available', true)->where('stock', '<=', 10)->orderBy('stock')->limit(5)->get(),
            'bestSellers' => $bestSellers,
        ]);
    }
}
