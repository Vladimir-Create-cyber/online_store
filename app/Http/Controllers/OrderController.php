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
     * Вывод списка заказов текущего пользователя.
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
     * Создание нового заказа из корзины.
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Для оформления заказа необходимо войти.');
        }

        // Валидация формы заказа
        $validated = $request->validate([
            'full_name'       => 'required|string|max:255',
            'phone'           => 'required|string|max:20',
            'address'         => 'required|string|max:255',
            'city'            => 'required|string|max:100',
            'country'         => 'nullable|string|max:100',
            'postal_code'     => 'required|string|max:20',
            'shipping_method' => 'required|string|max:100',
            'payment_method'  => 'required|string|max:100',
        ]);

        $user = Auth::user();

        // Получаем все товары в корзине пользователя
        $cartItems = CartItem::with('product')->where('user_id', $user->id)->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Корзина пуста.');
        }

        // Проверка наличия и достаточного количества товара
        $unavailable = $cartItems->filter(fn($item) => !$item->product || $item->product->stock < $item->quantity);
        if ($unavailable->isNotEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Некоторые товары недоступны.');
        }

        // Расчёт общей суммы заказа
        $total = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);

        try {
            // Транзакция сохранения заказа
            $order = DB::transaction(function () use ($user, $validated, $total, $cartItems) {
                // Создание записи заказа
                $order = Order::create([
                    'user_id'          => $user->id,
                    'total'            => $total,
                    'full_name'        => $validated['full_name'],
                    'phone'            => $validated['phone'],
                    'shipping_address' => $validated['address'],
                    'city'             => $validated['city'],
                    'country'          => $validated['country'],
                    'postal_code'      => $validated['postal_code'],
                    'shipping_method'  => $validated['shipping_method'],
                    'payment_method'   => $validated['payment_method'],
                    'status'           => 'pending',
                ]);

                // Сохранение товаров заказа
                foreach ($cartItems as $item) {
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $item->product_id,
                        'quantity'   => $item->quantity,
                        'price'      => $item->product->price,
                    ]);

                    $item->product->decrement('stock', $item->quantity);
                }

                // Очистка корзины
                CartItem::where('user_id', $user->id)->delete();

                // Отправка уведомления (если включена)
                if (class_exists(\App\Jobs\SendOrderNotification::class)) {
                    \App\Jobs\SendOrderNotification::dispatch($order);
                }

                return $order;
            });

            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Заказ успешно оформлен!');
        } catch (\Throwable $e) {
            \Log::error('Ошибка при оформлении заказа: ' . $e->getMessage());
            return back()->with('error', 'Ошибка при оформлении заказа. Попробуйте позже.');
        }
    }

    /**
     * Просмотр одного заказа.
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
     * Отмена заказа пользователем.
     */
    public function cancel($orderId)
    {
        try {
            // Проверка: заказ должен быть создан этим пользователем и быть в ожидании
            $order = Order::where('id', $orderId)
                ->where('user_id', Auth::id())
                ->where('status', 'pending')
                ->firstOrFail();

            DB::transaction(function () use ($order) {
                foreach ($order->orderItems as $item) {
                    if ($item->product) {
                        $item->product->increment('stock', $item->quantity);
                    }
                }

                $order->update([
                    'status' => 'cancelled',
                ]);
            });

            return redirect()->route('orders.index')->with('success', 'Заказ успешно отменён и товары возвращены на склад.');
        } catch (ModelNotFoundException) {
            return redirect()->route('orders.index')->with('error', 'Заказ не найден или уже обработан.');
        } catch (\Throwable $e) {
            \Log::error('Ошибка при отмене заказа: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Произошла ошибка при отмене заказа. Попробуйте позже.');
        }
    }

}
