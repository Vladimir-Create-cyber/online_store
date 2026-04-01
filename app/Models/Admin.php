<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $guard = 'admin';

    protected $fillable = ['name', 'email', 'password', 'is_super_admin'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_super_admin' => 'boolean',
    ];

    /**
     * Возвращает локализованное отображаемое имя для системного администратора.
     */
    public function getDisplayNameAttribute(): string
    {
        $rawName = (string) ($this->attributes['name'] ?? '');
        $normalized = mb_strtolower(trim($rawName));

        $systemAdminAliases = [
            'главный админ',
            'головний адмін',
            'main admin',
        ];

        if (in_array($normalized, $systemAdminAliases, true)) {
            return __('ui.main_admin_name');
        }

        return $rawName !== '' ? $rawName : __('ui.main_admin_name');
    }
}
