<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Модель изображения товара.
 *
 * @property int $id
 * @property int $product_id
 * @property string $path
 * @property bool $is_main
 * @property string $url
 *
 * @property-read Product $product
 */
class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'path',
        'is_main',
    ];

    protected $casts = [
        'is_main' => 'boolean',
    ];

    protected $appends = [
        'url',
    ];

    /**
     * Связь с товаром.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Публичный URL изображения.
     */
    public function getUrlAttribute(): string
    {
        return $this->path && Storage::disk('public')->exists($this->path)
            ? Storage::url($this->path)
            : asset('images/placeholder.png');
    }

    /**
     * Удаление изображения с диска при удалении записи.
     */
    protected static function booted(): void
    {
        static::deleting(function (ProductImage $image) {
            if ($image->path && Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
        });
    }
}
