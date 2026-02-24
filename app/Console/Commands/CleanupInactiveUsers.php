<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupInactiveUsers extends Command
{
    protected $signature = 'users:cleanup';
    protected $description = 'Hapus user yang tidak aktif selama periode tertentu (default 3 bulan)';

    public function handle()
    {
        // Ambil setting retensi, default 3 bulan
        $retentionMonths = \App\Models\ApplicationSetting::get('retention_months', 3);
        $cutoffDate = now()->subMonths($retentionMonths);

        $this->info("Mencari user yang tidak aktif sebelum: {$cutoffDate->format('d M Y')}");

        $users = \App\Models\User::where('last_activity_at', '<', $cutoffDate)
            ->where('role', '!=', 'it') // Jangan hapus IT/Superadmin
            ->get();

        $count = 0;
        foreach ($users as $user) {
            $this->info("Menghapus user: {$user->name} ({$user->role}) - Last Active: {$user->last_activity_at}");
            // Hapus data terkait jika perlu (ON DELETE CASCADE biasanya handle ini di level DB, tapi manual lebih aman)
            // Namun untuk sekarang kita pakai standar delete()
            $user->delete();
            $count++;
        }

        $this->info("Selesai. Total user dihapus: {$count}");
    }
}
