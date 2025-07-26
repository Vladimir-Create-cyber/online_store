@extends('admin.layouts.app')

@section('content')
    <h1>Профиль пользователя: {{ $user->name }}</h1>

    <div class="user-info mb-4">
        <img src="{{ $user->avatar_url }}" alt="Аватар" width="100">
        <p><strong>Email:</strong> {{ $user->email }}</p>
        <p><strong>Телефон:</strong> {{ $user->phone ?? '—' }}</p>
        <p><strong>Адрес:</strong> {{ $user->address ?? '—' }}</p>
        <p><strong>Роли:</strong> {{ $user->roles->pluck('name')->join(', ') }}</p>
        <p><strong>Заказов:</strong> {{ $user->orders()->count() }}</p>
        <p><strong>Отзывы:</strong> {{ $user->reviews()->count() }}</p>
    </div>

    <h3>Последние заказы</h3>
    <table class="table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Дата</th>
            <th>Сумма</th>
            <th>Статус</th>
        </tr>
        </thead>
        <tbody>
        @forelse($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->created_at->format('d.m.Y') }}</td>
                <td>{{ $order->total_price }} грн</td>
                <td>{{ $order->status }}</td>
            </tr>
        @empty
            <tr><td colspan="4">Нет заказов</td></tr>
        @endforelse
        </tbody>
    </table>
@endsection
