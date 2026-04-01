<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\UserPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Список политик для приложения.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Order::class => OrderPolicy::class,
        Product::class => ProductPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Регистрация сервисов.
     */
    public function register(): void
    {
    }

    /**
     * Загрузка сервисов аутентификации/авторизации.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('admin-access', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('manage-products', function ($user) {
            return $user->hasRole(['admin', 'manager']);
        });

        Gate::define('manage-orders', function ($user) {
            return $user->hasRole(['admin', 'manager']);
        });

        Gate::define('view-dashboard', function ($user) {
            return $user->hasRole(['admin', 'manager', 'content-manager']);
        });

        Gate::before(function ($user, $ability) {
            if ($user->isAdmin()) {
                return true;
            }
        });
    }
}
