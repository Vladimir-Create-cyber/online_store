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
        // Проверяем, авторизован ли пользователь
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Для оформления заказа необходимо войти в систему.');
        }

        $user = Auth::user();

        // Проверяем, есть ли товары в корзине
        $cartItems = CartItem::where('user_id', $user->id)->with('product')->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Корзина пуста.');
        }

        // Рассчитываем итоговую сумму заказа
        $calculatedTotal = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);

        // Используем транзакцию для надежного сохранения данных
        DB::transaction(function () use ($user, $request, $calculatedTotal, $cartItems) {
            // Создаём заказ
            $order = Order::create([
                'user_id'          => $user->id,
                'total'            => $calculatedTotal,
                'shipping_address' => $request->input('address'),
                'status'           => 'pending',
            ]);

            // Формируем массив данных для массовой вставки
            $orderItems = $cartItems->map(fn($cartItem) => [
                'order_id'   => $order->id,
                'product_id' => $cartItem->product_id,
                'quantity'   => $cartItem->quantity,
                'price'      => $cartItem->product->price,
                'created_at' => now(),
                'updated_at' => now(),
            ])->toArray();

            OrderItem::insert($orderItems);

            // Очищаем корзину после оформления заказа
            CartItem::where('user_id', $user->id)->delete();

            // Отправляем уведомление (если оно настроено)
            \App\Jobs\SendOrderNotification::dispatch($order);
        });

        return redirect()->route('orders.show', Order::latest()->first()->id)
            ->with('success', 'Заказ успешно оформлен!');
    }

    // Метод для отображения подробностей заказа
    public function show($orderId)
    {
        $order = Order::with('orderItems.product')->findOrFail($orderId);
        return view('orders.show', compact('order'));
    }
}
