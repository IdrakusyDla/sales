<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('password');
            }
            if (!Schema::hasColumn('users', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable()->after('is_active');
            }
        });

        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'status')) {
                $table->string('status')->default('pending_spv')->after('note');
            }
            if (!Schema::hasColumn('expenses', 'approved_by_spv_at')) {
                $table->timestamp('approved_by_spv_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('expenses', 'approved_by_finance_at')) {
                $table->timestamp('approved_by_finance_at')->nullable()->after('approved_by_spv_at');
            }
            if (!Schema::hasColumn('expenses', 'rejection_note')) {
                $table->text('rejection_note')->nullable()->after('approved_by_finance_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'last_activity_at']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['status', 'approved_by_spv_at', 'approved_by_finance_at', 'rejection_note']);
        });
    }
};
