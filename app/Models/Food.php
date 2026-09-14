<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    use HasFactory;

    protected $table = 'foods';

    protected $fillable = ['name', 'category', 'description', 'price', 'stock', 'is_available', 'image_url'];

    protected $casts = ['price' => 'decimal:2', 'is_available' => 'boolean'];

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function displayImageUrl(): ?string
    {
        return $this->image_url;
    }

    public function displayEmoji(): string
    {
        return match ($this->category) {
            'Bún - Phở' => '🍜',
            'Mì' => '🍝',
            'Bánh mì' => '🥖',
            'Ăn vặt' => '🥟',
            'Đồ uống' => '🥤',
            'Món sáng' => '🍳',
            default => '🍚',
        };
    }
}
