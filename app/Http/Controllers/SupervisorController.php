<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DailyLog;
use App\Models\Visit;
use App\Models\Expense;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;
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
    // DASHBOARD SUPERVISOR (Aktivitas Terbaru Tim)
    // ==========================================

    public function dashboard(Request $request)
    {
        $supervisor = Auth::user();
        $subordinateIds = $this->getSubordinateSalesQuery($supervisor)->pluck('id');

        $perPage = in_array((int) $request->get('per_page'), [10, 20, 50, 100])
            ? (int) $request->get('per_page')
            : 20;

        $type = $request->get('type', 'all');
        if (!in_array($type, ['all', 'check_in', 'check_out', 'visit'])) {
            $type = 'all';
        }

        $dateFrom = $request->filled('date_from') ? $request->date_from : null;
        $dateTo = $request->filled('date_to') ? $request->date_to : null;

        $activities = $this->buildTeamActivities($subordinateIds, $type, $dateFrom, $dateTo);

        $page = max(1, (int) $request->get('page', 1));
        $activities = new LengthAwarePaginator(
            $activities->forPage($page, $perPage),
            $activities->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Statistik hari ini, scoped ke bawahan supervisor
        $today = Carbon::today();
        $todayStats = [
            'check_in' => $subordinateIds->isNotEmpty()
                ? DailyLog::whereIn('user_id', $subordinateIds)->whereDate('date', $today)->whereNotNull('start_time')->count()
                : 0,
            'check_out' => $subordinateIds->isNotEmpty()
                ? DailyLog::whereIn('user_id', $subordinateIds)->whereDate('date', $today)->whereNotNull('end_time')->count()
                : 0,
            'visits' => $subordinateIds->isNotEmpty()
                ? Visit::whereHas('dailyLog', function ($q) use ($subordinateIds, $today) {
                    $q->whereIn('user_id', $subordinateIds)->whereDate('date', $today);
                })->count()
                : 0,
            'total_active' => User::whereIn('id', $subordinateIds)->where('is_active', true)->count(),
        ];

        $totalTeam = $subordinateIds->count();

        return view('supervisor.dashboard', compact('activities', 'todayStats', 'totalTeam', 'perPage'));
    }

    // ==========================================
    // DAFTAR KARYAWAN (Page lama "Tim Saya")
    // ==========================================

    public function teamList()
    {
        $supervisor = Auth::user();

        // Ambil sales yang ditugaskan ke supervisor ini (Support Double Logic: Pivot or Legacy)
        $sales = $this->getSubordinateSalesQuery($supervisor)->get();

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
                'failedVisits' => $dailyLog ? $dailyLog->visits()->where('status', 'failed')->count() : 0,
                'activeVisits' => $dailyLog ? $dailyLog->visits()->whereIn('status', ['pending', 'in_progress'])->count() : 0,
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

        return view('supervisor.team', compact('sales', 'todayAbsensi', 'stats'));
    }

    // ==========================================
    // HELPER: Query sales bawahan supervisor (Pivot OR Legacy)
    // ==========================================

    private function getSubordinateSalesQuery($supervisor)
    {
        return User::where('role', 'sales')
            ->where(function ($query) use ($supervisor) {
                $query->whereHas('supervisors', function ($q) use ($supervisor) {
                    $q->where('supervisor_sales.supervisor_id', $supervisor->id);
                })
                ->orWhere('users.supervisor_id', $supervisor->id); // Legacy fallback
            });
    }

    // ==========================================
    // HELPER: Bangun timeline aktivitas dari kumpulan user
    // ==========================================

    private function buildTeamActivities($userIds, $type, $dateFrom, $dateTo)
    {
        $activities = collect();

        if (empty($userIds) || (is_countable($userIds) ? count($userIds) === 0 : !$userIds)) {
            return $activities;
        }

        if (!in_array($type, ['visit'])) {
            $logsQuery = DailyLog::whereIn('user_id', $userIds)
                ->where(function ($q) {
                    $q->whereNotNull('start_time')->orWhereNotNull('end_time');
                })
                ->with('user');
            if ($dateFrom) {
                $logsQuery->whereDate('date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $logsQuery->whereDate('date', '<=', $dateTo);
            }

            foreach ($logsQuery->get() as $log) {
                if ($log->start_time && $type !== 'check_out') {
                    $activities->push([
                        'type' => 'check_in',
                        'user' => $log->user,
                        'time' => $log->start_time,
                        'date' => $log->date,
                        'meta' => $log,
                        'sort_time' => $log->date . ' ' . $log->start_time,
                    ]);
                }
                if ($log->end_time && $type !== 'check_in') {
                    $activities->push([
                        'type' => 'check_out',
                        'user' => $log->user,
                        'time' => $log->end_time,
                        'date' => $log->date,
                        'meta' => $log,
                        'sort_time' => $log->date . ' ' . $log->end_time,
                    ]);
                }
            }
        }

        if (!in_array($type, ['check_in', 'check_out'])) {
            $visits = Visit::whereHas('dailyLog', function ($q) use ($userIds, $dateFrom, $dateTo) {
                $q->whereIn('user_id', $userIds);
                if ($dateFrom) {
                    $q->whereDate('date', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $q->whereDate('date', '<=', $dateTo);
                }
            })
                ->with(['dailyLog.user'])
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($visits as $visit) {
                $activities->push([
                    'type' => 'visit',
                    'user' => $visit->dailyLog->user,
                    'time' => $visit->time,
                    'date' => $visit->dailyLog->date,
                    'meta' => $visit,
                    'sort_time' => $visit->dailyLog->date . ' ' . $visit->time,
                ]);
            }
        }

        return $activities->sortByDesc('sort_time')->values();
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
