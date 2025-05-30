@extends('layouts.app')

@section('content')
    <h1 class="page-title">Каталог товаров</h1>
    <div class="container">
        <div class="product-grid">
            @foreach ($products as $product)
                <div class="product-card">
                    <a href="{{ route('product.show', $product->slug) }}">
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                    </a>
                    <h2 class="product-name">{{ $product->name }}</h2>
                    <p class="product-description">{{ Str::limit($product->description, 60) }}</p>
                    <p class="product-price">{{ number_format($product->price, 2) }} грн.</p>
                    <div class="product-links">
                        <a href="{{ route('product.show', $product->slug) }}" class="btn-details">Подробнее</a>
                        <form action="{{ route('cart.add', $product->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-cart">В корзину</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="pagination">
        {{ $products->links('vendor.pagination.default') }}
    </div>
@endsection
