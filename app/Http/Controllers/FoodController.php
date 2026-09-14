<?php

namespace App\Http\Controllers;

use App\Models\Food;
use Illuminate\Http\Request;

class FoodController extends Controller
{
    public function index(Request $r)
    {
        $q = Food::query()->where('is_available', true);
        $q->when($r->filled('category'), fn ($query) => $query->where('category', $r->string('category')));
        $q->when($r->filled('search'), fn ($query) => $query->where(fn ($foodQuery) => $foodQuery
            ->where('name', 'like', '%'.$r->string('search').'%')
            ->orWhere('description', 'like', '%'.$r->string('search').'%')));

        return $q->orderBy('category')->orderBy('name')->paginate(min($r->integer('per_page', 15), 50));
    }

    public function store(Request $r)
    {
        $d = $r->validate(['name' => 'required|string|max:150', 'category' => 'nullable|string|max:80', 'description' => 'nullable|string', 'price' => 'required|numeric|min:0', 'stock' => 'required|integer|min:0', 'is_available' => 'boolean', 'image_url' => 'nullable|url']);

        return response()->json(Food::create($d), 201);
    }

    public function show(Food $food)
    {
        return $food->load('reviews.user');
    }

    public function update(Request $r, Food $food)
    {
        $d = $r->validate(['name' => 'sometimes|string|max:150', 'category' => 'nullable|string|max:80', 'description' => 'nullable|string', 'price' => 'sometimes|numeric|min:0', 'stock' => 'sometimes|integer|min:0', 'is_available' => 'sometimes|boolean', 'image_url' => 'nullable|url']);
        $food->update($d);

        return $food;
    }

    public function destroy(Food $food)
    {
        $food->update(['is_available' => false]);

        return response()->json(['message' => 'Đã ngừng bán món ăn.']);
    }
}
