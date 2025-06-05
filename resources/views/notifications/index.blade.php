@extends('layouts.app')

@section('title', 'Уведомления')

@section('content')
    <div class="notifications-container">
        <h1>Последние уведомления</h1>

        <div class="notifications-header">
            <div class="notifications-summary">
                <p>У вас {{ $notifications->count() }} уведомлений, {{ $unreadCount }} непрочитанных</p>
            </div>
            <div class="notifications-actions">
                <button class="btn-notification-action btn-mark-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Прочитать все
                </button>
                <button class="btn-notification-action btn-clear-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Очистить все
                </button>
            </div>
        </div>

        @if($notifications->count() > 0)
            <ul class="notifications-list">
                @foreach($notifications as $notification)
                    <li class="notification-type-{{ $notification->type }}">
                        <div class="notification-icon">
                            @switch($notification->type)
                                @case('system')
                                    ⚙️
                                    @break
                                @case('order')
                                    📦
                                    @break
                                @case('promo')
                                    🎁
                                    @break
                                @case('alert')
                                    🔔
                                    @break
                                @default
                                    💬
                            @endswitch
                        </div>
                        <div class="notification-info">
                            <p class="notification-message">{{ $notification->message }}</p>
                            <div class="notification-time">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $notification->created_at->format('d.m.Y H:i') }}
                            </div>
                            <div class="notification-actions">
                                @if($notification->unread)
                                    <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="notification-action-btn view">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Прочитано
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('notifications.delete', $notification->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="notification-action-btn delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Удалить
                                    </button>
                                </form>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="notifications-empty">
                <div class="notifications-empty-icon">📭</div>
                <h2>Нет уведомлений</h2>
                <p>Здесь будут появляться важные сообщения, обновления статусов заказов и специальные предложения. Как только у нас будут новости для вас - мы сразу сообщим!</p>
            </div>
        @endif
    </div>
@endsection
