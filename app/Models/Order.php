<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 'order_number','subtotal', 'tax', 'shipping_fee', 'total', 'payment_method', 'payment_status', 'order_status', 'notes'

    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function customer(){
        return $this->belongsTo(User::class, 'customer_id');

    }

    public function items(){
        return $this->hasMany(OrderItem::class);
    }

    public static function generateOrderNumber(){
        return 'ORD-' . strtoupper(uniqid());
    }
}

