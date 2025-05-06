<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Мини Магазин</title>
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    </head>
    <body>
        @include('partials.header')
        <div class="container">
            @yield('content')
        </div>
        @include('partials.footer')
        <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>
