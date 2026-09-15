<?php

namespace App\Http\Controllers;

use App\Models\Food;
use Illuminate\Http\Request;

class WebController extends Controller
{
    public function home(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:80'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $foods = Food::query()
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('is_available', true)
            ->where('stock', '>', 0)
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request) {
                $term = '%'.$request->string('search')->trim().'%';
                $query->where('name', 'like', $term)->orWhere('description', 'like', $term);
            }))
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')))
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(8)
            ->withQueryString();

        $categories = Food::where('is_available', true)->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');

        $cart = auth()->check() && auth()->user()->role === 'customer'
            ? auth()->user()->cart()->with('items.food')->firstOrCreate()
            : null;

        $queueStats = Food::query()
            ->where('is_available', true)
            ->where('stock', '>', 0)
            ->selectRaw('category, COUNT(*) as food_count, SUM(stock) as portions')
            ->groupBy('category')
            ->orderByDesc('food_count')
            ->limit(5)
            ->get();
        $availableFoodCount = Food::where('is_available', true)->where('stock', '>', 0)->count();

        return view('foods.index', compact('foods', 'categories', 'cart', 'queueStats', 'availableFoodCount'));
    }

    public function show(Food $food)
    {
        abort_unless($food->is_available, 404);

        $food->load(['reviews' => fn ($query) => $query->with('user:id,name')->latest()->limit(20)]);

        return view('foods.show', compact('food'));
    }
}
