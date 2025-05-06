@extends('layouts.app')

@section('content')
    <h1>Результаты поиска по запросу: "{{ $query }}"</h1>
    <div class="products">
        @if($products->count())
            @foreach($products as $product)
                <div class="product">
                    <a href="{{ route('product.show', $product->slug) }}">
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                        <h2>{{ $product->name }}</h2>
                        <p>{{ $product->price }} руб.</p>
                    </a>
                </div>
            @endforeach
        @else
            <p>По вашему запросу ничего не найдено.</p>
        @endif
    </div>

    {{ $products->links() }}
@endsection
