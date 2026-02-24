<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\ExpenseHistory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReimburseApprovalController extends Controller
{
    // ==========================================
    // DAFTAR REIMBURSE PENDING
    // ==========================================
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Expense::with(['dailyLog.user', 'user', 'histories']);

        // LOGIKA SPV: Hanya lihat milik sales bawahan yang statusnya 'pending_spv'
        if ($user->role === 'supervisor') {
            $query->where('status', 'pending_spv')
                ->whereHas('user', function ($q) use ($user) {
                    // Cek supervisor_id (single) atau many-to-many supervisors
                    $q->where('supervisor_id', $user->id)
                        ->orWhereHas('supervisors', function ($q2) use ($user) {
                            $q2->where('users.id', $user->id);
                        });
                });
        }
        // LOGIKA HRD: Hanya lihat yang sudah disetujui SPV ('pending_hrd')
        elseif ($user->role === 'hrd') {
            $query->where('status', 'pending_hrd');
        }
        // Role lain (IT/Sales/Finance tidak pakai ini - Finance punya controller sendiri)
        else {
            abort(403);
        }

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

        $pendingReimburses = $query->orderBy('date', 'desc')->paginate(20);

        // Get semua users untuk filter dropdown
        $users = User::whereIn('role', ['sales', 'supervisor'])
            ->orderBy('name')
            ->get();

        return view('approval.reimburse_index', compact('pendingReimburses', 'users'));
    }

    // ==========================================
    // APPROVE
    // ==========================================
    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        $expense = Expense::findOrFail($id);

        DB::beginTransaction();
        try {
            // LOGIKA SPV
            if ($user->role === 'supervisor') {
                if ($expense->status !== 'pending_spv') {
                    return back()->with('error', 'Status reimburse tidak valid untuk disetujui. Current: ' . $expense->status);
                }

                $expense->update([
                    'status' => 'pending_hrd',
                    'approved_by_spv_at' => now(),
                    'approved_by_spv_id' => $user->id,
                ]);

                // Catat history
                ExpenseHistory::create([
                    'expense_id' => $expense->id,
                    'status' => 'pending_hrd',
                    'changed_by' => $user->id,
                    'notes' => 'Disetujui oleh Supervisor',
                ]);

                DB::commit();
                return back()->with('success', 'Reimburse disetujui. Menunggu verifikasi HRD.');
            }

            // LOGIKA HRD
            if ($user->role === 'hrd') {
                if ($expense->status !== 'pending_hrd') {
                    return back()->with('error', 'Reimburse belum disetujui Supervisor atau status tidak valid. Current: ' . $expense->status);
                }

                $expense->update([
                    'status' => 'pending_finance',
                    'approved_by_hrd_at' => now(),
                    'approved_by_hrd_id' => $user->id,
                ]);

                // Catat history
                ExpenseHistory::create([
                    'expense_id' => $expense->id,
                    'status' => 'pending_finance',
                    'changed_by' => $user->id,
                    'notes' => 'Disetujui oleh HRD',
                ]);

                DB::commit();
                return back()->with('success', 'Reimburse disetujui. Menunggu verifikasi Finance.');
            }

            abort(403);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal approve: ' . $e->getMessage());
        }
    }

    // ==========================================
    // REJECT
    // ==========================================
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|min:5',
            'rejection_type' => 'required|in:revisi,permanent',
        ]);

        $user = Auth::user();
        $expense = Expense::with('user')->findOrFail($id);

        DB::beginTransaction();
        try {
            // Tentukan status berdasarkan tipe penolakan dan siapa yang menolak
            if ($request->rejection_type === 'permanent') {
                // Penolakan permanen - tidak bisa direvisi
                $expense->update([
                    'status' => 'rejected_permanent',
                    'rejection_note' => $request->reason,
                    'rejection_type' => 'permanent',
                    'revision_count' => DB::raw('revision_count + 1'),
                    'revised_at' => now(),
                ]);

                // Catat history
                ExpenseHistory::create([
                    'expense_id' => $expense->id,
                    'status' => 'rejected_permanent',
                    'changed_by' => $user->id,
                    'notes' => 'Ditolak permanen oleh ' . ($user->role === 'supervisor' ? 'Supervisor' : 'HRD') . ': ' . $request->reason,
                ]);

                DB::commit();
                return back()->with('success', 'Reimburse ditolak permanen.');
            }

            // Penolakan dengan permintaan revisi
            if ($user->role === 'supervisor') {
                // SPV reject → kirim ke Sales untuk revisi
                $expense->update([
                    'status' => 'needs_revision_sales',
                    'rejection_note' => $request->reason,
                    'rejection_type' => 'revisi',
                    'revision_count' => DB::raw('revision_count + 1'),
                    'revised_at' => now(),
                ]);

                // Catat history
                ExpenseHistory::create([
                    'expense_id' => $expense->id,
                    'status' => 'needs_revision_sales',
                    'changed_by' => $user->id,
                    'notes' => 'Perlu revisi (Sales) - Supervisor: ' . $request->reason,
                ]);

                DB::commit();
                return back()->with('success', 'Reimburse dikembalikan ke Sales untuk direvisi.');
            }

            if ($user->role === 'hrd') {
                // HRD reject → kirim ke Sales, Supervisor dapat notifikasi juga
                // Jika dari Supervisor, kirim ke Supervisor
                // Jika dari Sales, kirim ke Sales

                if ($expense->isFromSupervisor()) {
                    // Yang submit adalah SPV, kembalikan ke SPV
                    $expense->update([
                        'status' => 'needs_revision_spv',
                        'rejection_note' => $request->reason,
                        'rejection_type' => 'revisi',
                        'revision_count' => DB::raw('revision_count + 1'),
                        'revised_at' => now(),
                    ]);

                    // Catat history
                    ExpenseHistory::create([
                        'expense_id' => $expense->id,
                        'status' => 'needs_revision_spv',
                        'changed_by' => $user->id,
                        'notes' => 'Perlu revisi (Supervisor) - HRD: ' . $request->reason,
                    ]);

                    DB::commit();
                    return back()->with('success', 'Reimburse dikembalikan ke Supervisor untuk direvisi.');
                } else {
                    // Yang submit adalah Sales, kembalikan ke Sales (harus approve SPV lagi)
                    $expense->update([
                        'status' => 'needs_revision_sales',
                        'rejection_note' => $request->reason,
                        'rejection_type' => 'revisi',
                        'revision_count' => DB::raw('revision_count + 1'),
                        'revised_at' => now(),
                    ]);

                    // Catat history
                    ExpenseHistory::create([
                        'expense_id' => $expense->id,
                        'status' => 'needs_revision_sales',
                        'changed_by' => $user->id,
                        'notes' => 'Perlu revisi (Sales) - HRD: ' . $request->reason,
                    ]);

                    DB::commit();
                    return back()->with('success', 'Reimburse dikembalikan ke Sales untuk direvisi. Supervisor juga diberitahu.');
                }
            }

            abort(403);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal reject: ' . $e->getMessage());
        }
    }

    // ==========================================
    // BULK APPROVE (Optional - untuk Supervisor/HRD)
    // ==========================================
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'expense_ids' => 'required|array',
            'expense_ids.*' => 'exists:expenses,id',
        ]);

        $user = Auth::user();
        $expenseIds = $request->expense_ids;

        DB::beginTransaction();
        try {
            $approvedCount = 0;
            $targetStatus = $user->role === 'supervisor' ? 'pending_spv' : 'pending_hrd';
            $newStatus = $user->role === 'supervisor' ? 'pending_hrd' : 'pending_finance';
            $updateField = $user->role === 'supervisor' ? 'approved_by_spv' : 'approved_by_hrd';

            foreach ($expenseIds as $id) {
                $expense = Expense::where('status', $targetStatus)->find($id);

                if ($expense) {
                    $expense->update([
                        'status' => $newStatus,
                        "{$updateField}_at" => now(),
                        "{$updateField}_id" => $user->id,
                    ]);

                    // Catat history
                    ExpenseHistory::create([
                        'expense_id' => $expense->id,
                        'status' => $newStatus,
                        'changed_by' => $user->id,
                        'notes' => 'Disetujui oleh ' . ($user->role === 'supervisor' ? 'Supervisor' : 'HRD') . ' (Bulk)',
                    ]);

                    $approvedCount++;
                }
            }

            DB::commit();
            return back()->with('success', "{$approvedCount} reimburse berhasil disetujui!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal bulk approve: ' . $e->getMessage());
        }
    }

    /**
     * Serve expense receipt image securely with authorization checks.
     */
    public function showReceipt(Request $request, $id)
    {
        $user = Auth::user();
        $expense = Expense::with('dailyLog.user')->findOrFail($id);

        // If no receipt stored
        if (!$expense->photo_receipt) {
            abort(404, 'Receipt not found.');
        }

        // Authorization: sales can see own, supervisor can see subordinate, HRD/Finance/IT can see all
        if ($user->isSales()) {
            if ($expense->user_id !== $user->id) {
                abort(403);
            }
        } elseif ($user->isSupervisor()) {
            $owner = $expense->dailyLog->user;
            $isOwn = $owner->id === $user->id;
            $isSub = $owner->supervisor_id === $user->id || $owner->supervisors()->where('supervisor_sales.supervisor_id', $user->id)->exists();
            if (! $isOwn && ! $isSub) {
                abort(403);
            }
        } elseif (! in_array($user->role, ['hrd', 'finance', 'it'])) {
            abort(403);
        }

        $path = storage_path('app/public/' . $expense->photo_receipt);

        if (! file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }
}
