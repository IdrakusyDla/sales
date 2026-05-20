<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        \DB::table('roles')->where('slug', 'hrd')->update([
            'dashboard_url' => 'hrd.home',
        ]);
    }

    public function down(): void
    {
        \DB::table('roles')->where('slug', 'hrd')->update([
            'dashboard_url' => 'hrd.dashboard',
        ]);
    }
};
