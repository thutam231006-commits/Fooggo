<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\Order;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WebReviewController extends Controller
{
    public function store(Request $request, Order $order, Food $food, ReviewService $reviews): RedirectResponse
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'content' => ['nullable', 'string', 'max:1000'],
        ]);
        $reviews->create($request->user(), $order, $food, $data);

        return redirect()->route('web.orders.show', $order)->with('status', 'Cảm ơn bạn đã đánh giá món ăn.');
    }
}
