<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')
            ->where('slug', 'supervisor')
            ->update(['dashboard_url' => 'supervisor.home']);
    }

    public function down(): void
    {
        DB::table('roles')
            ->where('slug', 'supervisor')
            ->update(['dashboard_url' => 'supervisor.dashboard']);
    }
};
