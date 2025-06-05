@extends('layouts.app')

@section('title', 'История заказов')

@section('content')
    <div class="orders-container">
        <h1>Ваши заказы</h1>

        <ul class="order-list">
            @forelse($orders as $order)
                <li>
                    Заказ #{{ $order->id }} — <strong>{{ $order->status }}</strong>
                    <a href="{{ route('orders.show', $order->id) }}">Подробнее</a>
                </li>
            @empty
                <li>У вас пока нет заказов.</li>
            @endforelse
        </ul>
    </div>
@endsection
