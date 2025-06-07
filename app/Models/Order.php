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
        'total',
        'status',
        'shipping_address',
        'shipping_cost',
        'subtotal',
        'payment_method',
        'shipping_method'
    ];

    protected $casts = [
        'total' => 'float',
        'subtotal' => 'float',
        'shipping_cost' => 'float',
    ];

    // Отношение к пользователю
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Элементы заказа (переименовано из items в orderItems)
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Адрес доставки (если используется отдельная модель)
    public function shippingAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::class)
            ->where('type', 'shipping');
    }

    // Статус заказа с преобразованием
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

    // Форматированная сумма заказа
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 0, '', ' ') . ' ₽';
    }

    // Дата заказа в удобном формате
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('d.m.Y H:i');
    }
}
