<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    protected $fillable = ['name', 'name_uk', 'name_en', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Локализованное имя способа доставки с fallback на базовое поле.
     */
    public function getNameAttribute(?string $value): ?string
    {
        $locale = app()->getLocale();

        $localizedKey = match ($locale) {
            'uk' => 'name_uk',
            'en' => 'name_en',
            default => null,
        };

        if ($localizedKey) {
            $localizedValue = $this->attributes[$localizedKey] ?? null;
            if (is_string($localizedValue) && trim($localizedValue) !== '') {
                return $localizedValue;
            }
        }

        return $value;
    }
}
