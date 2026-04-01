@extends('admin.layouts.app')

@section('content')
    <div class="dashboard-container">
        <div class="dashboard-header d-flex justify-content-between align-items-center">
            <h1>{{ __('ui.product_management') }}</h1>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i> {{ __('ui.add_product') }}
            </a>
        </div>

        <div class="dashboard-content mt-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">{{ __('ui.products_list') }}</h2>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>{{ __('ui.product_name') }}</th>
                                <th>{{ __('ui.price') }}</th>
                                <th>{{ __('ui.stock') }}</th>
                                <th>{{ __('ui.status') }}</th>
                                <th>{{ __('ui.actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td>{{ $product->id }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ number_format($product->price, 2) }} {{ __('ui.currency_uah') }}</td>
                                    <td>{{ $product->stock }}</td>
                                    <td>
                                            <span class="badge {{ $product->is_active ? 'badge-success' : 'badge-danger' }}">
                                                {{ $product->is_active ? __('ui.active') : __('ui.inactive') }}
                                            </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-icon btn-edit">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('{{ __('ui.confirm_delete_product') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-icon btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-wrapper mt-4">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
