<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseHistory extends Model
{
    protected $fillable = [
        'expense_id',
        'status',
        'changed_by',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // ==========================================
    // RELASI
    // ==========================================

    /**
     * Relasi: History milik Expense
     */
    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    /**
     * Relasi: User yang mengubah status
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Cek apakah history ini adalah approval
     */
    public function isApproval(): bool
    {
        return in_array($this->status, ['pending_hrd', 'pending_finance', 'approved']);
    }

    /**
     * Cek apakah history ini adalah rejection
     */
    public function isRejection(): bool
    {
        return $this->status === 'rejected_permanent' ||
               str_starts_with($this->status, 'needs_revision');
    }

    /**
     * Get label status dalam bahasa Indonesia
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
}
