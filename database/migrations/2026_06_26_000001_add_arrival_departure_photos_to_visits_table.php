<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Rombak alur kunjungan: setiap toko menjadi "session" 2 tahap
 *   1. CHECK-IN  (sampai di toko)  -> arrival_photo  + arrival_time  + arrival_lat/long
 *   2. CHECK-OUT (pulang dari toko)-> departure_photo+ departure_time+ departure_lat/long
 *
 * Status baru: 'in_progress' (sudah check-in, belum check-out).
 *
 * BACKWARD COMPATIBILITY:
 *  - Kolom lama (time, lat, long, photo_path) TETAP dipertahankan.
 *  - Saat check-out, controller akan sinkronkan time=departure_time,
 *    photo_path=departure_photo, lat/long=departure coords agar semua
 *    view lama (history, export, feed HRD/SPV) tetap jalan tanpa perubahan.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom arrival & departure
        Schema::table('visits', function (Blueprint $table) {
            if (! Schema::hasColumn('visits', 'arrival_photo')) {
                $table->string('arrival_photo')->nullable()->after('photo_path');
            }
            if (! Schema::hasColumn('visits', 'departure_photo')) {
                $table->string('departure_photo')->nullable()->after('arrival_photo');
            }
            if (! Schema::hasColumn('visits', 'arrival_time')) {
                $table->dateTime('arrival_time')->nullable()->after('departure_photo');
            }
            if (! Schema::hasColumn('visits', 'departure_time')) {
                $table->dateTime('departure_time')->nullable()->after('arrival_time');
            }
            if (! Schema::hasColumn('visits', 'arrival_lat')) {
                $table->decimal('arrival_lat', 10, 8)->nullable()->after('departure_time');
            }
            if (! Schema::hasColumn('visits', 'arrival_long')) {
                $table->decimal('arrival_long', 11, 8)->nullable()->after('arrival_lat');
            }
        });

        // 2. Tambah status 'in_progress' ke enum (MySQL)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE visits MODIFY COLUMN status ENUM('pending','in_progress','completed','failed') NOT NULL DEFAULT 'pending'");
        }

        // 3. Migrasi data lama: visit yang sudah selesai (completed/failed) anggap
        //    foto lamanya = departure photo (karena dulu foto diambil saat selesai).
        DB::table('visits')
            ->whereIn('status', ['completed', 'failed'])
            ->whereNotNull('photo_path')
            ->whereNull('departure_photo')
            ->update([
                'departure_photo' => DB::raw('photo_path'),
                'departure_time'  => DB::raw('time'),
            ]);
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE visits MODIFY COLUMN status ENUM('pending','completed','failed') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('visits', function (Blueprint $table) {
            foreach (['arrival_photo', 'departure_photo', 'arrival_time', 'departure_time', 'arrival_lat', 'arrival_long'] as $col) {
                if (Schema::hasColumn('visits', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
