<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // <--- TAMBAHKAN BARIS INI
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Membuat User Super Admin Pertama
        User::create([
            'name' => 'Super Admin Duniatex',
            'email' => 'superadmin@duniatex.com',
            'password' => Hash::make('superadmin123'), 
            'role' => 'superadmin', 
        ]);
    }
}