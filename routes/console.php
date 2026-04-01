<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Product;
use App\Models\Category;
use App\Models\ShippingMethod;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('localization:backfill {--dry-run : Только показать изменения без записи}', function () {
    $dryRun = (bool) $this->option('dry-run');

    $this->info($dryRun
        ? 'Режим dry-run: изменения в БД не будут сохранены.'
        : 'Запуск заполнения локализованных полей...'
    );

    $productUpdated = 0;
    $categoryUpdated = 0;
    $shippingMethodUpdated = 0;
    $productScanned = 0;
    $categoryScanned = 0;
    $shippingMethodScanned = 0;

    Product::query()->chunkById(200, function ($products) use (&$productUpdated, &$productScanned, $dryRun) {
        foreach ($products as $product) {
            $productScanned++;

            $updates = [];
            $sourceName = $product->getRawOriginal('name');
            $sourceDescription = $product->getRawOriginal('description');

            if (blank($product->getRawOriginal('name_uk')) && filled($sourceName)) {
                $updates['name_uk'] = $sourceName;
            }
            if (blank($product->getRawOriginal('name_en')) && filled($sourceName)) {
                $updates['name_en'] = $sourceName;
            }
            if (blank($product->getRawOriginal('description_uk')) && filled($sourceDescription)) {
                $updates['description_uk'] = $sourceDescription;
            }
            if (blank($product->getRawOriginal('description_en')) && filled($sourceDescription)) {
                $updates['description_en'] = $sourceDescription;
            }

            if ($updates !== []) {
                $productUpdated++;
                if (! $dryRun) {
                    $product->updateQuietly($updates);
                }
            }
        }
    });

    Category::query()->chunkById(200, function ($categories) use (&$categoryUpdated, &$categoryScanned, $dryRun) {
        foreach ($categories as $category) {
            $categoryScanned++;

            $updates = [];
            $sourceName = $category->getRawOriginal('name');
            $sourceDescription = $category->getRawOriginal('description');

            if (blank($category->getRawOriginal('name_uk')) && filled($sourceName)) {
                $updates['name_uk'] = $sourceName;
            }
            if (blank($category->getRawOriginal('name_en')) && filled($sourceName)) {
                $updates['name_en'] = $sourceName;
            }
            if (blank($category->getRawOriginal('description_uk')) && filled($sourceDescription)) {
                $updates['description_uk'] = $sourceDescription;
            }
            if (blank($category->getRawOriginal('description_en')) && filled($sourceDescription)) {
                $updates['description_en'] = $sourceDescription;
            }

            if ($updates !== []) {
                $categoryUpdated++;
                if (! $dryRun) {
                    $category->updateQuietly($updates);
                }
            }
        }
    });

    ShippingMethod::query()->chunkById(200, function ($methods) use (&$shippingMethodUpdated, &$shippingMethodScanned, $dryRun) {
        foreach ($methods as $method) {
            $shippingMethodScanned++;
            $sourceName = $method->getRawOriginal('name');

            $updates = [];
            if (blank($method->getRawOriginal('name_uk')) && filled($sourceName)) {
                $updates['name_uk'] = $sourceName;
            }
            if (blank($method->getRawOriginal('name_en')) && filled($sourceName)) {
                $updates['name_en'] = $sourceName;
            }

            if ($updates !== []) {
                $shippingMethodUpdated++;
                if (! $dryRun) {
                    $method->updateQuietly($updates);
                }
            }
        }
    });

    $this->newLine();
    $this->line("Товары: проверено {$productScanned}, обновлено {$productUpdated}");
    $this->line("Категории: проверено {$categoryScanned}, обновлено {$categoryUpdated}");
    $this->line("Способы доставки: проверено {$shippingMethodScanned}, обновлено {$shippingMethodUpdated}");
    $this->info($dryRun ? 'Проверка завершена.' : 'Заполнение завершено.');
})->purpose('Заполняет пустые локализованные поля товаров и категорий из базовых RU-полей');

