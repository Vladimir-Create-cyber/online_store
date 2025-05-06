<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // Обработка корзины, расчет суммы и т.д.
        $order = Order::create([
            'user_id' => auth()->user()->id,
            'total' => $calculatedTotal,
            'shipping_address' => $request->input('address'),
        ]);

        // Добавление позиций заказа...

        // После успешного сохранения заказа можно поставить в очередь отправку уведомлений
        \App\Jobs\SendOrderNotification::dispatch($order);

        return redirect()->route('orders.show', $order->id)->with('success', 'Заказ оформлен');
    }

}
