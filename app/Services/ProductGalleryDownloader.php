<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Загрузка демо-галереи (5 фото на товар) с Unsplash CDN в storage/app/public/product_images.
 * Изображения подобраны по тематике; лицензия Unsplash: https://unsplash.com/license
 */
class ProductGalleryDownloader
{
    /**
     * Ключ — точное русское name из ProductSeeder; по 5 URL на товар.
     *
     * @return array<string, array<int, string>>
     */
    public static function demoUrlsByProductName(): array
    {
        $q = 'auto=format&fit=crop&w=1200&q=80';

        return [
            'Ноутбук Lenovo' => [
                "https://images.unsplash.com/photo-1511385348-a52b4a160dc2?{$q}",
                "https://images.unsplash.com/photo-1618424181497-157f25b6ddd5?{$q}",
                "https://images.unsplash.com/photo-1541807084-5c52b6b3adef?{$q}",
                "https://images.unsplash.com/photo-1491472253230-a044054ca35f?{$q}",
                "https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?{$q}",
            ],
            'Смартфон Samsung' => [
                "https://images.unsplash.com/photo-1592890288564-76628a30a657?{$q}",
                "https://images.unsplash.com/photo-1598327105666-5b89351aff97?{$q}",
                "https://images.unsplash.com/photo-1634403665481-74948d815f03?{$q}",
                "https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?{$q}",
                "https://images.unsplash.com/photo-1573152143286-0c422b4d2175?{$q}",
            ],
            'Беспроводные наушники' => [
                "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?{$q}",
                "https://images.unsplash.com/photo-1612858249937-1cc0852093dd?{$q}",
                "https://images.unsplash.com/photo-1609081219090-a6d81d3085bf?{$q}",
                "https://images.unsplash.com/photo-1637780852590-8ab27248ec41?{$q}",
                "https://images.unsplash.com/photo-1612465289702-7c84b5258fde?{$q}",
            ],
            'Игровая мышь Logitech' => [
                "https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?{$q}",
                "https://images.unsplash.com/photo-1551515300-2d3b7bb80920?{$q}",
                "https://images.unsplash.com/photo-1605773527852-c546a8584ea3?{$q}",
                "https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?{$q}",
                "https://images.unsplash.com/photo-1527814050087-3793815479db?{$q}",
            ],
            'Монитор 27" LG' => [
                "https://images.unsplash.com/photo-1611648694931-1aeda329f9da?{$q}",
                "https://images.unsplash.com/photo-1579765754037-5bfef757251a?{$q}",
                "https://images.unsplash.com/photo-1560131914-2e469a0e8607?{$q}",
                "https://images.unsplash.com/photo-1666771410333-3457e9603dd4?{$q}",
                "https://images.unsplash.com/photo-1594400344473-cf8b48733c1f?{$q}",
            ],
            'Механическая клавиатура Razer' => [
                "https://images.unsplash.com/photo-1612198188060-c7c2a3b66eae?{$q}",
                "https://images.unsplash.com/photo-1626958390943-a70309376444?{$q}",
                "https://images.unsplash.com/photo-1538481199705-c710c4e965fc?{$q}",
                "https://images.unsplash.com/photo-1520092352425-9699926a9b0b?{$q}",
                "https://images.unsplash.com/photo-1581351123004-757df051db8e?{$q}",
            ],
            'Гарнитура HyperX' => [
                "https://images.unsplash.com/photo-1629429407756-4a7703614972?{$q}",
                "https://images.unsplash.com/photo-1677086813101-496781a0f327?{$q}",
                "https://images.unsplash.com/photo-1610041321327-b794c052db27?{$q}",
                "https://images.unsplash.com/photo-1591105866700-cb5d708ccd93?{$q}",
                "https://images.unsplash.com/photo-1710265029735-434f63c672c4?{$q}",
            ],
            'Планшет Xiaomi Pad' => [
                "https://images.unsplash.com/photo-1561154464-82e9adf32764?{$q}",
                "https://images.unsplash.com/photo-1628591459313-a64214c5bfac?{$q}",
                "https://images.unsplash.com/photo-1638273266965-843b01e02a5c?{$q}",
                "https://images.unsplash.com/photo-1565797201241-da27b9ace18e?{$q}",
                "https://images.unsplash.com/photo-1644953798828-a92178929505?{$q}",
            ],
            'Внешний SSD Samsung 1TB' => [
                "https://images.unsplash.com/photo-1587145820098-23e484e69816?{$q}",
                "https://images.unsplash.com/photo-1551818014-7c8ace9c1b5c?{$q}",
                "https://images.unsplash.com/photo-1625886390251-01af1ea39853?{$q}",
                "https://images.unsplash.com/photo-1625886390223-ffad4ed815ad?{$q}",
                "https://images.unsplash.com/photo-1639675960002-2f414c58ed79?{$q}",
            ],
            'Геймпад Xbox Series X' => [
                "https://images.unsplash.com/photo-1592840496694-26d035b52b48?{$q}",
                "https://images.unsplash.com/photo-1632312527375-bd5d5a0d3484?{$q}",
                "https://images.unsplash.com/photo-1509198397868-475647b2a1e5?{$q}",
                "https://images.unsplash.com/photo-1603565913235-382c62d3b2ee?{$q}",
                "https://images.unsplash.com/photo-1554213352-5ffe6534af08?{$q}",
            ],
            'Фитнес-трекер Garmin' => [
                "https://images.unsplash.com/photo-1434494878577-86c23bcb06b9?{$q}",
                "https://images.unsplash.com/photo-1576243345690-4e4b79b63288?{$q}",
                "https://images.unsplash.com/photo-1532435109783-fdb8a2be0baa?{$q}",
                "https://images.unsplash.com/photo-1557935728-e6d1eaabe558?{$q}",
                "https://images.unsplash.com/photo-1654195131868-cac1d8429d86?{$q}",
            ],
            'Умные часы Apple Watch' => [
                "https://images.unsplash.com/photo-1660844817855-3ecc7ef21f12?{$q}",
                "https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?{$q}",
                "https://images.unsplash.com/photo-1617625802912-cde586faf331?{$q}",
                "https://images.unsplash.com/photo-1579586337278-3befd40fd17a?{$q}",
                "https://images.unsplash.com/photo-1632794716789-42d9995fb5b6?{$q}",
            ],
            'Электронная книга Kindle Paperwhite' => [
                "https://images.unsplash.com/photo-1643913398973-f8e24bf6d1c1?{$q}",
                "https://images.unsplash.com/photo-1591719675150-a9302a9cb467?{$q}",
                "https://images.unsplash.com/photo-1506953752663-add60014e80e?{$q}",
                "https://images.unsplash.com/photo-1603406136476-85d8c3ec76a5?{$q}",
                "https://images.unsplash.com/photo-1594498257673-9f36b767286c?{$q}",
            ],
            'Компьютерное кресло DXRacer' => [
                "https://images.unsplash.com/photo-1670946839270-cc4febd43b09?{$q}",
                "https://images.unsplash.com/photo-1636487658609-28282bb5a3a0?{$q}",
                "https://images.unsplash.com/photo-1612011213721-3936d387f318?{$q}",
                "https://images.unsplash.com/photo-1580480095047-4aa43ab3bd1d?{$q}",
                "https://images.unsplash.com/photo-1594501252356-79ebbbb10dd9?{$q}",
            ],
            'Портативная колонка JBL Charge' => [
                "https://images.unsplash.com/photo-1589256469067-ea99122bbdc4?{$q}",
                "https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?{$q}",
                "https://images.unsplash.com/photo-1589003077984-894e133dabab?{$q}",
                "https://images.unsplash.com/photo-1529359744902-86b2ab9edaea?{$q}",
                "https://images.unsplash.com/photo-1588131153911-a4ea5189fe19?{$q}",
            ],
        ];
    }

