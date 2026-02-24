<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus akun bawaan yang sudah ada (jika ada)
        $defaultUsernames = ['admin', 'hrd', 'supervisor', 'sales'];
        User::whereIn('username', $defaultUsernames)->delete();

        $this->command->info('🗑️  Menghapus akun bawaan yang lama...');

        // ==========================================
        // 1. AKUN IT (SUPERADMIN) - WAJIB ADA
        // ==========================================
        $admin = User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'role' => 'it',
        ]);

        // ==========================================
        // 2. AKUN HRD (OPSIONAL - UNTUK TESTING)
        // ==========================================
        $hrd = User::create([
            'name' => 'HRD Finance',
            'username' => 'hrd',
            'password' => Hash::make('hrd123'),
            'role' => 'hrd',
        ]);

        // ==========================================
        // 3. AKUN SUPERVISOR (OPSIONAL - UNTUK TESTING)
        // ==========================================
        $supervisor = User::create([
            'name' => 'Supervisor Sales',
            'username' => 'supervisor',
            'password' => Hash::make('supervisor123'),
            'role' => 'supervisor',
        ]);

        // ==========================================
        // 4. AKUN SALES (OPSIONAL - UNTUK TESTING)
        // ==========================================
        $sales = User::create([
            'name' => 'Sales Test',
            'username' => 'sales',
            'password' => Hash::make('sales123'),
            'role' => 'sales',
            'supervisor_id' => $supervisor->id,
        ]);

        $this->command->info('✅ Seeder berhasil dijalankan!');
        $this->command->info('');
        $this->command->info('📋 Daftar Akun yang dibuat:');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('👤 IT (Superadmin):');
        $this->command->info('   Username: admin');
        $this->command->info('   Password: admin123');
        $this->command->info('');
        $this->command->info('👤 HRD:');
        $this->command->info('   Username: hrd');
        $this->command->info('   Password: hrd123');
        $this->command->info('');
        $this->command->info('👤 Supervisor:');
        $this->command->info('   Username: supervisor');
        $this->command->info('   Password: supervisor123');
        $this->command->info('');
        $this->command->info('👤 Sales:');
        $this->command->info('   Username: sales');
        $this->command->info('   Password: sales123');
        $this->command->info('   Supervisor: Supervisor Sales');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}
