<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DailyLog;
use App\Models\ApplicationSetting;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CleanupOldPhotos extends Command
{
    /**
     * The name and signature of the console command.
     * Including --months option for manual execution override.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-old-photos {--months= : Override retention period (months) for manual cleanup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old photos from Daily Logs and Expenses based on retention settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. Determine Retention Period
        $monthsInput = $this->option('months');

        if ($monthsInput) {
            $retentionMonths = (int) $monthsInput;
            $this->info("Running MANUAL cleanup for photos older than {$retentionMonths} months.");
        } else {
            $retentionMonths = (int) ApplicationSetting::get('retention_months', 3);
            $this->info("Running AUTOMATIC cleanup for photos older than {$retentionMonths} months (default/setting).");
        }

        if ($retentionMonths < 1) {
            $this->error("Invalid retention period: {$retentionMonths}. Must be at least 1.");
            return;
        }

        // 2. Calculate Cutoff Date
        $cutoffDate = Carbon::now()->subMonths($retentionMonths)->startOfDay();
        $this->info("Cutoff Date: " . $cutoffDate->toDateTimeString());

        // 3. Process Daily Logs
        $logs = DailyLog::where('date', '<', $cutoffDate)->get();
        $countLogs = 0;
        $countFiles = 0;

        foreach ($logs as $log) {
            // Delete Start Photo
            if ($log->start_photo && Storage::disk('public')->exists($log->start_photo)) {
                Storage::disk('public')->delete($log->start_photo);
                $log->start_photo = null; // Or mark as [EXPIRED]
                $countFiles++;
            }

            // Delete End Photo
            if ($log->end_photo && Storage::disk('public')->exists($log->end_photo)) {
                Storage::disk('public')->delete($log->end_photo);
                $log->end_photo = null;
                $countFiles++;
            }

            // Delete Odometer Photos (if columns exist)
            if (isset($log->start_odo_photo) && $log->start_odo_photo && Storage::disk('public')->exists($log->start_odo_photo)) {
                Storage::disk('public')->delete($log->start_odo_photo);
                $log->start_odo_photo = null;
                $countFiles++;
            }
            if (isset($log->end_odo_photo) && $log->end_odo_photo && Storage::disk('public')->exists($log->end_odo_photo)) {
                Storage::disk('public')->delete($log->end_odo_photo);
                $log->end_odo_photo = null;
                $countFiles++;
            }

            // Save Log Changes
            // Only update if we changed something (optimization) - but here simply saving is fine
            $log->save();
            $countLogs++;

            // 4. Process Related Expenses (Receipts) for this Log
            foreach ($log->expenses as $expense) {
                if ($expense->photo_receipt && Storage::disk('public')->exists($expense->photo_receipt)) {
                    Storage::disk('public')->delete($expense->photo_receipt);
                    $expense->photo_receipt = null; // Mark as null implies "No Receipt" or we can store "[EXPIRED]"
                    // expense->photo_receipt = '[EXPIRED]' might break UI if it expects an image path. Null is safer for now.
                    $expense->save();
                    $countFiles++;
                }

                // Original Receipt Backup
                if (isset($expense->original_photo_receipt) && $expense->original_photo_receipt && Storage::disk('public')->exists($expense->original_photo_receipt)) {
                    Storage::disk('public')->delete($expense->original_photo_receipt);
                    $expense->original_photo_receipt = null;
                    $expense->save();
                    $countFiles++;
                }
            }
        }

        $this->info("Cleanup Completed.");
        $this->info("Processed Logs: {$countLogs}");
        $this->info("Deleted Files: {$countFiles}");

        Log::info("CleanupOldPhotos executed. Retention: {$retentionMonths} months. Deleted {$countFiles} files from {$countLogs} logs.");
    }
}