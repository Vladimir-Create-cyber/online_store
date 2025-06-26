<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\ShippingMethodController;

/*
|--------------------------------------------------------------------------
| Публичные маршруты
|--------------------------------------------------------------------------
*/

// Главная, каталог, поиск
Route::get('/', [ProductController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');
Route::get('/search', [ProductController::class, 'search'])->name('product.search');

// Корзина
Route::get('/cart', [CartController::class, 'viewCart'])->name('cart.index');
Route::post('/cart/add/{productId}', [CartController::class, 'addToCart'])->name('cart.add');
Route::get('/cart/remove/{productId}', [CartController::class, 'removeFromCart'])->name('cart.remove');

// Переход от корзины к форме оформления
Route::post('/cart/checkout', [CartController::class, 'showCheckoutForm'])
    ->middleware('auth')
    ->name('cart.checkout');

// Завершение оформления заказа и сохранение
Route::post('/cart/complete', [CartController::class, 'completeOrder'])
    ->middleware('auth')
    ->name('cart.complete');

// Аутентификация пользователей
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.perform');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.perform');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Личный кабинет
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

    // Уведомления
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');

    // Заказы
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
});

// Оплата
Route::post('/payment/process', [PaymentController::class, 'processPayment'])->name('payment.process');

/*
|--------------------------------------------------------------------------
| Админ-панель
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {

    // Аутентификация администратора
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');

    // Только для авторизованных админов
    Route::middleware('auth:admin')->group(function () {

        // Главная панель
        Route::get('/', fn() => view('admin.dashboard'))->name('index');
        Route::get('/dashboard', fn() => view('admin.dashboard'))->name('dashboard');

        // Управление товарами
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [AdminProductController::class, 'index'])->name('index');
            Route::get('/create', [AdminProductController::class, 'create'])->name('create');
            Route::post('/', [AdminProductController::class, 'store'])->name('store');
            Route::get('/{product}/edit', [AdminProductController::class, 'edit'])->name('edit');
            Route::put('/{product}', [AdminProductController::class, 'update'])->name('update');
            Route::delete('/{product}', [AdminProductController::class, 'destroy'])->name('destroy');
            Route::delete('/{product}/remove-image', [AdminProductController::class, 'removeImage'])->name('removeImage');
            Route::delete('/images/{image}', [AdminProductController::class, 'deleteImage'])->name('deleteImage');
        });

        // Управление категориями
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [AdminCategoryController::class, 'index'])->name('index');
            Route::get('/create', [AdminCategoryController::class, 'create'])->name('create');
            Route::post('/', [AdminCategoryController::class, 'store'])->name('store');
            Route::get('/{category}/edit', [AdminCategoryController::class, 'edit'])->name('edit');
            Route::put('/{category}', [AdminCategoryController::class, 'update'])->name('update');
            Route::delete('/{category}', [AdminCategoryController::class, 'destroy'])->name('destroy');
        });

        // Управление заказами
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [AdminOrderController::class, 'index'])->name('index');
            Route::get('/{order}', [AdminOrderController::class, 'show'])->name('show');
            Route::put('/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('updateStatus');
        });

        // Управление способами доставки
        Route::prefix('shipping_methods')->name('shipping_methods.')->group(function () {
            Route::get('/', [ShippingMethodController::class, 'index'])->name('index');
            Route::get('/create', [ShippingMethodController::class, 'create'])->name('create');
            Route::post('/', [ShippingMethodController::class, 'store'])->name('store');
            Route::get('/{shipping_method}/edit', [ShippingMethodController::class, 'edit'])->name('edit');
            Route::put('/{shipping_method}', [ShippingMethodController::class, 'update'])->name('update');
            Route::delete('/{shipping_method}', [ShippingMethodController::class, 'destroy'])->name('destroy');
        });

    });
});

