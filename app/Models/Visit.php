<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visit extends Model
{
    protected $fillable = [
        'daily_log_id',
        'client_name',
        'time',
        'status', // 'pending', 'completed', 'failed'
        'notes',
        'reason',
        'photo_path',
        'lat',
        'long',
        'is_planned', // true = dari rencana awal, false = kunjungan dadakan
    ];

    protected $casts = [
        'time' => 'datetime',
        'is_planned' => 'boolean',
        'lat' => 'decimal:8',
        'long' => 'decimal:8',
    ];

    // ==========================================
    // RELASI
    // ==========================================

    /**
     * Relasi: Visit milik DailyLog
     */
    public function dailyLog(): BelongsTo
    {
        return $this->belongsTo(DailyLog::class);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Cek apakah visit sudah selesai (completed atau failed)
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, ['completed', 'failed']);
    }

    /**
     * Cek apakah visit masih pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
