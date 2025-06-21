@extends('layouts.app')

@section('content')
    <h1 class="page-title">Каталог товаров</h1>

    <div class="container">
        <div class="product-grid">
            @foreach ($products as $product)
                <div class="product-card position-relative">
                    @if ($product->is_new)
                        <span class="badge-new">Новинка</span>
                    @endif

                    <a href="{{ route('product.show', $product->slug) }}">
                        @if($product->image)
                            <img
                                src="{{ asset('storage/' . $product->image) }}"
                                alt="{{ $product->name }}"
                                class="product-image"
                            >
                        @elseif($product->images->isNotEmpty())
                            <img
                                src="{{ asset('storage/' . $product->images->first()->path) }}"
                                alt="{{ $product->name }}"
                                class="product-image"
                            >
                        @else
                            <img
                                src="{{ asset('images/placeholder.png') }}"
                                alt="{{ $product->name }}"
                                class="product-image"
                            >
                        @endif
                    </a>

                    <h2 class="product-name">{{ $product->name }}</h2>

                    <p class="product-description">
                        {{ Str::limit($product->description, 60) }}
                    </p>

                    <p class="product-price">
                        @if ($product->sale_price)
                            <span class="text-muted text-decoration-line-through">
                                {{ number_format($product->price, 2) }} грн.
                            </span>
                            <span class="text-danger fw-bold ms-2">
                                {{ number_format($product->final_price, 2) }} грн.
                            </span>
                        @else
                            {{ number_format($product->final_price, 2) }} грн.
                        @endif
                    </p>

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

        <div class="pagination">
            {{ $products->links('vendor.pagination.default') }}
        </div>
    </div>
@endsection
