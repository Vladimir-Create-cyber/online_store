@extends('layouts.app')

@section('title', __('ui.order_history'))

@section('content')
    <div class="orders-container">
        <h1>@yield('title')</h1>

        @if($orders && $orders->count() > 0)
            <div class="table-responsive">
                <table class="orders-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>{{ __('ui.date') }}</th>
                        <th>{{ __('ui.total') }}</th>
                        <th>{{ __('ui.status') }}</th>
                        <th>{{ __('ui.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td data-label="ID">#{{ $order->id }}</td>
                            <td data-label="{{ __('ui.date') }}">{{ $order->created_at->format('d.m.Y H:i') }}</td>
                            <td data-label="{{ __('ui.total') }}">{{ number_format($order->total, 0, '', ' ') }} {{ __('ui.currency_uah') }}</td>
                            <td data-label="{{ __('ui.status') }}">
                                    <span class="status-badge status-{{ $order->status }}">
                                        @switch($order->status)
                                            @case('pending') {{ __('ui.order_status_pending') }} @break
                                            @case('processing') {{ __('ui.order_status_processing') }} @break
                                            @case('shipped') {{ __('ui.order_status_shipped') }} @break
                                            @case('completed') {{ __('ui.order_status_completed') }} @break
                                            @case('cancelled') {{ __('ui.order_status_cancelled') }} @break
                                            @default {{ $order->status }}
                                        @endswitch
                                    </span>
                            </td>
                            <td data-label="{{ __('ui.actions') }}">
                                <a href="{{ route('orders.show', $order->id) }}" class="action-link">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                    </svg>
                                    {{ __('ui.details') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div class="pagination-wrapper">
                    {{ $orders->links() }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 9h16.5m-16.5 6.75h16.5" />
                </svg>
                <p>{{ __('ui.no_orders_yet_user') }}</p>
                <a href="{{ route('products.index') }}" class="btn-primary">{{ __('ui.start_shopping') }}</a>
            </div>
        @endif

        <div class="dashboard-section">
            <h3 class="section-subtitle">{{ __('ui.latest_notifications') }}</h3>

            <div class="notifications-list">
                @php
                    $notifications = $notifications ?? collect();
                @endphp

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
                        <p>{{ __('ui.no_new_notifications') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
