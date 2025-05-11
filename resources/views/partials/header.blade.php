@vite(['resources/css/app.css', 'resources/js/app.js'])
<header>
    <nav>
        <a href="{{ route('home') }}">Главная</a>
        <form action="{{ route('product.search') }}" method="GET">
            <input type="text" name="query" placeholder="Поиск товаров...">
            <button type="submit">Найти</button>
        </form>
{{--        <a href="{{ route('product.search') }}">Поиск</a>--}}
        <a class="cart" href="{{ route('cart.index') }}">Корзина</a>
        {{-- Можно добавить ссылки на личный кабинет и регистрацию --}}
    </nav>
</header>
