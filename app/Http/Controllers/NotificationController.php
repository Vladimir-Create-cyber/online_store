<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Пожалуйста, войдите в систему.');
        }

        $notifications = Notification::where('user_id', Auth::id())->latest()->get();

        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $unreadCount = $this->getUnreadCount();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }
}
