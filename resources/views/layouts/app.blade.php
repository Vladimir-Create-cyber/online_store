<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    <link rel="icon" href="{{ asset('favicon.ico') }}">

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

        @stack('scripts')
    </body>
</html>
