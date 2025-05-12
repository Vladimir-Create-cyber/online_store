<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // Здесь должна быть логика расчёта итоговой суммы заказа
        // Например, $calculatedTotal = ...;
        $calculatedTotal = 100; // пример, замените на реальную логику

        $order = Order::create([
            'user_id'          => auth()->user()->id,
            'total'            => $calculatedTotal,
            'shipping_address' => $request->input('address'),
        ]);

        // После успешного сохранения заказа можно поставить в очередь отправку уведомлений
        \App\Jobs\SendOrderNotification::dispatch($order);

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Заказ оформлен');
    }

    // Метод для отображения подробностей заказа вынесен отдельно
    public function show($orderId)
    {
        $order = Order::findOrFail($orderId);
        return view('orders.show', compact('order'));
    }
}
