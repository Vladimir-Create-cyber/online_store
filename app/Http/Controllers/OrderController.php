<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Для оформления заказа необходимо войти в систему.');
        }

        $request->validate([
            'address' => 'required|string|max:255'
        ]);

        $user = Auth::user();
        $cartItems = CartItem::where('user_id', $user->id)->with(['product'])->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Корзина пуста.');
        }

        if ($cartItems->contains(fn($item) => !$item->product)) {
            return redirect()->route('cart.index')->with('error', 'Некоторые товары больше не доступны.');
        }

        $calculatedTotal = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);

        DB::transaction(function () use ($user, $request, $calculatedTotal, $cartItems) {
            $order = Order::create([
                'user_id'          => $user->id,
                'total'            => $calculatedTotal,
                'shipping_address' => $request->input('address'),
                'status'           => 'pending',
            ]);

            $orderItems = $cartItems->map(fn($cartItem) => [
                'order_id'   => $order->id,
                'product_id' => $cartItem->product_id,
                'quantity'   => $cartItem->quantity,
                'price'      => $cartItem->product->price,
                'created_at' => now(),
                'updated_at' => now(),
            ])->toArray();

            OrderItem::insert($orderItems);
            CartItem::where('user_id', $user->id)->delete();
            \App\Jobs\SendOrderNotification::dispatch($order);
        });

        return redirect()->route('orders.show', $order->id)->with('success', 'Заказ успешно оформлен!');
    }

    public function show($orderId)
    {
        $order = Order::with('orderItems.product')->findOrFail($orderId);
        return view('orders.show', compact('order'));
    }
}
