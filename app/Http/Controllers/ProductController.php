<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::paginate(10);

        if ($products->isEmpty()) {
            return view('products.empty');
        }

        return view('products.index', compact('products'));
    }

    public function show($slug)
    {
        $product = Product::where('slug', $slug)->first();

        if (!$product) {
            return redirect()->route('products.index')->with('error', 'Товар не найден');
        }

        return view('products.show', compact('product'));
    }

    public function search(Request $request)
    {
        $query = trim($request->input('query'));
        if (empty($query)) {
            return redirect()->route('products.index')->with('error', 'Введите запрос для поиска');
        }

        $products = Product::whereRaw('LOWER(name) LIKE ?', ["%".strtolower($query)."%"])->paginate(10);
        return view('products.search', compact('products', 'query'));
    }
}
