<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        Admin::create([
            'name' => 'Главный Админ',
            'email' => 'admin@store.local',
            'password' => Hash::make('SecurePassword123!'),
            'is_super_admin' => true,
        ]);

        $this->command->info('✅ Главный администратор успешно создан!');
        $this->command->line('👉 Логин: admin@store.local');
        $this->command->line('👉 Пароль: SecurePassword123!');
    }
}
