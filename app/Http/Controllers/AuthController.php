<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Показать форму входа в систему.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        $unreadCount = $this->getUnreadCount();
        return view('auth.login', compact('unreadCount'));
    }

    /**
     * Обработка отправки формы входа.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        // Валидация данных
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Получаем пользователя по email
        $user = \App\Models\User::where('email', $credentials['email'])->first();

        // Проверяем, заблокирован ли
        if ($user && $user->is_blocked) {
            return back()->withErrors([
                'email' => 'Ваш аккаунт заблокирован администрацией.',
            ])->withInput();
        }

        // Попытка входа
        if ($user && \Hash::check($credentials['password'], $user->password)) {
            Auth::login($user);
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        // Ошибка аутентификации
        return back()->withErrors([
            'email' => 'Неверные данные для входа.',
        ])->withInput();
    }


    /**
     * Показать форму регистрации пользователя.
     *
     * @return \Illuminate\View\View
     */
    public function showRegisterForm()
    {
        $unreadCount = $this->getUnreadCount();
        return view('auth.register', compact('unreadCount'));
    }

    /**
     * Обработка регистрации нового пользователя.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        // Валидация входных данных
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Создание пользователя
        $user = User::create([
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        // Автоматический вход
        Auth::login($user);

        return redirect()->intended('/');
    }

    /**
     * Выход пользователя из системы.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // Инвалидация сессии и защита от CSRF
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
