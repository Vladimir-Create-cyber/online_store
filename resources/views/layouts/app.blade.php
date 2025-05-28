<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Мини Магазин</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        @include('partials.header')
        <div class="container">
            @yield('content')
        </div>
        @include('partials.footer')
    </body>
</html>
