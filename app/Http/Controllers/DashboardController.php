<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Notification; // Импорт модели

class DashboardController extends Controller
{
    public function index()
    {
        // Проверяем, авторизован ли пользователь
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Пожалуйста, войдите в систему.');
        }

        $user = Auth::user();

        // Загружаем заказы пользователя с товарами внутри
        $orders = Order::where('user_id', $user->id)
            ->with(['orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Загружаем уведомления пользователя
        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        // Получаем количество непрочитанных уведомлений через прямой запрос
        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        $totalOrders = Order::where('user_id', $user->id)->count();
        $pendingOrders = Order::where('user_id', $user->id)->where('status', 'pending')->count();
        $completedOrders = Order::where('user_id', $user->id)->where('status', 'completed')->count();

        return view('dashboard.index', compact(
            'user',
            'orders',
            'notifications',
            'unreadCount',
            'totalOrders',
            'pendingOrders',
            'completedOrders'
        ));
    }
}
