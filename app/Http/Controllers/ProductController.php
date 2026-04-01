<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Review;

class ProductController extends Controller
{
    /**
     * Отображение каталога товаров на главной странице.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $products = Product::with(['images', 'mainImage']) // Загрузка изображений
        ->orderBy('created_at', 'desc') // Сортировка: сначала новинки
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
        $product->load(['images', 'category', 'reviews.user' => function ($q) {
            $q->select('id', 'name'); // подгружаем только имя пользователя
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
     * Поиск товаров по названию.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
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
            ->where('name', 'like', '%' . $query . '%')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('products.search', compact('products', 'query', 'unreadCount'));
    }
}
