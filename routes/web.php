<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;

// Главная страница — каталог товаров (доступна как по 'home')
Route::get('/', [ProductController::class, 'index'])->name('home');
// Дополнительный маршрут для каталога товаров (если в шаблонах вызывается 'products.index')
Route::get('/products', [ProductController::class, 'index'])->name('products.index');

// Детальная страница товара
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');

// Поиск товаров
Route::get('/search', [ProductController::class, 'search'])->name('product.search');

// Корзина — кастомные маршруты
Route::get('/cart', [CartController::class, 'viewCart'])->name('cart.index');
Route::post('/cart/add/{productId}', [CartController::class, 'addToCart'])->name('cart.add');
Route::get('/cart/remove/{productId}', [CartController::class, 'removeFromCart'])->name('cart.remove');
// Оформление заказа доступно только авторизованным пользователям
Route::post('/cart/checkout', [CartController::class, 'checkout'])
    ->middleware('auth')
    ->name('cart.checkout');

// Заказы (только для авторизованных пользователей)
Route::middleware('auth')->group(function () {
    Route::resource('orders', OrderController::class);
});

// Аутентификация
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.perform');

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.perform');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Личный кабинет (только для авторизованных пользователей)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

Route::post('/payment/process', [PaymentController::class, 'process']);
Route::post('/payment/process', [PaymentController::class, 'processPayment'])->name('payment.process');
