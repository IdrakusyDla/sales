<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'role',
        'supervisor_id',
        'is_active',
        'last_activity_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_activity_at' => 'datetime',
        ];
    }

    // ==========================================
    // RELASI
    // ==========================================

    /**
     * Relasi: Supervisor memiliki banyak Sales
     */
    public function sales()
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    /**
     * Relasi: Sales memiliki Supervisor (Single - Legacy Support / Primary)
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Relasi: Sales memiliki BANYAK Supervisor (Multi - New System)
     */
    public function supervisors()
    {
        return $this->belongsToMany(User::class, 'supervisor_sales', 'sales_id', 'supervisor_id')
            ->withTimestamps();
    }

    /**
     * Relasi: Supervisor memiliki BANYAK Sales (Multi - New System)
     */
    public function subordinates()
    {
        return $this->belongsToMany(User::class, 'supervisor_sales', 'supervisor_id', 'sales_id')
            ->withTimestamps();
    }

    /**
     * Relasi: User memiliki banyak Daily Logs
     */
    public function dailyLogs()
    {
        return $this->hasMany(DailyLog::class);
    }

    /**
     * Relasi: User memiliki banyak Expenses
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Relasi: User memiliki banyak Fuel Settings (individual)
     */
    public function fuelSettings()
    {
        return $this->hasMany(FuelSetting::class);
    }

    // ==========================================
    // HELPER METHODS - CEK ROLE
    // ==========================================

    /**
     * Cek apakah user adalah Sales
     */
    public function isSales(): bool
    {
        return $this->role === 'sales';
    }

    /**
     * Cek apakah user adalah Supervisor
     */
    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    /**
     * Cek apakah user adalah HRD
     */
    public function isHrd(): bool
    {
        return $this->role === 'hrd';
    }

    /**
     * Cek apakah user adalah IT (Superadmin)
     */
    public function isIt(): bool
    {
        return $this->role === 'it';
    }

    /**
     * Cek apakah user adalah Finance
     */
    public function isFinance(): bool
    {
        return $this->role === 'finance';
    }

    /**
     * Cek apakah user bisa melihat semua data (HRD, Finance, atau IT)
     */
    public function canViewAll(): bool
    {
        return $this->isHrd() || $this->isFinance() || $this->isIt();
    }

    /**
     * Cek apakah user bisa membuat akun HRD (hanya IT)
     */
    public function canCreateHrd(): bool
    {
        return $this->isIt();
    }

    /**
     * Cek apakah user bisa membuat akun Finance (IT atau HRD)
     */
    public function canCreateFinance(): bool
    {
        return $this->isIt() || $this->isHrd();
    }

    /**
     * Cek apakah password masih menggunakan password default
     */
    public function hasDefaultPassword(): bool
    {
        $defaultPasswords = [
            'sales' => 'sales123',
            'supervisor' => 'supervisor123',
            'hrd' => 'hrd123',
            'it' => 'admin123',
            'finance' => 'finance123',
        ];

        $defaultPassword = $defaultPasswords[$this->role] ?? null;

        if (!$defaultPassword) {
            return false; // Role tidak dikenal, anggap sudah ganti
        }

        return \Illuminate\Support\Facades\Hash::check($defaultPassword, $this->password);
    }
}
