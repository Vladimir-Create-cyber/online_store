@extends('admin.layouts.app')

@section('title', 'Способы доставки')

@section('content')
    <div class="shipping-methods-container">
        <div class="section-header">
            <h1 class="section-title">Способы доставки</h1>
            <a href="{{ route('admin.shipping_methods.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Добавить способ
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <i class="alert-icon fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        <div class="table-responsive">
            <table class="methods-table">
                <thead>
                <tr class="table-dark">
                    <th>ID</th>
                    <th>Название</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($shippingMethods as $method)
                    <tr>
                        <td data-label="ID">{{ $method->id }}</td>
                        <td data-label="Название">{{ $method->name }}</td>
                        <td data-label="Статус">
                        <span class="badge {{ $method->is_active ? 'badge-success' : 'badge-danger' }}">
                            {{ $method->is_active ? 'Активен' : 'Неактивен' }}
                        </span>
                        </td>
                        <td data-label="Действия">
                            <div class="actions-group">
                                <a href="{{ route('admin.shipping_methods.edit', $method) }}" class="btn-action edit-btn">
                                    <i class="fas fa-edit"></i> Редактировать
                                </a>
                                <form action="{{ route('admin.shipping_methods.destroy', $method) }}" method="POST" onsubmit="return confirm('Вы уверены, что хотите удалить этот способ доставки?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action delete-btn">
                                        <i class="fas fa-trash"></i> Удалить
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">Нет способов доставки</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
