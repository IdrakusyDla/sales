<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\DailyLog;
use App\Models\Visit;
use App\Models\Expense;
use App\Models\ExpenseHistory;
use App\Models\FuelSetting;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RekapExport;
use Carbon\Carbon;

class FinanceController extends Controller
{
    // ==========================================
    // DASHBOARD FINANCE
    // ==========================================

    public function dashboard(Request $request)
    {
        // Ambil semua sales dan supervisor
        $query = User::whereIn('role', ['sales', 'supervisor']);

        // Fitur Search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('name')->get();

        // Statistik quick summary
        $stats = [
            'pending_finance' => Expense::where('status', 'pending_finance')->count(),
            'approved_this_month' => Expense::where('status', 'approved')
                ->whereMonth('approved_by_finance_at', Carbon::now()->month)
                ->whereYear('approved_by_finance_at', Carbon::now()->year)
                ->count(),
            'rejected_this_month' => Expense::where('status', 'rejected_permanent')
                ->whereMonth('updated_at', Carbon::now()->month)
                ->whereYear('updated_at', Carbon::now()->year)
                ->count(),
            'total_expenses_this_month' => Expense::where('status', 'approved')
                ->whereMonth('approved_by_finance_at', Carbon::now()->month)
                ->whereYear('approved_by_finance_at', Carbon::now()->year)
                ->sum('amount'),
        ];

        return view('finance.dashboard', compact('users', 'stats'));
    }

    // ==========================================
    // APPROVAL REIMBURSE
    // ==========================================

    /**
     * List reimburse yang perlu di-approve Finance
     * Hanya yang statusnya pending_finance
     */
    public function reimbursementApproval(Request $request)
    {
        // Query: expenses yang statusnya pending_finance
        $query = Expense::with(['user', 'dailyLog', 'histories'])
            ->where('status', 'pending_finance')
            ->orderBy('date', 'desc');

        // Filter berdasarkan tanggal
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        // Filter berdasarkan user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter berdasarkan type expense
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $expenses = $query->paginate(20);

        // Get semua users untuk filter dropdown
        $users = User::whereIn('role', ['sales', 'supervisor'])
            ->orderBy('name')
            ->get();

        return view('finance.approval.index', compact('expenses', 'users'));
    }

