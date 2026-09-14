<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['order_id', 'transaction_code', 'method', 'amount', 'status', 'paid_at', 'refunded_at'];

    protected $casts = ['amount' => 'decimal:2', 'paid_at' => 'datetime', 'refunded_at' => 'datetime'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
