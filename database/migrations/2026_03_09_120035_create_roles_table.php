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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // Maps to users.role
            $table->json('permissions')->nullable(); // Array of permission strings
            $table->string('dashboard_url')->nullable(); // e.g. 'hrd.dashboard'
            $table->boolean('is_system_role')->default(false); // Prevents deletion of core roles
            $table->timestamps();
        });

        // Insert default roles to prevent system breakage
        $defaultRoles = [
            [
                'name' => 'IT System',
                'slug' => 'it',
                'permissions' => json_encode(['all']),
                'dashboard_url' => 'it.dashboard',
                'is_system_role' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Human Resources (HRD)',
                'slug' => 'hrd',
                'permissions' => json_encode(['manage_users', 'view_reports', 'approve_reimburse']),
                'dashboard_url' => 'hrd.dashboard',
                'is_system_role' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Finance',
                'slug' => 'finance',
                'permissions' => json_encode(['approve_reimburse', 'view_reports']),
                'dashboard_url' => 'finance.dashboard',
                'is_system_role' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Supervisor',
                'slug' => 'supervisor',
                'permissions' => json_encode(['approve_reimburse', 'view_sales']),
                'dashboard_url' => 'supervisor.dashboard',
                'is_system_role' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sales',
                'slug' => 'sales',
                'permissions' => json_encode(['create_visits', 'create_reimburse']),
                'dashboard_url' => 'sales.dashboard',
                'is_system_role' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('roles')->insert($defaultRoles);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
