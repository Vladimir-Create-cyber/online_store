<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Отображает каталог товаров.
     */
    public function index()
    {
        $products = Product::with(['images', 'mainImage'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        $unreadCount = $this->getUnreadCount();

        if ($products->isEmpty()) {
            return view('products.empty', compact('unreadCount'));
        }

        return view('products.index', compact('products', 'unreadCount'));
    }

    /**
     * Отображение страницы одного товара.
     */
    public function show(Product $product)
    {
        $product->load(['images', 'mainImage', 'category', 'reviews.user' => function ($q) {
            $q->select('id', 'name');
        }]);

        $reviews = $product->reviews()
            ->where('is_approved', true)
            ->latest()
            ->paginate(5);

        $averageRating = $product->reviews()
            ->where('is_approved', true)
            ->avg('rating');

        $unreadCount = $this->getUnreadCount();

        return view('products.show', compact(
            'product',
            'unreadCount',
            'reviews',
            'averageRating'
        ));
    }

    /**
     * Выполняет поиск товаров по названию.
     */
    public function search(Request $request)
    {
        $query = trim($request->input('query'));
        $unreadCount = $this->getUnreadCount();

        if (empty($query)) {
            return redirect()
                ->route('products.index')
                ->with('error', 'Введите запрос для поиска');
        }

        $products = Product::with(['images', 'mainImage'])
            ->where(function ($builder) use ($query) {
                $builder->where('name', 'like', '%' . $query . '%')
                    ->orWhere('name_uk', 'like', '%' . $query . '%')
                    ->orWhere('name_en', 'like', '%' . $query . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('products.search', compact('products', 'query', 'unreadCount'));
    }
}
