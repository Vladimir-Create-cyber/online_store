<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); // bigint unsigned auto_increment
            $table->string('name'); // Название категории
            $table->string('slug')->unique(); // SEO URL
            $table->text('description')->nullable(); // Описание (опционально)

            // Дополнительно — если вдруг понадобятся иерархии категорий
            // $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();

            $table->timestamps(); // created_at, updated_at
        });
        // В миграции:
        Schema::table('categories', function (Blueprint $table) {
            $table->text('description')->nullable()->after('slug');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
