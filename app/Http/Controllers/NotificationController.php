<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class NotificationController extends Controller
{
    private function getUnreadCount()
    {
        if (!Auth::check()) {
            return 0;
        }

        return Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();
    }

    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Пожалуйста, войдите в систему.');
        }

        $notifications = Notification::where('user_id', Auth::id())->latest()->get();

        // ИСПРАВЛЕНО ЗДЕСЬ:
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]); // Было: ['read_at' => now()]

        $unreadCount = 0;

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }
}
