<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>

    <!-- Подключение CSS/JS через Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Favicon (добавьте если есть) -->
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <!-- Дополнительные мета-теги -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
@include('partials.header')

<main class="container">
    @hasSection('dashboard-sidebar')
        <div class="dashboard-wrapper">
            <aside class="dashboard-sidebar">
                @yield('dashboard-sidebar')
            </aside>
            <div class="dashboard-main">
                @yield('content')
            </div>
        </div>
    @else
        @yield('content')
    @endif
</main>

@include('partials.footer')

<!-- Дополнительные скрипты можно добавить здесь -->
</body>
</html>
