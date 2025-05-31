@extends('layouts.app')

@section('content')
    <div class="cart-container">
        <h1 class="cart-title">Корзина</h1>

        {{-- Сообщения --}}
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

        <div class="cart-items">
            @forelse($cart as $id => $item)
                <div class="cart-item">
                    {{-- Изображение товара --}}
                    <div class="cart-item-image">
                        <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] }}" style="object-fit: cover">
                    </div>

                    {{-- Информация о товаре --}}
                    <div class="cart-item-content">
                        <h3>{{ $item['name'] }}</h3>
                        <p class="price">Цена: <strong>{{ number_format($item['price'], 2) }} грн.</strong></p>
                        <p>Количество: <span class="quantity">{{ $item['quantity'] }}</span></p>
                    </div>

                    {{-- Действия --}}
                    <div class="cart-item-actions">
                        <form action="{{ route('cart.remove', $id) }}" method="get">
                            @csrf
                            <button type="submit" class="btn-remove">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
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

        @if(count($cart) > 0)
            <div class="cart-checkout">
                <form action="{{ route('cart.checkout') }}" method="post">
                    @csrf
                    <button type="submit" class="btn-checkout">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        Оформить заказ
                    </button>
                </form>
            </div>
        @endif
    </div>
@endsection
