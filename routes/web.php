<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;


// Главная страница – каталог товаров
Route::get('/', [ProductController::class, 'index'])->name('home');

// Детальная страница товара
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');

// Поиск
Route::get('/search', [ProductController::class, 'search'])->name('product.search');

// Корзина (можно использовать ресурсный контроллер)
Route::resource('cart', CartController::class);

// Заказы (только авторизованным пользователям)
Route::middleware('auth')->group(function () {
    Route::resource('orders', OrderController::class);
});

//Route::get('/', function () {
//    return view('welcome');
//});

Route::get('/', function () {
    return view('products.index');
});

Route::get('/', [ProductController::class, 'index'])->name('home');


Route::get('/cart', [CartController::class, 'viewCart'])->name('cart.index');
Route::post('/cart/add/{productId}', [CartController::class, 'addToCart'])->name('cart.add');
Route::get('/cart/remove/{productId}', [CartController::class, 'removeFromCart'])->name('cart.remove');
Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');



