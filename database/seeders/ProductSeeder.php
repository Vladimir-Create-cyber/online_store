<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Проверяем, существует ли категория с id = 1
        if (!Category::find(1)) {
            Category::create(['id' => 1, 'name' => 'Электроника', 'slug' => 'electronics']);
        }

        $products = [
            ['name' => 'Ноутбук Lenovo', 'price' => 45000, 'description' => 'Мощный ноутбук с SSD 512GB', 'image' => 'laptop.jpg'],
            ['name' => 'Смартфон Samsung', 'price' => 30000, 'description' => 'Камера 108MP, экран AMOLED', 'image' => 'smartphone.jpg'],
            ['name' => 'Беспроводные наушники', 'price' => 7000, 'description' => 'Чистый звук, шумоподавление', 'image' => 'headphones.jpg'],
            ['name' => 'Игровая мышь Logitech', 'price' => 3500, 'description' => 'RGB-подсветка, быстрый отклик', 'image' => 'mouse.jpg'],
            ['name' => 'Монитор 27" LG', 'price' => 12000, 'description' => 'IPS-экран, 144Hz', 'image' => 'monitor.jpg'],
            ['name' => 'Механическая клавиатура Razer', 'price' => 8500, 'description' => 'Оптические переключатели, RGB', 'image' => 'keyboard.jpg'],
            ['name' => 'Гарнитура HyperX', 'price' => 6500, 'description' => 'Комфортная посадка, мощный звук', 'image' => 'hyperx.jpg'],
            ['name' => 'Планшет Xiaomi Pad', 'price' => 25000, 'description' => '10.5" экран, 8GB RAM', 'image' => 'tablet.jpg'],
            ['name' => 'Внешний SSD Samsung 1TB', 'price' => 13000, 'description' => 'Скорость до 1050MB/s', 'image' => 'ssd.jpg'],
            ['name' => 'Геймпад Xbox Series X', 'price' => 5000, 'description' => 'Тактильная отдача, удобный захват', 'image' => 'gamepad.jpg'],
            ['name' => 'Фитнес-трекер Garmin', 'price' => 12000, 'description' => 'Мониторинг здоровья, GPS', 'image' => 'tracker.jpg'],
            ['name' => 'Умные часы Apple Watch', 'price' => 25000, 'description' => 'OLED-дисплей, мониторинг сна', 'image' => 'apple_watch.jpg'],
            ['name' => 'Электронная книга Kindle Paperwhite', 'price' => 9500, 'description' => 'E-Ink экран, подсветка', 'image' => 'kindle.jpg'],
            ['name' => 'Компьютерное кресло DXRacer', 'price' => 18000, 'description' => 'Эргономичный дизайн, поддержка спины', 'image' => 'dxracer.jpg'],
            ['name' => 'Портативная колонка JBL Charge', 'price' => 10000, 'description' => 'Мощный бас, влагозащита', 'image' => 'jbl.jpg'],
        ];

        foreach ($products as $data) {
            $slug = Str::slug($data['name']);
            $count = Product::where('slug', 'LIKE', "{$slug}%")->count();

            if ($count > 0) {
                $slug .= '-' . ($count + 1);
            }

            Product::create([
                'name' => $data['name'],
                'slug' => $slug, // Используем уже уникальный `slug`
                'price' => $data['price'],
                'description' => $data['description'],
                'image' => 'products/' . $data['image'],
                'category_id' => 1,
                'stock' => rand(5, 50),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
