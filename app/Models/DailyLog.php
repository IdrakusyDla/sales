<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyLog extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'start_photo',
        'start_odo_value',
        'start_odo_photo',
        'lat',
        'long',
        'daily_plan',
        'end_time',
        'end_photo',
        'end_odo_value',
        'end_odo_photo',
        'end_lat',
        'end_long',
        'end_type', // 'home', 'last_store', 'other'
        'end_notes',
        'system_calculated_distance',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'start_odo_value' => 'decimal:2',
        'end_odo_value' => 'decimal:2',
        'lat' => 'decimal:8',
        'long' => 'decimal:8',
        'end_lat' => 'decimal:8',
        'end_long' => 'decimal:8',
        'system_calculated_distance' => 'decimal:2',
    ];

    // ==========================================
    // RELASI
    // ==========================================

    /**
     * Relasi: DailyLog milik User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: DailyLog memiliki banyak Visit
     */
    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    /**
     * Relasi: DailyLog memiliki banyak Expense
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Cek apakah sudah absen masuk
     */
    public function hasStarted(): bool
    {
        return !is_null($this->start_time);
    }

    /**
     * Cek apakah sudah absen keluar
     */
    public function hasEnded(): bool
    {
        return !is_null($this->end_time);
    }

    /**
     * Hitung total KM dari odometer
     */
    public function getTotalKmAttribute(): float
    {
        if (!$this->start_odo_value || !$this->end_odo_value) {
            return 0;
        }
        return $this->end_odo_value - $this->start_odo_value;
    }
}
