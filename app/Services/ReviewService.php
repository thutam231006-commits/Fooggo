<?php

namespace App\Services;

use App\Models\Food;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    public function create(User $customer, Order $order, Food $food, array $data): Review
    {
        $eligible = $order->user_id === $customer->id
            && $order->status === 'completed'
            && $order->items()->where('food_id', $food->id)->exists();

        if (! $eligible) {
            throw ValidationException::withMessages([
                'review' => 'Chỉ được đánh giá món thuộc đơn hàng đã hoàn tất của bạn.',
            ]);
        }

        $review = Review::firstOrCreate([
            'user_id' => $customer->id,
            'order_id' => $order->id,
            'food_id' => $food->id,
        ], [
            'rating' => $data['rating'],
            'content' => $data['content'] ?? null,
        ]);

        if (! $review->wasRecentlyCreated) {
            throw ValidationException::withMessages(['review' => 'Bạn đã đánh giá món này trong đơn hàng.']);
        }

        return $review;
    }
}
