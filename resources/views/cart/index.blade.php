@extends('layouts.app')

@section('title', __('ui.cart'))

@section('content')
    <div class="cart-container">
        <h1 class="cart-title">{{ __('ui.cart') }}</h1>

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
                    <div class="cart-item-image">
                        <img src="{{ asset('storage/' . $item['image']) }}"
                             alt="{{ $item['name'] }}"
                             style="object-fit: cover; width: 100px; height: 100px;">
                    </div>

                    <div class="cart-item-content">
                        <h3>{{ $item['name'] }}</h3>
                        <p class="price">
                            {{ __('ui.price') }}:
                            <strong>{{ number_format($item['price'], 2) }} {{ __('ui.currency_uah') }}</strong>
                        </p>
                        <p>{{ __('ui.quantity') }}:
                            <span class="quantity">{{ $item['quantity'] }}</span>
                        </p>
                    </div>

                    <div class="cart-item-actions">
                        <form action="{{ route('cart.remove', $id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-remove">
                                {{ __('ui.delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="empty-cart">
                    {{ __('ui.cart_empty') }}
                </div>
            @endforelse
        </div>

        @if(count($cart) > 0)
            <div class="cart-total">
                <p>
                    <strong>{{ __('ui.total') }}:</strong>
                    {{ number_format($total, 2) }} {{ __('ui.currency_uah') }}
                </p>
            </div>

            <div class="cart-checkout">
                <a href="{{ route('cart.checkout') }}" class="btn-checkout">
                    {{ __('ui.checkout') }}
                </a>
            </div>
        @endif
    </div>
@endsection
