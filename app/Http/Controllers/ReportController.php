<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function revenue(Request $r)
    {
        $dates = $this->dates($r);
        $from = $dates['from'];
        $to = $dates['to'];
        $q = Order::where('status', 'completed')->whereBetween('ordered_at', [$from, $to]);

        return ['from' => $from, 'to' => $to, 'orders' => $q->count(), 'revenue' => (float) $q->sum('total')];
    }

    public function bestSellers(Request $r)
    {
        $dates = $this->dates($r);
        $limit = min(max($r->integer('limit', 10), 1), 100);

        return OrderItem::select('food_id', DB::raw('SUM(quantity) as quantity'), DB::raw('SUM(subtotal) as revenue'))
            ->whereHas('order', fn ($query) => $query
                ->where('status', 'completed')
                ->whereBetween('ordered_at', [$dates['from'], $dates['to']]))
            ->with('food:id,name')
            ->groupBy('food_id')
            ->orderByDesc('quantity')
            ->limit($limit)
            ->get();
    }

    private function dates(Request $request): array
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        return [
            'from' => isset($data['from']) ? Carbon::parse($data['from'])->startOfDay() : now()->startOfMonth(),
            'to' => isset($data['to']) ? Carbon::parse($data['to'])->endOfDay() : now()->endOfDay(),
        ];
    }
}
