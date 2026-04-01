@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.search_results_for', ['query' => $query]) }}</h1>
    <div class="products">
        @if($products->count())
            @foreach($products as $product)
                <div class="product">
                    <a href="{{ route('product.show', $product->slug) }}">
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                        <h2>{{ $product->name }}</h2>
                        <p>{{ $product->price }} {{ __('ui.currency_uah') }}</p>
                    </a>
                </div>
            @endforeach
        @else
            <p>{{ __('ui.nothing_found') }}</p>
        @endif
    </div>

    {{ $products->links() }}
@endsection
