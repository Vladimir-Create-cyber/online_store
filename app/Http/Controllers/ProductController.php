<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;


class ProductController extends Controller
{
    // Вывод каталога товаров
    public function index()
    {
        $products = Product::paginate(10);
        return view('products.index', compact('products'));
    }

    // Детальная страница товара
    public function show($slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();
        return view('products.show', compact('product'));
    }

    // Поиск товара
    public function search(Request $request)
    {
        $query = $request->input('query');
        $products = Product::where('name', 'like', '%'.$query.'%')->paginate(10);
        return view('products.search', compact('products', 'query'));
    }
}
