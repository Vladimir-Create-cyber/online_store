<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use App\Models\Product; // импорт модели Product
use App\Traits\HandlesUnreadNotifications;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderController extends Controller
{
    use HandlesUnreadNotifications;

    public function index()
    {
        // Получаем заказы текущего пользователя с загрузкой связанных товаров (orderItems)
        $orders = Order::where('user_id', Auth::id())
            ->with('orderItems.product')
            ->withCount(['orderItems as items_count'])
            ->latest()
            ->paginate(10);

        // Вычисляем статистику заказов для текущего пользователя
        $totalOrders = Order::where('user_id', Auth::id())->count();
        $pendingOrders = Order::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->count();
        $completedOrders = Order::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->count();

        return view('orders.index', [
            'orders'          => $orders,
            'unreadCount'     => $this->getUnreadCount(),
            'totalOrders'     => $totalOrders,
            'pendingOrders'   => $pendingOrders,
            'completedOrders' => $completedOrders,
        ]);
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Для оформления заказа необходимо войти в систему.');
        }

        // Валидация адреса доставки
        $validated = $request->validate([
            'address' => 'required|string|max:255'
        ]);

        $user = Auth::user();

        // Получаем товары в корзине с предварительной загрузкой продуктов
        $cartItems = CartItem::with('product')
            ->where('user_id', $user->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Корзина пуста.');
        }

        // Проверка доступности товаров (наличие на складе)
        $unavailableItems = $cartItems->filter(function ($item) {
            return !$item->product || $item->product->stock < $item->quantity;
        });

        if ($unavailableItems->isNotEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Некоторые товары недоступны или недостаточно на складе.');
        }

        // Расчет общей суммы заказа
        $calculatedTotal = $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        try {
            $order = DB::transaction(function () use ($user, $validated, $calculatedTotal, $cartItems) {
                // Создаем заказ
                $order = Order::create([
                    'user_id'          => $user->id,
                    'total'            => $calculatedTotal,
                    'shipping_address' => $validated['address'],
                    'status'           => 'pending',
                ]);

                // Создаем элементы заказа и обновляем остатки на складе товара
                foreach ($cartItems as $cartItem) {
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $cartItem->product_id,
                        'quantity'   => $cartItem->quantity,
                        'price'      => $cartItem->product->price,
                    ]);

                    // Уменьшаем количество товара на складе
                    $cartItem->product->decrement('stock', $cartItem->quantity);
                }

                // Очищаем корзину пользователя после оформления заказа
                CartItem::where('user_id', $user->id)->delete();

                // Отправляем уведомление о новом заказе (если класс задачи существует)
                if (class_exists(\App\Jobs\SendOrderNotification::class)) {
                    \App\Jobs\SendOrderNotification::dispatch($order);
                }

                return $order;
            });
        } catch (\Exception $e) {
            \Log::error('Order creation failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Произошла ошибка при оформлении заказа. Пожалуйста, попробуйте позже.');
        }

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Заказ успешно оформлен!');
    }

    public function show($orderId)
    {
        try {
            // Загружаем заказ с его элементами и связанными продуктами
            $order = Order::with('orderItems.product')
                ->findOrFail($orderId);
            $unreadCount = $this->getUnreadCount();

            // Проверка: пользователь может просматривать только свои заказы
            if ($order->user_id !== Auth::id()) {
                return redirect()->route('orders.index')
                    ->with('error', 'Вы не можете просматривать этот заказ.');
            }

            return view('orders.show', compact('order', 'unreadCount'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('orders.index')
                ->with('error', 'Заказ не найден.');
        }
    }
}
