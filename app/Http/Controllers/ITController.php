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

class ITController extends Controller
{
    // ==========================================
    // DASHBOARD IT (Sama seperti HRD)
    // ==========================================

    public function dashboard(Request $request)
    {
        // Ambil semua sales, supervisor, HRD, dan Finance
        $query = User::whereIn('role', ['sales', 'supervisor', 'hrd', 'finance']);

        // Fitur Search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('name')->get();

        return view('it.dashboard', compact('users'));
    }

    // ==========================================
    // MANAJEMEN AKUN SALES (Sama seperti HRD)
    // ==========================================

    public function createSales()
    {
        $supervisors = User::where('role', 'supervisor')->get();
        return view('it.create_sales', compact('supervisors'));
    }

    public function storeSales(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'username' => 'required|string|unique:users',
            'supervisor_id' => 'nullable|exists:users,id',
        ]);

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

        return redirect()->route('it.dashboard')->with('success', 'Akun sales berhasil dibuat!');
    }

    // ==========================================
    // MANAJEMEN AKUN SUPERVISOR (Sama seperti HRD)
    // ==========================================

    public function createSupervisor()
    {
        return view('it.create_supervisor');
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

        return redirect()->route('it.dashboard')->with('success', 'Akun supervisor berhasil dibuat!');
    }

    // ==========================================
    // MANAJEMEN AKUN HRD (KHUSUS IT)
    // ==========================================

    public function createHrd()
    {
        return view('it.create_hrd');
    }

    public function storeHrd(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'username' => 'required|string|unique:users',
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make('hrd123'),
            'role' => 'hrd',
        ]);

        return redirect()->route('it.dashboard')->with('success', 'Akun HRD berhasil dibuat!');
    }

    // ==========================================
    // MANAJEMEN AKUN FINANCE (KHUSUS IT)
    // ==========================================

    public function createFinance()
    {
        return view('it.create_finance');
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

        return redirect()->route('it.dashboard')->with('success', 'Akun Finance berhasil dibuat!');
    }

    // ==========================================
    // DETAIL KARYAWAN (Sama seperti HRD)
    // ==========================================

    public function showUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (!in_array($user->role, ['sales', 'supervisor', 'hrd', 'finance'])) {
            return redirect()->route('it.dashboard')->with('error', 'User tidak valid.');
        }

        $dailyLogsQuery = DailyLog::where('user_id', $id)
            ->with(['visits', 'expenses']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $dailyLogsQuery->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $dailyLogs = $dailyLogsQuery->orderBy('date', 'desc')->paginate(10);

        $stats = [
            'total_absensi' => $dailyLogs->total(),
            'total_visits' => \App\Models\Visit::whereHas('dailyLog', function ($q) use ($id, $request) {
                $q->where('user_id', $id);
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $q->whereBetween('date', [$request->start_date, $request->end_date]);
                }
            })->count(),
            'total_expenses' => \App\Models\Expense::where('user_id', $id)
                ->when($request->filled('start_date') && $request->filled('end_date'), function ($q) use ($request) {
                    $q->whereBetween('date', [$request->start_date, $request->end_date]);
                })->sum('amount'),
        ];

        return view('it.show_user', compact('user', 'dailyLogs', 'stats'));
    }

    // ==========================================
    // RESET PASSWORD (Sama seperti HRD)
    // ==========================================

    public function resetPassword($id)
    {
        $user = User::findOrFail($id);

        $defaultPassword = match ($user->role) {
            'sales' => 'sales123',
            'supervisor' => 'supervisor123',
            'hrd' => 'hrd123',
            default => 'password123',
        };

        $user->update([
            'password' => Hash::make($defaultPassword),
        ]);

        return back()->with('success', "Password direset menjadi: {$defaultPassword}");
    }

    // ==========================================
    // BLOCK / UNBLOCK USER
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
    // HAPUS AKUN (KHUSUS IT)
    // ==========================================

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $userName = $user->name;
        $userRole = $user->role;

        // Hapus semua data terkait user (daily logs, visits, expenses, etc.)
        // Karena sudah ada foreign key cascade atau soft delete, kita bisa langsung delete
        $user->delete();

        return redirect()->route('it.dashboard')->with('success', "Akun {$userName} ({$userRole}) berhasil dihapus.");
    }

    // ==========================================
    // LAPORAN & EXPORT (Sama seperti HRD)
    // ==========================================

    public function exportPage()
    {
        $users = User::whereIn('role', ['sales', 'supervisor', 'hrd'])->get();
        return view('it.export', compact('users'));
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->start_date ?? Carbon::today()->toDateString();
        $endDate = $request->end_date ?? Carbon::today()->toDateString();
        $userId = $request->user_id;
        $type = $request->report_type;

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'report_type' => 'required|in:activity,expense,combined',
        ]);

        $fileName = 'Laporan_' . $type . '_' . date('d-m-Y') . '.xlsx';

        return Excel::download(
            new RekapExport($type, $startDate, $endDate, $userId),
            $fileName
        );
    }
    // ==========================================
    // PENGATURAN SISTEM
    // ==========================================

    public function settings()
    {
        $retention_months = \App\Models\ApplicationSetting::get('retention_months', 3);
        return view('it.settings', compact('retention_months'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'retention_months' => 'required|integer|min:1',
        ]);

        \App\Models\ApplicationSetting::set('retention_months', $request->retention_months);

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function cleanupInactiveUsers(Request $request)
    {
        // Capture output buffer
        $exitCode = \Illuminate\Support\Facades\Artisan::call('users:cleanup');
        $output = \Illuminate\Support\Facades\Artisan::output();

        return back()->with('success', 'Pembersihan user tidak aktif selesai.')->with('cleanup_output', $output);
    }
}
