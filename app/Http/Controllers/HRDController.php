<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DailyLog;
use App\Models\Visit;
use App\Models\Expense;
use App\Models\Company;
use App\Models\JobPosition;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RekapExport;
use Carbon\Carbon;

class HRDController extends Controller
{
    // ==========================================
    // HOME - AKTIVITAS FEED
    // ==========================================

    public function home()
    {
        // Ambil ID user yang role-nya sales atau supervisor
        $targetUserIds = User::whereIn('role', ['sales', 'supervisor'])->pluck('id');

        // Ambil daily logs terbaru (absensi masuk/keluar)
        $dailyLogs = DailyLog::whereIn('user_id', $targetUserIds)
            ->where(function ($q) {
                $q->whereNotNull('start_time')->orWhereNotNull('end_time');
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // Ambil kunjungan terbaru
        $visits = Visit::whereHas('dailyLog', function ($q) use ($targetUserIds) {
            $q->whereIn('user_id', $targetUserIds);
        })
            ->with(['dailyLog.user'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // Gabungkan jadi satu collection dengan timestamp unified
        $activities = collect();

        foreach ($dailyLogs as $log) {
            if ($log->start_time) {
                $activities->push([
                    'type' => 'check_in',
                    'user' => $log->user,
                    'time' => $log->start_time,
                    'date' => $log->date,
                    'meta' => $log,
                    'sort_time' => $log->date . ' ' . $log->start_time,
                ]);
            }
            if ($log->end_time) {
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

        // Sort by time descending dan paginate manual
        $activities = $activities->sortByDesc('sort_time')->values();
        $activities = new \Illuminate\Pagination\LengthAwarePaginator(
            $activities->forPage(request()->get('page', 1), 20),
            $activities->count(),
            20,
            request()->get('page', 1),
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Statistik hari ini
        $today = Carbon::today();
        $todayStats = [
            'check_in' => DailyLog::whereIn('user_id', $targetUserIds)
                ->whereDate('date', $today)
                ->whereNotNull('start_time')
                ->count(),
            'check_out' => DailyLog::whereIn('user_id', $targetUserIds)
                ->whereDate('date', $today)
                ->whereNotNull('end_time')
                ->count(),
            'visits' => Visit::whereHas('dailyLog', function ($q) use ($targetUserIds, $today) {
                $q->whereIn('user_id', $targetUserIds)->whereDate('date', $today);
            })->count(),
            'total_active' => User::whereIn('role', ['sales', 'supervisor'])->where('is_active', true)->count(),
        ];

        return view('hrd.home', compact('activities', 'todayStats'));
    }

    // ==========================================
    // DASHBOARD HRD (KARYAWAN)
    // ==========================================

    public function dashboard(Request $request)
    {
        $query = User::whereIn('role', ['sales', 'supervisor', 'finance']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('job_position_id')) {
            $query->where('job_position_id', $request->job_position_id);
        }

        $users = $query->orderBy('name')->get();

        $stats = [
            'pending_hrd' => Expense::where('status', 'pending_hrd')->count(),
        ];

        $companies = Company::orderBy('name')->get();
        $jobPositions = JobPosition::orderBy('name')->get();

        return view('hrd.dashboard', compact('users', 'stats', 'companies', 'jobPositions'));
    }

    // ==========================================
    // MANAJEMEN AKUN (DINAMIS UNTUK HRD)
    // ==========================================

    public function createUser()
    {
        $roles = \App\Models\Role::where('hrd_can_create', true)->orderBy('name')->get();
        $supervisors = User::where('role', 'supervisor')->get();
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $jobPositions = JobPosition::where('is_active', true)->orderBy('name')->get();

        return view('hrd.create_user', compact('roles', 'supervisors', 'companies', 'jobPositions'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'username' => 'required|string|unique:users',
            'role' => 'required|exists:roles,slug',
            'supervisor_id' => 'nullable|exists:users,id',
            'company_id' => 'nullable|exists:companies,id',
            'job_position_id' => 'nullable|exists:job_positions,id',
            'fuel_reimbursement_enabled' => 'boolean',
        ]);

        $role = \App\Models\Role::where('slug', $request->role)->firstOrFail();
        if (!$role->hrd_can_create) {
            abort(403, 'Akses Ditolak: HRD tidak diizinkan membuat akun dengan role ini.');
        }

        if ($request->filled('supervisor_id')) {
            $supervisor = User::findOrFail($request->supervisor_id);
            if ($supervisor->role !== 'supervisor') {
                return back()->withErrors(['supervisor_id' => 'User yang dipilih bukan supervisor.'])->withInput();
            }
        }

        $defaultPassword = $request->role . '123';

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($defaultPassword),
            'role' => $request->role,
            'supervisor_id' => $request->supervisor_id,
            'company_id' => $request->company_id,
            'job_position_id' => $request->job_position_id,
            'fuel_reimbursement_enabled' => $request->has('fuel_reimbursement_enabled'),
        ]);

        if ($request->role === 'sales' && $request->filled('supervisor_id')) {
            $user->supervisors()->attach($request->supervisor_id);
        }

        return redirect()->route('hrd.dashboard')->with('success', "Akun ({$role->name}) berhasil dibuat!");
    }

    // ==========================================
    // DETAIL KARYAWAN
    // ==========================================

    public function showUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Cek apakah user adalah sales, supervisor, atau finance
        if (!in_array($user->role, ['sales', 'supervisor', 'finance'])) {
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
            'total_visits' => Visit::whereHas('dailyLog', function ($q) use ($id, $request) {
                $q->where('user_id', $id);
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $q->whereBetween('date', [$request->start_date, $request->end_date]);
                }
            })->count(),
            'total_expenses' => Expense::where('user_id', $id)
                ->when($request->filled('start_date') && $request->filled('end_date'), function ($q) use ($request) {
                    $q->whereBetween('date', [$request->start_date, $request->end_date]);
                })->sum('amount'),
        ];

        // Ambil semua supervisor untuk dropdown tambah
        $allSupervisors = User::where('role', 'supervisor')->orderBy('name')->get();

        // Hitung reimburse menunggu persetujuan HRD milik user ini (badge tombol persetujuan)
        $pendingReimburseCount = Expense::where('user_id', $id)
            ->where('status', 'pending_hrd')
            ->count();

        return view('hrd.show_user', compact('user', 'dailyLogs', 'stats', 'allSupervisors', 'pendingReimburseCount'));
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
        // Ambil semua sales, supervisor, dan finance untuk filter
        $users = User::whereIn('role', ['sales', 'supervisor', 'finance'])->get();

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

    // ==========================================
    // MANAJEMEN PERUSAHAAN (COMPANY)
    // ==========================================

    public function companyIndex()
    {
        $companies = Company::withCount('users')->orderBy('name')->get();
        return view('hrd.companies.index', compact('companies'));
    }

    public function companyStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:companies,name',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
        ]);

        $data = ['name' => $request->name];

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('companies', 'public');
        }

        Company::create($data);

        return back()->with('success', 'Perusahaan berhasil ditambahkan!');
    }

