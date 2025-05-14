<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Форма входа (GET)
    public function showLoginForm()
    {
        return view('auth.login');
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
        return view('auth.register');
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
