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
        'name_uk',
        'name_en',
        'slug',
        'description',
        'description_uk',
        'description_en',
        'price',
        'stock',
        'image',
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
     * Все отзывы.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Только одобренные отзывы.
     */
    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    /**
     * Средний рейтинг.
     */
    public function averageRating(): float
    {
        return round($this->approvedReviews()->avg('rating') ?: 0, 1);
    }

    /**
     * Кол-во одобренных отзывов.
     */
    public function reviewsCount(): int
    {
        return $this->approvedReviews()->count();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
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
        if ($this->relationLoaded('mainImage') && $this->mainImage) {
            return Storage::url($this->mainImage->path);
        }

        if ($this->image) {
            return Storage::url($this->image);
        }

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
     * Локализованное название товара с fallback на базовое поле.
     */
    public function getNameAttribute(?string $value): ?string
    {
        return $this->resolveLocalizedAttribute('name', $value);
    }

    /**
     * Локализованное описание товара с fallback на базовое поле.
     */
    public function getDescriptionAttribute(?string $value): ?string
    {
        return $this->resolveLocalizedAttribute('description', $value);
    }

    /**
     * Определяет значение поля по текущей локали.
     */
    private function resolveLocalizedAttribute(string $baseKey, ?string $fallback): ?string
    {
        $locale = app()->getLocale();

        $localizedKey = match ($locale) {
            'uk' => $baseKey . '_uk',
            'en' => $baseKey . '_en',
            default => null,
        };

        if ($localizedKey) {
            $localizedValue = $this->attributes[$localizedKey] ?? null;
            if (is_string($localizedValue) && trim($localizedValue) !== '') {
                return $localizedValue;
            }
        }

        return $fallback;
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
