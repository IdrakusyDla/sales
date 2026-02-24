<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Tipe penolakan: revisi (bisa direvisi) atau permanent (tidak bisa direvisi)
            $table->string('rejection_type')->nullable()->after('rejection_note');

            // User yang pertama kali submit expense (untuk tracking revisi)
            $table->unsignedBigInteger('submitted_by')->nullable()->after('rejection_type');

            // Timestamp revisi terakhir
            $table->timestamp('revised_at')->nullable()->after('submitted_by');

            // Jumlah revisi yang sudah dilakukan
            $table->integer('revision_count')->default(0)->after('revised_at');
        });

        // Update existing data: set submitted_by = user_id untuk expense yang sudah ada
        \Illuminate\Support\Facades\DB::statement('UPDATE expenses SET submitted_by = user_id WHERE submitted_by IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['rejection_type', 'submitted_by', 'revised_at', 'revision_count']);
        });
    }
};
