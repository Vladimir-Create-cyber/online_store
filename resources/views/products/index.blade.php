@extends('layouts.app')

@section('content')
    <h1 class="page-title">{{ __('ui.catalog_title') }}</h1>

    <div class="container">
        <div class="product-grid">
            @foreach ($products as $product)
                <div class="product-card position-relative">
                    @if ($product->is_new)
                        <span class="badge-new">{{ __('ui.new_badge') }}</span>
                    @endif

                    <a href="{{ route('product.show', $product->slug) }}">
                        <img
                            src="{{ $product->image_url }}"
                            alt="{{ $product->name }}"
                            class="product-image"
                        >
                    </a>

                    <h2 class="product-name">{{ $product->name }}</h2>

                    <p class="product-description">
                        {{ Str::limit($product->description, 60) }}
                    </p>

                    <p class="product-price">
                        @if ($product->sale_price)
                            <span class="text-muted text-decoration-line-through">
                                {{ number_format($product->price, 2) }} {{ __('ui.currency_uah') }}
                            </span>
                            <span class="text-danger fw-bold ms-2">
                                {{ number_format($product->final_price, 2) }} {{ __('ui.currency_uah') }}
                            </span>
                        @else
                            {{ number_format($product->final_price, 2) }} {{ __('ui.currency_uah') }}
                        @endif
                    </p>

                    <div class="product-links">
                        <a href="{{ route('product.show', $product->slug) }}" class="btn-details">{{ __('ui.details') }}</a>

                        <form action="{{ route('cart.add', $product->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn-cart">{{ __('ui.to_cart') }}</button>
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
