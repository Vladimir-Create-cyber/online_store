<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Notification; // Добавили импорт модели

class AuthController extends Controller
{
    // Новая реализация метода для получения количества непрочитанных уведомлений
    private function getUnreadCount()
    {
        if (!Auth::check()) {
            return 0;
        }

        // Прямой запрос к базе данных
        return Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();
    }

    // Форма входа (GET)
    public function showLoginForm()
    {
        $unreadCount = $this->getUnreadCount();
        return view('auth.login', compact('unreadCount'));
    }

    // Обработка входа (POST)
    public function login(Request $request)
    {
        // Валидация данных
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Попытка входа
        if (Auth::attempt($credentials)) {
            // Обновляем сессию для защиты от фиксации сессии
            $request->session()->regenerate();

            return redirect()->intended('/');
        }

        // Если не удалось — возвращаем ошибку
        return back()->withErrors([
            'email' => 'Неверные данные для входа.',
        ])->withInput();
    }

    // Форма регистрации (GET)
    public function showRegisterForm()
    {
        $unreadCount = $this->getUnreadCount();
        return view('auth.register', compact('unreadCount'));
    }

    // Обработка регистрации (POST)
    public function register(Request $request)
    {
        // Валидация переданных данных
        $validator = Validator::make($request->all(), [
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email|max:255',
            'password'              => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Создаём нового пользователя
        $user = User::create([
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        // Автоматический вход для нового пользователя
        Auth::login($user);

        return redirect()->intended('/');
    }

    // Выход (POST)
    public function logout(Request $request)
    {
        Auth::logout();

        // Инвалидация сессии и регенерация CSRF-токена
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
