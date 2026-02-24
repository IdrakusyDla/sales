<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DailyLog;
use App\Models\Expense;
use App\Models\FuelSetting;
use Carbon\Carbon;

class RecalculateFuelReimbursement extends Command
{
    protected $signature = 'fuel:recalculate {--date= : Tanggal spesifik (Y-m-d), kosongkan untuk semua}';
    protected $description = 'Hitung ulang reimburse bahan bakar untuk daily logs yang sudah ada';

    public function handle()
    {
        $this->info('🔄 Memulai perhitungan ulang reimburse bahan bakar...');
        $this->newLine();

        // Ambil fuel setting general (harus ada)
        $fuelSetting = FuelSetting::whereNull('user_id')
            ->where('is_active', true)
            ->first();

        if (!$fuelSetting) {
            $this->error('❌ Fuel setting general belum dibuat!');
            $this->info('💡 Silakan buat fuel setting terlebih dahulu melalui menu HRD/IT.');
            return 1;
        }

        $this->info("✅ Menggunakan fuel setting:");
        $this->info("   KM per Liter: {$fuelSetting->km_per_liter}");
        $this->info("   Harga per Liter: Rp " . number_format($fuelSetting->fuel_price, 0, ',', '.'));
        $this->newLine();

        // Query daily logs yang sudah absen keluar
        $query = DailyLog::whereNotNull('end_time')
            ->whereNotNull('end_odo_value')
            ->whereNotNull('start_odo_value');

        // Filter tanggal jika ada
        if ($this->option('date')) {
            $date = Carbon::parse($this->option('date'));
            $query->whereDate('date', $date);
            $this->info("📅 Filter tanggal: {$date->format('d/m/Y')}");
        }

        $dailyLogs = $query->with('expenses')->get();

        if ($dailyLogs->isEmpty()) {
            $this->warn('⚠️  Tidak ada daily log yang memenuhi kriteria.');
            return 0;
        }

        $this->info("📊 Ditemukan {$dailyLogs->count()} daily log(s)");
        $this->newLine();

        $processed = 0;
        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($dailyLogs as $dailyLog) {
            $totalKm = $dailyLog->total_km;
            
            if ($totalKm <= 0) {
                $skipped++;
                continue;
            }

            // Cek apakah sudah ada fuel expense
            $existingFuelExpense = $dailyLog->expenses()
                ->where('type', 'fuel')
                ->where('is_auto_calculated', true)
                ->first();

            // Ambil setting untuk user ini (individual atau general)
            $userFuelSetting = FuelSetting::getActiveSettingForUser($dailyLog->user_id);
            
            if (!$userFuelSetting) {
                $this->warn("⚠️  Daily Log #{$dailyLog->id} ({$dailyLog->date->format('d/m/Y')}): Tidak ada fuel setting untuk user ini, menggunakan general setting");
                $userFuelSetting = $fuelSetting;
            }

            // Hitung reimburse
            $reimbursementAmount = $userFuelSetting->calculateFuelReimbursement($totalKm);

            if ($reimbursementAmount <= 0) {
                $skipped++;
                continue;
            }

            $deadline = Expense::calculateDeadline($dailyLog->date);

            if ($existingFuelExpense) {
                // Update yang sudah ada
                $existingFuelExpense->update([
                    'amount' => $reimbursementAmount,
                    'km_total' => $totalKm,
                    'deadline_date' => $deadline,
                    'note' => "Auto calculated: {$totalKm} KM ÷ {$userFuelSetting->km_per_liter} KM/L × Rp " . number_format($userFuelSetting->fuel_price, 0, ',', '.') . "/L",
                ]);
                $updated++;
                $this->info("✅ Updated: Daily Log #{$dailyLog->id} ({$dailyLog->date->format('d/m/Y')}) - {$totalKm} KM = Rp " . number_format($reimbursementAmount, 0, ',', '.'));
            } else {
                // Buat baru
                Expense::create([
                    'user_id' => $dailyLog->user_id,
                    'daily_log_id' => $dailyLog->id,
                    'date' => $dailyLog->date,
                    'type' => 'fuel',
                    'amount' => $reimbursementAmount,
                    'km_total' => $totalKm,
                    'is_auto_calculated' => true,
                    'deadline_date' => $deadline,
                    'photo_receipt' => null, // Fuel expense tidak perlu foto
                    'note' => "Auto calculated: {$totalKm} KM ÷ {$userFuelSetting->km_per_liter} KM/L × Rp " . number_format($userFuelSetting->fuel_price, 0, ',', '.') . "/L",
                ]);
                $created++;
                $this->info("✨ Created: Daily Log #{$dailyLog->id} ({$dailyLog->date->format('d/m/Y')}) - {$totalKm} KM = Rp " . number_format($reimbursementAmount, 0, ',', '.'));
            }

            $processed++;
        }

        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info("📊 Ringkasan:");
        $this->info("   ✅ Diproses: {$processed}");
        $this->info("   ✨ Dibuat baru: {$created}");
        $this->info("   🔄 Diupdate: {$updated}");
        $this->info("   ⏭️  Dilewati: {$skipped}");
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        return 0;
    }
}

