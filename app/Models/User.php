<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'phone',
        'address',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    // УДАЛЯЕМ все кастомные методы для уведомлений
    // Они не нужны, так как мы используем прямые запросы

    // Добавляем метод для получения количества непрочитанных уведомлений
    // без использования отношений ORM
    public function getUnreadNotificationsCountAttribute()
    {
        // Прямой запрос к базе данных
        return \App\Models\Notification::where('user_id', $this->id)
            ->where('is_read', false)
            ->count();
    }
}
