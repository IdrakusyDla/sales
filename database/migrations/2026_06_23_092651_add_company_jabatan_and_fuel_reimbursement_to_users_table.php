<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. TABEL COMPANIES (Daftar Perusahaan - Group)
        if (!Schema::hasTable('companies')) {
            Schema::create('companies', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. TABEL JOB_POSITIONS (Daftar Jabatan, misal SMD, SPG)
        if (!Schema::hasTable('job_positions')) {
            Schema::create('job_positions', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 3. TAMBAH KOLOM KE USERS
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'company_id')) {
                $table->foreignId('company_id')
                    ->nullable()
                    ->constrained('companies')
                    ->nullOnDelete()
                    ->after('supervisor_id');
            }
            if (!Schema::hasColumn('users', 'job_position_id')) {
                $table->foreignId('job_position_id')
                    ->nullable()
                    ->constrained('job_positions')
                    ->nullOnDelete()
                    ->after('company_id');
            }
            if (!Schema::hasColumn('users', 'fuel_reimbursement_enabled')) {
                $table->boolean('fuel_reimbursement_enabled')->default(true)->after('job_position_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            }
            if (Schema::hasColumn('users', 'job_position_id')) {
                $table->dropForeign(['job_position_id']);
                $table->dropColumn('job_position_id');
            }
            if (Schema::hasColumn('users', 'fuel_reimbursement_enabled')) {
                $table->dropColumn('fuel_reimbursement_enabled');
            }
        });

        Schema::dropIfExists('job_positions');
        Schema::dropIfExists('companies');
    }
};
