<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Отобразить список всех товаров.
     */
    public function index()
    {
        $products = Product::orderBy('created_at', 'desc')->paginate(12);
        return view('admin.products.index', compact('products'));
    }

    /**
     * Форма создания нового товара.
     */
    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Сохранить новый товар.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_uk' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'description_uk' => 'nullable|string',
            'description_en' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(5);
        $validated['is_new'] = $request->has('is_new');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $imageFile) {
                $path = $imageFile->store('product_images', 'public');
                $product->images()->create([
                    'path' => $path,
                    'is_main' => !$request->hasFile('image') && $index === 0,
                ]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Товар успешно добавлен!');
    }

    /**
     * Форма редактирования товара.
     */
    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Обновить существующий товар.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_uk' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'description_uk' => 'nullable|string',
            'description_en' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'additional_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $product->fill([
            'name' => $validated['name'],
            'name_uk' => $validated['name_uk'] ?? null,
            'name_en' => $validated['name_en'] ?? null,
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'description' => $validated['description'] ?? null,
            'description_uk' => $validated['description_uk'] ?? null,
            'description_en' => $validated['description_en'] ?? null,
            'category_id' => $validated['category_id'],
            'is_new' => $request->has('is_new'),
        ]);

        if ($request->hasFile('image')) {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $product->image = $request->file('image')->store('products', 'public');
        }

        $product->save();

        if ($request->hasFile('additional_images')) {
            foreach ($request->file('additional_images') as $file) {
                $path = $file->store('product_images', 'public');

                $product->images()->create([
                    'path' => $path,
                    'is_main' => false,
                ]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Товар успешно обновлён');
    }

    /**
     * Удалить товар (и изображения).
     */
    public function destroy(Product $product)
    {
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        foreach ($product->images as $image) {
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
            $image->delete();
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Товар успешно удалён!');
    }

    /**
     * Удалить главное изображение.
     */
    public function removeImage(Product $product)
    {
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
            $product->image = null;
            $product->save();
        }

        return back()->with('success', 'Главное изображение удалено');
    }

    /**
     * Удалить дополнительное изображение.
     */
    public function deleteImage(ProductImage $image)
    {
        if (Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->delete($image->path);
        }

        $image->delete();

        return back()->with('success', 'Изображение удалено');
    }
}
