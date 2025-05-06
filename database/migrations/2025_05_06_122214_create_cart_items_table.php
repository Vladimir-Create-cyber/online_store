<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();                          // Уникальный идентификатор товара в корзине
            $table->unsignedBigInteger('user_id'); // ID пользователя (если корзина привязана к аккаунту)
            $table->unsignedBigInteger('product_id'); // ID товара
            $table->integer('quantity')->default(1); // Количество товаров
            $table->timestamps();                 // Время создания и обновления записи

            // Связи с таблицами users и products
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items'); // Удаление таблицы при откате миграции
    }
};

