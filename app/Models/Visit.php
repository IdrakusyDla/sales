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
        'status', // 'pending', 'in_progress', 'completed', 'failed'
        'notes',
        'reason',
        'photo_path',        // LEGACY (di-sync = departure_photo saat check-out)
        'lat',               // LEGACY (di-sync = departure coords saat check-out)
        'long',              // LEGACY
        'is_planned',        // true = dari rencana awal, false = kunjungan dadakan
        'arrival_photo',     // foto saat sampai di toko (check-in)
        'departure_photo',   // foto saat pulang dari toko (check-out)
        'arrival_time',
        'departure_time',
        'arrival_lat',
        'arrival_long',
    ];

    protected $casts = [
        'time' => 'datetime',
        'arrival_time' => 'datetime',
        'departure_time' => 'datetime',
        'is_planned' => 'boolean',
        'lat' => 'decimal:8',
        'long' => 'decimal:8',
        'arrival_lat' => 'decimal:8',
        'arrival_long' => 'decimal:8',
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
     * Cek apakah visit masih pending (belum check-in sama sekali)
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Cek apakah visit sudah check-in (sampai) tapi belum check-out (pulang)
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Cek apakah visit sudah check-in di toko (in_progress atau sudah selesai)
     */
    public function hasArrived(): bool
    {
        return in_array($this->status, ['in_progress', 'completed', 'failed'])
            && !is_null($this->arrival_photo);
    }

    /**
     * Cek apakah sales masih berada di dalam toko (sudah check-in, belum check-out)
     * Alias semantik untuk isInProgress()
     */
    public function isAtStore(): bool
    {
        return $this->isInProgress();
    }
}
