<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DailyLog;
use App\Models\Expense;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SupervisorController extends Controller
{
    // ==========================================
    // HOME SUPERVISOR (Sama seperti Sales Home)
    // ==========================================

    public function home()
    {
        $user = Auth::user();

        $todayLogs = DailyLog::where('user_id', $user->id)
            ->where('date', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->get();

        $latestLog = $todayLogs->first();

        return view('sales.home', compact('user', 'todayLogs', 'latestLog'));
    }

    // ==========================================
    // DASHBOARD SUPERVISOR (Tim Saya)
    // ==========================================

    public function dashboard()
    {
        $supervisor = Auth::user();

        // Ambil sales yang ditugaskan ke supervisor ini (Support Double Logic: Pivot OR Legacy)
        $sales = User::whereHas('supervisors', function($q) use ($supervisor) {
                // Gunakan table name explicit untuk menghindari ambiguous column
                $q->where('supervisor_sales.supervisor_id', $supervisor->id);
            })
            ->orWhere('users.supervisor_id', $supervisor->id) // Legacy fallback (explicit table)
            ->where('role', 'sales')
            ->get();

        // Ambil data absen hari ini untuk semua sales
        $todayAbsensi = [];
        foreach ($sales as $sale) {
            $dailyLog = DailyLog::where('user_id', $sale->id)
                ->where('date', Carbon::today())
                ->first();

            $todayAbsensi[$sale->id] = [
                'user' => $sale,
                'dailyLog' => $dailyLog,
                'hasStarted' => $dailyLog ? $dailyLog->hasStarted() : false,
                'hasEnded' => $dailyLog ? $dailyLog->hasEnded() : false,
                'visitsCount' => $dailyLog ? $dailyLog->visits()->count() : 0,
                'completedVisits' => $dailyLog ? $dailyLog->visits()->where('status', 'completed')->count() : 0,
            ];
        }

        // Statistik pending reimburse untuk supervisor
        $stats = [
            'pending_spv' => Expense::where('status', 'pending_spv')
                ->whereHas('user', function ($q) use ($supervisor) {
                    $q->where('supervisor_id', $supervisor->id)
                        ->orWhereHas('supervisors', function ($q2) use ($supervisor) {
                            $q2->where('users.id', $supervisor->id);
                        });
                })->count(),
        ];

        return view('supervisor.dashboard', compact('sales', 'todayAbsensi', 'stats'));
    }

    // ==========================================
    // DETAIL SALES
    // ==========================================

    public function showSales($id)
    {
        $supervisor = Auth::user();

        // Cek apakah sales ini ditugaskan ke supervisor ini (Support Pivot OR Legacy)
        $sales = User::where('id', $id)
            ->where(function($query) use ($supervisor) {
                $query->whereHas('supervisors', function($q) use ($supervisor) {
                    $q->where('supervisor_sales.supervisor_id', $supervisor->id);
                })
                ->orWhere('users.supervisor_id', $supervisor->id); // Legacy fallback
            })
            ->where('role', 'sales')
            ->firstOrFail();

        // Ambil daily logs dengan relasi
        $dailyLogs = DailyLog::where('user_id', $sales->id)
            ->with(['visits', 'expenses'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Hitung reimburse menunggu persetujuan SPV milik sales ini (badge tombol persetujuan)
        $pendingReimburseCount = Expense::where('user_id', $sales->id)
            ->where('status', 'pending_spv')
            ->count();

        return view('supervisor.show_sales', compact('sales', 'dailyLogs', 'pendingReimburseCount'));
    }

    // ==========================================
    // REVISI REIMBURSE (SPV - jika Finance reject ke SPV)
    // ==========================================

    /**
     * SPV merevisi expense yang ditolak Finance dan perlu revisi
     */
    public function reviseReimburse(Request $request, $expenseId)
    {
        $supervisor = Auth::user();

        // Ambil expense
        $expense = Expense::with('dailyLog.user')->findOrFail($expenseId);

        // Cek apakah expense memerlukan revisi dari SPV
        if (!$expense->needsRevisionBySPV()) {
            return back()->with('error', 'Reimburse ini tidak memerlukan revisi dari Anda.');
        }

        // Cek apakah SPV berwenang (expense dari sales bawahan atau SPV sendiri)
        $expenseOwner = $expense->dailyLog->user;
        $isOwnExpense = $expenseOwner->id === $supervisor->id;
        $isSubordinateExpense = $expenseOwner->supervisor_id === $supervisor->id;

        if (!$isOwnExpense && !$isSubordinateExpense) {
            abort(403, 'Anda tidak memiliki akses untuk merevisi reimburse ini.');
        }

        $request->validate([
            'photo_receipt' => 'required', // Base64 image
            'note' => 'nullable|string',
        ]);

        // Upload foto struk baru
        $receiptPath = $this->saveBase64Image($request->photo_receipt, 'expenses');

        // Tentukan status target setelah revisi:
        // Jika sudah di-approve oleh HRD sebelumnya, berarti Finance yang meminta revisi, kembalikan ke Finance.
        // Jika belum di-approve oleh HRD, berarti HRD yang meminta revisi, kembalikan ke HRD.
        $targetStatus = $expense->approved_by_hrd_at ? 'pending_finance' : 'pending_hrd';

        // Update expense dengan data baru
        $expense->update([
            'photo_receipt' => $receiptPath,
            'note' => $request->note ?? $expense->note,
            'status' => $targetStatus,
            'rejection_note' => null,
            'rejection_type' => null,
            'revised_at' => now(),
            // revision_count TIDAK di-increment di sini: sudah dicatat saat approver menolak/minta revisi.
        ]);

        $approverLabel = $targetStatus === 'pending_finance' ? 'Finance' : 'HRD';
        return back()->with('success', "Revisi berhasil dikirim! Menunggu persetujuan {$approverLabel}.");
    }

    /**
     * Simpan base64 image ke storage
     */
    private function saveBase64Image($base64_string, $folder)
    {
        $image_parts = explode(";base64,", $base64_string);
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = $folder . '/' . uniqid() . '.png';
        Storage::disk('public')->put($fileName, $image_base64);
        return $fileName;
    }

    // ==========================================
    // SUPERVISOR BISA ABSEN SEPERTI SALES
    // ==========================================
    // Note: Supervisor menggunakan SalesController untuk absen
    // karena logikanya sama persis
}
