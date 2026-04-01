<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HandlesUnreadNotifications;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HandlesUnreadNotifications;

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
        'is_blocked' => 'boolean',
    ];

    protected $appends = ['unread_notifications_count', 'avatar_url'];

    /**
     * Возвращает заказы пользователя.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Возвращает отзывы пользователя.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Возвращает роли пользователя.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Проверяет, есть ли у пользователя указанная роль.
     */
    public function hasRole($role): bool
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }

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

    /**
     * Возвращает количество непрочитанных уведомлений.
     */
    public function getUnreadNotificationsCountAttribute(): int
    {
        return $this->getUnreadCount();
    }

    /**
     * Проверяет, является ли пользователь администратором.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Возвращает URL аватара или изображение по умолчанию.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            if (filter_var($this->avatar, FILTER_VALIDATE_URL)) {
                return $this->avatar;
            }
            return asset('storage/' . ltrim($this->avatar, '/'));
        }

        return asset('images/default-avatar.png');
    }

    /**
     * Проверяет, загружен ли у пользователя аватар.
     */
    public function hasAvatar(): bool
    {
        return !empty($this->avatar);
    }
}
