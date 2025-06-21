<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Получить количество непрочитанных уведомлений пользователя.
     *
     * @return int
     */
    private function getUnreadCount(): int
    {
        if (!Auth::check()) {
            return 0;
        }

        return Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();
    }

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
     * Отображение страницы одного товара по его slug.
     *
     * @param string $slug
     * @return \Illuminate\View\View
     */
    public function show(string $slug)
    {
        $product = Product::with('images')->where('slug', $slug)->firstOrFail();

        $unreadCount = $this->getUnreadCount();

        return view('products.show', compact('product', 'unreadCount'));
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
            ->whereRaw('LOWER(name) LIKE ?', ["%" . strtolower($query) . "%"])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('products.search', compact('products', 'query', 'unreadCount'));
    }
}
