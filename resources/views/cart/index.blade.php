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
        @forelse($cart as $id => $item)
            <div class="cart-item">
                <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] }}">
                <h3>{{ $item['name'] }}</h3>
                <p class="price">Цена: <strong>{{ number_format($item['price'], 2) }} руб.</strong></p>
                <p>Количество: {{ $item['quantity'] }}</p>
                <form action="{{ route('cart.remove', $id) }}" method="get">
                    <button type="submit" class="btn-remove">Удалить</button>
                </form>
            </div>
        @empty
            <p class="empty-cart">Ваша корзина пуста.</p>
        @endforelse
    </div>

    @if($cart)
        <form action="{{ route('cart.checkout') }}" method="post">
            @csrf
            <button type="submit" class="btn-checkout">Оформить заказ</button>
        </form>
    @endif
@endsection
