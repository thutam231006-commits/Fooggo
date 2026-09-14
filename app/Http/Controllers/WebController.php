<?php

namespace App\Http\Controllers;

use App\Models\Food;
use Illuminate\Http\Request;

class WebController extends Controller
{
    public function home(Request $request)
    {
        $foods = Food::query()
            ->where('is_available', true)
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Food::where('is_available', true)->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');

        return view('foods.index', compact('foods', 'categories'));
    }

    public function show(Food $food)
    {
        abort_unless($food->is_available, 404);

        $food->load(['reviews' => fn ($query) => $query->with('user:id,name')->latest()->limit(20)]);

        return view('foods.show', compact('food'));
    }
}
