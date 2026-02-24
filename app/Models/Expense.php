<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Expense extends Model
{
    protected $fillable = [
        'user_id',
        'daily_log_id',
        'date',
        'type', // 'fuel', 'hotel', 'toll', 'transport', 'other'
        'amount',
        'note',
        'photo_receipt',
        'photo_km', // Legacy but keep for compatibility
        'km_total', // Total KM untuk bahan bakar (auto calculated)
        'is_auto_calculated', // true = bahan bakar otomatis, false = manual
        'deadline_date', // Batas pengisian (Selasa minggu depan)
        'status', // 'pending_spv', 'pending_hrd', 'pending_finance', 'needs_revision_sales', 'needs_revision_spv', 'approved', 'rejected_permanent'
        'approved_by_spv_at',
        'approved_by_hrd_at',
        'approved_by_finance_at',
        'approved_by_spv_id',
        'approved_by_hrd_id',
        'approved_by_finance_id',
        'rejection_note',
        'rejection_type', // 'revisi' atau 'permanent'
        'submitted_by', // User ID yang pertama kali submit
        'revised_at',
        'revision_count',
    ];

    protected $casts = [
        'date' => 'date',
        'deadline_date' => 'date',
        'amount' => 'decimal:2',
        'km_total' => 'decimal:2',
        'is_auto_calculated' => 'boolean',
        'is_generated_receipt' => 'boolean',
        'approved_by_spv_at' => 'datetime',
        'approved_by_hrd_at' => 'datetime',
        'approved_by_finance_at' => 'datetime',
        'revised_at' => 'datetime',
        'revision_count' => 'integer',
    ];

    // ==========================================
    // RELASI
    // ==========================================

    /**
     * Relasi: Expense milik User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: Expense milik DailyLog
     */
    public function dailyLog(): BelongsTo
    {
        return $this->belongsTo(DailyLog::class);
    }

    /**
     * Relasi: User yang submit expense
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Relasi: Supervisor yang approve
     */
    public function approvedBySpv(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_spv_id');
    }

    /**
     * Relasi: HRD yang approve
     */
    public function approvedByHrd(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_hrd_id');
    }

    /**
     * Relasi: Finance yang approve
     */
    public function approvedByFinance(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_finance_id');
    }

    /**
     * Relasi: Expense memiliki banyak History
     */
    public function histories(): HasMany
    {
        return $this->hasMany(ExpenseHistory::class)->orderBy('created_at', 'desc');
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Cek apakah expense adalah bahan bakar (auto calculated)
     */
    public function isFuel(): bool
    {
        return $this->type === 'fuel' && $this->is_auto_calculated;
    }

    /**
     * Cek apakah sudah melewati deadline
     */
    public function isOverdue(): bool
    {
        if (!$this->deadline_date) {
            return false;
        }
        return Carbon::today()->gt($this->deadline_date);
    }

    /**
     * Cek apakah expense ditolak (baik revisi maupun permanent)
     */
    public function isRejected(): bool
    {
        return in_array($this->status, [
            'needs_revision_sales',
            'needs_revision_spv',
            'rejected_permanent',
        ]);
    }

    /**
     * Cek apakah expense perlu direvisi oleh Sales
     */
    public function needsRevisionBySales(): bool
    {
        return $this->status === 'needs_revision_sales';
    }

    /**
     * Cek apakah expense perlu direvisi oleh SPV
     */
    public function needsRevisionBySPV(): bool
    {
        return $this->status === 'needs_revision_spv';
    }

    /**
     * Cek apakah expense bisa direvisi
     */
    public function canRevise(): bool
    {
        return in_array($this->status, ['needs_revision_sales', 'needs_revision_spv']);
    }

    /**
     * Cek apakah expense ditolak permanen
     */
    public function isRejectedPermanent(): bool
    {
        return $this->status === 'rejected_permanent';
    }

    // ==========================================
    // HELPER METHODS - 3 LEVEL APPROVAL
    // ==========================================

    /**
     * Cek apakah menunggu approval Supervisor
     */
    public function isPendingSpv(): bool
    {
        return $this->status === 'pending_spv';
    }

    /**
     * Cek apakah menunggu approval HRD
     */
    public function isPendingHrd(): bool
    {
        return $this->status === 'pending_hrd';
    }

    /**
     * Cek apakah menunggu approval Finance
     */
    public function isPendingFinance(): bool
    {
        return $this->status === 'pending_finance';
    }

    /**
     * Cek apakah sudah di-approve Supervisor
     */
    public function isApprovedBySpv(): bool
    {
        return !is_null($this->approved_by_spv_at);
    }

    /**
     * Cek apakah sudah di-approve HRD
     */
    public function isApprovedByHrd(): bool
    {
        return !is_null($this->approved_by_hrd_at);
    }

    /**
     * Cek apakah sudah di-approve Finance
     */
    public function isApprovedByFinance(): bool
    {
        return !is_null($this->approved_by_finance_at);
    }

    /**
     * Cek apakah sudah final approved
     */
    public function isFinalApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Get status label dalam bahasa Indonesia
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending_spv' => 'Menunggu Supervisor',
            'pending_hrd' => 'Menunggu HRD',
            'pending_finance' => 'Menunggu Finance',
            'approved' => 'Disetujui',
            'rejected_permanent' => 'Ditolak Permanen',
            'needs_revision_sales' => 'Perlu Revisi (Sales)',
            'needs_revision_spv' => 'Perlu Revisi (Supervisor)',
            default => $this->status,
        };
    }

    /**
     * Cek apakah user (berdasarkan role) yang seharusnya approve sekarang
     */
    public function shouldBeApprovedBy(User $user): bool
    {
        return match ($this->status) {
            'pending_spv' => $user->isSupervisor(),
            'pending_hrd' => $user->isHrd(),
            'pending_finance' => $user->role === 'finance',
            default => false,
        };
    }

    /**
     * Get next approver role name
     */
    public function getNextApproverAttribute(): ?string
    {
        return match ($this->status) {
            'pending_spv' => 'Supervisor',
            'pending_hrd' => 'HRD',
            'pending_finance' => 'Finance',
            default => null,
        };
    }

    /**
     * Cek apakah expense dari Supervisor (bukan Sales)
     */
    public function isFromSupervisor(): bool
    {
        return $this->user && $this->user->role === 'supervisor';
    }

    /**
     * Cek apakah expense dari Sales
     */
    public function isFromSales(): bool
    {
        return $this->user && $this->user->role === 'sales';
    }

    /**
     * Hitung deadline date berdasarkan tanggal absen
     * Deadline: Selasa setelah akhir minggu (Minggu-Sabtu)
     * 
     * Contoh:
     * - Absen Minggu 11 Jan - Sabtu 17 Jan → Deadline: Selasa 20 Jan
     * - Absen Minggu 18 Jan - Sabtu 24 Jan → Deadline: Selasa 27 Jan
     */
    public static function calculateDeadline(Carbon $date): Carbon
    {
        // Cari akhir minggu (Sabtu) dari tanggal absen
        // Carbon: Sunday = 0, Monday = 1, ..., Saturday = 6
        $dayOfWeek = $date->dayOfWeek; // 0 = Sunday, 6 = Saturday

        // Hitung berapa hari sampai Sabtu
        // Jika hari Minggu (0), Sabtu adalah 6 hari ke depan
        // Jika hari Senin (1), Sabtu adalah 5 hari ke depan
        // Jika hari Sabtu (6), Sabtu adalah hari ini (0 hari)
        $daysUntilSaturday = (Carbon::SATURDAY - $dayOfWeek + 7) % 7;

        // Jika hari ini Sabtu, maka daysUntilSaturday = 0, tetap di minggu ini
        $endOfWeek = $date->copy()->addDays($daysUntilSaturday);

        // Deadline adalah Selasa setelah Sabtu (2 hari setelah Sabtu = Senin, 3 hari = Selasa)
        // Dari Sabtu ke Selasa = 3 hari
        $deadline = $endOfWeek->copy()->addDays(3);

        return $deadline;
    }
}
