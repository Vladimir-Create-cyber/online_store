@extends('layouts.app')

@section('title', 'Личный кабинет')

@section('content')
    <div class="account-container">
        <!-- Боковая панель -->
        <aside class="account-sidebar">
            <div class="user-profile">
                <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : asset('images/default-avatar.png') }}"
                     alt="Аватар {{ Auth::user()->name }}"
                     class="user-avatar">
                <h2 class="user-name">{{ Auth::user()->name }}</h2>
                <p class="user-email">{{ Auth::user()->email }}</p>
                <p class="user-status">{{ Auth::user()->role ?? 'Пользователь' }}</p>
            </div>

            <ul class="account-menu">
                <li>
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <svg class="account-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Главная
                    </a>
                </li>
                <li>
                    <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                        <svg class="account-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Редактировать профиль
                    </a>
                </li>
                <li>
                    <!-- Изменён вызов маршрута: route('orders') -> route('orders.index') -->
                    <a href="{{ route('orders.index') }}" class="{{ request()->routeIs('orders.index') ? 'active' : '' }}">
                        <svg class="account-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        История заказов
                    </a>
                </li>
                <li>
                    <a href="{{ route('notifications') }}" class="{{ request()->routeIs('notifications') ? 'active' : '' }}">
                        <svg class="account-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        Уведомления
                        @if($unreadCount > 0)
                            <span class="notification-badge">{{ $unreadCount }}</span>
                        @endif
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Основной контент -->
        <main class="account-content">
            <h2 class="section-title">Главная</h2>

            <div class="dashboard-summary">
                <p class="summary-text">Последние события аккаунта и статус заказов:</p>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value">{{ $totalOrders }}</div>
                        <div class="stat-label">Всего заказов</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ $pendingOrders }}</div>
                        <div class="stat-label">Ожидают обработки</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ $completedOrders }}</div>
                        <div class="stat-label">Завершённые</div>
                    </div>
                </div>
            </div>

            <div class="dashboard-section">
                <h3 class="section-subtitle">Последние заказы</h3>

                <div class="order-history">
                    @forelse($orders as $order)
                        <div class="order-card">
                            <div class="order-header">
                                <span class="order-id">Заказ #{{ $order->id }}</span>
                                <span class="order-date">{{ $order->created_at->format('d.m.Y H:i') }}</span>
                                <span class="order-status status-{{ $order->status }}">{{ $order->status_name }}</span>
                            </div>

                            <div class="order-details">
                                <div class="detail-item">
                                    <strong>Сумма</strong>
                                    <span>{{ number_format($order->total, 2, ',', ' ') }} грн</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Товаров</strong>
                                    <span>{{ $order->items_count }}</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Оплата</strong>
                                    <span>{{ $order->payment_method }}</span>
                                </div>
                            </div>

                            <a href="{{ route('orders.show', $order->id) }}" class="btn-view-order">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Детали заказа
                            </a>
                        </div>
                    @empty
                        <div class="empty-section">
                            <svg xmlns="http://www.w3.org/2000/svg" class="empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p>У вас пока нет заказов</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="dashboard-section">
                <h3 class="section-subtitle">Последние уведомления</h3>

                <div class="notifications-list">
                    @forelse($notifications as $notification)
                        <div class="notification-item {{ $notification->unread ? 'unread' : '' }}">
                            <div class="notification-content">
                                <p class="notification-message">{{ $notification->message }}</p>
                                <small class="notification-time">{{ $notification->created_at->format('d.m.Y H:i') }}</small>
                            </div>
                            @if($notification->unread)
                                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="mark-as-read">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <div class="empty-section">
                            <svg xmlns="http://www.w3.org/2000/svg" class="empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p>Нет новых уведомлений</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </main>
    </div>
@endsection
