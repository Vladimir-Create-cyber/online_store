<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['user_id', 'product_id', 'quantity'];

    /**
     * Возвращает товар в позиции корзины.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Возвращает пользователя, которому принадлежит позиция корзины.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
