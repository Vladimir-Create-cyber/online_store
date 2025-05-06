<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Ноутбук Lenovo', 'price' => 45000, 'description' => 'Мощный ноутбук с SSD 512GB', 'image' => 'laptop.jpg'],
            ['name' => 'Смартфон Samsung', 'price' => 30000, 'description' => 'Камера 108MP, экран AMOLED', 'image' => 'smartphone.jpg'],
            ['name' => 'Беспроводные наушники', 'price' => 7000, 'description' => 'Чистый звук, шумоподавление', 'image' => 'headphones.jpg'],
            ['name' => 'Игровая мышь Logitech', 'price' => 3500, 'description' => 'RGB-подсветка, быстрый отклик', 'image' => 'mouse.jpg'],
        ];

        foreach ($products as $data) {
            Product::create([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'price' => $data['price'],
                'description' => $data['description'],
                'image' => $data['image'],
                'category_id' => 1, // Нужно убедиться, что есть категория с id = 1
                'stock' => rand(5, 50),
            ]);
        }
    }
}