Artisan::command('localization:demo-translate {--dry-run : Только показать изменения без записи} {--force : Перезаписать существующие EN/UK значения}', function () {
    $dryRun = (bool) $this->option('dry-run');
    $force = (bool) $this->option('force');

    $this->info($dryRun
        ? 'Режим dry-run: изменения в БД не будут сохранены.'
        : 'Запуск автоперевода demo-данных...'
    );

    $productNameMap = [
        'ноутбук lenovo' => ['uk' => 'Ноутбук Lenovo', 'en' => 'Lenovo Laptop'],
        'смартфон samsung' => ['uk' => 'Смартфон Samsung', 'en' => 'Samsung Smartphone'],
        'беспроводные наушники' => ['uk' => 'Бездротові навушники', 'en' => 'Wireless Headphones'],
        'игровая мышь logitech' => ['uk' => 'Ігрова миша Logitech', 'en' => 'Logitech Gaming Mouse'],
        'монитор 27" lg' => ['uk' => 'Монітор 27" LG', 'en' => 'LG 27" Monitor'],
        'механическая клавиатура razer' => ['uk' => 'Механічна клавіатура Razer', 'en' => 'Razer Mechanical Keyboard'],
        'гарнитура hyperx' => ['uk' => 'Гарнітура HyperX', 'en' => 'HyperX Headset'],
        'кофеварка philips' => ['uk' => 'Кавоварка Philips', 'en' => 'Philips Coffee Maker'],
        'блендер bosch' => ['uk' => 'Блендер Bosch', 'en' => 'Bosch Blender'],
        'микроволновая печь samsung' => ['uk' => 'Мікрохвильова піч Samsung', 'en' => 'Samsung Microwave Oven'],
        'стиральная машина lg' => ['uk' => 'Пральна машина LG', 'en' => 'LG Washing Machine'],
        'холодильник beko' => ['uk' => 'Холодильник Beko', 'en' => 'Beko Refrigerator'],
        'пылесос dyson' => ['uk' => 'Пилосос Dyson', 'en' => 'Dyson Vacuum Cleaner'],
        'утюг tefal' => ['uk' => 'Праска Tefal', 'en' => 'Tefal Iron'],
        'электрочайник xiaomi' => ['uk' => 'Електрочайник Xiaomi', 'en' => 'Xiaomi Electric Kettle'],
        'планшет xiaomi pad' => ['uk' => 'Планшет Xiaomi Pad', 'en' => 'Xiaomi Pad Tablet'],
        'внешний ssd samsung 1tb' => ['uk' => 'Зовнішній SSD Samsung 1TB', 'en' => 'Samsung External SSD 1TB'],
        'геймпад xbox series x' => ['uk' => 'Геймпад Xbox Series X', 'en' => 'Xbox Series X Gamepad'],
        'фитнес-трекер garmin' => ['uk' => 'Фітнес-трекер Garmin', 'en' => 'Garmin Fitness Tracker'],
        'умные часы apple watch' => ['uk' => 'Розумний годинник Apple Watch', 'en' => 'Apple Watch Smartwatch'],
        'электронная книга kindle paperwhite' => ['uk' => 'Електронна книга Kindle Paperwhite', 'en' => 'Kindle Paperwhite E-reader'],
        'компьютерное кресло dxracer' => ['uk' => "Комп'ютерне крісло DXRacer", 'en' => 'DXRacer Gaming Chair'],
        'портативная колонка jbl charge' => ['uk' => 'Портативна колонка JBL Charge', 'en' => 'JBL Charge Portable Speaker'],
    ];

    $productDescriptionMap = [
        'ноутбук lenovo' => ['uk' => 'Потужний ноутбук з SSD 512GB', 'en' => 'Powerful laptop with 512GB SSD'],
        'смартфон samsung' => ['uk' => 'Камера 108MP, AMOLED-екран', 'en' => '108MP camera, AMOLED display'],
        'беспроводные наушники' => ['uk' => 'Чистий звук, шумозаглушення', 'en' => 'Clear sound, noise cancellation'],
        'игровая мышь logitech' => ['uk' => 'RGB-підсвітка, швидкий відгук', 'en' => 'RGB backlight, fast response'],
        'монитор 27" lg' => ['uk' => 'IPS-екран, 144Hz', 'en' => 'IPS display, 144Hz'],
        'механическая клавиатура razer' => ['uk' => 'Оптичні перемикачі, RGB', 'en' => 'Optical switches, RGB'],
        'гарнитура hyperx' => ['uk' => 'Комфортна посадка, потужний звук', 'en' => 'Comfortable fit, powerful sound'],
        'планшет xiaomi pad' => ['uk' => 'Екран 10.5", 8GB RAM', 'en' => '10.5" display, 8GB RAM'],
        'внешний ssd samsung 1tb' => ['uk' => 'Швидкість до 1050MB/s', 'en' => 'Speed up to 1050MB/s'],
        'геймпад xbox series x' => ['uk' => 'Тактильна віддача, зручний хват', 'en' => 'Haptic feedback, comfortable grip'],
        'фитнес-трекер garmin' => ['uk' => "Моніторинг здоров'я, GPS", 'en' => 'Health monitoring, GPS'],
        'умные часы apple watch' => ['uk' => 'OLED-дисплей, моніторинг сну', 'en' => 'OLED display, sleep tracking'],
        'электронная книга kindle paperwhite' => ['uk' => 'E-Ink екран, підсвітка', 'en' => 'E-Ink display, backlight'],
        'компьютерное кресло dxracer' => ['uk' => 'Ергономічний дизайн, підтримка спини', 'en' => 'Ergonomic design, back support'],
        'портативная колонка jbl charge' => ['uk' => 'Потужний бас, вологозахист', 'en' => 'Powerful bass, water resistance'],
        'кофеварка philips' => ['uk' => 'Крапельна кавоварка для щоденного використання', 'en' => 'Drip coffee maker for everyday use'],
        'блендер bosch' => ['uk' => 'Надійний блендер для кухні', 'en' => 'Reliable blender for your kitchen'],
        'микроволновая печь samsung' => ['uk' => 'Швидкий розігрів та розморожування', 'en' => 'Fast heating and defrosting'],
        'стиральная машина lg' => ['uk' => 'Енергоефективне прання для дому', 'en' => 'Energy-efficient washing for home'],
        'холодильник beko' => ['uk' => 'Місткий холодильник для продуктів', 'en' => 'Spacious refrigerator for groceries'],
        'пылесос dyson' => ['uk' => 'Потужне прибирання без дротів', 'en' => 'Powerful cordless cleaning'],
        'утюг tefal' => ['uk' => 'Швидке прасування з парою', 'en' => 'Fast ironing with steam'],
        'электрочайник xiaomi' => ['uk' => 'Швидке кипʼятіння води', 'en' => 'Fast water boiling'],
    ];

    $categoryNameMap = [
        'ноутбуки' => ['uk' => 'Ноутбуки', 'en' => 'Laptops'],
        'смартфоны' => ['uk' => 'Смартфони', 'en' => 'Smartphones'],
        'аудио' => ['uk' => 'Аудіо', 'en' => 'Audio'],
        'аксессуары' => ['uk' => 'Аксесуари', 'en' => 'Accessories'],
        'бытовая техника' => ['uk' => 'Побутова техніка', 'en' => 'Home Appliances'],
    ];

    $shippingMethodNameMap = [
        'новая почта' => ['uk' => 'Нова пошта', 'en' => 'Nova Poshta'],
        'укрпочта' => ['uk' => 'Укрпошта', 'en' => 'Ukrposhta'],
        'курьером' => ['uk' => "Кур'єром", 'en' => 'Courier delivery'],
        'самовывоз' => ['uk' => 'Самовивіз', 'en' => 'Self pickup'],
    ];

    $descriptionMap = [
        'мощный ноутбук' => ['uk' => 'Потужний ноутбук', 'en' => 'Powerful laptop'],
        'ssd' => ['uk' => 'SSD', 'en' => 'SSD'],
        'камера' => ['uk' => 'Камера', 'en' => 'Camera'],
        'экран' => ['uk' => 'Екран', 'en' => 'Display'],
        'чистый звук' => ['uk' => 'Чистий звук', 'en' => 'Clear sound'],
        'шумоподавление' => ['uk' => 'шумозаглушення', 'en' => 'noise cancellation'],
        'rgb-подсветка' => ['uk' => 'RGB-підсвітка', 'en' => 'RGB backlight'],
        'быстрый отклик' => ['uk' => 'швидкий відгук', 'en' => 'fast response'],
        'ips-экран' => ['uk' => 'IPS-екран', 'en' => 'IPS display'],
        'оптические переключатели' => ['uk' => 'оптичні перемикачі', 'en' => 'optical switches'],
        'комфортная посадка' => ['uk' => 'комфортна посадка', 'en' => 'comfortable fit'],
        'мощный звук' => ['uk' => 'потужний звук', 'en' => 'powerful sound'],
        '8gb ram' => ['uk' => '8GB RAM', 'en' => '8GB RAM'],
        'скорость до' => ['uk' => 'швидкість до', 'en' => 'speed up to'],
        'тактильная отдача' => ['uk' => 'тактильна віддача', 'en' => 'haptic feedback'],
        'удобный захват' => ['uk' => 'зручний хват', 'en' => 'comfortable grip'],
        'мониторинг здоровья' => ['uk' => "моніторинг здоров'я", 'en' => 'health monitoring'],
        'умные часы' => ['uk' => 'розумний годинник', 'en' => 'smartwatch'],
        'oled-дисплей' => ['uk' => 'OLED-дисплей', 'en' => 'OLED display'],
        'мониторинг сна' => ['uk' => 'моніторинг сну', 'en' => 'sleep tracking'],
        'электронная книга' => ['uk' => 'електронна книга', 'en' => 'e-reader'],
        'e-ink экран' => ['uk' => 'E-Ink екран', 'en' => 'E-Ink display'],
        'эргономичный дизайн' => ['uk' => 'ергономічний дизайн', 'en' => 'ergonomic design'],
        'поддержка спины' => ['uk' => 'підтримка спини', 'en' => 'back support'],
        'мощный бас' => ['uk' => 'потужний бас', 'en' => 'powerful bass'],
        'влагозащита' => ['uk' => 'вологозахист', 'en' => 'water resistance'],
    ];

    $translateDescription = function (?string $source, string $locale) use ($descriptionMap): ?string {
        if (! filled($source)) {
            return $source;
        }

        $result = $source;
        foreach ($descriptionMap as $needle => $translations) {
            $translated = $translations[$locale] ?? $needle;
            $result = str_ireplace($needle, $translated, $result);
        }

        return $result;
    };

    $productUpdated = 0;
    $categoryUpdated = 0;
    $shippingMethodUpdated = 0;

    Product::query()->chunkById(200, function ($products) use (
        &$productUpdated,
        $dryRun,
        $productNameMap,
        $productDescriptionMap,
        $translateDescription,
        $force
    ) {
        foreach ($products as $product) {
            $sourceName = (string) $product->getRawOriginal('name');
            $sourceDescription = $product->getRawOriginal('description');
            $key = mb_strtolower(trim($sourceName));

            $translatedNameUk = $productNameMap[$key]['uk'] ?? $sourceName;
            $translatedNameEn = $productNameMap[$key]['en'] ?? $sourceName;
            $translatedDescriptionUk = $productDescriptionMap[$key]['uk'] ?? $translateDescription($sourceDescription, 'uk');
            $translatedDescriptionEn = $productDescriptionMap[$key]['en'] ?? $translateDescription($sourceDescription, 'en');

            $updates = [];
            if ($force || $product->getRawOriginal('name_uk') === $sourceName || blank($product->getRawOriginal('name_uk'))) {
                $updates['name_uk'] = $translatedNameUk;
            }
            if ($force || $product->getRawOriginal('name_en') === $sourceName || blank($product->getRawOriginal('name_en'))) {
                $updates['name_en'] = $translatedNameEn;
            }
            if ($force || $product->getRawOriginal('description_uk') === $sourceDescription || blank($product->getRawOriginal('description_uk'))) {
                $updates['description_uk'] = $translatedDescriptionUk;
            }
            if ($force || $product->getRawOriginal('description_en') === $sourceDescription || blank($product->getRawOriginal('description_en'))) {
                $updates['description_en'] = $translatedDescriptionEn;
            }

            if ($updates !== []) {
                $productUpdated++;
                if (! $dryRun) {
                    $product->updateQuietly($updates);
                }
            }
        }
    });

    Category::query()->chunkById(200, function ($categories) use (&$categoryUpdated, $dryRun, $categoryNameMap, $force) {
        foreach ($categories as $category) {
            $sourceName = (string) $category->getRawOriginal('name');
            $sourceDescription = $category->getRawOriginal('description');
            $key = mb_strtolower(trim($sourceName));

            $translatedNameUk = $categoryNameMap[$key]['uk'] ?? $sourceName;
            $translatedNameEn = $categoryNameMap[$key]['en'] ?? $sourceName;

            $updates = [];
            if ($force || $category->getRawOriginal('name_uk') === $sourceName || blank($category->getRawOriginal('name_uk'))) {
                $updates['name_uk'] = $translatedNameUk;
            }
            if ($force || $category->getRawOriginal('name_en') === $sourceName || blank($category->getRawOriginal('name_en'))) {
                $updates['name_en'] = $translatedNameEn;
            }
            if ($force || $category->getRawOriginal('description_uk') === $sourceDescription || blank($category->getRawOriginal('description_uk'))) {
                $updates['description_uk'] = $sourceDescription;
            }
            if ($force || $category->getRawOriginal('description_en') === $sourceDescription || blank($category->getRawOriginal('description_en'))) {
                $updates['description_en'] = $sourceDescription;
            }

            if ($updates !== []) {
                $categoryUpdated++;
                if (! $dryRun) {
                    $category->updateQuietly($updates);
                }
            }
        }
    });

    ShippingMethod::query()->chunkById(200, function ($methods) use (&$shippingMethodUpdated, $dryRun, $shippingMethodNameMap, $force) {
        foreach ($methods as $method) {
            $sourceName = (string) $method->getRawOriginal('name');
            $key = mb_strtolower(trim($sourceName));

            $translatedNameUk = $shippingMethodNameMap[$key]['uk'] ?? $sourceName;
            $translatedNameEn = $shippingMethodNameMap[$key]['en'] ?? $sourceName;

            $updates = [];
            if ($force || $method->getRawOriginal('name_uk') === $sourceName || blank($method->getRawOriginal('name_uk'))) {
                $updates['name_uk'] = $translatedNameUk;
            }
            if ($force || $method->getRawOriginal('name_en') === $sourceName || blank($method->getRawOriginal('name_en'))) {
                $updates['name_en'] = $translatedNameEn;
            }

            if ($updates !== []) {
                $shippingMethodUpdated++;
                if (! $dryRun) {
                    $method->updateQuietly($updates);
                }
            }
        }
    });

    $this->newLine();
    $this->line("Товары: обновлено {$productUpdated}");
    $this->line("Категории: обновлено {$categoryUpdated}");
    $this->line("Способы доставки: обновлено {$shippingMethodUpdated}");
    $this->info($dryRun ? 'Проверка завершена.' : 'Автоперевод завершён.');
})->purpose('Автопереводит demo-товары, категории и доставку на EN/UK, если поля пустые или равны RU');
