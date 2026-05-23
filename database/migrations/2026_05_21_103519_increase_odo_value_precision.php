<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE daily_logs MODIFY start_odo_value DECIMAL(10, 2) NULL');
        DB::statement('ALTER TABLE daily_logs MODIFY end_odo_value DECIMAL(10, 2) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE daily_logs MODIFY start_odo_value DECIMAL(12, 2) NULL');
        DB::statement('ALTER TABLE daily_logs MODIFY end_odo_value DECIMAL(12, 2) NULL');
    }
};
