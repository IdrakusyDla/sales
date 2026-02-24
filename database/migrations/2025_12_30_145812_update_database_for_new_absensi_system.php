<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. USERS: ubah enum role menjadi string + tambah supervisor_id
        // MySQL tidak bisa langsung alter enum, jadi pakai raw SQL
        if (Schema::hasColumn('users', 'role')) {
            DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` VARCHAR(20) NOT NULL DEFAULT 'sales'");
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'supervisor_id')) {
                $table->foreignId('supervisor_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete()
                    ->after('role');
            }
        });

        // 2. DAILY LOGS: tambah data odometer & absen keluar
        Schema::table('daily_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_logs', 'start_odo_value')) {
                $table->decimal('start_odo_value', 10, 2)->nullable()->after('daily_plan');
            }
            if (!Schema::hasColumn('daily_logs', 'start_odo_photo')) {
                $table->string('start_odo_photo')->nullable()->after('start_odo_value');
            }

            if (!Schema::hasColumn('daily_logs', 'end_time')) {
                $table->dateTime('end_time')->nullable()->after('start_time');
            }
            if (!Schema::hasColumn('daily_logs', 'end_photo')) {
                $table->string('end_photo')->nullable()->after('end_time');
            }
            if (!Schema::hasColumn('daily_logs', 'end_odo_value')) {
                $table->decimal('end_odo_value', 10, 2)->nullable()->after('end_photo');
            }
            if (!Schema::hasColumn('daily_logs', 'end_odo_photo')) {
                $table->string('end_odo_photo')->nullable()->after('end_odo_value');
            }
            if (!Schema::hasColumn('daily_logs', 'end_lat')) {
                $table->decimal('end_lat', 10, 8)->nullable()->after('lat');
            }
            if (!Schema::hasColumn('daily_logs', 'end_long')) {
                $table->decimal('end_long', 11, 8)->nullable()->after('end_lat');
            }
            if (!Schema::hasColumn('daily_logs', 'end_type')) {
                // end_type: home (pulang), last_store (absen keluar di toko terakhir), other (fallback)
                $table->string('end_type', 20)->nullable()->after('end_long');
            }
            if (!Schema::hasColumn('daily_logs', 'end_notes')) {
                $table->text('end_notes')->nullable()->after('end_type');
            }
        });

        // 3. VISITS: tambah kolom is_planned untuk membedakan rencana vs kunjungan dadakan
        Schema::table('visits', function (Blueprint $table) {
            if (!Schema::hasColumn('visits', 'is_planned')) {
                $table->boolean('is_planned')->default(true)->after('status');
            }
        });

        // 4. EXPENSES: simpan referensi ke daily log & penanda auto fuel
        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'daily_log_id')) {
                $table->foreignId('daily_log_id')
                    ->nullable()
                    ->constrained('daily_logs')
                    ->cascadeOnDelete()
                    ->after('user_id');
            }
            if (!Schema::hasColumn('expenses', 'km_total')) {
                $table->decimal('km_total', 10, 2)->nullable()->after('amount');
            }
            if (!Schema::hasColumn('expenses', 'is_auto_calculated')) {
                $table->boolean('is_auto_calculated')->default(false)->after('km_total');
            }
            if (!Schema::hasColumn('expenses', 'deadline_date')) {
                $table->date('deadline_date')->nullable()->after('is_auto_calculated');
            }
        });

        // 5. SUPERVISOR-SALES mapping (flexibel, kalau perlu banyak sales per SPV)
        if (!Schema::hasTable('supervisor_sales')) {
            Schema::create('supervisor_sales', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supervisor_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('sales_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['supervisor_id', 'sales_id']);
            });
        }

        // 6. FUEL SETTINGS (general & individual)
        if (!Schema::hasTable('fuel_settings')) {
            Schema::create('fuel_settings', function (Blueprint $table) {
                $table->id();
                // user_id null = setting general; kalau diisi = override per karyawan
                $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->decimal('km_per_liter', 8, 2); // ratio km per liter
                $table->decimal('fuel_price', 12, 2);  // harga per liter
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Urutan balik untuk menghindari masalah constraint
        if (Schema::hasTable('fuel_settings')) {
            Schema::dropIfExists('fuel_settings');
        }

        if (Schema::hasTable('supervisor_sales')) {
            Schema::dropIfExists('supervisor_sales');
        }

        Schema::table('visits', function (Blueprint $table) {
            if (Schema::hasColumn('visits', 'is_planned')) {
                $table->dropColumn('is_planned');
            }
        });

        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'deadline_date')) {
                $table->dropColumn('deadline_date');
            }
            if (Schema::hasColumn('expenses', 'is_auto_calculated')) {
                $table->dropColumn('is_auto_calculated');
            }
            if (Schema::hasColumn('expenses', 'km_total')) {
                $table->dropColumn('km_total');
            }
            if (Schema::hasColumn('expenses', 'daily_log_id')) {
                $table->dropForeign(['daily_log_id']);
                $table->dropColumn('daily_log_id');
            }
        });

        Schema::table('daily_logs', function (Blueprint $table) {
            $cols = [
                'end_notes',
                'end_type',
                'end_long',
                'end_lat',
                'end_odo_photo',
                'end_odo_value',
                'end_photo',
                'end_time',
                'start_odo_photo',
                'start_odo_value',
            ];

            foreach ($cols as $col) {
                if (Schema::hasColumn('daily_logs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'supervisor_id')) {
                $table->dropForeign(['supervisor_id']);
                $table->dropColumn('supervisor_id');
            }
            
            // Kembalikan role ke enum (jika perlu rollback)
            // Note: Rollback enum tidak sempurna, tapi ini untuk safety
        });
    }
};
