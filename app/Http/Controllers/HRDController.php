<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DailyLog;
use App\Models\Visit;
use App\Models\Expense;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RekapExport;
use Carbon\Carbon;

class HRDController extends Controller
{
    // ==========================================
    // DASHBOARD HRD
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

        // Statistik pending reimburse untuk HRD
        $stats = [
            'pending_hrd' => Expense::where('status', 'pending_hrd')->count(),
        ];

        return view('hrd.dashboard', compact('users', 'stats'));
    }

    // ==========================================
    // MANAJEMEN AKUN SALES
    // ==========================================

    public function createSales()
    {
        // Ambil semua supervisor untuk dropdown
        $supervisors = User::where('role', 'supervisor')->get();

        return view('hrd.create_sales', compact('supervisors'));
    }

    public function storeSales(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'username' => 'required|string|unique:users',
            'supervisor_id' => 'nullable|exists:users,id',
        ]);

        // Cek supervisor jika diisi
        if ($request->filled('supervisor_id')) {
            $supervisor = User::findOrFail($request->supervisor_id);
            if ($supervisor->role !== 'supervisor') {
                return back()->withErrors(['supervisor_id' => 'User yang dipilih bukan supervisor.'])->withInput();
            }
        }

        $sales = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make('sales123'),
            'role' => 'sales',
            'supervisor_id' => $request->supervisor_id,
        ]);

        // Attach ke relasi many-to-many supervisors jika supervisor dipilih
        if ($request->filled('supervisor_id')) {
            $sales->supervisors()->attach($request->supervisor_id);
        }

        return redirect()->route('hrd.dashboard')->with('success', 'Akun sales berhasil dibuat!');
    }

    // ==========================================
    // MANAJEMEN AKUN SUPERVISOR
    // ==========================================

    public function createSupervisor()
    {
        return view('hrd.create_supervisor');
    }

    public function storeSupervisor(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'username' => 'required|string|unique:users',
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make('supervisor123'),
            'role' => 'supervisor',
        ]);

        return redirect()->route('hrd.dashboard')->with('success', 'Akun supervisor berhasil dibuat!');
    }

    // ==========================================
    // MANAJEMEN AKUN FINANCE
    // ==========================================

    public function createFinance()
    {
        return view('hrd.create_finance');
    }

    public function storeFinance(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'username' => 'required|string|unique:users',
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make('finance123'),
            'role' => 'finance',
        ]);

        return redirect()->route('hrd.dashboard')->with('success', 'Akun finance berhasil dibuat!');
    }

    // ==========================================
    // DETAIL KARYAWAN
    // ==========================================

    public function showUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Cek apakah user adalah sales atau supervisor
        if (!in_array($user->role, ['sales', 'supervisor'])) {
            return redirect()->route('hrd.dashboard')->with('error', 'User tidak valid.');
        }

        // Ambil daily logs dengan filter tanggal
        $dailyLogsQuery = DailyLog::where('user_id', $id)
            ->with(['visits', 'expenses']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $dailyLogsQuery->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $dailyLogs = $dailyLogsQuery->orderBy('date', 'desc')->orderBy('created_at', 'desc')->paginate(10);

        // Hitung statistik
        $stats = [
            'total_absensi' => $dailyLogs->total(),
            'total_visits' => Visit::whereHas('dailyLog', function ($q) use ($id) {
                $q->where('user_id', $id);
            })->count(),
            'total_expenses' => Expense::where('user_id', $id)->sum('amount'),
        ];

        // Ambil semua supervisor untuk dropdown tambah
        $allSupervisors = User::where('role', 'supervisor')->orderBy('name')->get();

        return view('hrd.show_user', compact('user', 'dailyLogs', 'stats', 'allSupervisors'));
    }

    // ==========================================
    // MANAJEMEN SUPERVISOR SALES (MULTI-SUPERVISOR)
    // ==========================================

    public function addSupervisor(Request $request, $id)
    {
        $sales = User::findOrFail($id);

        // Validasi
        $request->validate([
            'supervisor_id' => 'required|exists:users,id',
        ]);

        $supervisor = User::findOrFail($request->supervisor_id);

        if ($sales->role !== 'sales') {
            return back()->with('error', 'User bukan sales.');
        }

        if ($supervisor->role !== 'supervisor') {
            return back()->with('error', 'User yang dipilih bukan supervisor.');
        }

        // Cek apakah sudah ada relasi
        if ($sales->supervisors()->where('supervisor_sales.supervisor_id', $supervisor->id)->exists()) {
            return back()->with('error', 'Supervisor sudah ditugaskan ke sales ini.');
        }

        // Attach
        $sales->supervisors()->attach($supervisor->id);

        // Jika kolom legacy `supervisor_id` kosong, isi agar kompatibel dengan logika lama
        if (is_null($sales->supervisor_id)) {
            $sales->supervisor_id = $supervisor->id;
            $sales->save();
        }

        return back()->with('success', 'Supervisor berhasil ditambahkan!');
    }

    public function removeSupervisor(Request $request, $id)
    {
        $sales = User::findOrFail($id);

        // Validasi
        $request->validate([
            'supervisor_id' => 'required|exists:users,id',
        ]);

        // Sales harus punya minimal 1 supervisor (opsional, tapi sebaiknya jangan ditinggal kosong)
        // if ($sales->supervisors()->count() <= 1) {
        //     return back()->with('error', 'Sales harus memiliki minimal 1 supervisor.');
        // }

        // Detach
        $sales->supervisors()->detach($request->supervisor_id);

        // Jika kolom legacy `supervisor_id` menunjuk pada supervisor yang dihapus,
        // update ke supervisor pivot pertama (jika ada) atau null.
        if ($sales->supervisor_id == $request->supervisor_id) {
            $first = $sales->supervisors()->first();
            $sales->supervisor_id = $first ? $first->id : null;
            $sales->save();
        }

        return back()->with('success', 'Supervisor berhasil dihapus!');
    }

    // ==========================================
    // RESET PASSWORD
    // ==========================================

    public function resetPassword($id)
    {
        $user = User::findOrFail($id);

        // Tentukan password default berdasarkan role
        $defaultPassword = match ($user->role) {
            'sales' => 'sales123',
            'supervisor' => 'supervisor123',
            default => 'password123',
        };

        $user->update([
            'password' => Hash::make($defaultPassword),
        ]);

        return back()->with('success', "Password direset menjadi: {$defaultPassword}");
    }

    // ==========================================
    // NONAKTIFKAN / AKTIFKAN KARYAWAN
    // ==========================================

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        // Prevent blocking yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun berhasil {$status}.");
    }

    // ==========================================
    // LAPORAN & EXPORT
    // ==========================================

    public function exportPage()
    {
        // Ambil semua sales dan supervisor untuk filter
        $users = User::whereIn('role', ['sales', 'supervisor'])->get();

        return view('hrd.export', compact('users'));
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->start_date ?? Carbon::today()->toDateString();
        $endDate = $request->end_date ?? Carbon::today()->toDateString();
        $userId = $request->user_id; // Bisa null (semua) atau ID tertentu
        $type = $request->report_type; // 'activity' atau 'expense'

        // Validasi
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'report_type' => 'required|in:activity,expense,combined',
        ]);

        // Nama file
        $fileName = 'Laporan_' . $type . '_' . date('d-m-Y') . '.xlsx';

        // Download
        return Excel::download(
            new RekapExport($type, $startDate, $endDate, $userId),
            $fileName
        );
    }
}
