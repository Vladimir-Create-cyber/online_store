<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = ['name', 'name_uk', 'name_en', 'slug', 'description', 'description_uk', 'description_en'];

    /**
     * Возвращает товары текущей категории.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Автоматически генерирует slug при изменении имени.
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = \Str::slug($value);
    }

    /**
     * Локализованное название категории с fallback на базовое поле.
     */
    public function getNameAttribute(?string $value): ?string
    {
        return $this->resolveLocalizedAttribute('name', $value);
    }

    /**
     * Локализованное описание категории с fallback на базовое поле.
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
}
