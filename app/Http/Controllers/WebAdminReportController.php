<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WebAdminReportController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $from = isset($data['from']) ? Carbon::parse($data['from'])->startOfDay() : now()->startOfMonth();
        $to = isset($data['to']) ? Carbon::parse($data['to'])->endOfDay() : now()->endOfDay();

        $orders = Order::query()
            ->where('status', 'completed')
            ->whereBetween('ordered_at', [$from, $to]);
        $bestSellers = OrderItem::query()
            ->select('food_id', DB::raw('SUM(quantity) as quantity'), DB::raw('SUM(subtotal) as revenue'))
            ->whereHas('order', fn ($query) => $query->where('status', 'completed')->whereBetween('ordered_at', [$from, $to]))
            ->with('food:id,name,category')
            ->groupBy('food_id')
            ->orderByDesc('quantity')
            ->limit(10)
            ->get();

        return view('admin.reports.index', [
            'from' => $from,
            'to' => $to,
            'orderCount' => (clone $orders)->count(),
            'revenue' => (clone $orders)->sum('total'),
            'averageOrder' => (clone $orders)->avg('total') ?? 0,
            'bestSellers' => $bestSellers,
        ]);
    }
}
