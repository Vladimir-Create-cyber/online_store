<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    // Явное указание имени таблицы (опционально)
    protected $table = 'categories';

    // Поля, разрешённые к массовому заполнению
    protected $fillable = ['name', 'slug', 'description'];

    // Если будут булевые или json поля — указываем здесь
    // protected $casts = [
    //     'is_active' => 'boolean',
    //     'options' => 'array',
    // ];

    /**
     * Связь с товарами: одна категория имеет много товаров
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Автоматическая генерация slug при установке имени
     * (можно использовать в контроллере вместо этого)
     */
     public function setNameAttribute($value)
     {
         $this->attributes['name'] = $value;
         $this->attributes['slug'] = \Str::slug($value);
     }
}
