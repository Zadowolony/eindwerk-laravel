<?php

namespace App\Models;

use App\Models\User;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'available_sizes', 'price', 'brand_id', 'description', 'image'
    ];

    protected $casts = [
        'available_sizes' => 'array',
    ];

    public function brand() {
        return $this->belongsTo(Brand::class);
    }

    public function cart() {
        return $this->belongsToMany(User::class, 'shopping_cart')->withPivot('quantity', 'size');
        //Hierdoor kunnen wij nu dit opvragen : $user->cart
        // $product->pivot->quantity en $product->pivot->size
    }

    public function order(){
        return $this->belongsToMany(Order::class);
    }

}
