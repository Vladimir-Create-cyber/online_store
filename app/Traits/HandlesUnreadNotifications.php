<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

trait HandlesUnreadNotifications
{
    /**
     * Возвращает количество непрочитанных уведомлений текущего пользователя.
     */
    protected function getUnreadCount(): int
    {
        if (!Auth::check()) {
            return 0;
        }

        return Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();
    }
}
