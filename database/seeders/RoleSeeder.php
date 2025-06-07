<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Role::create(['name' => 'admin', 'description' => 'Администратор']);
        Role::create(['name' => 'manager', 'description' => 'Менеджер']);
        Role::create(['name' => 'user', 'description' => 'Пользователь']);
    }
}
