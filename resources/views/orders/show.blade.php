@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Детали заказа #{{ $order->id }}</h1>
    <p><strong>Общая сумма:</strong> {{ $order->total }} грн.</p>
    <p><strong>Статус:</strong> {{ $order->status }}</p>
    <!-- Добавьте дополнительную информацию о заказе, например, дату, список позиций и т.д. -->
@endsection
