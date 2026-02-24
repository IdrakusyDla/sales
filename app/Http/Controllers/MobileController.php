<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RekapExport;

class MobileController extends Controller
{
    // ==========================================
    // 1. LOGIKA UMUM (DASHBOARD & REDIRECT)
    // ==========================================
    
    public function index()
    {
        $user = Auth::user();

        // JIKA HRD: Tampilkan Dashboard HRD (+ Logic Search)
        if ($user->role === 'hrd') {
            $query = User::where('role', 'sales');

            // Fitur Search Nama Sales
            if (request('search')) {
                $query->where('name', 'like', '%' . request('search') . '%');
            }

            $users = $query->get();
            return view('hrd.index', compact('users'));
        }

        // JIKA SALES: Tampilkan Dashboard Sales
        // Ambil history hari ini (Start & Visit)
        
        // 1. Ambil Log Pagi
        $dailyLog = DB::table('daily_logs')
            ->where('user_id', $user->id)
            ->where('date', Carbon::today())
            ->first();

        // 2. Ambil Visit
        $visits = [];
        if ($dailyLog) {
            $visits = DB::table('visits')
                ->where('daily_log_id', $dailyLog->id)
                ->orderBy('time', 'desc')
                ->get();
        }

        // Gabungkan data untuk tampilan timeline
        // Kita kirim data mentah saja biar view yang ngatur
        return view('sales.home', compact('user', 'dailyLog', 'visits'));
    }

    // ==========================================
    // 2. LOGIKA FITUR HRD (JANGAN DIHAPUS)
    // ==========================================

