@vite(['resources/css/app.css', 'resources/js/app.js'])

<header>
    <div class="w-full max-w-screen-xl mx-auto px-4 py-4">
        <nav class="flex justify-between items-center">
            <!-- Левая часть: Главная и Корзина -->
            <div class="flex items-center space-x-6">
                <a href="{{ route('home') }}" class="text-xl font-bold text-gray-800 hover:text-blue-600 transition-colors">
                    Главная
                </a>
                <a href="{{ route('cart.index') }}" class="text-xl font-bold text-gray-800 hover:text-blue-600 transition-colors">
                    Корзина
                </a>
            </div>

            <!-- Средняя часть: Поиск -->
            <div class="flex flex-grow justify-center mx-4">
                <form action="{{ route('product.search') }}" method="GET" class="flex w-full max-w-lg">
                    <input type="text"
                           name="query"
                           placeholder="Поиск товаров..."
                           class="w-full border border-gray-300 rounded-l px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">
                    <button type="submit"
                            class="bg-blue-500 text-white px-4 py-2 rounded-r hover:bg-blue-600 transition-colors">
                        Найти
                    </button>
                </form>
            </div>

            <!-- Правая часть: Аутентификация -->
            <div class="flex items-center space-x-6">
                @guest
                    <a href="{{ route('login') }}" class="text-xl font-bold text-gray-800 hover:text-blue-600 transition-colors">
                        Вход
                    </a>
                    <a href="{{ route('register') }}" class="text-xl font-bold text-gray-800 hover:text-blue-600 transition-colors">
                        Регистрация
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold text-gray-800 hover:text-blue-600 transition-colors">
                        Личный кабинет
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-xl font-bold text-gray-800 hover:text-blue-600 transition-colors">
                            Выход
                        </button>
                    </form>
                @endguest
            </div>
        </nav>
    </div>
</header>
