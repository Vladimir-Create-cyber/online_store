<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;

class CartController extends Controller
{
    // Показываем содержимое корзины
    public function viewCart()
    {
        $cart = session()->get('cart', []);
        return view('cart.index', compact('cart'));
    }

    // Добавляем товар в корзину
    public function addToCart(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
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

    // Удаляем товар из корзины
    public function removeFromCart($productId)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
        }

        return redirect()->back()->with('success', 'Товар удалён из корзины!');
    }

    // Оформление заказа
    public function checkout()
    {
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
