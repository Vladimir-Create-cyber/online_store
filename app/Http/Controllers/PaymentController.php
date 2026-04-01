<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Обрабатывает оплату заказа через Google Pay + Stripe.
     *
     * Получает payment_token от клиента, создаёт платёж в Stripe и обновляет статус заказа.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processPayment(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Требуется авторизация.',
            ], 401);
        }

        // Валидация входящих данных
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_token' => 'required|string',
        ]);

        // Получаем заказ
        $order = Order::findOrFail($request->order_id);

        if ((int) $order->user_id !== (int) Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя оплатить чужой заказ.',
            ], 403);
        }

        if (!in_array($order->status, ['pending', 'processing'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Этот заказ нельзя оплатить в текущем статусе.',
            ], 422);
        }

        // Инициализируем Stripe с секретным ключом
        $stripe = new StripeClient(config('services.stripe.secret'));

        try {
            // Создаём платежное намерение (Payment Intent) в Stripe
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => intval($order->total * 100), // Сумма в копейках (или центах)
                'currency' => 'uah',                     // Валюта
                'payment_method_data' => [
                    'type' => 'card',
                    'token' => $request->payment_token,  // Токен из Google Pay
                ],
                'confirmation_method' => 'automatic',
                'confirm' => true,
                'metadata' => [
                    'order_id' => $order->id,
                ],
            ]);

            // Если оплата успешна, обновляем статус заказа
            if ($paymentIntent->status === 'succeeded') {
                $order->update(['status' => 'paid']);

                return response()->json(['success' => true]);
            } else {
                // Если статус не успешен — возвращаем ошибку
                return response()->json([
                    'success' => false,
                    'message' => 'Оплата не была подтверждена. Статус: ' . $paymentIntent->status,
                ]);
            }

        } catch (\Exception $e) {
            // Логируем ошибку для отладки
            Log::error('Payment error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обработке платежа: ' . $e->getMessage(),
            ]);
        }
    }
}
