<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use App\Models\Product;
use App\Traits\HandlesUnreadNotifications;

class OrderController extends Controller
{
    use HandlesUnreadNotifications;

    /**
     * Показ списка заказов пользователя.
     */
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with('orderItems.product')
            ->withCount(['orderItems as items_count'])
            ->latest()
            ->paginate(10);

        return view('orders.index', [
            'orders'          => $orders,
            'unreadCount'     => $this->getUnreadCount(),
            'totalOrders'     => $orders->total(),
            'pendingOrders'   => Order::where('user_id', Auth::id())->where('status', 'pending')->count(),
            'completedOrders' => Order::where('user_id', Auth::id())->where('status', 'completed')->count(),
        ]);
    }

    /**
     * Оформление нового заказа из корзины.
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Для оформления заказа необходимо войти.');
        }

        $validated = $request->validate([
            'address' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $cartItems = CartItem::with('product')->where('user_id', $user->id)->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Корзина пуста.');
        }

        // Проверка доступности
        $unavailable = $cartItems->filter(fn($item) => !$item->product || $item->product->stock < $item->quantity);
        if ($unavailable->isNotEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Некоторые товары недоступны.');
        }

        $total = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);

        try {
            $order = DB::transaction(function () use ($user, $validated, $total, $cartItems) {
                $order = Order::create([
                    'user_id'          => $user->id,
                    'total'            => $total,
                    'shipping_address' => $validated['address'],
                    'status'           => 'pending',
                ]);

                foreach ($cartItems as $cartItem) {
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $cartItem->product_id,
                        'quantity'   => $cartItem->quantity,
                        'price'      => $cartItem->product->price,
                    ]);

                    $cartItem->product->decrement('stock', $cartItem->quantity);
                }

                CartItem::where('user_id', $user->id)->delete();

                if (class_exists(\App\Jobs\SendOrderNotification::class)) {
                    \App\Jobs\SendOrderNotification::dispatch($order);
                }

                return $order;
            });
        } catch (\Throwable $e) {
            \Log::error('Ошибка при оформлении заказа: ' . $e->getMessage());
            return back()->with('error', 'Ошибка при оформлении заказа. Попробуйте позже.');
        }

        return redirect()->route('orders.show', $order->id)->with('success', 'Заказ успешно оформлен!');
    }

    /**
     * Просмотр деталей конкретного заказа.
     */
    public function show($orderId)
    {
        try {
            $order = Order::with('orderItems.product')->findOrFail($orderId);

            if ($order->user_id !== Auth::id()) {
                return redirect()->route('orders.index')->with('error', 'Этот заказ не принадлежит вам.');
            }

            return view('orders.show', [
                'order' => $order,
                'unreadCount' => $this->getUnreadCount(),
            ]);
        } catch (ModelNotFoundException) {
            return redirect()->route('orders.index')->with('error', 'Заказ не найден.');
        }
    }

    /**
     * Отмена заказа пользователем (если заказ в статусе ожидания).
     */
    public function cancel($orderId)
    {
        try {
            $order = Order::where('id', $orderId)
                ->where('user_id', Auth::id())
                ->where('status', 'pending')
                ->firstOrFail();

            DB::transaction(function () use ($order) {
                // Возврат товара на склад
                foreach ($order->orderItems as $item) {
                    if ($item->product) {
                        $item->product->increment('stock', $item->quantity);
                    }
                }

                // Обновление статуса заказа
                $order->status = 'cancelled';
                $order->save();
            });

            return back()->with('success', 'Заказ успешно отменён.');
        } catch (ModelNotFoundException) {
            return redirect()->route('orders.index')->with('error', 'Невозможно отменить заказ.');
        } catch (\Throwable $e) {
            \Log::error('Ошибка при отмене заказа: ' . $e->getMessage());
            return back()->with('error', 'Произошла ошибка. Попробуйте позже.');
        }
    }
}
