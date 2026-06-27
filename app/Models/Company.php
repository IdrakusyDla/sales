<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Company extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($company) {
            if (empty($company->slug)) {
                $company->slug = Str::slug($company->name);
            }
        });

        static::updating(function ($company) {
            if ($company->isDirty('name') && empty($company->slug)) {
                $company->slug = Str::slug($company->name);
            }
        });
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * URL publik untuk menampilkan logo perusahaan (tanpa auth, dipakai di halaman login).
     */
    public function logoUrl(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return route('files.company.logo', $this);
    }

    /**
     * Cek apakah file logo benar-benar ada di disk.
     */
    public function logoExists(): bool
    {
        return $this->logo_path
            && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->logo_path);
    }
}
