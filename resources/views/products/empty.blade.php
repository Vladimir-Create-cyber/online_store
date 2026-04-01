@extends('layouts.app')

@section('content')
    <div class="alert alert-warning">
        <h2>{{ __('ui.catalog_empty') }}</h2>
        <p>{{ __('ui.no_products_available') }}</p>
    </div>
@endsection
