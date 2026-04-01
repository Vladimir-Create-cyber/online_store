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
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\ShippingMethodController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;

/*
|--------------------------------------------------------------------------
| Публичные маршруты
|--------------------------------------------------------------------------
*/
Route::get('/',                 [ProductController::class, 'index'])->name('home');
Route::get('/products',         [ProductController::class, 'index'])->name('products.index');
Route::get('/product/{product}',[ProductController::class, 'show'])->name('product.show');
Route::get('/search',           [ProductController::class, 'search'])->name('product.search');

/*
|--------------------------------------------------------------------------
| Корзина
|--------------------------------------------------------------------------
*/
Route::get('/cart',                     [CartController::class, 'viewCart'])->name('cart.index');
Route::post('/cart/add/{productId}',    [CartController::class, 'addToCart'])->name('cart.add');
Route::post('/cart/remove/{productId}', [CartController::class, 'removeFromCart'])->name('cart.remove');

/*
|--------------------------------------------------------------------------
| Оформление заказа (чекаут)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // GET  /cart/checkout  — просто показывает форму
    Route::get('/cart/checkout', [CartController::class, 'showCheckoutForm'])
        ->name('cart.checkout');

    // POST /cart/complete  — обрабатывает и сохраняет заказ
    Route::post('/cart/complete', [CartController::class, 'completeOrder'])
        ->name('cart.complete');
});

/*
|--------------------------------------------------------------------------
| Аутентификация
|--------------------------------------------------------------------------
*/
Route::get('/login',    [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login',   [AuthController::class, 'login'])->name('login.perform');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register',[AuthController::class, 'register'])->name('register.perform');
Route::post('/logout',  [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Личный кабинет
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard',             [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile/edit',          [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/update',      [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/notifications',         [NotificationController::class, 'index'])->name('notifications');

    Route::get('/orders',                [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}',        [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel',[OrderController::class, 'cancel'])->name('orders.cancel');

    Route::post('/products/{product:slug}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{review}/edit',            [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{review}',                 [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}',              [ReviewController::class, 'destroy'])->name('reviews.destroy');
});

/*
|--------------------------------------------------------------------------
| Админ-панель
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login',  [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('login.submit');
    Route::post('/logout',[AdminLoginController::class, 'logout'])->name('logout');

    Route::middleware('auth:admin')->group(function () {
        Route::get('/',           [AdminDashboardController::class, 'index'])->name('index');
        Route::get('/dashboard',  [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/',               [AdminProductController::class, 'index'])->name('index');
            Route::get('/create',         [AdminProductController::class, 'create'])->name('create');
            Route::post('/',              [AdminProductController::class, 'store'])->name('store');
            Route::get('/{product}/edit', [AdminProductController::class, 'edit'])->name('edit');
            Route::put('/{product}',      [AdminProductController::class, 'update'])->name('update');
            Route::delete('/{product}',   [AdminProductController::class, 'destroy'])->name('destroy');
            Route::delete('/{product}/remove-image',[AdminProductController::class,'removeImage'])->name('removeImage');
            Route::delete('/images/{image}',        [AdminProductController::class,'deleteImage'])->name('deleteImage');
        });

        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/',               [AdminCategoryController::class, 'index'])->name('index');
            Route::get('/create',         [AdminCategoryController::class, 'create'])->name('create');
            Route::post('/',              [AdminCategoryController::class, 'store'])->name('store');
            Route::get('/{category}/edit',[AdminCategoryController::class, 'edit'])->name('edit');
            Route::put('/{category}',     [AdminCategoryController::class, 'update'])->name('update');
            Route::delete('/{category}',  [AdminCategoryController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/',               [AdminOrderController::class, 'index'])->name('index');
            Route::get('/{order}',        [AdminOrderController::class, 'show'])->name('show');
            Route::put('/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('updateStatus');
        });

        Route::prefix('shipping_methods')->name('shipping_methods.')->group(function () {
            Route::get('/',                        [ShippingMethodController::class, 'index'])->name('index');
            Route::get('/create',                  [ShippingMethodController::class, 'create'])->name('create');
            Route::post('/',                       [ShippingMethodController::class, 'store'])->name('store');
            Route::get('/{shipping_method}/edit',  [ShippingMethodController::class, 'edit'])->name('edit');
            Route::put('/{shipping_method}',       [ShippingMethodController::class, 'update'])->name('update');
            Route::delete('/{shipping_method}',    [ShippingMethodController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/',                  [AdminReviewController::class, 'index'])->name('index');
            Route::put('/{review}/approve',  [AdminReviewController::class, 'approve'])->name('approve');
            Route::delete('/{review}',       [AdminReviewController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/',               [AdminUserController::class, 'index'])->name('index');
            Route::get('/create',         [AdminUserController::class, 'create'])->name('create');
            Route::post('/',              [AdminUserController::class, 'store'])->name('store');
            Route::get('/{user}',         [AdminUserController::class, 'show'])->name('show');
            Route::get('/{user}/edit',    [AdminUserController::class, 'edit'])->name('edit');
            Route::put('/{user}',         [AdminUserController::class, 'update'])->name('update');
            Route::delete('/{user}',      [AdminUserController::class, 'destroy'])->name('destroy');
            Route::patch('/{user}/toggle-block', [AdminUserController::class, 'toggleBlock'])->name('toggle-block');
        });
    });
});

Route::middleware('auth')->group(function () {
    Route::post('/payment/process', [PaymentController::class, 'processPayment'])->name('payment.process');
});
