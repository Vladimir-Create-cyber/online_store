@extends('admin.layouts.app')

@section('title', 'Список заказов')

@section('content')
    <div class="orders-container">
        <div class="section-header">
            <h1 class="section-title">Список заказов</h1>
        </div>

        <form method="GET" class="filters-form">
            <div class="form-group">
                <input type="text" name="order_id" value="{{ request('order_id') }}"
                       placeholder="ID заказа" class="form-control" />
            </div>

            <div class="form-group">
                <input type="text" name="user_name" value="{{ request('user_name') }}"
                       placeholder="Имя пользователя" class="form-control" />
            </div>

            <div class="form-group">
                <input type="text" name="user_email" value="{{ request('user_email') }}"
                       placeholder="Email пользователя" class="form-control" />
            </div>

            <div class="form-group">
                <select name="status" class="form-control">
                    <option value="">Все статусы</option>
                    <option value="pending" @selected(request('status') === 'pending')>В ожидании</option>
                    <option value="processing" @selected(request('status') === 'processing')>Обработка</option>
                    <option value="shipped" @selected(request('status') === 'shipped')>Отправлен</option>
                    <option value="completed" @selected(request('status') === 'completed')>Завершён</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Отменён</option>
                </select>
            </div>

            <div class="form-group date-group">
                <label class="date-label">Дата от</label>
                <input type="date" id="date_from" name="date_from"
                       value="{{ request('date_from') }}" class="form-control" />
            </div>

            <div class="form-group date-group">
                <label class="date-label">Дата до</label>
                <input type="date" id="date_to" name="date_to"
                       value="{{ request('date_to') }}" class="form-control" />
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Фильтровать
                </button>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-reset">
                    <i class="fas fa-undo"></i> Сбросить
                </a>
            </div>
        </form>

        @if(session('success'))
            <div class="alert alert-success">
                <i class="alert-icon fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        <div class="table-responsive">
            <table class="orders-table">
                <thead>
                <tr class="table-dark">
                    <th>ID</th>
                    <th>Пользователь</th>
                    <th>Сумма</th>
                    <th>Статус</th>
                    <th>Дата</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td data-label="ID">{{ $order->id }}</td>
                        <td data-label="Пользователь">{{ $order->user->name ?? 'Гость' }}</td>
                        <td data-label="Сумма">{{ number_format($order->total, 2) }} ₴</td>
                        <td data-label="Статус">
                        <span class="badge badge-{{ $order->status }}">
                            {{ $order->status }}
                        </span>
                        </td>
                        <td data-label="Дата">{{ $order->created_at->format('d.m.Y H:i') }}</td>
                        <td data-label="Действия">
                            <a href="{{ route('admin.orders.show', $order) }}"
                               class="btn-action view-btn">
                                <i class="fas fa-eye"></i> Просмотр
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="pagination-container">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
@endsection
