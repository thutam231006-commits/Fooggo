<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'food_id', 'quantity', 'unit_price', 'subtotal'];

    protected $casts = ['unit_price' => 'decimal:2', 'subtotal' => 'decimal:2'];

    public function food()
    {
        return $this->belongsTo(Food::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
