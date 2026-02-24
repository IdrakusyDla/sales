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
            $table->string('original_photo_receipt')->nullable()->after('photo_receipt');
            $table->boolean('is_generated_receipt')->default(false)->after('original_photo_receipt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('original_photo_receipt');
            $table->dropColumn('is_generated_receipt');
        });
    }
};
