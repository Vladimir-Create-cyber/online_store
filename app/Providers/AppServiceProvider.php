<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema; // Для работы с длинными индексами
use Illuminate\Support\Facades\URL;    // Для генерации HTTPS URL
use Illuminate\Support\Facades\Gate;   // Для политик
use App\Models\Order;                  // Модель заказа
use App\Policies\OrderPolicy;          // Политика заказа
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Регистрация сервисов приложения.
     */
    public function register(): void
    {
        // Для продакшена: принудительное использование HTTPS
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }

    /**
     * Загрузка сервисов приложения.
     */
    public function boot(): void
    {
        // Фикс для миграций с длинными индексами (MySQL)
        Schema::defaultStringLength(191);

        // Регистрация политики для заказов
        Gate::policy(Order::class, OrderPolicy::class);

        // 👇 Регистрируем кастомный шаблон пагинации
        Paginator::defaultView('vendor.pagination.custom');

        // Глобальные правила валидации можно добавить здесь
        // Validator::extend(...);
    }
}
