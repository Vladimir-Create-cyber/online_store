<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order; // правильный импорт модели
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()->with('user');

        if ($request->filled('order_id')) {
            $query->where('id', $request->order_id);
        }

        if ($request->filled('user_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->user_name . '%');
            });
        }

        if ($request->filled('user_email')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('email', 'like', '%' . $request->user_email . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'orderItems.product', 'shippingAddress', 'billingAddress']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,completed,cancelled',
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        if ($oldStatus === $newStatus) {
            return back()->with('info', 'Статус не изменился.');
        }

        try {
            DB::transaction(function () use ($order, $oldStatus, $newStatus) {
                // Возврат товара при отмене
                if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                    foreach ($order->orderItems as $item) {
                        if ($item->product) {
                            $item->product->increment('stock', $item->quantity);
                        }
                    }
                }

                // Повторное списание при смене из cancelled обратно
                if ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
                    foreach ($order->orderItems as $item) {
                        $product = $item->product;
                        if ($product) {
                            if ($product->stock < $item->quantity) {
                                throw new \Exception("Недостаточно товара: {$product->name} (нужно {$item->quantity}, в наличии {$product->stock})");
                            }
                            $product->decrement('stock', $item->quantity);
                        }
                    }
                }

                $order->status = $newStatus;
                $order->save();
            });

            return redirect()
                ->route('admin.orders.show', $order)
                ->with('success', 'Статус заказа обновлён.');
        } catch (\Throwable $e) {
            \Log::error('Ошибка смены статуса заказа: ' . $e->getMessage());
            return back()->with('error', 'Ошибка при смене статуса: ' . $e->getMessage());
        }
    }
}
