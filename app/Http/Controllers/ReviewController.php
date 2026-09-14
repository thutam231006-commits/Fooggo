<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\Order;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Food $food)
    {
        return $food->reviews()->with('user:id,name')->latest()->paginate(15);
    }

    public function store(Request $r, Food $food, ReviewService $reviews)
    {
        $data = $r->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'content' => ['nullable', 'string', 'max:1000'],
        ]);
        $order = Order::findOrFail($data['order_id']);
        $review = $reviews->create($r->user(), $order, $food, $data);

        return response()->json($review, 201);
    }
}
