<?php
//
//use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\Admin\Auth\LoginController;
//use App\Http\Controllers\Admin\DashboardController;
//use App\Http\Controllers\Admin\ProductController;
//
//// 🔓 Маршруты доступные без аутентификации
//Route::get('/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
//Route::post('/login', [LoginController::class, 'login'])->name('admin.login.post');
//
//// =====================================================================
//// 🔐 ЗАЩИЩЕННЫЕ МАРШРУТЫ - ТРЕБУЮТ АУТЕНТИФИКАЦИИ И ПОДТВЕРЖДЕНИЯ EMAIL
//// =====================================================================
//Route::middleware(['auth:admin', 'verified'])->group(function () {
//
//    // 🚪 Выход из системы
//    Route::post('/logout', [LoginController::class, 'logout'])->name('admin.logout');
//
//    // 🎛️ Главная панель администратора
//    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
//
//    // ✅ Исправленный маршрут `/admin/products`
//    Route::prefix('admin')->group(function () {
//        Route::get('/products', [ProductController::class, 'index'])->name('admin.products.index');
//        Route::get('/products/create', [ProductController::class, 'create'])->name('admin.products.create');
//        Route::post('/products/store', [ProductController::class, 'store'])->name('admin.products.store');
//        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
//        Route::patch('/products/{product}', [ProductController::class, 'update'])->name('admin.products.update');
//        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('admin.products.destroy');
//    });
//
//
//});
