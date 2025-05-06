@extends('layouts.app')

@section('content')
    <h1>Корзина</h1>

    @if(session('success'))
        <p class="alert alert-success">{{ session('success') }}</p>
    @endif

    @if(session('error'))
        <p class="alert alert-danger">{{ session('error') }}</p>
    @endif

    <div class="cart-items">
        @foreach($cart as $id => $item)
            <div class="cart-item">
                <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] }}">
                <h3>{{ $item['name'] }}</h3>
                <p>Цена: {{ $item['price'] }} руб.</p>
                <p>Количество: {{ $item['quantity'] }}</p>
                <form action="{{ route('cart.remove', $id) }}" method="get">
                    <button type="submit">Удалить</button>
                </form>
            </div>
        @endforeach
    </div>

    <form action="{{ route('cart.checkout') }}" method="post">
        @csrf
        <button type="submit">Оформить заказ</button>
    </form>
@endsection
