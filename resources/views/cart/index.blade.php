{{-- resources/views/cart/index.blade.php --}}

@extends('layouts.app')

@section('title', 'Корзина')

@section('content')
    <div class="cart-container">
        <h1 class="cart-title">Корзина</h1>

        {{-- Флеш-сообщения --}}
        @if(session('success'))
            <div class="cart-alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="cart-alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        {{-- Список товаров --}}
        <div class="cart-items">
            @forelse($cart as $id => $item)
                <div class="cart-item">
                    <div class="cart-item-image">
                        <img src="{{ asset('storage/' . $item['image']) }}"
                             alt="{{ $item['name'] }}"
                             style="object-fit: cover; width: 100px; height: 100px;">
                    </div>

                    <div class="cart-item-content">
                        <h3>{{ $item['name'] }}</h3>
                        <p class="price">
                            Цена:
                            <strong>{{ number_format($item['price'], 2) }} грн</strong>
                        </p>
                        <p>Количество:
                            <span class="quantity">{{ $item['quantity'] }}</span>
                        </p>
                    </div>

                    <div class="cart-item-actions">
                        <form action="{{ route('cart.remove', $id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-remove">
                                Удалить
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="empty-cart">
                    Ваша корзина пуста.
                </div>
            @endforelse
        </div>

        {{-- Кнопка “Оформить заказ” --}}
        @if(count($cart) > 0)
            <div class="cart-total">
                <p>
                    <strong>Итого:</strong>
                    {{ number_format($total, 2) }} грн
                </p>
            </div>

            <div class="cart-checkout">
                <a href="{{ route('cart.checkout') }}" class="btn-checkout">
                    Оформить заказ
                </a>
            </div>
        @endif
    </div>
@endsection