    public function create()
    {
        return view('hrd.create');
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'username' => 'required|string|unique:users', // Validasi username
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username, // Simpan username
            'password' => \Illuminate\Support\Facades\Hash::make('sales123'),
            'role' => 'sales'
        ]);

        return redirect()->route('dashboard')->with('success', 'Sales Berhasil Ditambahkan!');
    }

    public function showUser(Request $request, $id)
    {
        $targetUser = User::findOrFail($id);

        // --- A. DATA AKTIVITAS (ABSEN + VISIT) ---
        
        // 1. Absen Pagi (Daily Logs)
        // TRICK: Kita gabungkan date + start_time agar jadi format Timestamp lengkap untuk sorting
        $logs = DB::table('daily_logs')
            ->where('user_id', $id)
            ->select(
                'id', 
                'date', 
                DB::raw("CONCAT(date, ' ', start_time) as time"), // Gabung jadi Datetime
                'start_photo as photo_path', 
                'daily_plan as notes', 
                'lat', 'long',
                DB::raw("'Absen Masuk' as client_name"),
                DB::raw("'IN' as type"),
                DB::raw("'completed' as status")
            )->get();

        // 2. Kunjungan (Visits)
        $visits = DB::table('visits')
            ->join('daily_logs', 'visits.daily_log_id', '=', 'daily_logs.id')
            ->where('daily_logs.user_id', $id)
            ->select(
                'visits.id', 'daily_logs.date', 'visits.time', 'visits.photo_path', 
                'visits.notes', 'visits.lat', 'visits.long', 'visits.client_name', 'visits.status',
                DB::raw("'VISIT' as type")
            )->get();

        $history = $logs->merge($visits);

        // --- B. DATA KEUANGAN (EXPENSES) ---
        $expenses = DB::table('expenses')
            ->where('user_id', $id)
            ->select('*', DB::raw("'EXPENSE' as type"))
            ->orderBy('created_at', 'desc')
            ->get();

        // --- FILTER ---
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $history = $history->whereBetween('date', [$request->start_date, $request->end_date]);
            $expenses = $expenses->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        // --- SORTING FINAL (FIXED) ---
        // Sekarang semua punya format 'time' yang lengkap (Y-m-d H:i:s), jadi urutannya pasti benar
        $history = $history->sortByDesc('time');

        return view('hrd.show', compact('targetUser', 'history', 'expenses'));
    }

    public function resetPassword($id)
    {
        $user = User::findOrFail($id);
        $user->password = \Illuminate\Support\Facades\Hash::make('sales123');
        $user->save();

        return back()->with('success', 'Password direset jadi: sales123');
    }

    // ==========================================
    // 3. LOGIKA SALES BARU (SMART WORKFLOW)
    // ==========================================

    // Dispatcher: Menentukan Sales harus buka form apa (Absen Pagi atau Lapor Kunjungan)
    public function dispatchAction()
    {
        $user = Auth::user();
        
        // Validasi: HRD tidak boleh masuk sini
        if($user->role == 'hrd') return redirect()->route('dashboard');

        // Cek Absen Pagi
        $todayLog = DB::table('daily_logs')
            ->where('user_id', $user->id)
            ->where('date', Carbon::today())
            ->first();

        if (!$todayLog) {
            // KONDISI 1: Belum Absen Pagi -> Buka Form Start
            return view('sales.form_start');
        } else {
            // KONDISI 2: Sudah Absen -> Buka Form Lapor Kunjungan
            // Ambil rencana yang masih pending
            $plannedVisits = DB::table('visits')
                ->where('daily_log_id', $todayLog->id)
                ->where('status', 'pending')
                ->get();

            return view('sales.form_visit', compact('plannedVisits'));
        }
    }

    // Simpan Absen Pagi + Rencana
    public function storeStart(Request $request)
    {
        $request->validate([
            'photo' => 'required',
            'lat' => 'required',
            'destinations' => 'required|array|min:1',
        ]);

        // Upload Foto
        $image_parts = explode(";base64,", $request->photo);
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = 'attendance/' . uniqid() . '.png';
        Storage::disk('public')->put($fileName, $image_base64);

        // Insert Log Pagi
        $logId = DB::table('daily_logs')->insertGetId([
            'user_id' => Auth::id(),
            'date' => Carbon::today(),
            'start_time' => Carbon::now(),
            'start_photo' => $fileName,
            'daily_plan' => implode(', ', $request->destinations),
            'lat' => $request->lat,
            'long' => $request->long,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert Rencana Kunjungan (Status: Pending)
        foreach ($request->destinations as $dest) {
            DB::table('visits')->insert([
                'daily_log_id' => $logId,
                'client_name' => $dest,
                'time' => Carbon::now(),
                'status' => 'pending',
                'photo_path' => null, // Kosong dulu
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Absen Masuk Berhasil!');
    }

    // Simpan Laporan Kunjungan (Realisasi)
    public function storeVisit(Request $request)
    {
        $request->validate([
            'photo' => 'required',
            'status' => 'required',
            'lat' => 'required',
        ]);

        // Upload Foto
        $image_parts = explode(";base64,", $request->photo);
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = 'visits/' . uniqid() . '.png';
        Storage::disk('public')->put($fileName, $image_base64);

        // Jika Kunjungan Dadakan (New)
        if ($request->visit_id == 'new') {
            $log = DB::table('daily_logs')->where('user_id', Auth::id())->where('date', Carbon::today())->first();
            
            DB::table('visits')->insert([
                'daily_log_id' => $log->id,
                'client_name' => $request->new_client_name,
                'time' => Carbon::now(),
                'status' => $request->status,
                'notes' => $request->notes,
                'reason' => $request->reason,
                'photo_path' => $fileName,
                'lat' => $request->lat,
                'long' => $request->long,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } 
        // Jika Update Rencana (Pending -> Completed/Failed)
        else {
            DB::table('visits')->where('id', $request->visit_id)->update([
                'time' => Carbon::now(),
                'status' => $request->status,
                'notes' => $request->notes,
                'reason' => $request->reason,
                'photo_path' => $fileName,
                'lat' => $request->lat,
                'long' => $request->long,
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Laporan Terkirim!');
    }

    // --- HISTORY PRIBADI SALES (UPDATE) ---
    public function myHistory(Request $request)
    {
        $id = Auth::id();

        // --- A. DATA AKTIVITAS ---
        $logs = DB::table('daily_logs')
            ->where('user_id', $id)
            ->select(
                'id', 
                'date', 
                DB::raw("CONCAT(date, ' ', start_time) as time"), 
                'start_photo as photo_path', 
                'daily_plan as notes', 'lat', 'long',
                DB::raw("'Absen Masuk' as client_name"),
                DB::raw("'IN' as type"),
                DB::raw("'completed' as status")
            )->get();

        $visits = DB::table('visits')
            ->join('daily_logs', 'visits.daily_log_id', '=', 'daily_logs.id')
            ->where('daily_logs.user_id', $id)
            ->select(
                'visits.id', 'daily_logs.date', 'visits.time', 'visits.photo_path', 
                'visits.notes', 'visits.lat', 'visits.long', 'visits.client_name', 'visits.status',
                DB::raw("'VISIT' as type")
            )->get();

        $history = $logs->merge($visits);

        // --- B. DATA KEUANGAN ---
        $expenses = DB::table('expenses')
            ->where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        // --- FILTER ---
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $history = $history->whereBetween('date', [$request->start_date, $request->end_date]);
            $expenses = $expenses->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $history = $history->filter(function ($item) use ($search) {
                return str_contains(strtolower($item->client_name), $search);
            });
        }

        // --- SORTING ---
        $history = $history->sortByDesc('time');

        return view('sales.history', compact('history', 'expenses'));
    }

    // ==========================================
    // 4. FITUR TAMBAHAN (PASSWORD & EXPORT)
    // ==========================================

    // --- GANTI PASSWORD (SALES) ---
    public function editPassword()
    {
        return view('sales.password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user(); // Kita ambil user yang login

        // 1. Cek Password Lama
        if (!\Illuminate\Support\Facades\Hash::check($request->old_password, $user->password)) {
            return back()->withErrors(['old_password' => 'Password lama salah!']);
        }

        // 2. Ganti Password Baru (CARA BARU - LEBIH AMAN)
        // Kita ubah langsung property-nya, bukan pakai array update()
        $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        
        // 3. Simpan ke Database
        $user->save();

        return redirect()->route('dashboard')->with('success', 'Password berhasil diganti!');
    }

    // --- EXPORT DATA (UPDATED: SUPPORT EXPENSES) ---
    public function exportExcel(Request $request)
{
    $startDate = $request->start_date ?? Carbon::today()->toDateString();
    $endDate = $request->end_date ?? Carbon::today()->toDateString();
    $userId = $request->user_id;
    $type = $request->report_type; 

    // Nama file .xlsx
    $fileName = 'Laporan_' . $type . '_' . date('d-m-Y') . '.xlsx';

    // Download
    return Excel::download(new RekapExport($type, $startDate, $endDate, $userId), $fileName);
}

    // --- HALAMAN MENU EXPORT (HRD) ---
    public function exportPage()
    {
        // Ambil data sales untuk dropdown filter
        $users = User::where('role', 'sales')->get();
        return view('hrd.export', compact('users'));
    }

    // --- FITUR PENGELUARAN (REIMBURSE) ---
    
    public function createExpense()
    {
        return view('sales.form_expense');
    }

    public function storeExpense(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'amount' => 'required|numeric',
            'photo_receipt' => 'required', // Struk wajib
            // Foto KM wajib JIKA tipe = gas (bensin)
            'photo_km' => 'required_if:type,gas', 
        ]);

        // 1. Upload Struk
        $receiptPath = $this->saveBase64Image($request->photo_receipt, 'expenses');
        
        // 2. Upload KM (Jika ada)
        $kmPath = null;
        if ($request->filled('photo_km')) {
            $kmPath = $this->saveBase64Image($request->photo_km, 'expenses');
        }

        // 3. Simpan ke Database
        DB::table('expenses')->insert([
            'user_id' => Auth::id(),
            'date' => Carbon::today(),
            'type' => $request->type,
            'amount' => $request->amount,
            'note' => $request->note,
            'photo_receipt' => $receiptPath,
            'photo_km' => $kmPath,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Pengeluaran Berhasil Dicatat!');
    }

    // Helper Function untuk save base64 (Biar kodingan rapi)
    private function saveBase64Image($base64_string, $folder)
    {
        $image_parts = explode(";base64,", $base64_string);
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = $folder . '/' . uniqid() . '.png';
        Storage::disk('public')->put($fileName, $image_base64);
        return $fileName;
    }

    // --- HALAMAN PROFIL ---
    public function profilePage()
    {
        return view('sales.profile');
    }
}