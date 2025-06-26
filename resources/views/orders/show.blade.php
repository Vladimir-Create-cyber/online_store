@extends('layouts.app')

@section('title', 'Детали заказа #' . $order->id)

@section('content')
    <div class="order-detail-container">

        {{-- Кнопка "Назад" и заголовок --}}
        <div class="order-header">
            <a href="{{ route('orders.index') }}" class="back-link">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Назад к заказам
            </a>
            <h1>Детали заказа #{{ $order->id }}</h1>

            {{-- Статус заказа --}}
            <div class="order-status-badge status-{{ $order->status }}">
                @switch($order->status)
                    @case('pending') Ожидание оплаты @break
                    @case('processing') В обработке @break
                    @case('completed') Завершён @break
                    @case('cancelled') Отменён @break
                    @default {{ $order->status }}
                @endswitch
            </div>
        </div>

        {{-- Информация о заказе и доставке --}}
        <div class="order-detail-grid">
            {{-- Блок: информация о заказе --}}
            <div class="order-info-card">
                <h2 class="info-card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    Информация о заказе
                </h2>
                <div class="info-grid">
                    <div class="info-label">Дата создания:</div>
                    <div class="info-value">{{ $order->created_at->format('d.m.Y H:i') }}</div>

                    <div class="info-label">Общая сумма:</div>
                    <div class="info-value">{{ number_format($order->total, 0, '', ' ') }} грн.</div>

                    <div class="info-label">Способ оплаты:</div>
                    <div class="info-value">{{ $order->payment_method ?? 'Не указано' }}</div>

                    <div class="info-label">Способ доставки:</div>
                    <div class="info-value">{{ $order->shipping_method ?? 'Не указано' }}</div>
                </div>
            </div>

            {{-- Блок: адрес доставки --}}
            <div class="order-info-card">
                <h2 class="info-card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                    </svg>
                    Адрес доставки
                </h2>
                <div class="info-grid">
                    <div class="info-label">Имя:</div>
                    <div class="info-value">{{ $order->shippingAddress->full_name ?? 'Не указано' }}</div>

                    <div class="info-label">Телефон:</div>
                    <div class="info-value">{{ $order->shippingAddress->phone ?? 'Не указано' }}</div>

                    <div class="info-label">Адрес:</div>
                    <div class="info-value">
                        {{ $order->shippingAddress->address ?? 'Не указано' }},
                        {{ $order->shippingAddress->city ?? '' }},
                        {{ $order->shippingAddress->country ?? '' }}
                    </div>

                    <div class="info-label">Почтовый индекс:</div>
                    <div class="info-value">{{ $order->shippingAddress->postal_code ?? 'Не указано' }}</div>
                </div>
            </div>
        </div>

        {{-- Товары в заказе --}}
        <div class="order-items-card">
            <h2 class="info-card-title">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                </svg>
                Товары в заказе
            </h2>

            <div class="order-items-list">
                @foreach($order->orderItems as $item)
                    <div class="order-item">
                        <div class="item-image">
                            @if($item->product)
                                <img src="{{ $item->product->image_url }}"
                                     alt="{{ $item->product_name }}"
                                     style="width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd;">
                            @else
                                <div class="image-placeholder" style="width: 100px; height: 100px; background: #f2f2f2;"></div>
                            @endif
                        </div>

                        <div class="item-details">
                            <h3 class="item-name">{{ $item->product_name }}</h3>
                            @if($item->variant)
                                <p class="item-variant">{{ $item->variant->name }}</p>
                            @endif
                        </div>

                        <div class="item-quantity">x{{ $item->quantity }}</div>
                        <div class="item-price">{{ number_format($item->price, 0, '', ' ') }} грн.</div>
                        <div class="item-total">{{ number_format($item->price * $item->quantity, 0, '', ' ') }} грн.</div>
                    </div>
                @endforeach
            </div>
        </div>

        @if($order->status === 'pending')
            <div class="order-actions">
                <button class="btn-pay">Оплатить заказ</button>

                <form method="POST" action="{{ route('orders.cancel', $order->id) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-cancel" onclick="return confirm('Вы уверены, что хотите отменить заказ?');">
                        Отменить заказ
                    </button>
                </form>
            </div>
        @endif
    </div>
@endsection
