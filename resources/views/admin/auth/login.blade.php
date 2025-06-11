<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход для администратора | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="admin-auth-container">
    <div class="auth-card">
        <div class="auth-logo">
            <!-- Замените на путь к вашему лого -->
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#3b82f6" width="60" height="60">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
            </svg>
        </div>

        <h1 class="auth-title">Панель управления</h1>

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf

            <!-- Email -->
            <div class="form-group">
                <label for="email" class="form-label">Адрес электронной почты</label>
                <div class="input-with-icon">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="#64748b">
                        <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                    </svg>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        placeholder="your@email.com"
                        required
                        autofocus
                    >
                </div>
                @error('email')
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password" class="form-label">Пароль</label>
                <div class="input-with-icon">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="#64748b">
                        <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                    </svg>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="••••••••"
                        required
                    >
                    <!-- Кнопка показать/скрыть пароль -->
                    <button type="button" class="password-toggle">
                        <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
                @error('password')
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="remember-group">
                <input
                    type="checkbox"
                    id="remember"
                    name="remember"
                    class="form-checkbox"
                >
                <label for="remember">Запомнить меня</label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-auth">
                <svg class="btn-icon" viewBox="0 0 24 24" fill="white">
                    <path d="M10.09 15.59L11.5 17l5-5-5-5-1.41 1.41L12.67 11H3v2h9.67l-2.58 2.59zM19 3H5c-1.11 0-2 .9-2 2v4h2V5h14v14H5v-4H3v4c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/>
                </svg>
                Войти в систему
            </button>
        </form>

        <!-- Footer Links -->
        <div class="auth-footer">
            <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved</p>

            <div class="auth-links">
                <a href="#">
                    <svg class="link-icon" viewBox="0 0 24 24" fill="#3b82f6">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z"/>
                    </svg>
                    Забыли пароль?
                </a>

                <a href="{{ url('/') }}">
                    <svg class="link-icon" viewBox="0 0 24 24" fill="#3b82f6">
                        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                    </svg>
                    На главную
                </a>
            </div>
        </div>
    </div>
</div>
<script type="module">
    import { initPasswordToggle } from '{{ Vite::asset("resources/js/password-toggle.js") }}';

    document.addEventListener('DOMContentLoaded', () => {
        initPasswordToggle();

        // Дополнительная анимация для глазика
        document.querySelectorAll('.password-toggle').forEach(toggle => {
            toggle.addEventListener('click', function() {
                this.animate([
                    { transform: 'translateY(-50%) scale(1)' },
                    { transform: 'translateY(-50%) scale(1.2)' },
                    { transform: 'translateY(-50%) scale(1)' }
                ], {
                    duration: 300,
                    easing: 'ease-in-out'
                });
            });
        });
    });
</script>
</body>
</html>
