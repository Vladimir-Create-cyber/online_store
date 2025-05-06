<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create(['name' => 'Ноутбуки', 'slug' => 'noutbuki', 'description' => 'Опис категорії ноутбуків']);
        Category::create(['name' => 'Смартфони', 'slug' => 'smartfoni', 'description' => 'Опис категорії смартфонів']);
        Category::create(['name' => 'Аксесуари', 'slug' => 'aksesuari', 'description' => 'Опис категорії аксесуарів']);
        // Додайте інші необхідні категорії
    }
}
