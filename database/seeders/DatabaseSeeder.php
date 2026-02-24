<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Panggil UserSeeder untuk membuat akun default
        $this->call([
            UserSeeder::class,
        ]);
    }
}