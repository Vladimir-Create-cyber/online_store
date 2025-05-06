@extends('layouts.app')

@section('content')
    <h1>Оформление заказа</h1>

    <p>Вы собираетесь оформить заказ на сумму {{ collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']) }} грн.</p>

    <form action="{{ route('cart.checkout') }}" method="post">
        @csrf
        <button type="submit">Подтвердить заказ</button>
    </form>
@endsection
