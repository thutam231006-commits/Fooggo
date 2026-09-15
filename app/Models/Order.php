<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'processed_by', 'ordered_at', 'pickup_slot', 'pickup_code', 'fulfillment_type', 'payment_method', 'service_fee', 'total', 'status', 'rejection_reason', 'expires_at', 'prepared_at', 'ready_at', 'completed_at'];

    protected $casts = ['ordered_at' => 'datetime', 'expires_at' => 'datetime', 'prepared_at' => 'datetime', 'ready_at' => 'datetime', 'completed_at' => 'datetime', 'service_fee' => 'decimal:2', 'total' => 'decimal:2'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