    /**
     * @return array{products:int, images_ok:int, images_failed:int, skipped:int}
     */
    public function downloadForDemoProducts(bool $force = false): array
    {
        $result = [
            'products' => 0,
            'images_ok' => 0,
            'images_failed' => 0,
            'skipped' => 0,
        ];

        foreach (self::demoUrlsByProductName() as $name => $urls) {
            $product = Product::query()->where('name', $name)->first();
            if (! $product) {
                $result['skipped']++;

                continue;
            }

            if ($product->images()->exists() && ! $force) {
                $result['skipped']++;

                continue;
            }

            if ($force) {
                $product->images()->each(fn (ProductImage $img) => $img->delete());
            }

            $result['products']++;
            $firstPath = null;

            foreach (array_values($urls) as $index => $url) {
                $path = $this->downloadOne($url, $product->id, $index);
                if ($path === null) {
                    $result['images_failed']++;

                    continue;
                }

                $result['images_ok']++;
                if ($index === 0) {
                    $firstPath = $path;
                }

                ProductImage::query()->create([
                    'product_id' => $product->id,
                    'path' => $path,
                    'is_main' => $index === 0,
                ]);
            }

            if ($firstPath !== null) {
                $product->update(['image' => $firstPath]);
            }
        }

        return $result;
    }

    private function downloadOne(string $url, int $productId, int $index): ?string
    {
        $response = Http::timeout(90)
            ->withHeaders([
                'User-Agent' => 'OnlineStoreDemo/1.0 (+https://unsplash.com/license)',
                'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
            ])
            ->get($url);

        if (! $response->successful()) {
            return null;
        }

        $body = $response->body();
        if ($body === '' || strlen($body) < 500) {
            return null;
        }

        $ext = 'jpg';
        $ct = $response->header('Content-Type');
        if (is_string($ct)) {
            if (str_contains($ct, 'png')) {
                $ext = 'png';
            } elseif (str_contains($ct, 'webp')) {
                $ext = 'webp';
            }
        }

        $path = "product_images/demo_p{$productId}_{$index}.{$ext}";
        Storage::disk('public')->put($path, $body);

        return $path;
    }
}
