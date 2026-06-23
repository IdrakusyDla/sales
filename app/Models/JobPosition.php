<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JobPosition extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($jobPosition) {
            if (empty($jobPosition->slug)) {
                $jobPosition->slug = Str::slug($jobPosition->name);
            }
        });

        static::updating(function ($jobPosition) {
            if ($jobPosition->isDirty('name') && empty($jobPosition->slug)) {
                $jobPosition->slug = Str::slug($jobPosition->name);
            }
        });
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
