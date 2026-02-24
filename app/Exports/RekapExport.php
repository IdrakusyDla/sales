<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RekapExport implements FromView, ShouldAutoSize, WithDrawings
{
    protected $type;
    protected $startDate;
    protected $endDate;
    protected $userId;
    protected $drawings = [];

    // 1. Terima data dari Controller
    public function __construct($type, $startDate, $endDate, $userId)
    {
        $this->type = $type;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->userId = $userId;
    }

    // 2. Siapkan Tampilan (View) Excel
    public function view(): View
    {
        // --- A. JIKA JENIS LAPORAN: AKTIVITAS (ABSEN) ---
        if ($this->type == 'activity') {
            $query = DB::table('daily_logs')
                ->join('users', 'daily_logs.user_id', '=', 'users.id')
                ->select('daily_logs.*', 'users.name as sales_name')
                ->whereBetween('daily_logs.date', [$this->startDate, $this->endDate]);

            $userName = null;
            if ($this->userId) {
                $query->where('daily_logs.user_id', $this->userId);
                $user = DB::table('users')->where('id', $this->userId)->first();
                $userName = $user ? $user->name : null;
            }

            $data = $query->orderBy('date', 'desc')->orderBy('start_time', 'desc')->get();

            // Ambil detail visits untuk setiap log (agar bisa diloop di view)
            foreach ($data as $row) {
                $row->visits = DB::table('visits')->where('daily_log_id', $row->id)->orderBy('time', 'asc')->get();
            }

            return view('exports.activity', [
                'logs' => $data,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'userName' => $userName
            ]);
        }

        // --- B. JIKA JENIS LAPORAN: KEUANGAN (REIMBURSE) ---
        elseif ($this->type == 'expense') {
            $query = DB::table('expenses')
                ->join('users', 'expenses.user_id', '=', 'users.id')
                ->leftJoin('daily_logs', 'expenses.daily_log_id', '=', 'daily_logs.id')
                ->select('expenses.*', 'users.name as sales_name', 'daily_logs.system_calculated_distance')
                ->whereBetween('expenses.date', [$this->startDate, $this->endDate]);

            $userName = null;
            if ($this->userId) {
                $query->where('expenses.user_id', $this->userId);
                $user = DB::table('users')->where('id', $this->userId)->first();
                $userName = $user ? $user->name : null;
            }

            $data = $query->orderBy('date', 'desc')->orderBy('created_at', 'desc')->get();

            // Siapkan data untuk drawings (gambar struk)
            $this->prepareDrawings($data);

            return view('exports.expense', [
                'expenses' => $data,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'userName' => $userName,
            ]);
        }

        // --- C. JIKA JENIS LAPORAN: GABUNGAN (COMBINED) ---
        else {
            // Ambil data daily logs
            $logsQuery = DB::table('daily_logs')
                ->join('users', 'daily_logs.user_id', '=', 'users.id')
                ->select('daily_logs.*', 'users.name as sales_name')
                ->whereBetween('daily_logs.date', [$this->startDate, $this->endDate]);

            $userName = null;
            if ($this->userId) {
                $logsQuery->where('daily_logs.user_id', $this->userId);
                $user = DB::table('users')->where('id', $this->userId)->first();
                $userName = $user ? $user->name : null;
            }

            $logs = $logsQuery->orderBy('date', 'asc')->orderBy('start_time', 'asc')->get();

            // Ambil visits dan expenses untuk setiap log
            foreach ($logs as $log) {
                $log->visits = DB::table('visits')->where('daily_log_id', $log->id)->orderBy('time', 'asc')->get();
                $log->expenses = DB::table('expenses')
                    ->where('daily_log_id', $log->id)
                    ->orderBy('created_at', 'asc')
                    ->get();
            }

            // Siapkan drawings untuk semua expenses
            $allExpenses = DB::table('expenses')
                ->whereBetween('date', [$this->startDate, $this->endDate])
                ->whereNotNull('daily_log_id');

            if ($this->userId) {
                $allExpenses->where('user_id', $this->userId);
            }

            $expensesForDrawings = $allExpenses->get();
            $this->prepareCombinedDrawings($logs, $expensesForDrawings);

            return view('exports.combined', [
                'logs' => $logs,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'userName' => $userName,
            ]);
        }
    }

    /**
     * Siapkan drawings untuk gambar struk
     */
    protected function prepareDrawings($expenses)
    {
        // Header: 3 baris (judul, periode, spacer)
        // Thead: 1 baris
        // Total: 4 baris sebelum data
        $row = 5; // Mulai dari baris 5 (setelah 3 header + 1 thead)

        foreach ($expenses as $expense) {
            // Kolom I (kolom ke-9) untuk Foto Struk
            if ($expense->photo_receipt && Storage::disk('public')->exists($expense->photo_receipt)) {
                try {
                    $drawing = new Drawing();
                    $drawing->setPath(Storage::disk('public')->path($expense->photo_receipt));
                    $drawing->setCoordinates('I' . $row);
                    $drawing->setWidth(120);
                    $drawing->setHeight(80);
                    $drawing->setOffsetX(5);
                    $drawing->setOffsetY(5);
                    $drawing->setResizeProportional(true);

                    $this->drawings[] = $drawing;
                } catch (\Exception $e) {
                    // Skip jika gambar tidak bisa dimuat
                }
            }

            // Kolom J (kolom ke-10) untuk Foto KM (jika ada)
            if (isset($expense->photo_km) && $expense->photo_km && Storage::disk('public')->exists($expense->photo_km)) {
                try {
                    $drawing = new Drawing();
                    $drawing->setPath(Storage::disk('public')->path($expense->photo_km));
                    $drawing->setCoordinates('J' . $row);
                    $drawing->setWidth(120);
                    $drawing->setHeight(80);
                    $drawing->setOffsetX(5);
                    $drawing->setOffsetY(5);
                    $drawing->setResizeProportional(true);

                    $this->drawings[] = $drawing;
                } catch (\Exception $e) {
                    // Skip jika gambar tidak bisa dimuat
                }
            }

            $row++;
        }
    }

    /**
     * Siapkan drawings untuk laporan gabungan
     */
    protected function prepareCombinedDrawings($logs, $expenses)
    {
        // Header: 3 baris (judul, periode, spacer)
        // Thead: 1 baris
        // Total: 4 baris sebelum data
        $row = 5; // Mulai dari baris 5 (setelah 3 header + 1 thead)

        foreach ($logs as $log) {
            $visitCount = count($log->visits ?? []);
            $expenseCount = count($log->expenses ?? []);

            // Hitung jumlah baris yang dibutuhkan (max antara visits dan expenses)
            $rowsNeeded = max(1, max($visitCount, $expenseCount));

            // Loop untuk setiap baris yang dibutuhkan
            for ($i = 0; $i < $rowsNeeded; $i++) {

                // --- 1. GAMBAR UNTUK DAILY LOG (ODO) ---
                // Hanya di baris pertama dari group ini
                if ($i == 0) {
                    // Kolom G (kolom ke-7) untuk Foto Odo Awal
                    if ($log->start_odo_photo && Storage::disk('public')->exists($log->start_odo_photo)) {
                        try {
                            $drawing = new Drawing();
                            $drawing->setPath(Storage::disk('public')->path($log->start_odo_photo));
                            $drawing->setCoordinates('G' . $row);
                            $drawing->setWidth(120);
                            $drawing->setHeight(80);
                            $drawing->setOffsetX(5);
                            $drawing->setOffsetY(5);
                            $drawing->setResizeProportional(true);
                            $this->drawings[] = $drawing;
                        } catch (\Exception $e) {
                        }
                    }

                    // Kolom I (kolom ke-9) untuk Foto Odo Akhir
                    if ($log->end_odo_photo && Storage::disk('public')->exists($log->end_odo_photo)) {
                        try {
                            $drawing = new Drawing();
                            $drawing->setPath(Storage::disk('public')->path($log->end_odo_photo));
                            $drawing->setCoordinates('I' . $row);
                            $drawing->setWidth(120);
                            $drawing->setHeight(80);
                            $drawing->setOffsetX(5);
                            $drawing->setOffsetY(5);
                            $drawing->setResizeProportional(true);
                            $this->drawings[] = $drawing;
                        } catch (\Exception $e) {
                        }
                    }
                }

                // --- 2. GAMBAR UNTUK EXPENSE (STRUK) ---
                // Jika ada expense di baris ini
                if (isset($log->expenses) && isset($log->expenses[$i])) {
                    $expense = $log->expenses[$i];

                    // Kolom P (kolom ke-16) untuk Foto Struk
                    if ($expense->photo_receipt && Storage::disk('public')->exists($expense->photo_receipt)) {
                        try {
                            $drawing = new Drawing();
                            $drawing->setPath(Storage::disk('public')->path($expense->photo_receipt));
                            $drawing->setCoordinates('P' . $row);
                            $drawing->setWidth(120);
                            $drawing->setHeight(80);
                            $drawing->setOffsetX(5);
                            $drawing->setOffsetY(5);
                            $drawing->setResizeProportional(true);

                            $this->drawings[] = $drawing;
                        } catch (\Exception $e) {
                            // Skip jika gambar tidak bisa dimuat
                        }
                    }
                }

                $row++;
            }
        }
    }

    /**
     * Return drawings untuk Excel
     */
    public function drawings()
    {
        return $this->drawings;
    }
}