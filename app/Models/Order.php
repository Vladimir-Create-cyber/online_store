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
            'pending' => __('ui.order_status_pending'),
            'processing' => __('ui.order_status_processing'),
            'shipped' => __('ui.order_status_shipped'),
            'completed' => __('ui.order_status_completed'),
            'cancelled' => __('ui.order_status_cancelled'),
            'paid' => __('ui.order_status_paid'),
            default => $this->status,
        };
    }

    /**
     * Возвращает отформатированную сумму заказа.
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 0, '', ' ') . ' ' . __('ui.currency_uah');
    }

    /**
     * Возвращает дату заказа в удобном формате.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('d.m.Y H:i');
    }

    /**
     * Локализованное название способа оплаты.
     */
    public function getPaymentMethodTextAttribute(): string
    {
        return match ($this->payment_method) {
            'card', 'Картой онлайн', 'Карткою онлайн', 'Online card' => __('ui.pay_online_card'),
            'cash_on_delivery', 'Наложенный платёж', 'Післяплата', 'Cash on delivery' => __('ui.cash_on_delivery'),
            default => (string) $this->payment_method,
        };
    }

    /**
     * Локализованное название способа доставки (если есть запись в БД).
     */
    public function getShippingMethodTextAttribute(): string
    {
        if (! filled($this->shipping_method)) {
            return __('ui.not_specified');
        }

        $method = ShippingMethod::query()
            ->where('name', $this->shipping_method)
            ->orWhere('name_uk', $this->shipping_method)
            ->orWhere('name_en', $this->shipping_method)
            ->first();

        return $method?->name ?? (string) $this->shipping_method;
    }

    public function billingAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::class)->where('type', 'billing');
    }

}
