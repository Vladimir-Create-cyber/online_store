<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель управления | {{ config('app.name') }}</title>
    @vite(['resources/css/admin.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Временные стили для диагностики */
        .debug * {
            outline: 1px solid red !important;
        }
    </style>
</head>
<body class="admin-body">
<div class="admin-layout">
    <!-- Сайдбар -->
    <div class="admin-sidebar">
        <div class="admin-brand">
            <i class="fas fa-cogs"></i>
            <span>ADMIN PANEL</span>
        </div>

        <nav class="admin-menu">
            <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Главная</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-shopping-bag"></i>
                <span>Товары</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-list-alt"></i>
                <span>Категории</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Пользователи</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-shopping-cart"></i>
                <span>Заказы</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-chart-bar"></i>
                <span>Аналитика</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-cog"></i>
                <span>Настройки</span>
            </a>
        </nav>
    </div>

    <!-- Основной контент -->
    <div class="admin-content">
        <!-- Шапка -->
        <header class="admin-header">
            <div class="admin-header-content">
                <div class="admin-info">
                    <div class="admin-avatar">
                        @if(Auth::guard('admin')->user()->avatar_url)
                            <img src="{{ Auth::guard('admin')->user()->avatar_url }}"
                                 alt="{{ Auth::guard('admin')->user()->name }}"
                                 class="admin-avatar-img">
                        @else
                            <div class="avatar-placeholder">
                                {{ substr(Auth::guard('admin')->user()->name, 0, 1) }}
                            </div>
                        @endif
                    </div>

                    <div class="admin-details">
                        <span class="admin-name">{{ Auth::guard('admin')->user()->name }}</span>
                        <span class="admin-role">Супер администратор</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.logout') }}" class="logout-form">
                    @csrf
                    <button type="submit" class="logout-btn" title="Выйти из системы">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="logout-text">Выйти</span>
                    </button>
                </form>
            </div>
        </header>

        <!-- Основной контент страницы -->
        <main class="admin-main">
            @yield('content')
        </main>
    </div>
</div>

@yield('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@vite(['resources/js/admin.js'])
</body>
</html>
