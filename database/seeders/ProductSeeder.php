<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Имена демо-файлов в storage/app/public/products — при --force перезаписываются уникальными заглушками.
     */
    private static function demoSeedFilenames(): array
    {
        return [
            'laptop.jpg', 'smartphone.jpg', 'headphones.jpg', 'mouse.jpg', 'monitor.jpg',
            'keyboard.jpg', 'hyperx.jpg', 'tablet.jpg', 'ssd.jpg', 'gamepad.jpg',
            'tracker.jpg', 'apple_watch.jpg', 'kindle.jpg', 'dxracer.jpg', 'jbl.jpg',
        ];
    }

    /**
     * Демо-товары с локализацией (базовое поле name/description — RU).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function demoProductDefinitions(): array
    {
        return [
            [
                'name' => 'Ноутбук Lenovo',
                'name_uk' => 'Ноутбук Lenovo',
                'name_en' => 'Lenovo Laptop',
                'description' => 'Мощный ноутбук с SSD 512GB',
                'description_uk' => 'Потужний ноутбук з SSD 512GB',
                'description_en' => 'Powerful laptop with 512GB SSD',
                'price' => 45000,
                'image' => 'laptop.jpg',
            ],
            [
                'name' => 'Смартфон Samsung',
                'name_uk' => 'Смартфон Samsung',
                'name_en' => 'Samsung Smartphone',
                'description' => 'Камера 108MP, экран AMOLED',
                'description_uk' => 'Камера 108MP, екран AMOLED',
                'description_en' => '108MP camera, AMOLED display',
                'price' => 30000,
                'image' => 'smartphone.jpg',
            ],
            [
                'name' => 'Беспроводные наушники',
                'name_uk' => 'Бездротові навушники',
                'name_en' => 'Wireless Headphones',
                'description' => 'Чистый звук, шумоподавление',
                'description_uk' => 'Чистий звук, шумозаглушення',
                'description_en' => 'Clear sound, noise cancellation',
                'price' => 7000,
                'image' => 'headphones.jpg',
            ],
            [
                'name' => 'Игровая мышь Logitech',
                'name_uk' => 'Ігрова миша Logitech',
                'name_en' => 'Logitech Gaming Mouse',
                'description' => 'RGB-подсветка, быстрый отклик',
                'description_uk' => 'RGB-підсвітка, швидкий відгук',
                'description_en' => 'RGB backlight, fast response',
                'price' => 3500,
                'image' => 'mouse.jpg',
            ],
            [
                'name' => 'Монитор 27" LG',
                'name_uk' => 'Монітор 27" LG',
                'name_en' => 'LG 27" Monitor',
                'description' => 'IPS-экран, 144Hz',
                'description_uk' => 'IPS-екран, 144Hz',
                'description_en' => 'IPS display, 144Hz',
                'price' => 12000,
                'image' => 'monitor.jpg',
            ],
            [
                'name' => 'Механическая клавиатура Razer',
                'name_uk' => 'Механічна клавіатура Razer',
                'name_en' => 'Razer Mechanical Keyboard',
                'description' => 'Оптические переключатели, RGB',
                'description_uk' => 'Оптичні перемикачі, RGB',
                'description_en' => 'Optical switches, RGB',
                'price' => 8500,
                'image' => 'keyboard.jpg',
            ],
            [
                'name' => 'Гарнитура HyperX',
                'name_uk' => 'Гарнітура HyperX',
                'name_en' => 'HyperX Headset',
                'description' => 'Комфортная посадка, мощный звук',
                'description_uk' => 'Комфортна посадка, потужний звук',
                'description_en' => 'Comfortable fit, powerful sound',
                'price' => 6500,
                'image' => 'hyperx.jpg',
            ],
            [
                'name' => 'Планшет Xiaomi Pad',
                'name_uk' => 'Планшет Xiaomi Pad',
                'name_en' => 'Xiaomi Pad Tablet',
                'description' => '10.5" экран, 8GB RAM',
                'description_uk' => 'Екран 10.5", 8GB RAM',
                'description_en' => '10.5" display, 8GB RAM',
                'price' => 25000,
                'image' => 'tablet.jpg',
            ],
            [
                'name' => 'Внешний SSD Samsung 1TB',
                'name_uk' => 'Зовнішній SSD Samsung 1TB',
                'name_en' => 'Samsung External SSD 1TB',
                'description' => 'Скорость до 1050MB/s',
                'description_uk' => 'Швидкість до 1050MB/s',
                'description_en' => 'Speed up to 1050MB/s',
                'price' => 13000,
                'image' => 'ssd.jpg',
            ],
            [
                'name' => 'Геймпад Xbox Series X',
                'name_uk' => 'Геймпад Xbox Series X',
                'name_en' => 'Xbox Series X Gamepad',
                'description' => 'Тактильная отдача, удобный захват',
                'description_uk' => 'Тактильна віддача, зручний хват',
                'description_en' => 'Haptic feedback, comfortable grip',
                'price' => 5000,
                'image' => 'gamepad.jpg',
            ],
            [
                'name' => 'Фитнес-трекер Garmin',
                'name_uk' => 'Фітнес-трекер Garmin',
                'name_en' => 'Garmin Fitness Tracker',
                'description' => 'Мониторинг здоровья, GPS',
                'description_uk' => 'Моніторинг здоров\'я, GPS',
                'description_en' => 'Health monitoring, GPS',
                'price' => 12000,
                'image' => 'tracker.jpg',
            ],
            [
                'name' => 'Умные часы Apple Watch',
                'name_uk' => 'Розумний годинник Apple Watch',
                'name_en' => 'Apple Watch Smartwatch',
                'description' => 'OLED-дисплей, мониторинг сна',
                'description_uk' => 'OLED-дисплей, моніторинг сну',
                'description_en' => 'OLED display, sleep tracking',
                'price' => 25000,
                'image' => 'apple_watch.jpg',
            ],
            [
                'name' => 'Электронная книга Kindle Paperwhite',
                'name_uk' => 'Електронна книга Kindle Paperwhite',
                'name_en' => 'Kindle Paperwhite E-reader',
                'description' => 'E-Ink экран, подсветка',
                'description_uk' => 'E-Ink екран, підсвітка',
                'description_en' => 'E-Ink display, backlight',
                'price' => 9500,
                'image' => 'kindle.jpg',
            ],
            [
                'name' => 'Компьютерное кресло DXRacer',
                'name_uk' => 'Комп\'ютерне крісло DXRacer',
                'name_en' => 'DXRacer Gaming Chair',
                'description' => 'Эргономичный дизайн, поддержка спины',
                'description_uk' => 'Ергономічний дизайн, підтримка спини',
                'description_en' => 'Ergonomic design, back support',
                'price' => 18000,
                'image' => 'dxracer.jpg',
            ],
            [
                'name' => 'Портативная колонка JBL Charge',
                'name_uk' => 'Портативна колонка JBL Charge',
                'name_en' => 'JBL Charge Portable Speaker',
                'description' => 'Мощный бас, влагозащита',
                'description_uk' => 'Потужний бас, вологозахист',
                'description_en' => 'Powerful bass, water resistance',
                'price' => 10000,
                'image' => 'jbl.jpg',
            ],
        ];
    }

    public function run(): void
    {
        if (! Category::query()->exists()) {
            Category::create(['name' => 'Электроника', 'slug' => 'electronics']);
        }

        $this->ensureDefaultProductFallbackImage();

        foreach (self::demoProductDefinitions() as $data) {
            $this->ensureSeededProductImage($data['image'], false);

            $slug = Str::slug($data['name']);
            $count = Product::where('slug', 'LIKE', "{$slug}%")->count();

            if ($count > 0) {
                $slug .= '-'.($count + 1);
            }

            Product::create([
                'name' => $data['name'],
                'name_uk' => $data['name_uk'],
                'name_en' => $data['name_en'],
                'slug' => $slug,
                'price' => $data['price'],
                'description' => $data['description'],
                'description_uk' => $data['description_uk'],
                'description_en' => $data['description_en'],
                'image' => 'products/'.$data['image'],
                'category_id' => Category::query()->orderBy('id')->value('id'),
                'stock' => rand(5, 50),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Для уже заполненной БД: создать недостающие файлы по полю image (после смены окружения / Docker).
     *
     * @param  bool  $force  перезаписать демо-файлы (исправляет одинаковые копии одной картинки)
     */
    public function ensureMissingProductFiles(bool $force = false): void
    {
        $this->ensureDefaultProductFallbackImage();
        foreach (Product::query()->whereNotNull('image')->cursor() as $product) {
            $this->ensureSeededProductImage(basename($product->image), $force);
        }
    }

    /**
     * Заполняет EN/UK поля для демо-товаров по точному совпадению русского name.
     */
    public function syncDemoProductLocales(): void
    {
        foreach (self::demoProductDefinitions() as $row) {
            Product::query()->where('name', $row['name'])->update([
                'name_uk' => $row['name_uk'],
                'name_en' => $row['name_en'],
                'description_uk' => $row['description_uk'],
                'description_en' => $row['description_en'],
            ]);
        }
    }

    /**
     * Уникальная заглушка JPEG по имени файла (без копирования одного и того же upload везде).
     */
    private function ensureSeededProductImage(string $filename, bool $force): void
    {
        $path = 'products/'.$filename;
        $isDemoSeed = in_array($filename, self::demoSeedFilenames(), true);

        if (Storage::disk('public')->exists($path)) {
            if (! $force || ! $isDemoSeed) {
                return;
            }
        }

        $this->writeUniquePlaceholderJpeg($path);
    }

    private function writeUniquePlaceholderJpeg(string $path): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            return;
        }

        $filename = basename($path);
        $crc = crc32($filename);
        $r1 = 110 + (($crc >> 16) & 0x7F);
        $g1 = 110 + (($crc >> 8) & 0x7F);
        $b1 = 110 + ($crc & 0x7F);
        $r2 = 80 + (((~$crc) >> 16) & 0x7F);
        $g2 = 80 + (((~$crc) >> 8) & 0x7F);
        $b2 = 80 + ((~$crc) & 0x7F);

        $img = imagecreatetruecolor(400, 300);
        for ($y = 0; $y < 300; $y++) {
            $t = $y / 299;
            $r = (int) ($r1 * (1 - $t) + $r2 * $t);
            $g = (int) ($g1 * (1 - $t) + $g2 * $t);
            $b = (int) ($b1 * (1 - $t) + $b2 * $t);
            $line = imagecolorallocate($img, $r, $g, $b);
            imageline($img, 0, $y, 400, $y, $line);
        }

        ob_start();
        imagejpeg($img, null, 86);
        $jpeg = ob_get_clean();
        imagedestroy($img);

        Storage::disk('public')->put($path, $jpeg);
    }

    /**
     * Заглушка для Product::getImageUrlAttribute, когда у товара нет картинки.
     */
    private function ensureDefaultProductFallbackImage(): void
    {
        $target = public_path('images/default-product.png');
        if (File::exists($target)) {
            return;
        }

        if (! function_exists('imagecreatetruecolor')) {
            return;
        }

        File::ensureDirectoryExists(dirname($target));
        $img = imagecreatetruecolor(400, 300);
        $bg = imagecolorallocate($img, 230, 233, 237);
        imagefill($img, 0, 0, $bg);
        imagepng($img, $target);
        imagedestroy($img);
    }
}
