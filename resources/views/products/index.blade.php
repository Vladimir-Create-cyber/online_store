@extends('layouts.app')

@section('content')
    <h1>Каталог товаров</h1>
    <div class="products">
        @foreach($products as $product)
            <div class="product">
                <a href="{{ route('product.show', $product->slug) }}">
                    <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">
                    <h2>{{ $product->name }}</h2>
                    <p>{{ $product->price }} грн.</p>
                </a>
                <!-- Форма для добавления товара в корзину -->
                <form action="{{ route('cart.add', $product->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-add">
                        Добавить в корзину
                    </button>
                </form>
            </div>
        @endforeach
    </div>
    {{ $products->links() }}
@endsection
