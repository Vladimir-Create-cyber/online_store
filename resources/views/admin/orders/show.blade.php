@extends('admin.layouts.app')

@section('title', 'Детали заказа #' . $order->id)

@section('content')
    <div class="order-details-container">
        <div class="section-header">
            <h1 class="section-title">Заказ #{{ $order->id }}</h1>
        </div>

        <div class="order-info-grid">
            <div class="info-block">
                <h3 class="info-title">Информация о покупателе</h3>
                <p><strong>Имя:</strong> {{ $order->user->name ?? 'Гость' }}</p>
                <p><strong>Email:</strong> {{ $order->user->email ?? '—' }}</p>
                <p><strong>Телефон:</strong> {{ $order->phone ?? '—' }}</p>
            </div>

            <div class="info-block">
                <h3 class="info-title">Детали заказа</h3>
                <p><strong>Статус:</strong> <span class="badge badge-{{ $order->status }}">{{ $order->status_text }}</span></p>
                <p><strong>Итого:</strong> {{ number_format($order->total, 2, '.', ' ') }} ₽</p>
                <p><strong>Дата оформления:</strong> {{ $order->created_at->format('d.m.Y H:i') }}</p>
            </div>
        </div>

        {{-- Форма смены статуса --}}
        <div class="status-form-container">
            <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST" class="status-form">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="status" class="form-label">Сменить статус</label>
                    <div class="form-select-wrapper">
                        <select name="status" id="status" class="form-control">
                            <option value="pending" @selected($order->status === 'pending')>В ожидании</option>
                            <option value="processing" @selected($order->status === 'processing')>В обработке</option>
                            <option value="shipped" @selected($order->status === 'shipped')>Отправлен</option>
                            <option value="completed" @selected($order->status === 'completed')>Завершён</option>
                            <option value="cancelled" @selected($order->status === 'cancelled')>Отменён</option>
                        </select>
                        <i class="fas fa-chevron-down select-icon"></i>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sync-alt"></i> Обновить статус
                </button>
            </form>
        </div>

        <div class="order-content-grid">
            <div class="products-section">
                <h3 class="section-subtitle">Товары в заказе</h3>
                <ul class="product-list">
                    @foreach($order->orderItems as $item)
                        <li class="product-item">
                            <div class="product-info">
                                <h4>{{ $item->product->name }}</h4>
                                <p>{{ $item->quantity }} шт. × {{ number_format($item->price, 2, '.', ' ') }} ₽</p>
                                <p class="stock-info">Остаток на складе: {{ $item->product->stock }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="addresses-grid">
                <div class="address-section">
                    <h3 class="section-subtitle">Адрес доставки</h3>
                    <div class="address-card">
                        <p>{{ $order->shippingAddress->full_name ?? '—' }}</p>
                        <p>{{ $order->shippingAddress->address ?? '—' }}</p>
                        <p>{{ $order->shippingAddress->city ?? '—' }}, {{ $order->shippingAddress->country ?? '—' }}</p>
                        <p>{{ $order->shippingAddress->postal_code ?? '—' }}</p>
                    </div>
                </div>

                <div class="address-section">
                    <h3 class="section-subtitle">Адрес оплаты</h3>
                    <div class="address-card">
                        <p>{{ $order->billingAddress->full_name ?? '—' }}</p>
                        <p>{{ $order->billingAddress->address ?? '—' }}</p>
                        <p>{{ $order->billingAddress->city ?? '—' }}, {{ $order->billingAddress->country ?? '—' }}</p>
                        <p>{{ $order->billingAddress->postal_code ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="action-bar">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Назад к заказам
            </a>
        </div>

        {{-- Вывод сообщений об успехе --}}
        @if(session('success'))
            <div class="alert alert-success">
                <i class="alert-icon fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif
    </div>
@endsection
