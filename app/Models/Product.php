<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'image',
        'rating',
        'is_active',
        'sale_price',
        'sku'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock' => 'integer',
        'rating' => 'float',
        'is_active' => 'boolean'
    ];

    protected $appends = ['image_url', 'final_price'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Полный URL изображения
    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? Storage::url($this->image)
            : asset('images/default-product.png');
    }

    // Цена со скидкой или обычная
    public function getFinalPriceAttribute(): float
    {
        return $this->sale_price ?: $this->price;
    }

    // Уменьшение остатков
    public function decreaseStock(int $quantity): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        if ($this->stock < $quantity) {
            throw new \Exception("Недостаточно товара '{$this->name}' на складе. Доступно: {$this->stock}");
        }

        $this->stock -= $quantity;
        return $this->save();
    }

    // Увеличение остатков
    public function increaseStock(int $quantity): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        $this->stock += $quantity;
        return $this->save();
    }

    // Проверка доступности
    public function isAvailable(): bool
    {
        return $this->is_active && $this->stock > 0;
    }

    // Средний рейтинг
    public function updateRating(): void
    {
        $this->rating = $this->reviews()->avg('rating') ?: 0;
        $this->save();
    }

    // Активные продукты
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // В наличии
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    // Поиск по слагу
    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }
}
