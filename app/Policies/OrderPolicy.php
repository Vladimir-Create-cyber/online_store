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
     * Определяет, может ли пользователь просматривать заказ.
     */
    public function view(User $user, Order $order): Response
    {
        if ($user->id === $order->user_id) {
            return Response::allow();
        }

        if ($user->isAdmin()) {
            return Response::allow();
        }

        return Response::deny('Вы не можете просматривать этот заказ.');
    }

    /**
     * Определяет, может ли пользователь изменять заказ.
     */
    public function update(User $user, Order $order): Response
    {
        if ($user->isAdmin()) {
            return Response::allow();
        }

        if ($user->hasRole('manager') && $order->status === 'processing') {
            return Response::allow();
        }

        return Response::deny('Только администратор или менеджер могут изменять заказы.');
    }

    /**
     * Определяет, может ли пользователь отменять заказ.
     */
    public function cancel(User $user, Order $order): Response
    {
        if ($user->id === $order->user_id && $order->status === 'pending') {
            return Response::allow();
        }

        if ($user->isAdmin() || $user->hasRole('manager')) {
            return Response::allow();
        }

        return Response::deny('Вы не можете отменить этот заказ.');
    }
}
