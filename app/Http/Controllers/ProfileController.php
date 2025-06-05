<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification; // Добавили импорт модели

class ProfileController extends Controller
{
    // Новая реализация метода для получения количества непрочитанных уведомлений
    private function getUnreadCount()
    {
        if (!Auth::check()) {
            return 0;
        }

        // Прямой запрос к базе данных
        return Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();
    }

    public function edit()
    {
        $user = Auth::user();
        $unreadCount = $this->getUnreadCount();

        return view('profile.edit', compact('user', 'unreadCount'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Профиль успешно обновлен!');
    }
}
