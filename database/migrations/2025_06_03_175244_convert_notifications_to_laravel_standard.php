<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Добавляем notifiable_type
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('notifiable_type')->after('id')->default('App\Models\User');
        });

        // Переименовываем user_id -> notifiable_id
        Schema::table('notifications', function (Blueprint $table) {
            $table->renameColumn('user_id', 'notifiable_id');
        });

        // Добавляем read_at
        Schema::table('notifications', function (Blueprint $table) {
            $table->timestamp('read_at')->nullable()->after('is_read');
        });

        // Переносим данные: если is_read=true → read_at=текущая дата
        DB::table('notifications')->where('is_read', true)->update([
            'read_at' => now()
        ]);

        // Удаляем временный столбец is_read
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('is_read');
        });
    }

    public function down()
    {
        // Возвращаем is_read
        Schema::table('notifications', function (Blueprint $table) {
            $table->boolean('is_read')->default(false);
        });

        // Переносим данные обратно
        DB::table('notifications')->update([
            'is_read' => DB::raw('read_at IS NOT NULL')
        ]);

        // Откатываем остальные изменения: удаляем read_at
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('read_at');
        });
    }
};
