@vite(['resources/css/app.css', 'resources/js/app.js'])

<header>
    <nav class="header-nav">
        <!-- Левая часть: Главная и Корзина -->
        <div class="header-left">
            <a href="{{ route('home') }}">Главная</a>
            <a href="{{ route('cart.index') }}">Корзина</a>
        </div>

        <!-- Средняя часть: Поиск с ИСПРАВЛЕННОЙ иконкой -->
        <div class="search-form">
            <form action="{{ route('product.search') }}" method="GET">
                <span class="search-icon">🔍</span>
                <input type="text" name="query" placeholder="Поиск товаров..." >
                <button type="submit">Найти</button>
            </form>
        </div>

        <!-- Правая часть: Аутентификация -->
        <div class="header-right">
            @guest
                <a href="{{ route('login') }}" class="auth-btn btn-login">Вход</a>
                <a href="{{ route('register') }}" class="auth-btn btn-register">Регистрация</a>
            @else
                <a href="{{ route('dashboard') }}" class="auth-btn btn-account">Личный кабинет</a>
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="auth-btn btn-logout">Выход</button>
                </form>
            @endguest
        </div>
    </nav>
</header>
