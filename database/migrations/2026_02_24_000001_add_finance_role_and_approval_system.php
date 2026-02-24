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
        // 1. Buat tabel expense_histories untuk tracking semua perubahan status
        if (!Schema::hasTable('expense_histories')) {
            Schema::create('expense_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('expense_id')->constrained('expenses')->cascadeOnDelete();
                $table->string('status')->comment('Status baru: pending_spv, pending_hrd, pending_finance, approved, rejected_permanent, needs_revision_sales, needs_revision_spv');
                $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete()->comment('User yang mengubah status');
                $table->text('notes')->nullable()->comment('Catatan saat approve/reject');
                $table->timestamps();

                $table->index(['expense_id', 'created_at']);
            });
        }

        // 2. Update tabel expenses untuk support 3-level approval
        Schema::table('expenses', function (Blueprint $table) {
            // Tambah kolom HRD approval
            if (!Schema::hasColumn('expenses', 'approved_by_hrd_at')) {
                $table->timestamp('approved_by_hrd_at')->nullable()->after('approved_by_spv_at');
            }

            // Tambah user ID yang approve
            if (!Schema::hasColumn('expenses', 'approved_by_spv_id')) {
                $table->unsignedBigInteger('approved_by_spv_id')->nullable()->after('approved_by_spv_at');
            }
            if (!Schema::hasColumn('expenses', 'approved_by_hrd_id')) {
                $table->unsignedBigInteger('approved_by_hrd_id')->nullable()->after('approved_by_hrd_at');
            }
            if (!Schema::hasColumn('expenses', 'approved_by_finance_id')) {
                $table->unsignedBigInteger('approved_by_finance_id')->nullable()->after('approved_by_finance_at');
            }

            // Tambah foreign keys
            if (!Schema::hasColumn('expenses', 'approved_by_spv_id')) {
                $table->foreign('approved_by_spv_id')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('expenses', 'approved_by_hrd_id')) {
                $table->foreign('approved_by_hrd_id')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('expenses', 'approved_by_finance_id')) {
                $table->foreign('approved_by_finance_id')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys dulu
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'approved_by_finance_id')) {
                $table->dropForeign(['approved_by_finance_id']);
            }
            if (Schema::hasColumn('expenses', 'approved_by_hrd_id')) {
                $table->dropForeign(['approved_by_hrd_id']);
            }
            if (Schema::hasColumn('expenses', 'approved_by_spv_id')) {
                $table->dropForeign(['approved_by_spv_id']);
            }
        });

        // Drop columns
        Schema::table('expenses', function (Blueprint $table) {
            $columns = ['approved_by_hrd_at', 'approved_by_spv_id', 'approved_by_hrd_id', 'approved_by_finance_id'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('expenses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Drop expense_histories table
        if (Schema::hasTable('expense_histories')) {
            Schema::dropIfExists('expense_histories');
        }
    }
};
