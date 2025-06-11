<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;

// Маршруты доступные без аутентификации
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/login', [LoginController::class, 'login'])->name('admin.login.post');

// =====================================================================
// ЗАЩИЩЕННЫЕ МАРШРУТЫ - ТРЕБУЮТ АУТЕНТИФИКАЦИИ И ПОДТВЕРЖДЕНИЯ EMAIL
// =====================================================================
Route::middleware(['auth:admin', 'verified'])->group(function () {

    // Выход из системы
    Route::post('/logout', [LoginController::class, 'logout'])->name('admin.logout');

    // Главная панель администратора
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Другие защищенные маршруты будут добавляться здесь:
    // Route::resource('products', ProductController::class);
    // Route::resource('users', UserController::class);
});
