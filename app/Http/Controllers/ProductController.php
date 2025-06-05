<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Notification; // Добавили импорт модели
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    // Новая реализация метода для получения количества непрочитанных уведомлений
    private function getUnreadCount()
    {
        if (!Auth::check()) {
            return 0;
        }

        // Прямой запрос к базе данных
        return Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();
    }

    public function index()
    {
        $products = Product::paginate(10);
        $unreadCount = $this->getUnreadCount();

        if ($products->isEmpty()) {
            return view('products.empty', compact('unreadCount'));
        }

        return view('products.index', compact('products', 'unreadCount'));
    }

    public function show($slug)
    {
        $product = Product::where('slug', $slug)->first();
        $unreadCount = $this->getUnreadCount();

        if (!$product) {
            return redirect()->route('products.index')->with('error', 'Товар не найден');
        }

        return view('products.show', compact('product', 'unreadCount'));
    }

    public function search(Request $request)
    {
        $query = trim($request->input('query'));
        $unreadCount = $this->getUnreadCount();

        if (empty($query)) {
            return redirect()->route('products.index')->with('error', 'Введите запрос для поиска');
        }

        $products = Product::whereRaw('LOWER(name) LIKE ?', ["%".strtolower($query)."%"])->paginate(10);
        return view('products.search', compact('products', 'query', 'unreadCount'));
    }
}
