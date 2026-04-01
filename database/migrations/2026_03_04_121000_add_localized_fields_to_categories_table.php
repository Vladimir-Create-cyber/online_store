<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('name_uk')->nullable()->after('name');
            $table->string('name_en')->nullable()->after('name_uk');
            $table->text('description_uk')->nullable()->after('description');
            $table->text('description_en')->nullable()->after('description_uk');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn([
                'name_uk',
                'name_en',
                'description_uk',
                'description_en',
            ]);
        });
    }
};
