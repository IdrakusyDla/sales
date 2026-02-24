<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL tidak bisa langsung alter enum, jadi pakai raw SQL
        // Ubah enum type untuk menambahkan 'fuel'
        DB::statement("ALTER TABLE `expenses` MODIFY COLUMN `type` ENUM('gas', 'toll', 'parking', 'other', 'fuel') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke enum lama (tanpa fuel)
        DB::statement("ALTER TABLE `expenses` MODIFY COLUMN `type` ENUM('gas', 'toll', 'parking', 'other') NOT NULL");
    }
};
