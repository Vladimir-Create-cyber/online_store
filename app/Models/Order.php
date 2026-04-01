<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'phone',
        'address',
        'city',
        'country',
        'postal_code',
        'total',
        'status',
        'shipping_cost',
        'subtotal',
        'payment_method',
        'shipping_method',
    ];

    protected $casts = [
        'total' => 'float',
        'subtotal' => 'float',
        'shipping_cost' => 'float',
    ];

    /**
     * Возвращает пользователя, которому принадлежит заказ.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Возвращает позиции заказа.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Возвращает адрес доставки.
     */
    public function shippingAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::class)
            ->where('type', 'shipping');
    }

    /**
     * Возвращает локализованное название статуса заказа.
     */
    public function getStatusTextAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Ожидание',
            'processing' => 'В обработке',
            'completed' => 'Завершен',
            'cancelled' => 'Отменен',
            default => $this->status,
        };
    }

    /**
     * Возвращает отформатированную сумму заказа.
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 0, '', ' ') . ' ₽';
    }

    /**
     * Возвращает дату заказа в удобном формате.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('d.m.Y H:i');
    }

    public function billingAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::class)->where('type', 'billing');
    }

}