    /**
     * Approve reimburse
     */
    public function approveReimburse(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        // Validasi: hanya boleh approve jika status pending_finance
        if ($expense->status !== 'pending_finance') {
            return back()->withErrors(['msg' => 'Expense ini tidak bisa di-approve. Status: ' . $expense->status_label]);
        }

        DB::beginTransaction();
        try {
            // Update expense
            $expense->update([
                'status' => 'approved',
                'approved_by_finance_at' => now(),
                'approved_by_finance_id' => auth()->id(),
            ]);

            // Catat history
            ExpenseHistory::create([
                'expense_id' => $expense->id,
                'status' => 'approved',
                'changed_by' => auth()->id(),
                'notes' => $request->notes ?? 'Disetujui oleh Finance',
            ]);

            DB::commit();
            return back()->with('success', 'Reimburse berhasil disetujui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => 'Gagal approve: ' . $e->getMessage()]);
        }
    }

    /**
     * Bulk approve reimburse
     */
    public function bulkApproveReimburse(Request $request)
    {
        $request->validate([
            'expense_ids' => 'required|array',
            'expense_ids.*' => 'exists:expenses,id',
            'notes' => 'nullable|string',
        ]);

        $expenseIds = $request->expense_ids;

        DB::beginTransaction();
        try {
            $approvedCount = 0;

            foreach ($expenseIds as $id) {
                $expense = Expense::where('status', 'pending_finance')->find($id);

                if ($expense) {
                    $expense->update([
                        'status' => 'approved',
                        'approved_by_finance_at' => now(),
                        'approved_by_finance_id' => auth()->id(),
                    ]);

                    // Catat history untuk tiap expense
                    ExpenseHistory::create([
                        'expense_id' => $expense->id,
                        'status' => 'approved',
                        'changed_by' => auth()->id(),
                        'notes' => $request->notes ?? 'Disetujui oleh Finance (Bulk)',
                    ]);

                    $approvedCount++;
                }
            }

            DB::commit();
            return back()->with('success', "{$approvedCount} reimburse berhasil disetujui!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => 'Gagal bulk approve: ' . $e->getMessage()]);
        }
    }

    /**
     * Reject reimburse (permanent)
     */
    public function rejectReimburse(Request $request, $id)
    {
        $request->validate([
            'rejection_note' => 'required|string',
        ]);

        $expense = Expense::findOrFail($id);

        // Validasi: hanya boleh reject jika status pending_finance
        if ($expense->status !== 'pending_finance') {
            return back()->withErrors(['msg' => 'Expense ini tidak bisa di-reject. Status: ' . $expense->status_label]);
        }

        DB::beginTransaction();
        try {
            // Tentukan status reject
            // Jika dari Supervisor, return ke Supervisor
            // Jika dari Sales, return ke Sales
            $newStatus = $expense->isFromSupervisor()
                ? 'needs_revision_spv'
                : 'needs_revision_sales';

            $expense->update([
                'status' => $newStatus,
                'rejection_note' => $request->rejection_note,
                'rejection_type' => 'permanent',
                'revision_count' => DB::raw('revision_count + 1'),
                'revised_at' => now(),
            ]);

            // Catat history
            ExpenseHistory::create([
                'expense_id' => $expense->id,
                'status' => $newStatus,
                'changed_by' => auth()->id(),
                'notes' => 'Ditolak oleh Finance: ' . $request->rejection_note,
            ]);

            DB::commit();
            return back()->with('success', 'Reimburse berhasil ditolak. User telah diberitahu.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => 'Gagal reject: ' . $e->getMessage()]);
        }
    }

    // ==========================================
    // DETAIL USER & ABSENSI
    // ==========================================

    /**
     * Lihat detail user (seperti Supervisor/HRD)
     */
    public function showUser($id)
    {
        $user = User::findOrFail($id);

        // Hanya boleh lihat sales & supervisor
        if (!in_array($user->role, ['sales', 'supervisor'])) {
            abort(403, 'Finance hanya bisa melihat data Sales dan Supervisor.');
        }

        // Get daily logs
        $dailyLogs = DailyLog::where('user_id', $id)
            ->with('visits', 'expenses')
            ->orderBy('date', 'desc')
            ->paginate(20);

        return view('finance.show_user', compact('user', 'dailyLogs'));
    }

    /**
     * Lihat detail history absensi
     */
    public function showDetail($id)
    {
        $dailyLog = DailyLog::with([
            'user',
            'visits',
            'expenses.user',
            'expenses.approvedBySpv',
            'expenses.approvedByHrd',
            'expenses.approvedByFinance',
            'expenses.histories.changedBy'
        ])->findOrFail($id);

        return view('finance.show_detail', compact('dailyLog'));
    }

    // ==========================================
    // FUEL SETTINGS (BAHAN BAKAR)
    // ==========================================

    /**
     * Index fuel settings
     */
    public function fuelSettingsIndex()
    {
        $generalSettings = FuelSetting::whereNull('user_id')
            ->orderBy('created_at', 'desc')
            ->get();

        $individualSettings = FuelSetting::with('user')
            ->whereNotNull('user_id')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get semua sales & supervisor untuk dropdown
        $users = User::whereIn('role', ['sales', 'supervisor'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('finance.fuel_settings.index', compact(
            'generalSettings',
            'individualSettings',
            'users'
        ));
    }

    /**
     * Store general fuel setting
     */
    public function storeGeneralFuelSetting(Request $request)
    {
        $request->validate([
            'km_per_liter' => 'required|numeric|min:1',
            'fuel_price' => 'required|numeric|min:1',
        ]);

        FuelSetting::create([
            'user_id' => null,
            'km_per_liter' => $request->km_per_liter,
            'fuel_price' => $request->fuel_price,
            'is_active' => true,
        ]);

        return back()->with('success', 'Setting bahan bakar umum berhasil dibuat!');
    }

    /**
     * Store individual fuel setting
     */
    public function storeIndividualFuelSetting(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'km_per_liter' => 'required|numeric|min:1',
            'fuel_price' => 'required|numeric|min:1',
        ]);

        // Validate user adalah sales atau supervisor
        $user = User::findOrFail($request->user_id);
        if (!in_array($user->role, ['sales', 'supervisor'])) {
            return back()->withErrors(['user_id' => 'Hanya bisa membuat setting untuk Sales atau Supervisor.']);
        }

        FuelSetting::create([
            'user_id' => $request->user_id,
            'km_per_liter' => $request->km_per_liter,
            'fuel_price' => $request->fuel_price,
            'is_active' => true,
        ]);

        return back()->with('success', 'Setting bahan bakar individual berhasil dibuat!');
    }

    /**
     * Update fuel setting
     */
    public function updateFuelSetting(Request $request, $id)
    {
        $request->validate([
            'km_per_liter' => 'required|numeric|min:1',
            'fuel_price' => 'required|numeric|min:1',
            'is_active' => 'boolean',
        ]);

        $setting = FuelSetting::findOrFail($id);
        $setting->update([
            'km_per_liter' => $request->km_per_liter,
            'fuel_price' => $request->fuel_price,
            'is_active' => $request->is_active ?? true,
        ]);

        return back()->with('success', 'Setting bahan bakar berhasil diupdate!');
    }

    /**
     * Deactivate fuel setting
     */
    public function deactivateFuelSetting($id)
    {
        $setting = FuelSetting::findOrFail($id);
        $setting->update(['is_active' => false]);

        return back()->with('success', 'Setting bahan bakar berhasil dinonaktifkan.');
    }

    // ==========================================
    // EXPORT LAPORAN
    // ==========================================

    /**
     * Halaman export
     */
    public function exportPage()
    {
        return view('finance.export');
    }

    /**
     * Export Excel
     */
    public function exportExcel(Request $request)
    {
        $request->validate([
            'type' => 'required|in:activity,expense,combined',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $type = $request->type;
        $userId = $request->user_id; // Bisa null (semua) atau ID tertentu

        $fileName = match ($type) {
            'activity' => "Laporan_Aktivitas_{$dateFrom}_sd_{$dateTo}.xlsx",
            'expense' => "Laporan_Reimburse_{$dateFrom}_sd_{$dateTo}.xlsx",
            'combined' => "Laporan_Combined_{$dateFrom}_sd_{$dateTo}.xlsx",
        };

        return Excel::download(new RekapExport($type, $dateFrom, $dateTo, $userId), $fileName);
    }
}
