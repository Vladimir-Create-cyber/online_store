<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderAddress;
use App\Models\Notification;
use App\Models\ShippingMethod;

class CartController extends Controller
{
    /**
     * Подсчёт количества непрочитанных уведомлений пользователя.
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
     * Отображение корзины с товарами.
     */
    public function viewCart()
    {
        $cart = session()->get('cart', []);
        $unreadCount = $this->getUnreadCount();

        return view('cart.index', [
            'cart' => $cart,
            'unreadCount' => $unreadCount,
        ]);
    }

    /**
     * Добавление товара в корзину по ID.
     */
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
                'product_id' => $productId,
                'name' => $product->name,
                'price' => $product->price,
                'image' => $product->image,
                'quantity' => 1,
            ];
        }

        session()->put('cart', $cart);

        return redirect()->back()->with('success', 'Товар добавлен в корзину!');
    }

    /**
     * Удаление товара из корзины.
     */
    public function removeFromCart($productId)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
        }

        return redirect()->back()->with('success', 'Товар удалён из корзины!');
    }

    /**
     * Шаг 1: Отображение формы оформления заказа.
     */
    public function showCheckoutForm()
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Ваша корзина пуста.');
        }

        $shippingMethods = ShippingMethod::where('is_active', true)->get();

        return view('cart.checkout', [
            'cart' => $cart,
            'shippingMethods' => $shippingMethods,
        ]);
    }

    /**
     * Шаг 2: Завершение оформления заказа и сохранение данных.
     */
    public function completeOrder(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'required|string|max:20',
            'shipping_method_code' => 'required|string|exists:shipping_methods,code',
            'payment_method' => 'required|string',
        ]);

        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Корзина пуста!');
        }

        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);

            if (!$product) {
                return redirect()->route('cart.index')->with('error', "Товар #$productId не найден.");
            }

            if ($product->stock < $item['quantity']) {
                return redirect()->route('cart.index')->with('error', "Недостаточно товара: {$product->name} (доступно {$product->stock}, в корзине {$item['quantity']})");
            }
        }

        $order = Order::create([
            'user_id' => Auth::id(),
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'country' => $validated['country'],
            'postal_code' => $validated['postal_code'],
            'shipping_method' => $validated['shipping_method_code'],
            'payment_method' => $validated['payment_method'],
            'total' => collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']),
            'status' => 'pending',
        ]);

        OrderAddress::create([
            'order_id'    => $order->id,
            'type'        => 'shipping',
            'full_name'   => $validated['full_name'],
            'phone'       => $validated['phone'],
            'address'     => $validated['address'],
            'city'        => $validated['city'],
            'country'     => $validated['country'],
            'postal_code' => $validated['postal_code'],
        ]);

        foreach ($cart as $productId => $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);

            $product = Product::find($productId);
            $product->decrement('stock', $item['quantity']);
        }

        session()->forget('cart');

        return redirect()->route('orders.show', $order->id)->with('success', 'Ваш заказ успешно оформлен!');
    }
}
