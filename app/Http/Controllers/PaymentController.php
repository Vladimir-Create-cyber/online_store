<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    public function processPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_token' => 'required|string'
        ]);

        $order = Order::findOrFail($request->order_id);

        $response = Http::post('https://pay.google.com/gpapi/v1/payments', [
            'merchantId' => env('GOOGLE_PAY_MERCHANT_ID'),
            'publicKey' => env('GOOGLE_PAY_PUBLIC_KEY'),
            'token' => $request->payment_token,
            'amount' => $order->total,
            'currency' => 'USD'
        ]);

        if ($response->successful()) {
            $order->update(['status' => 'paid']);
            return redirect()->route('orders.show', $order->id)->with('success', 'Оплата прошла успешно!');
        }

        return redirect()->back()->with('error', 'Ошибка при обработке платежа!');
    }
}

