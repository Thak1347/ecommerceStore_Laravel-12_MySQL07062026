<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;


class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'description', 'image', 'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    public function products(){
        return $this->hasMany(Product::class);
    }

    public function getProductsCountAttribute()
    {
        return $this->products()->count();
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
