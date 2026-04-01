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
     * Отображает форму входа.
     */
    public function showLoginForm()
    {
        $unreadCount = $this->getUnreadCount();
        return view('auth.login', compact('unreadCount'));
    }

    /**
     * Выполняет вход пользователя.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = \App\Models\User::where('email', $credentials['email'])->first();

        if ($user && $user->is_blocked) {
            return back()->withErrors([
                'email' => 'Ваш аккаунт заблокирован администрацией.',
            ])->withInput();
        }

        if ($user && \Hash::check($credentials['password'], $user->password)) {
            Auth::login($user);
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Неверные данные для входа.',
        ])->withInput();
    }


    /**
     * Отображает форму регистрации.
     */
    public function showRegisterForm()
    {
        $unreadCount = $this->getUnreadCount();
        return view('auth.register', compact('unreadCount'));
    }

    /**
     * Регистрирует нового пользователя.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        Auth::login($user);

        return redirect()->intended('/');
    }

    /**
     * Выполняет выход пользователя.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
