<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

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
            ->with(['orderItems.product']) // Загружаем товары внутри заказа
            ->orderBy('created_at', 'desc')
            ->get();

        // Проверяем, есть ли заказы
        if ($orders->isEmpty()) {
            return view('dashboard.index', compact('user'))->with('info', 'У вас пока нет заказов.');
        }

        return view('dashboard.index', compact('user', 'orders'));
    }
}
