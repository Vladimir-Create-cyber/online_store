<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['user_id', 'product_id', 'quantity'];

    // Связь с таблицей Products
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Связь с таблицей Users
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
