<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
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
        'image', // для одиночного "старого" изображения
        'rating',
        'is_active',
        'sale_price',
        'sku',
        'is_new',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock' => 'integer',
        'rating' => 'float',
        'is_active' => 'boolean',
        'is_new' => 'boolean',
    ];

    protected $appends = [
        'image_url',
        'final_price',
    ];

    /**
     * Категория товара.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Отзывы на товар.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Лайки.
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Позиции заказов.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Все изображения товара (галерея).
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Главное изображение (is_main = true).
     */
    public function mainImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_main', true);
    }

    /**
     * URL изображения — главное, или legacy, или fallback.
     */
    public function getImageUrlAttribute(): string
    {
        // если есть загруженное отношение mainImage
        if ($this->relationLoaded('mainImage') && $this->mainImage) {
            return Storage::url($this->mainImage->path);
        }

        // если есть просто поле image (старое)
        if ($this->image) {
            return Storage::url($this->image);
        }

        // fallback изображение
        return asset('images/default-product.png');
    }

    /**
     * Цена со скидкой (если указана), иначе обычная.
     */
    public function getFinalPriceAttribute(): float
    {
        return $this->sale_price ?: $this->price;
    }

    /**
     * Уменьшить остаток товара.
     */
    public function decreaseStock(int $quantity): bool
    {
        if ($quantity <= 0 || $this->stock < $quantity) {
            throw new \Exception("Недостаточно товара '{$this->name}' на складе.");
        }

        $this->stock -= $quantity;
        return $this->save();
    }

    /**
     * Увеличить остаток товара.
     */
    public function increaseStock(int $quantity): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        $this->stock += $quantity;
        return $this->save();
    }

    /**
     * Проверить, доступен ли товар для покупки.
     */
    public function isAvailable(): bool
    {
        return $this->is_active && $this->stock > 0;
    }

    /**
     * Обновить рейтинг товара на основе отзывов.
     */
    public function updateRating(): void
    {
        $this->rating = $this->reviews()->avg('rating') ?: 0;
        $this->save();
    }

    /**
     * Скоуп: активные товары.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Скоуп: товары в наличии.
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Скоуп: найти товар по slug.
     */
    public function scopeBySlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', $slug);
    }
}
