<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Order;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Проверка просмотра заказа
     */
    public function view(User $user, Order $order): Response
    {
        // Проверяем принадлежность заказа пользователю
        if ($user->id === $order->user_id) {
            return Response::allow();
        }

        // Для администраторов разрешаем просмотр любых заказов
        if ($user->isAdmin()) {
            return Response::allow();
        }

        return Response::deny('Вы не можете просматривать этот заказ.');
    }

    /**
     * Проверка изменения заказа
     */
    public function update(User $user, Order $order): Response
    {
        // Администраторы могут изменять любые заказы
        if ($user->isAdmin()) {
            return Response::allow();
        }

        // Менеджеры могут изменять только заказы в обработке
        if ($user->hasRole('manager') && $order->status === 'processing') {
            return Response::allow();
        }

        return Response::deny('Только администратор или менеджер могут изменять заказы.');
    }

    /**
     * Проверка отмены заказа
     */
    public function cancel(User $user, Order $order): Response
    {
        // Пользователь может отменить только свой заказ в статусе "ожидание"
        if ($user->id === $order->user_id && $order->status === 'pending') {
            return Response::allow();
        }

        // Администраторы и менеджеры могут отменять любые заказы
        if ($user->isAdmin() || $user->hasRole('manager')) {
            return Response::allow();
        }

        return Response::deny('Вы не можете отменить этот заказ.');
    }
}
