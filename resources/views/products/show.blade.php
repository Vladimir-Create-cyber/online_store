@extends('layouts.app')

@section('content')
    <h1>{{ $product->name }}</h1>
    <div class="product-details">
        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
        <p>{{ $product->description }}</p>
        <p>Цена: {{ $product->price }} грн.</p>
        {{-- Здесь можно добавить кнопку для добавления в корзину --}}
    </div>

    {{-- Например, можно вывести отзывы, кнопки "лайк" и т.д. --}}
@endsection
