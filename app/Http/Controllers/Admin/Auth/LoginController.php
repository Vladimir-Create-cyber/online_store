<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    /**
     * Отображает форму входа администратора.
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * Выполняет аутентификацию администратора.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $key = 'login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors([
                'email' => 'Слишком много попыток входа. Попробуйте позже.',
            ]);
        }

        if (Auth::guard('admin')->attempt(
            $request->only('email', 'password'),
            $request->filled('remember')
        )) {
            RateLimiter::clear($key);
            return redirect()->intended('/admin/dashboard');
        }

        RateLimiter::hit($key, 60);

        return back()->withErrors(['email' => 'Неверные учетные данные']);
    }

    /**
     * Завершает сессию администратора.
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        return redirect('/admin/login');
    }

    /**
     * Возвращает поле логина для аутентификации.
     */
    protected function username()
    {
        return 'email';
    }
}
