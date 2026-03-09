<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'permissions',
        'dashboard_url',
        'is_system_role',
        'hrd_can_create'
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_system_role' => 'boolean',
        'hrd_can_create' => 'boolean'
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'role', 'slug');
    }
}
