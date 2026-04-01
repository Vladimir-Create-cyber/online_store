@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold mt-6">Личный кабинет</h1>
        <p class="mt-4">Добро пожаловать, {{ Auth::user()->name }}!</p>

        <h2 class="mt-8 text-2xl font-semibold">Ваши заказы:</h2>

        @if($orders->isEmpty())
            <p class="mt-2">У вас пока нет заказов.</p>
        @else
            <div class="overflow-x-auto mt-4">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Номер заказа</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сумма</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действие</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($orders as $order)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $order->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $order->total }} грн.</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $order->status }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $order->created_at->format('d.m.Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('orders.show', $order->id) }}" class="text-blue-500 hover:underline">Подробнее</a>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="5" class="px-6 py-4">
                                <h4 class="text-lg font-semibold mt-2">Товары в заказе:</h4>
                                <ul class="list-disc ml-6">
                                    @foreach($order->orderItems as $item)
                                        <li>{{ $item->product->name }} — {{ $item->quantity }} шт. ({{ $item->price }} грн.)</li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
