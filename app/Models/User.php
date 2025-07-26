<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HandlesUnreadNotifications; // Добавляем трейт

class User extends Authenticatable
{
    use HasFactory, Notifiable, HandlesUnreadNotifications; // Используем трейт

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'phone',
        'address',
        'role_id',
        'is_blocked',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $appends = ['unread_notifications_count', 'avatar_url']; // Добавили avatar_url

    // Отношение с заказами
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // Отношение с отзывами
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // Отношение с ролями
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    // Проверка роли пользователя
    public function hasRole($role): bool
    {
        // Проверяем, передана ли строка или массив ролей
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }

        // Если передана коллекция или массив ролей
        if (is_iterable($role)) {
            foreach ($role as $r) {
                if ($this->roles->contains('name', $r)) {
                    return true;
                }
            }
            return false;
        }

        return false;
    }

    // Виртуальный атрибут для количества непрочитанных уведомлений
    public function getUnreadNotificationsCountAttribute(): int
    {
        return $this->getUnreadCount(); // Используем метод из трейта
    }

    // Проверка администратора
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    // Получение аватара с fallback
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            // Проверяем, является ли avatar URL или путем хранения
            if (filter_var($this->avatar, FILTER_VALIDATE_URL)) {
                return $this->avatar;
            }
            return asset('storage/' . ltrim($this->avatar, '/'));
        }

        return asset('images/default-avatar.png');
    }

    // Добавим метод для проверки наличия аватара
    public function hasAvatar(): bool
    {
        return !empty($this->avatar);
    }
}
