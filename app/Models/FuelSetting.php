<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelSetting extends Model
{
    protected $fillable = [
        'user_id', // null = general setting, ada nilai = individual setting
        'role', // null = general/individual, ada nilai = role-based setting ('sales', 'supervisor')
        'km_per_liter', // KM per liter (misal: 10 berarti 1 liter = 10 km)
        'fuel_price', // Harga bahan bakar per liter
        'is_active', // Aktif atau tidak
    ];

    protected $casts = [
        'km_per_liter' => 'decimal:2',
        'fuel_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // ==========================================
    // RELASI
    // ==========================================

    /**
     * Relasi: FuelSetting milik User (optional, null untuk general)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Cek apakah ini general setting
     */
    public function isGeneral(): bool
    {
        return is_null($this->user_id) && empty($this->role);
    }

    /**
     * Cek apakah ini role-based setting
     */
    public function isRoleBase(): bool
    {
        return !empty($this->role) && is_null($this->user_id);
    }

    /**
     * Hitung nominal reimburse bahan bakar berdasarkan KM
     */
    public function calculateFuelReimbursement(float $kmTotal): float
    {
        if (!$this->is_active || $this->km_per_liter <= 0 || $this->fuel_price <= 0) {
            return 0;
        }

        // Hitung liter yang dibutuhkan
        // km_per_liter = berapa KM per 1 liter, jadi liter = km / km_per_liter
        $litersNeeded = $kmTotal / $this->km_per_liter;
        
        // Hitung total harga
        return $litersNeeded * $this->fuel_price;
    }

    /**
     * Ambil setting aktif untuk user tertentu
     * Priority: Individual > Role > General
     */
    public static function getActiveSettingForUser(?User $user = null): ?self
    {
        if ($user) {
            // Priority 1: Individual Setting
            $individual = self::where('user_id', $user->id)
                ->where('is_active', true)
                ->first();
            
            if ($individual) {
                return $individual;
            }

            // Priority 2: Role-based Setting
            $roleSetting = self::whereNull('user_id')
                ->where('role', $user->role)
                ->where('is_active', true)
                ->first();

            if ($roleSetting) {
                return $roleSetting;
            }
        }

        // Priority 3: General Setting (user_id null, role null/empty)
        return self::whereNull('user_id')
            ->where(function ($query) {
                $query->whereNull('role')->orWhere('role', '');
            })
            ->where('is_active', true)
            ->first();
    }
}