    public function companyUpdate(Request $request, $id)
    {
        $company = Company::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255|unique:companies,name,' . $id,
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
        ]);

        $data = ['name' => $request->name];

        if ($request->hasFile('logo')) {
            // Hapus logo lama jika ada
            if ($company->logo_path && Storage::disk('public')->exists($company->logo_path)) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('companies', 'public');
        }

        $company->update($data);

        return back()->with('success', 'Perusahaan berhasil diperbarui!');
    }

    public function companyToggleStatus($id)
    {
        $company = Company::findOrFail($id);
        $company->is_active = !$company->is_active;
        $company->save();

        $status = $company->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Perusahaan berhasil {$status}.");
    }

    public function companyDestroy($id)
    {
        $company = Company::findOrFail($id);

        if ($company->users()->count() > 0) {
            return back()->with('error', 'Tidak bisa menghapus perusahaan yang masih memiliki karyawan. Nonaktifkan saja.');
        }

        if ($company->logo_path && Storage::disk('public')->exists($company->logo_path)) {
            Storage::disk('public')->delete($company->logo_path);
        }

        $company->delete();
        return back()->with('success', 'Perusahaan berhasil dihapus!');
    }

    // ==========================================
    // MANAJEMEN JABATAN (JOB POSITION)
    // ==========================================

    public function jobPositionIndex()
    {
        $jobPositions = JobPosition::withCount('users')->orderBy('name')->get();
        return view('hrd.job_positions.index', compact('jobPositions'));
    }

    public function jobPositionStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:job_positions,name',
        ]);

        JobPosition::create([
            'name' => $request->name,
        ]);

        return back()->with('success', 'Jabatan berhasil ditambahkan!');
    }

    public function jobPositionUpdate(Request $request, $id)
    {
        $jobPosition = JobPosition::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255|unique:job_positions,name,' . $id,
        ]);

        $jobPosition->update(['name' => $request->name]);

        return back()->with('success', 'Jabatan berhasil diperbarui!');
    }

    public function jobPositionToggleStatus($id)
    {
        $jobPosition = JobPosition::findOrFail($id);
        $jobPosition->is_active = !$jobPosition->is_active;
        $jobPosition->save();

        $status = $jobPosition->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Jabatan berhasil {$status}.");
    }

    public function jobPositionDestroy($id)
    {
        $jobPosition = JobPosition::findOrFail($id);

        if ($jobPosition->users()->count() > 0) {
            return back()->with('error', 'Tidak bisa menghapus jabatan yang masih memiliki karyawan. Nonaktifkan saja.');
        }

        $jobPosition->delete();
        return back()->with('success', 'Jabatan berhasil dihapus!');
    }

    // ==========================================
    // UPDATE DATA KARYAWAN (Perusahaan, Jabatan, Fuel Toggle)
    // ==========================================

    public function updateUserProfile(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'job_position_id' => 'nullable|exists:job_positions,id',
            'fuel_reimbursement_enabled' => 'boolean',
        ]);

        $user->update([
            'company_id' => $request->company_id,
            'job_position_id' => $request->job_position_id,
            'fuel_reimbursement_enabled' => $request->has('fuel_reimbursement_enabled'),
        ]);

        return back()->with('success', 'Data karyawan berhasil diperbarui!');
    }
}
