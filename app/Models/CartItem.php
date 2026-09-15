<?php

namespace App\Models;

use App\Services\FoodCustomizationService;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'food_id', 'quantity', 'options', 'unit_price', 'option_signature'];

    protected $casts = ['options' => 'array', 'unit_price' => 'decimal:2'];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function food()
    {
        return $this->belongsTo(Food::class);
    }

    public function getEffectiveUnitPriceAttribute(): float
    {
        return (float) ($this->unit_price ?? $this->food->price);
    }

    public function optionLabels(): array
    {
        return app(FoodCustomizationService::class)->labels($this->options ?? []);
    }
}
