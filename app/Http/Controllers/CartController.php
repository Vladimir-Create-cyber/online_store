<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;

class CartController extends Controller
{
    public function viewCart()
    {
        if (!session()->has('cart')) {
            session()->put('cart', []);
        }

        return view('cart.index', ['cart' => session()->get('cart')]);
    }

    public function addToCart(Request $request, $productId)
    {
        try {
            $product = Product::findOrFail($productId);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Товар не найден!');
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            if ($cart[$productId]['quantity'] >= $product->stock) {
                return redirect()->back()->with('error', 'На складе недостаточно товара!');
            }
            $cart[$productId]['quantity']++;
        } else {
            $cart[$productId] = [
                'name' => $product->name,
                'price' => $product->price,
                'image' => $product->image,
                'quantity' => 1
            ];
        }

        session()->put('cart', $cart);
        return redirect()->back()->with('success', 'Товар добавлен в корзину!');
    }

    public function removeFromCart($productId)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
        }
        return redirect()->back()->with('success', 'Товар удалён из корзины!');
    }

    public function checkout()
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Вы должны войти, чтобы оформить заказ!');
        }

        $cart = session()->get('cart', []);

        if (!$cart) {
            return redirect()->back()->with('error', 'Корзина пуста!');
        }

        $order = Order::create([
            'user_id' => auth()->id(),
            'total' => collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']),
            'status' => 'pending'
        ]);

        foreach ($cart as $id => $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $id,
                'quantity' => $item['quantity'],
                'price' => $item['price']
            ]);
        }

        session()->forget('cart');
        return redirect()->route('orders.show', $order->id)->with('success', 'Заказ оформлен!');
    }
}
