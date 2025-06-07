<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

trait HandlesUnreadNotifications
{
    protected function getUnreadCount()
    {
        if (!Auth::check()) {
            return 0;
        }

        return Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();
    }
}
