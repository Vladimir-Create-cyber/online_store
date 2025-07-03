<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}"> <!-- Добавлен CSRF-токен -->
        <title>Панель управления | {{ config('app.name') }}</title>

        @vite(['resources/css/admin.css'])
        <!-- Подключаем только необходимые иконки для оптимизации -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/solid.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/fontawesome.min.css">

        @stack('styles')

    </head>
    <body class="admin-body @if(config('app.debug')) debug @endif">
        <div class="admin-layout">
            <!-- Сайдбар -->
            <aside class="admin-sidebar">
                <div class="admin-brand">
                    <i class="fas fa-cogs"></i>
                    <span>ADMIN PANEL</span>
                </div>

                <nav class="admin-menu">
                    <!-- Используем route() для безопасного формирования URL -->
                    <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home"></i>
                        <span>Главная</span>
                    </a>

                    <a href="{{ route('admin.products.index') }}" class="menu-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Товары</span>
                    </a>

                    <a href="{{ route('admin.categories.index') }}" class="menu-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <i class="fas fa-list-alt"></i>
                        <span>Категории</span>
                    </a>

                    <a href="{{ route('admin.orders.index') }}" class="menu-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Заказы</span>
                    </a>

                    <a href="{{ route('admin.shipping_methods.index') }}" class="menu-item {{ request()->routeIs('admin.shipping_methods.*') ? 'active' : '' }}">
                        <i class="fas fa-truck"></i>
                        <span>Способы доставки</span>
                    </a>

                    <a href="{{ route('admin.reviews.index') }}" class="menu-item {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                        <i class="fas fa-star"></i>
                        <span>Отзывы</span>
                    </a>



                    <!-- Заглушки для будущих разделов -->
                    <a href="#" class="menu-item disabled">
                        <i class="fas fa-users"></i>
                        <span>Пользователи</span>
                    </a>

                    <a href="#" class="menu-item disabled">
                        <i class="fas fa-chart-bar"></i>
                        <span>Аналитика</span>
                    </a>
                    <a href="#" class="menu-item disabled">
                        <i class="fas fa-cog"></i>
                        <span>Настройки</span>
                    </a>
                </nav>
            </aside>

            <!-- Контент -->
            <div class="admin-content">
                <!-- Шапка -->
                <header class="admin-header">
                    <div class="admin-header-content">
                        <div class="admin-info">
                            <div class="admin-avatar">
                                @auth('admin')
                                    @if(Auth::guard('admin')->user()->avatar_url)
                                        <img src="{{ Auth::guard('admin')->user()->avatar_url }}"
                                             alt="{{ Auth::guard('admin')->user()->name }}"
                                             class="admin-avatar-img">
                                    @else
                                        <div class="avatar-placeholder">
                                            {{ substr(Auth::guard('admin')->user()->name, 0, 1) }}
                                        </div>
                                    @endif
                                @endauth
                            </div>

                            <div class="admin-details">
                                @auth('admin')
                                    <span class="admin-name">{{ Auth::guard('admin')->user()->name }}</span>
                                    <span class="admin-role">
                                        {{ Auth::guard('admin')->user()->is_super_admin ? 'Супер администратор' : 'Администратор' }}
                                    </span>
                                @endauth
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.logout') }}" class="logout-form">
                            @csrf
                            <button type="submit" class="logout-btn" title="Выйти">
                                <i class="fas fa-sign-out-alt"></i>
                                <span class="logout-text">Выйти</span>
                            </button>
                        </form>
                    </div>
                </header>

                <!-- Основная часть -->
                <main class="admin-main">
                    @yield('content')
                </main>
            </div>
        </div>

        @stack('scripts')

        <!-- Подключаем Chart.js только если он нужен на странице -->
        @stack('chart-scripts')
        @if(request()->routeIs('admin.dashboard'))
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        @endif

        @vite(['resources/js/admin.js'])
    </body>
</html>
