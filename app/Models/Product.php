<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'category_id', 'sku', 'name', 'slug', 'description', 'image', 'price', 'cost_price', 'stock_qty', 'active'
    ];

    protected $casts = [
        'active' => 'boolean',
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2'
    ];

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function orderItems(){
        return $this->hasMany(OrderItem::class);
    }

    public function getImageUrlAttribute(){
        return $this->image ? asset('storage/' .$this->image): null;
    }

    public function decreaseStock($quantity){
        $this->stock_qty -= $quantity;
        $this->save();

    }
    public function increaseStock($quantity){
        $this->stock_qty += $quantity;
        $this->save();
    }
}
