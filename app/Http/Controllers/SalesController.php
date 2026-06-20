<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DailyLog;
use App\Models\Visit;
use App\Models\Expense;
use App\Models\FuelSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SalesController extends Controller
{
    // ==========================================
    // DASHBOARD SALES
    // ==========================================

    public function dashboard()
    {
        $user = Auth::user();

        // Ambil SEMUA log hari ini (untuk support multiple log/lembur) - Urutkan log terakhir di ATAS
        $todayLogs = DailyLog::where('user_id', $user->id)
            ->where('date', Carbon::today())
            ->orderBy('created_at', 'desc') // Yang terbaru di atas
            ->get();

        // Ambil log yang paling terakhir dibuat (untuk status utama di header)
        $latestLog = $todayLogs->first();

        return view('sales.home', compact('user', 'todayLogs', 'latestLog'));
    }

    // ==========================================
    // ABSEN MASUK (Sebelum Berangkat)
    // ==========================================

    public function showAbsenMasuk()
    {
        $user = Auth::user();

        // Cek log TERAKHIR hari ini
        $lastLog = DailyLog::where('user_id', $user->id)
            ->where('date', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->first();

        // Jika ada log dan BELUM absen keluar (masih aktif)
        if ($lastLog && !$lastLog->hasEnded()) {
            return redirect()->route('dashboard')->with('info', 'Anda masih memiliki sesi absen aktif. Silakan checkout terlebih dahulu sebelum absen baru.');
        }

        // Jika $lastLog ada dan hasEnded() == true, berarti ini absen kedua (Lembur/Emergency) -> ALLOWED

        // Data untuk Information Cards
        $plannedVisitsCount = 0; // Default, user belum absen masuk hari ini

        // Cari log terakhir yang sudah masuk (untuk referensi)
        $lastCompletedLog = DailyLog::where('user_id', $user->id)
            ->where('date', Carbon::today())
            ->whereNotNull('start_time')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastCompletedLog) {
            // Hitung rencana kunjungan dari log terakhir
            $plannedVisitsCount = $lastCompletedLog->visits()
                ->where('is_planned', true)
                ->count();
        }

        // Hitung pending reimburse (yang belum ada struk/belum lengkap)
        $pendingReimburseCount = Expense::where('user_id', $user->id)
            ->where('status', 'pending_spv')
            ->whereNull('photo_receipt')
            ->count();

        return view('sales.absen_masuk', compact(
            'user',
            'plannedVisitsCount',
            'pendingReimburseCount'
        ));
    }

    public function storeAbsenMasuk(Request $request)
    {
        // Validasi manual odometer (pesan ramah via session)
        if (!$request->odometer_value || !is_numeric($request->odometer_value) || $request->odometer_value < 0) {
            return back()->with('error', 'Nilai odometer wajib diisi dengan angka yang valid.')->withInput();
        }
        if ($request->odometer_value > 99999999) {
            return back()->with('error', 'Nilai odometer terlalu besar. Maksimal 8 digit (99.999.999 km).')->withInput();
        }

        $request->validate([
            'photo' => 'required',
            'odometer_photo' => 'required',
            'odometer_value' => 'required|numeric|min:0',
            'lat' => 'required',
            'long' => 'required',
            'destinations' => 'required|array|min:1',
            'destinations.*' => 'required|string',
        ]);

        $user = Auth::user();

        // Upload foto selfie
        $selfiePath = $this->saveBase64Image($request->photo, 'attendance');

        // Upload foto odometer
        $odometerPhotoPath = $this->saveBase64Image($request->odometer_photo, 'odometer');

        // Buat daily log
        $dailyLog = DailyLog::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => Carbon::now(),
            'start_photo' => $selfiePath,
            'start_odo_value' => $request->odometer_value,
            'start_odo_photo' => $odometerPhotoPath,
            'lat' => $request->lat,
            'long' => $request->long,
            'daily_plan' => implode(', ', $request->destinations),
        ]);

        // Buat rencana kunjungan (status: pending)
        foreach ($request->destinations as $destination) {
            Visit::create([
                'daily_log_id' => $dailyLog->id,
                'client_name' => $destination,
                'time' => Carbon::now(),
                'status' => 'pending',
                'is_planned' => true,
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Absen masuk berhasil!');
    }

    // ==========================================
    // ABSEN KUNJUNGAN TOKO
    // ==========================================

    public function showAbsenToko()
    {
        $user = Auth::user();

        // Cek log TERAKHIR yang AKTIF (belum absen keluar) hari ini
        $todayLog = DailyLog::where('user_id', $user->id)
            ->where('date', Carbon::today())
            ->whereNull('end_time')  // Hanya log yang belum selesai
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$todayLog) {
            return redirect()->route('sales.absen.masuk')->with('error', 'Silakan absen masuk terlebih dahulu.');
        }

        // Ambil rencana kunjungan yang masih pending
        $plannedVisits = $todayLog->visits()
            ->where('status', 'pending')
            ->where('is_planned', true)
            ->get();

        // Statistik untuk information cards desktop (SEMUA kunjungan: rencana + dadakan)
        $allVisits = $todayLog->visits()->get();
        $totalVisits = $allVisits->count();
        $completedVisits = $allVisits->where('status', 'completed')->count();
        $failedVisits = $allVisits->where('status', 'failed')->count();

        return view('sales.absen_toko', compact('plannedVisits', 'todayLog', 'totalVisits', 'completedVisits', 'failedVisits'));
    }

    public function storeAbsenToko(Request $request)
    {
        $request->validate([
            'photo' => 'required',
            'status' => 'required|in:completed,failed',
            'lat' => 'required',
            'long' => 'required',
            'reason' => 'required_if:status,failed',
        ]);

        $user = Auth::user();

        // Ambil log TERAKHIR yang AKTIF (belum absen keluar) hari ini
        $todayLog = DailyLog::where('user_id', $user->id)
            ->where('date', Carbon::today())
            ->whereNull('end_time')  // Hanya log yang belum selesai
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$todayLog) {
            return redirect()->route('sales.absen.masuk')->with('error', 'Silakan absen masuk terlebih dahulu.');
        }

        // Upload foto selfie
        $photoPath = $this->saveBase64Image($request->photo, 'visits');

        // Jika kunjungan dari rencana (update yang pending)
        if ($request->visit_id && $request->visit_id != 'new') {
            $visit = Visit::where('id', $request->visit_id)
                ->where('daily_log_id', $todayLog->id)
                ->firstOrFail();

            $visit->update([
                'time' => Carbon::now(),
                'status' => $request->status,
                'notes' => $request->status === 'completed' ? $request->notes : null,
                'reason' => $request->status === 'failed' ? $request->reason : null,
                'photo_path' => $photoPath,
                'lat' => $request->lat,
                'long' => $request->long,
            ]);
        }
        // Jika kunjungan dadakan (tambah baru)
        else {
            $request->validate([
                'new_client_name' => 'required|string',
            ]);

            Visit::create([
                'daily_log_id' => $todayLog->id,
                'client_name' => $request->new_client_name,
                'time' => Carbon::now(),
                'status' => $request->status,
                'notes' => $request->status === 'completed' ? $request->notes : null,
                'reason' => $request->status === 'failed' ? $request->reason : null,
                'photo_path' => $photoPath,
                'lat' => $request->lat,
                'long' => $request->long,
                'is_planned' => false, // Kunjungan dadakan
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Laporan kunjungan terkirim!');
    }

    // ==========================================
    // ABSEN KELUAR (2 Skenario)
    // ==========================================

    public function showAbsenKeluar()
    {
        $user = Auth::user();

        $today = Carbon::today();
        $now = Carbon::now();

        // 1. Cek Open Log HARI INI
        $activeLog = DailyLog::where('user_id', $user->id)
            ->where('date', $today)
            ->whereNull('end_time')
            ->orderBy('created_at', 'desc')
            ->first();

        // 2. Jika tidak ada log hari ini, Cek Open Log KEMARIN (Late Night Checkout)
        // Hanya valid jika waktu sekarang dini hari (misal jam 00:00 - 04:00)
        if (!$activeLog && $now->hour < 4) {
            $yesterday = Carbon::yesterday();
            $activeLog = DailyLog::where('user_id', $user->id)
                ->where('date', $yesterday)
                ->whereNull('end_time')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($activeLog) {
                session()->flash('info', 'Anda melakukan absen keluar untuk sesi KEMARIN.');
            }
        }

        if (!$activeLog) {
            // Cek apakah karena belum absen masuk sama sekali
            return redirect()->route('sales.absen.masuk')->with('error', 'Silakan absen masuk terlebih dahulu.');
        }

        // Cek apakah masih ada kunjungan yang pending
        $pendingVisits = $activeLog->visits()->where('status', 'pending')->count();
        if ($pendingVisits > 0) {
            return redirect()->route('dashboard')->with('error', 'Silakan selesaikan semua kunjungan terlebih dahulu sebelum absen keluar.');
        }

        // Hitung statistik kunjungan untuk Information Cards
        $allVisits = $activeLog->visits;
        $completedVisits = $allVisits->where('status', 'completed')->count();
        $failedVisits = $allVisits->where('status', 'failed')->count();
        $totalVisits = $allVisits->count();
        $plannedVisits = $allVisits->where('is_planned', true)->count();

        return view('sales.absen_keluar', [
            'todayLog' => $activeLog,
            'completedVisits' => $completedVisits,
            'failedVisits' => $failedVisits,
            'totalVisits' => $totalVisits,
            'plannedVisits' => $plannedVisits,
        ]);
    }

    public function storeAbsenKeluar(Request $request)
    {
        // Validasi manual odometer (pesan ramah via session)
        if (!$request->odometer_value || !is_numeric($request->odometer_value) || $request->odometer_value < 0) {
            return back()->with('error', 'Nilai odometer wajib diisi dengan angka yang valid.')->withInput();
        }
        if ($request->odometer_value > 99999999) {
            return back()->with('error', 'Nilai odometer terlalu besar. Maksimal 8 digit (99.999.999 km).')->withInput();
        }

        $request->validate([
            'photo' => 'required',
            'odometer_photo' => 'required',
            'odometer_value' => 'required|numeric|min:0',
            'lat' => 'required',
            'long' => 'required',
            'end_type' => 'required|in:home,last_store,other',
            'end_notes' => 'nullable|string',
        ]);

        $user = Auth::user();
        $today = Carbon::today();
        $now = Carbon::now();

        // 1. Cari Log AKTIF Hari Ini
        $activeLog = DailyLog::where('user_id', $user->id)
            ->where('date', $today)
            ->whereNull('end_time')
            ->orderBy('created_at', 'desc')
            ->first();

        // 2. Jika tidak ada, Cari Log AKTIF Kemarin (Late Checkout)
        if (!$activeLog && $now->hour < 4) {
            $yesterday = Carbon::yesterday();
            $activeLog = DailyLog::where('user_id', $user->id)
                ->where('date', $yesterday)
                ->whereNull('end_time')
                ->orderBy('created_at', 'desc')
                ->first();
        }

        if (!$activeLog) {
            // Bisa jadi karena tidak ada log, ATAU log sudah diclose semua
            return redirect()->route('dashboard')->with('error', 'Tidak ada sesi absen aktif yang perlu di-checkout.');
        }

        // Use variable name $todayLog for compatibility with rest of method logic if needed, or update usage
        $todayLog = $activeLog;

        if ($todayLog->hasEnded()) {
            return redirect()->route('dashboard')->with('error', 'Anda sudah absen keluar hari ini.');
        }

        // Cek apakah masih ada kunjungan yang pending
        $pendingVisits = $todayLog->visits()->where('status', 'pending')->count();
        if ($pendingVisits > 0) {
            return redirect()->route('dashboard')->with('error', 'Silakan selesaikan semua kunjungan terlebih dahulu sebelum absen keluar.');
        }

        // Validasi: odometer akhir harus >= odometer awal
        if ($request->odometer_value < $todayLog->start_odo_value) {
            return back()->with('error', 'Nilai odometer akhir tidak boleh kurang dari odometer awal.')->withInput();
        }

        // Validasi: odometer maksimal 8 digit (99.999.999)
        if ($request->odometer_value > 99999999) {
            return back()->with('error', 'Nilai odometer terlalu besar. Maksimal 8 digit (99.999.999 km).')->withInput();
        }

        // Upload foto selfie
        $selfiePath = $this->saveBase64Image($request->photo, 'attendance');

        // Upload foto odometer
        $odometerPhotoPath = $this->saveBase64Image($request->odometer_photo, 'odometer');

        // Update daily log dengan absen keluar
        $todayLog->update([
            'end_time' => Carbon::now(),
            'end_photo' => $selfiePath,
            'end_odo_value' => $request->odometer_value,
            'end_odo_photo' => $odometerPhotoPath,
            'end_lat' => $request->lat,
            'end_long' => $request->long,
            'end_type' => $request->end_type,
            'end_notes' => $request->end_notes,
        ]);

        // Auto hitung & simpan reimburse bahan bakar
        $this->calculateFuelReimbursement($todayLog);

        return redirect()->route('dashboard')->with('success', 'Absen keluar berhasil! Reimburse bahan bakar otomatis dihitung.');
    }

    // ==========================================
    // REIMBURSE MANUAL (Pengeluaran Lain)
    // ==========================================

    public function showReimburseForm($dailyLogId)
    {
        $user = Auth::user();
        $dailyLog = DailyLog::where('id', $dailyLogId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Cek deadline (Selasa minggu depan)
        $deadline = Expense::calculateDeadline($dailyLog->date);

        // Cek apakah sudah melewati deadline
        if (Carbon::today()->gt($deadline)) {
            return redirect()->route('sales.history')->with('error', 'Batas pengisian reimburse sudah lewat.');
        }

        // Ambil expenses yang sudah ada untuk hari ini
        $expenses = $dailyLog->expenses()
            ->where('is_auto_calculated', false)
            ->get();

        return view('sales.reimburse_form', compact('dailyLog', 'expenses', 'deadline'));
    }

    public function storeReimburse(Request $request, $dailyLogId)
    {
        $user = Auth::user();
        $dailyLog = DailyLog::where('id', $dailyLogId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Cek deadline
        $deadline = Expense::calculateDeadline($dailyLog->date);
        if (Carbon::today()->gt($deadline)) {
            return redirect()->route('sales.history')->with('error', 'Batas pengisian reimburse sudah lewat.');
        }

        // Validasi
        $request->validate([
            'type' => 'required',
            'amount' => 'required|numeric|min:0',
            'photo_receipt' => 'nullable', // Kita cek manual di bawah
            'note' => 'nullable|string',
            'generate_receipt' => 'nullable|boolean',
            'license_plate' => 'required_if:generate_receipt,1',
            'parking_location' => 'required_if:generate_receipt,1',
        ]);

        // Cek Foto (Wajib jika TIDAK auto generate receipt)
        if (!$request->generate_receipt && empty($request->photo_receipt)) {
            return back()->withErrors(['photo_receipt' => 'Foto bukti pembayaran wajib diupload.'])->withInput();
        }

        $receiptPath = null;

        // A. JIKA AUTO GENERATE RECEIPT (PARKIR)
        if ($request->generate_receipt && $request->type == 'parking') {
            if (!extension_loaded('gd')) {
                return back()->withErrors([
                    'generate_receipt' => 'Fitur buat struk otomatis memerlukan ekstensi GD di PHP aktif. Silakan hubungi IT Administrator atau aktifkan "extension=gd" di php.ini Anda lalu restart server, ATAU matikan pilihan ini dan upload/foto struk manual.'
                ])->withInput();
            }

            $receiptPath = $this->generateParkingReceipt(
                $request->amount,
                $dailyLog->date,
                $request->license_plate,
                $request->parking_location
            );
            // Tambahkan keterangan auto ke note
            $request->merge(['note' => $request->note . " [Auto Receipt: {$request->parking_location}]"]);
        }
        // B. JIKA FOTO MANUAL
        else {
            $receiptPath = $this->saveBase64Image($request->photo_receipt, 'expenses');
        }

        // Simpan expense
        // Status flow:
        // - Sales: pending_spv ( Supervisor -> HRD -> Finance)
        // - Supervisor: pending_hrd ( HRD -> Finance)
        $initialStatus = $user->role === 'supervisor' ? 'pending_hrd' : 'pending_spv';

        Expense::create([
            'user_id' => $user->id,
            'daily_log_id' => $dailyLog->id,
            'date' => $dailyLog->date,
            'type' => $request->type,
            'amount' => $request->amount,
            'note' => $request->note,
            'photo_receipt' => $receiptPath,
            'is_auto_calculated' => false,
            'status' => $initialStatus,
            'deadline_date' => $deadline,
            'submitted_by' => $user->id, // Track siapa yang submit untuk revisi flow
        ]);

        return redirect()->route('sales.history')->with('success', 'Pengeluaran berhasil dicatat!');
    }

    /**
     * Show form untuk upload struk bahan bakar
     */
    public function showFuelReceiptForm($expenseId)
    {
        $user = Auth::user();

        // Ambil expense dengan relasi
        $expense = Expense::with('dailyLog')->findOrFail($expenseId);

        // Cek akses: hanya bisa upload struk untuk expense milik sendiri
        if ($expense->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses untuk mengupload struk ini.');
        }

        // Cek apakah ini fuel expense yang auto calculated
        if (!$expense->isFuel()) {
            return redirect()->route('sales.history')->with('error', 'Hanya bisa upload struk untuk bahan bakar yang dihitung otomatis.');
        }

        // Cek deadline
        $deadline = Expense::calculateDeadline($expense->dailyLog->date);
        if (Carbon::today()->gt($deadline)) {
            return redirect()->route('sales.history')->with('error', 'Batas pengisian struk sudah lewat.');
        }

        return view('sales.fuel_receipt_form', compact('expense', 'deadline'));
    }

    /**
     * Store struk bahan bakar
     */
    public function storeFuelReceipt(Request $request, $expenseId)
    {
        $user = Auth::user();

        // Ambil expense
        $expense = Expense::with('dailyLog')->findOrFail($expenseId);

        // Cek akses
        if ($expense->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses untuk mengupload struk ini.');
        }

        // Cek apakah ini fuel expense yang auto calculated
        if (!$expense->isFuel()) {
            return redirect()->route('sales.history')->with('error', 'Hanya bisa upload struk untuk bahan bakar yang dihitung otomatis.');
        }

        // Cek deadline
        $deadline = Expense::calculateDeadline($expense->dailyLog->date);
        if (Carbon::today()->gt($deadline)) {
            return redirect()->route('sales.history')->with('error', 'Batas pengisian struk sudah lewat.');
        }

        $request->validate([
            'photo_receipt' => 'required',
        ]);

        // Upload foto struk
        $receiptPath = $this->saveBase64Image($request->photo_receipt, 'expenses');

        // Update expense dengan photo_receipt
        $expense->update([
            'photo_receipt' => $receiptPath,
        ]);

        return redirect()->route('sales.history.detail', $expense->daily_log_id)->with('success', 'Struk bahan bakar berhasil diupload!');
    }

    // ==========================================
    // REVISI REIMBURSE (SALES)
    // ==========================================

    /**
     * Sales merevisi expense yang ditolak dan perlu revisi
     */
    public function reviseReimburse(Request $request, $expenseId)
    {
        $user = Auth::user();

        // Ambil expense
        $expense = Expense::with('dailyLog')->findOrFail($expenseId);

        // Cek akses: hanya bisa revisi expense milik sendiri
        if ($expense->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses untuk merevisi reimburse ini.');
        }

        // Cek apakah expense memerlukan revisi dari Sales
        if (!$expense->needsRevisionBySales()) {
            return redirect()->route('sales.history.detail', $expense->daily_log_id)
                ->with('error', 'Reimburse ini tidak memerlukan revisi.');
        }

        // Cek apakah menggunakan generate receipt (untuk parkir)
        $useGenerateReceipt = $request->input('generate_receipt') == '1' && $expense->type === 'parking';

        if ($useGenerateReceipt) {
            if (!extension_loaded('gd')) {
                return back()->withErrors([
                    'generate_receipt' => 'Fitur buat struk otomatis memerlukan ekstensi GD di PHP aktif. Silakan hubungi IT Administrator atau aktifkan "extension=gd" di php.ini Anda lalu restart server, ATAU upload/foto struk manual.'
                ])->withInput();
            }

            // Validasi untuk generate receipt
            $request->validate([
                'license_plate' => 'required|string',
                'parking_location' => 'required|string',
                'note' => 'nullable|string',
            ]);

            // Generate parking receipt
            $receiptPath = $this->generateParkingReceipt(
                $expense->amount,
                $expense->date ?? now(),
                $request->license_plate,
                $request->parking_location
            );

            // Mark as generated
            $expense->is_generated_receipt = true;
        } else {
            // Validasi untuk upload foto
            $request->validate([
                'photo_receipt' => 'required', // Base64 image
                'note' => 'nullable|string',
            ]);

            // Upload foto struk baru
            $receiptPath = $this->saveBase64Image($request->photo_receipt, 'expenses');
            $expense->is_generated_receipt = false;
        }

        // Tentukan status target setelah revisi:
        // Jika sudah di-approve oleh HRD sebelumnya (berarti Finance yang meminta revisi), langsung ke Finance.
        // Jika belum, kembalikan ke SPV.
        $targetStatus = $expense->approved_by_hrd_at ? 'pending_finance' : 'pending_spv';

        // Update expense dengan data baru
        $expense->update([
            'photo_receipt' => $receiptPath,
            'note' => $request->note ?? $expense->note,
            'status' => $targetStatus,
            'rejection_note' => null, // Clear rejection note
            'rejection_type' => null,
            'revised_at' => now(),
            'revision_count' => $expense->revision_count + 1,
            'is_generated_receipt' => $expense->is_generated_receipt,
        ]);

        $approverLabel = $targetStatus === 'pending_finance' ? 'Finance' : 'SPV';
        return redirect()->route('sales.history.detail', $expense->daily_log_id)
            ->with('success', "Revisi berhasil dikirim! Menunggu persetujuan {$approverLabel}.");
    }

    // ==========================================
    // HISTORY PRIBADI
    // ==========================================

    public function history(Request $request)
    {
        $user = Auth::user();

        // Ambil daily logs dengan relasi
        $dailyLogs = DailyLog::where('user_id', $user->id)
            ->with(['visits', 'expenses'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');

        // Filter tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $dailyLogs->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $dailyLogs = $dailyLogs->get();

        return view('sales.history', compact('dailyLogs'));
    }

    /**
     * Show detail history absen
     */
    public function showDetail($id)
    {
        $user = Auth::user();

        // Ambil daily log dengan semua relasi
        $dailyLog = DailyLog::with(['user', 'visits', 'expenses'])
            ->findOrFail($id);

        // Cek akses: Sales hanya bisa lihat miliknya sendiri
        // Supervisor bisa lihat miliknya sendiri DAN sales yang dibawahinya
        // HRD/IT bisa lihat semua
        if ($user->role === 'sales') {
            if ($dailyLog->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses untuk melihat detail ini.');
            }
        } elseif ($user->role === 'supervisor') {
            // Supervisor bisa lihat miliknya sendiri
            if ($dailyLog->user_id === $user->id) {
                // OK - supervisor melihat datanya sendiri
            } else {
                // Cek apakah sales ini dibawah supervisor ini (Multi-Supervisor Support)
                $isSubordinate = $user->subordinates()->where('sales_id', $dailyLog->user_id)->exists();
                
                // Fallback: Cek legacy supervisor_id jika relasi pivot belum ada (opsional)
                if (!$isSubordinate && $dailyLog->user->supervisor_id === $user->id) {
                    $isSubordinate = true;
                }

                if (!$isSubordinate) {
                    abort(403, 'Anda tidak memiliki akses untuk melihat detail ini.');
                }
            }
        } elseif (!in_array($user->role, ['hrd', 'it', 'finance'])) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        return view('sales.history_detail', compact('dailyLog'));
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Simpan base64 image ke storage
     */
    private function saveBase64Image($base64_string, $folder)
    {
        // Validasi input
        if (empty($base64_string)) {
            throw new \Exception('Data gambar tidak valid atau kosong.');
        }

        // Bersihkan string dari whitespace dan newline
        $base64_string = trim($base64_string);
        
        // Ambil bagian base64 saja (setelah prefix data URI jika ada)
        $base64_data = $base64_string;
        
        // Cek apakah string mengandung prefix data URI (data:image/xxx;base64,)
        if (strpos($base64_string, ';base64,') !== false) {
            // Format standar: data:image/png;base64,XXXX
            $image_parts = explode(";base64,", $base64_string);
            $base64_data = isset($image_parts[1]) ? $image_parts[1] : $base64_string;
        } elseif (strpos($base64_string, ',') !== false) {
            // Format alternatif: data:image/xxx,XXXX 
            $image_parts = explode(",", $base64_string);
            $base64_data = end($image_parts);
        }
        
        // Bersihkan karakter yang tidak valid dari base64
        $base64_data = preg_replace('/\s+/', '', $base64_data); // Hapus semua whitespace
        $base64_data = str_replace(["\r", "\n", " "], '', $base64_data); // Pastikan tidak ada newline/spasi
        
        // Decode base64 (strict = false untuk lebih toleran)
        $image_base64 = base64_decode($base64_data, false);

        // Validasi hasil decode
        if ($image_base64 === false || strlen($image_base64) < 100) {
            // Jika masih gagal, coba decode langsung dari string asli (tanpa prefix)
            $image_base64 = base64_decode(preg_replace('/\s+/', '', $base64_string), false);
            
            if ($image_base64 === false || strlen($image_base64) < 100) {
                \Log::error('Base64 decode failed', [
                    'length' => strlen($base64_string),
                    'first_50_chars' => substr($base64_string, 0, 50)
                ]);
                throw new \Exception('Gagal decode data gambar base64. Silakan coba ambil foto ulang.');
            }
        }

        $fileName = $folder . '/' . uniqid() . '.png';
        Storage::disk('public')->put($fileName, $image_base64);
        return $fileName;
    }


    /**
     * Hitung & simpan reimburse bahan bakar otomatis
     */
    private function calculateFuelReimbursement(DailyLog $dailyLog)
    {
        // Hitung total KM
        $totalKm = $dailyLog->total_km;

        if ($totalKm <= 0) {
            return; // Tidak ada KM, skip
        }

        // Ambil setting bahan bakar aktif untuk user
        $fuelSetting = FuelSetting::getActiveSettingForUser($dailyLog->user);

        if (!$fuelSetting) {
            return; // Tidak ada setting, skip
        }

        // Hitung nominal reimburse
        $reimbursementAmount = $fuelSetting->calculateFuelReimbursement($totalKm);

        if ($reimbursementAmount <= 0) {
            return; // Tidak ada nominal, skip
        }

        // Hitung deadline (Selasa minggu depan)
        $deadline = Expense::calculateDeadline($dailyLog->date);

        // Status flow:
        // - Sales: pending_spv (Supervisor -> HRD -> Finance)
        // - Supervisor: pending_hrd (HRD -> Finance)
        $initialStatus = $dailyLog->user->role === 'supervisor' ? 'pending_hrd' : 'pending_spv';

        // Simpan expense bahan bakar (auto calculated)
        Expense::create([
            'user_id' => $dailyLog->user_id,
            'daily_log_id' => $dailyLog->id,
            'date' => $dailyLog->date,
            'type' => 'fuel',
            'amount' => $reimbursementAmount,
            'km_total' => $totalKm,
            'is_auto_calculated' => true,
            'deadline_date' => $deadline,
            'status' => $initialStatus,
            'submitted_by' => $dailyLog->user_id, // Track siapa yang submit
            'note' => "Auto calculated: {$totalKm} KM ÷ {$fuelSetting->km_per_liter} KM/L × Rp " . number_format($fuelSetting->fuel_price, 0, ',', '.') . "/L",
        ]);
    }

    /**
     * Generate struk parkir otomatis
     */
    private function generateParkingReceipt($amount, $date, $plate, $location)
    {
        // 1. Setup Canvas
        $width = 400;
        $height = 500;
        $image = imagecreatetruecolor($width, $height);

        // Warna
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $gray = imagecolorallocate($image, 200, 200, 200);
        $blue = imagecolorallocate($image, 50, 50, 200);

        // Fill background
        imagefill($image, 0, 0, $white);

        // Border
        imagerectangle($image, 10, 10, $width - 11, $height - 11, $black);
        imagerectangle($image, 12, 12, $width - 13, $height - 13, $black);

        // Path font (Gunakan font sistem atau fallback ke default GD font jika tdk ada)
// Karena kita tidak yakin ada .ttf, kita pakai built-in font numbers (1-5)

        // Header
        $centerX = $width / 2;

        // "STRUK PARKIR" (Font size 5 - terbesar di built-in)
        $text = "STRUK PARKIR";
        $fontW = imagefontwidth(5) * strlen($text);
        imagestring($image, 5, $centerX - ($fontW / 2), 40, $text, $black);

        // Garis pemisah
        imageline($image, 30, 70, $width - 30, 70, $black);

        // Lokasi
        $text = strtoupper($location);
        $fontW = imagefontwidth(5) * strlen($text);
        if ($fontW > 340)
            $text = substr($text, 0, 30) . '...'; // Truncate jika kepanjangan
        $fontW = imagefontwidth(5) * strlen($text); // Recalculate
        imagestring($image, 5, $centerX - ($fontW / 2), 90, $text, $blue);

        // TANGGAL
        imagestring($image, 4, 40, 150, "Tanggal", $gray);
        imagestring($image, 4, 180, 150, ": " . \Carbon\Carbon::parse($date)->format('d/m/Y'), $black);

        // NO KENDARAAN
        imagestring($image, 4, 40, 190, "No. Polisi", $gray);
        imagestring($image, 4, 180, 190, ": " . strtoupper($plate), $black);

        // JENIS
        imagestring($image, 4, 40, 230, "Jenis", $gray);
        imagestring($image, 4, 180, 230, ": MOBIL / MOTOR", $black);

        // TOTAL
        imageline($image, 30, 280, $width - 30, 280, $black);

        imagestring($image, 5, 40, 310, "TOTAL BAYAR", $black);

        $priceText = "Rp " . number_format($amount, 0, ',', '.');
        $fontW = imagefontwidth(5) * strlen($priceText);
        imagestring($image, 5, $width - 40 - $fontW, 310, $priceText, $black);

        imageline($image, 30, 350, $width - 30, 350, $black);

        // Footer
        $footer1 = "Terima Kasih";
        $footer2 = "Struk ini sah sebagai bukti pembayaran";

        $fontW = imagefontwidth(4) * strlen($footer1);
        imagestring($image, 4, $centerX - ($fontW / 2), 400, $footer1, $black);

        $fontW = imagefontwidth(2) * strlen($footer2);
        imagestring($image, 2, $centerX - ($fontW / 2), 430, $footer2, $gray);


        // Simpan File
        $fileName = 'expenses/parking_' . uniqid() . '.png';

        // Simpan ke Object Storage atau Local Storage (Public disk)
// Kita butuh stream untuk di-put ke storage facade
        ob_start();
        imagepng($image);
        $imageContent = ob_get_clean();
        imagedestroy($image);

        // Use Storage facade to put content
        \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $imageContent);

        return $fileName;
    }

    /**
     * Generate struk manual (Custom - HRD)
     */
    public function generateCustomReceipt(Request $request, $id)
    {
        // Cek akses (HRD/IT only)
        if (!in_array(Auth::user()->role, ['hrd', 'it'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!extension_loaded('gd')) {
            return response()->json([
                'message' => 'Gagal: Fitur generate struk memerlukan ekstensi GD di PHP aktif. Silakan hubungi IT Administrator atau aktifkan "extension=gd" di php.ini Anda lalu restart server.'
            ], 422);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'location' => 'required|string',
        ]);

        $expense = Expense::findOrFail($id);

        // Backup original receipt if needed
        if (!$expense->is_generated_receipt && $expense->photo_receipt) {
            $expense->original_photo_receipt = $expense->photo_receipt;
        }

        // Generate Image
        $receiptPath = $this->generateGenericReceipt(
            $request->amount,
            $request->date,
            $request->location,
            ($expense->type === 'fuel' ? 'STRUK BAHAN BAKAR' : 'STRUK PEMBAYARAN')
        );

        // Update Expense
        $expense->photo_receipt = $receiptPath;
        $expense->is_generated_receipt = true;

        // Append note if not already there
        if (!str_contains($expense->note ?? '', '[Manual Receipt Generated by HRD]')) {
            $expense->note = ($expense->note ?? '') . " [Manual Receipt Generated by HRD]";
        }

        $expense->save();

        return response()->json([
            'message' => 'Struk berhasil digenerate.',
            'photo_receipt' => $receiptPath
        ]);
    }

    /**
     * Delete/Revert struk (HRD)
     */
    public function deleteReceipt($id)
    {
        // Cek akses (HRD/IT only)
        if (!in_array(Auth::user()->role, ['hrd', 'it'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $expense = Expense::findOrFail($id);

        if ($expense->is_generated_receipt) {
            // Revert Logic
            // 1. Delete generated file path
            if ($expense->photo_receipt && Storage::disk('public')->exists($expense->photo_receipt)) {
                // Storage::disk('public')->delete($expense->photo_receipt); // Optional delete
            }

            // 2. Restore original if exists
            if ($expense->original_photo_receipt) {
                $expense->photo_receipt = $expense->original_photo_receipt;
                $expense->original_photo_receipt = null;
            } else {
                $expense->photo_receipt = null;
            }

            $expense->is_generated_receipt = false;
            $expense->save();

            return response()->json(['message' => 'Struk berhasil dikembalikan ke asli.']);
        } else {
            // Check if photo exists but NOT generated
            if ($expense->photo_receipt) {
                return response()->json(['message' => 'Tidak bisa menghapus struk asli yang diupload Sales.'], 403);
            }
            return response()->json(['message' => 'Tidak ada struk untuk dihapus.']);
        }
    }

    /**
     * Helper: Generate Generic Receipt Image
     */
    private function generateGenericReceipt($amount, $date, $location, $title)
    {
        // 1. Setup Canvas
        $width = 400;
        $height = 500;
        $image = imagecreatetruecolor($width, $height);

        // Warna
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $gray = imagecolorallocate($image, 200, 200, 200);
        $blue = imagecolorallocate($image, 50, 50, 200);

        // Fill background
        imagefill($image, 0, 0, $white);

        // Border
        imagerectangle($image, 10, 10, $width - 11, $height - 11, $black);
        imagerectangle($image, 12, 12, $width - 13, $height - 13, $black);

        // Header
        $centerX = $width / 2;

        // TITLE
        $text = strtoupper($title);
        $fontW = imagefontwidth(5) * strlen($text);
        if ($fontW > 380)
            $text = "STRUK PEMBAYARAN"; // Fallback if too long
        $fontW = imagefontwidth(5) * strlen($text);
        imagestring($image, 5, $centerX - ($fontW / 2), 40, $text, $black);

        // Garis pemisah
        imageline($image, 30, 70, $width - 30, 70, $black);

        // LOKASI
        $text = strtoupper($location);
        $fontW = imagefontwidth(5) * strlen($text);
        if ($fontW > 340)
            $text = substr($text, 0, 30) . '...';
        $fontW = imagefontwidth(5) * strlen($text);
        imagestring($image, 5, $centerX - ($fontW / 2), 90, $text, $blue);

        // TANGGAL
        imagestring($image, 4, 40, 150, "Tanggal", $gray);
        imagestring($image, 4, 180, 150, ": " . \Carbon\Carbon::parse($date)->format('d/m/Y'), $black);

        // TOTAL
        imageline($image, 30, 280, $width - 30, 280, $black);

        imagestring($image, 5, 40, 310, "TOTAL", $black);

        $priceText = "Rp " . number_format($amount, 0, ',', '.');
        $fontW = imagefontwidth(5) * strlen($priceText);
        imagestring($image, 5, $width - 40 - $fontW, 310, $priceText, $black);

        imageline($image, 30, 350, $width - 30, 350, $black);

        // Footer
        $footer1 = "Terima Kasih";
        $footer2 = "Generated by HRD System";

        $fontW = imagefontwidth(4) * strlen($footer1);
        imagestring($image, 4, $centerX - ($fontW / 2), 400, $footer1, $black);

        $fontW = imagefontwidth(2) * strlen($footer2);
        imagestring($image, 2, $centerX - ($fontW / 2), 430, $footer2, $gray);


        // Simpan File
        $fileName = 'expenses/generated_' . uniqid() . '.png';

        ob_start();
        imagepng($image);
        $imageContent = ob_get_clean();
        imagedestroy($image);

        // Use Storage facade to put content
        \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $imageContent);

        return $fileName;
    }

    /**
     * Store system calculated distance from HRD verification
     */
    public function storeSystemDistance(Request $request, $id)
    {
        $user = Auth::user();

        $dailyLog = DailyLog::with(['visits', 'user'])->findOrFail($id);

        // === Hak akses ===
        // HRD/IT/Finance: semua karyawan. Supervisor: HANYA bawahannya (bukan absen sendiri). Selainnya: tolak.
        if (in_array($user->role, ['hrd', 'it', 'finance'])) {
            // boleh untuk log siapa saja
        } elseif ($user->role === 'supervisor') {
            if ($dailyLog->user_id === $user->id) {
                return response()->json(['message' => 'Supervisor tidak dapat menghitung absennya sendiri.'], 403);
            }
            $isSubordinate = $user->subordinates()->where('sales_id', $dailyLog->user_id)->exists();
            if (!$isSubordinate && $dailyLog->user && $dailyLog->user->supervisor_id === $user->id) {
                $isSubordinate = true;
            }
            if (!$isSubordinate) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // === Hitung jarak di SERVER (deterministik, tanpa依赖 API eksternal/OSRM) ===
        // Titik berurutan: absen masuk -> kunjungan (urut waktu) -> absen keluar
        $points = [];
        if ($dailyLog->lat && $dailyLog->long) {
            $points[] = [(float) $dailyLog->lat, (float) $dailyLog->long];
        }
        foreach ($dailyLog->visits->sortBy('time') as $visit) {
            if ($visit->lat && $visit->long) {
                $points[] = [(float) $visit->lat, (float) $visit->long];
            }
        }
        if ($dailyLog->end_lat && $dailyLog->end_long) {
            $points[] = [(float) $dailyLog->end_lat, (float) $dailyLog->end_long];
        }

        $straightKm = 0.0;
        for ($i = 1; $i < count($points); $i++) {
            $straightKm += $this->haversineKm(
                $points[$i - 1][0], $points[$i - 1][1],
                $points[$i][0],     $points[$i][1]
            );
        }

        // Faktor jalan: jarak tempuh nyata ~30% lebih jauh dari garis lurus
        $distance = round($straightKm * 1.3, 2);

        $dailyLog->update(['system_calculated_distance' => $distance]);

        return response()->json([
            'message' => 'Jarak berhasil diverifikasi dan disimpan.',
            'distance' => $distance,
        ]);
    }

    /**
     * Jarak garis lurus (Haversine) antara dua koordinat dalam kilometer.
     */
    private function haversineKm($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return 2 * $earthRadius * asin(sqrt($a));
    }
}