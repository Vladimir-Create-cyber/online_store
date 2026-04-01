<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

abstract class Controller
{
    protected function getUnreadCount(): int
    {
        if (! Auth::check()) {
            return 0;
        }

        return Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();
    }
}
