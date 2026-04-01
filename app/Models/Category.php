<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = ['name', 'slug', 'description'];

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
}
