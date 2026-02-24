<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\HRDController;
use App\Http\Controllers\ITController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FuelSettingController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;


// ====================================================
// AREA 1: ROUTE DENGAN MIDDLEWARE AUTH
// ====================================================
Route::middleware(['auth', 'force.password.change'])->group(function () {

    // --- DASHBOARD UMUM (Redirect berdasarkan role) ---
    Route::get('/', function () {
        $user = Auth::user();
        return match ($user->role) {
            'sales', 'supervisor' => redirect()->route('sales.dashboard'),
            'hrd' => redirect()->route('hrd.dashboard'),
            'finance' => redirect()->route('finance.dashboard'),
            'it' => redirect()->route('it.dashboard'),
            default => redirect()->route('login'),
        };
    })->name('dashboard');

    // ==========================================
    // ROUTE SALES & SUPERVISOR (Absensi & History)
    // ==========================================
    Route::middleware(['role:sales,supervisor'])->group(function () {

        // --- DASHBOARD SALES/SUPERVISOR ---
        Route::get('/sales/dashboard', [SalesController::class, 'dashboard'])->name('sales.dashboard');

        // --- ABSEN MASUK ---
        Route::get('/absen/masuk', [SalesController::class, 'showAbsenMasuk'])->name('sales.absen.masuk');
        Route::post('/absen/masuk', [SalesController::class, 'storeAbsenMasuk'])->name('sales.absen.masuk.store');

        // --- ABSEN TOKO (Kunjungan) ---
        Route::get('/absen/toko', [SalesController::class, 'showAbsenToko'])->name('sales.absen.toko');
        Route::post('/absen/toko', [SalesController::class, 'storeAbsenToko'])->name('sales.absen.toko.store');

        // --- ABSEN KELUAR ---
        Route::get('/absen/keluar', [SalesController::class, 'showAbsenKeluar'])->name('sales.absen.keluar');
        Route::post('/absen/keluar', [SalesController::class, 'storeAbsenKeluar'])->name('sales.absen.keluar.store');

        // --- HISTORY ---
        Route::get('/history', [SalesController::class, 'history'])->name('sales.history');

        // --- REIMBURSE ---
        Route::get('/reimburse/{dailyLogId}', [SalesController::class, 'showReimburseForm'])->name('sales.reimburse.form');
        Route::post('/reimburse/{dailyLogId}', [SalesController::class, 'storeReimburse'])->name('sales.reimburse.store');

        // --- UPLOAD STRUK BAHAN BAKAR ---
        Route::get('/fuel-receipt/{expenseId}', [SalesController::class, 'showFuelReceiptForm'])->name('sales.fuel.receipt.form');
        Route::post('/fuel-receipt/{expenseId}', [SalesController::class, 'storeFuelReceipt'])->name('sales.fuel.receipt.store');

        // --- REVISI REIMBURSE (Sales) ---
        Route::post('/reimburse/{expenseId}/revise', [SalesController::class, 'reviseReimburse'])->name('sales.reimburse.revise');
    });

    // ==========================================
    // ROUTE SUPERVISOR (Lihat Tim Sales)
    // ==========================================
    Route::middleware(['role:supervisor'])->group(function () {
        Route::get('/supervisor/dashboard', [SupervisorController::class, 'dashboard'])->name('supervisor.dashboard');
        Route::get('/supervisor/sales/{id}', [SupervisorController::class, 'showSales'])->name('supervisor.show.sales');

        // APPROVAL REIMBURSE
        Route::get('/supervisor/approval/reimburse', [\App\Http\Controllers\ReimburseApprovalController::class, 'index'])->name('supervisor.reimburse.approval');
        Route::post('/supervisor/approval/reimburse/{id}/approve', [\App\Http\Controllers\ReimburseApprovalController::class, 'approve'])->name('supervisor.reimburse.approve');
        Route::post('/supervisor/approval/reimburse/{id}/reject', [\App\Http\Controllers\ReimburseApprovalController::class, 'reject'])->name('supervisor.reimburse.reject');

        // REVISI REIMBURSE (SPV - jika Finance reject ke SPV)
        Route::post('/supervisor/reimburse/{expenseId}/revise', [SupervisorController::class, 'reviseReimburse'])->name('supervisor.reimburse.revise');
    });

    // Route untuk menampilkan file receipt/struk secara aman melalui controller
    Route::get('/expenses/{id}/receipt', [\App\Http\Controllers\ReimburseApprovalController::class, 'showReceipt'])
        ->middleware('auth')
        ->name('expenses.receipt.show');

    // Secure file routes for attendance and visit photos
    Route::get('/files/daily/{id}/{kind}', [\App\Http\Controllers\FileController::class, 'dailyPhoto'])
        ->middleware('auth')
        ->name('files.daily.photo');

    Route::get('/files/visit/{id}', [\App\Http\Controllers\FileController::class, 'visitPhoto'])
        ->middleware('auth')
        ->name('files.visit.photo');

    // ==========================================
    // ROUTE HRD (Manajemen & Laporan)
    // ==========================================
    Route::middleware(['role:hrd'])->group(function () {

        // --- DASHBOARD ---
        Route::get('/hrd/dashboard', [HRDController::class, 'dashboard'])->name('hrd.dashboard');

        // --- MANAJEMEN AKUN SALES ---
        Route::get('/hrd/sales/create', [HRDController::class, 'createSales'])->name('hrd.sales.create');
        Route::post('/hrd/sales/store', [HRDController::class, 'storeSales'])->name('hrd.sales.store');

        // --- MANAJEMEN AKUN SUPERVISOR ---
        Route::get('/hrd/supervisor/create', [HRDController::class, 'createSupervisor'])->name('hrd.supervisor.create');
        Route::post('/hrd/supervisor/store', [HRDController::class, 'storeSupervisor'])->name('hrd.supervisor.store');

        // --- MANAJEMEN AKUN FINANCE ---
        Route::get('/hrd/finance/create', [HRDController::class, 'createFinance'])->name('hrd.finance.create');
        Route::post('/hrd/finance/store', [HRDController::class, 'storeFinance'])->name('hrd.finance.store');

        // --- DETAIL KARYAWAN ---
        Route::get('/hrd/user/{id}', [HRDController::class, 'showUser'])->name('hrd.show.user');

        // --- MANAJEMEN SUPERVISOR (MULTI-SUPERVISOR) ---
        Route::post('/hrd/user/{id}/add-supervisor', [HRDController::class, 'addSupervisor'])->name('hrd.add.supervisor');
        Route::post('/hrd/user/{id}/remove-supervisor', [HRDController::class, 'removeSupervisor'])->name('hrd.remove.supervisor');

        // --- NONAKTIFKAN/AKTIFKAN KARYAWAN ---
        Route::post('/hrd/user/{id}/toggle-status', [HRDController::class, 'toggleStatus'])->name('hrd.user.toggle_status');

        // --- RESET PASSWORD ---
        Route::post('/hrd/user/{id}/reset', [HRDController::class, 'resetPassword'])->name('hrd.reset.password');

        // --- LAPORAN & EXPORT ---
        Route::get('/hrd/export', [HRDController::class, 'exportPage'])->name('hrd.export.page');
        Route::get('/hrd/export/excel', [HRDController::class, 'exportExcel'])->name('hrd.export.excel');

        // --- CUSTOM RECEIPT GENERATION (HRD) ---
        Route::post('/sales/expense/{id}/generate-custom-receipt', [SalesController::class, 'generateCustomReceipt'])->name('sales.expense.generate_custom_receipt');
        Route::post('/sales/expense/{id}/delete-receipt', [SalesController::class, 'deleteReceipt'])->name('sales.expense.delete_receipt');

        // --- APPROVAL REIMBURSE ---
        Route::get('/hrd/approval/reimburse', [\App\Http\Controllers\ReimburseApprovalController::class, 'index'])->name('hrd.reimburse.approval');
        Route::post('/hrd/approval/reimburse/{id}/approve', [\App\Http\Controllers\ReimburseApprovalController::class, 'approve'])->name('hrd.reimburse.approve');
        Route::post('/hrd/approval/reimburse/{id}/reject', [\App\Http\Controllers\ReimburseApprovalController::class, 'reject'])->name('hrd.reimburse.reject');

        // --- SETTING BAHAN BAKAR ---
        Route::get('/fuel-settings', [FuelSettingController::class, 'index'])->name('fuel_settings.index');
        Route::post('/fuel-settings/general', [FuelSettingController::class, 'storeGeneral'])->name('fuel_settings.store.general');
        Route::post('/fuel-settings/individual', [FuelSettingController::class, 'storeIndividual'])->name('fuel_settings.store.individual');
        Route::put('/fuel-settings/{id}', [FuelSettingController::class, 'update'])->name('fuel_settings.update');
        Route::post('/fuel-settings/{id}/deactivate', [FuelSettingController::class, 'deactivate'])->name('fuel_settings.deactivate');
    });

    // ==========================================
    // ROUTE FINANCE (Approval & Laporan)
    // ==========================================
    Route::middleware(['role:finance'])->group(function () {

        // --- DASHBOARD ---
        Route::get('/finance/dashboard', [FinanceController::class, 'dashboard'])->name('finance.dashboard');

        // --- APPROVAL REIMBURSE ---
        Route::get('/finance/approval/reimburse', [FinanceController::class, 'reimbursementApproval'])->name('finance.reimburse.approval');
        Route::post('/finance/approval/reimburse/{id}/approve', [FinanceController::class, 'approveReimburse'])->name('finance.reimburse.approve');
        Route::post('/finance/approval/reimburse/{id}/reject', [FinanceController::class, 'rejectReimburse'])->name('finance.reimburse.reject');
        Route::post('/finance/approval/reimburse/bulk-approve', [FinanceController::class, 'bulkApproveReimburse'])->name('finance.reimburse.bulk_approve');

        // --- DETAIL USER & ABSENSI ---
        Route::get('/finance/user/{id}', [FinanceController::class, 'showUser'])->name('finance.show.user');
        Route::get('/finance/history/{id}', [FinanceController::class, 'showDetail'])->name('finance.history.detail');

        // --- FUEL SETTINGS ---
        Route::get('/finance/fuel-settings', [FinanceController::class, 'fuelSettingsIndex'])->name('finance.fuel_settings.index');
        Route::post('/finance/fuel-settings/general', [FinanceController::class, 'storeGeneralFuelSetting'])->name('finance.fuel_settings.store.general');
        Route::post('/finance/fuel-settings/individual', [FinanceController::class, 'storeIndividualFuelSetting'])->name('finance.fuel_settings.store.individual');
        Route::put('/finance/fuel-settings/{id}', [FinanceController::class, 'updateFuelSetting'])->name('finance.fuel_settings.update');
        Route::post('/finance/fuel-settings/{id}/deactivate', [FinanceController::class, 'deactivateFuelSetting'])->name('finance.fuel_settings.deactivate');

        // --- EXPORT LAPORAN ---
        Route::get('/finance/export', [FinanceController::class, 'exportPage'])->name('finance.export.page');
        Route::get('/finance/export/excel', [FinanceController::class, 'exportExcel'])->name('finance.export.excel');
    });

    // ==========================================
    // ROUTE IT (Superadmin - Semua Fitur HRD + Buat Akun HRD)
    // ==========================================
    Route::middleware(['role:it'])->group(function () {

        // --- DASHBOARD ---
        Route::get('/it/dashboard', [ITController::class, 'dashboard'])->name('it.dashboard');

        // --- MANAJEMEN AKUN SALES ---
        Route::get('/it/sales/create', [ITController::class, 'createSales'])->name('it.sales.create');
        Route::post('/it/sales/store', [ITController::class, 'storeSales'])->name('it.sales.store');

        // --- MANAJEMEN AKUN SUPERVISOR ---
        Route::get('/it/supervisor/create', [ITController::class, 'createSupervisor'])->name('it.supervisor.create');
        Route::post('/it/supervisor/store', [ITController::class, 'storeSupervisor'])->name('it.supervisor.store');

        // --- MANAJEMEN AKUN HRD (KHUSUS IT) ---
        Route::get('/it/hrd/create', [ITController::class, 'createHrd'])->name('it.hrd.create');
        Route::post('/it/hrd/store', [ITController::class, 'storeHrd'])->name('it.hrd.store');

        // --- MANAJEMEN AKUN FINANCE (KHUSUS IT) ---
        Route::get('/it/finance/create', [ITController::class, 'createFinance'])->name('it.finance.create');
        Route::post('/it/finance/store', [ITController::class, 'storeFinance'])->name('it.finance.store');

        // --- DETAIL KARYAWAN ---
        Route::get('/it/user/{id}', [ITController::class, 'showUser'])->name('it.show.user');

        // --- RESET PASSWORD ---
        Route::post('/it/user/{id}/reset', [ITController::class, 'resetPassword'])->name('it.reset.password');

        // --- BLOCK / UNBLOCK USER ---
        Route::post('/it/user/{id}/toggle-status', [ITController::class, 'toggleStatus'])->name('it.user.toggle_status');

        // --- HAPUS AKUN (KHUSUS IT) ---
        Route::delete('/it/user/{id}', [ITController::class, 'deleteUser'])->name('it.user.delete');

        // --- LAPORAN & EXPORT ---
        Route::get('/it/export', [ITController::class, 'exportPage'])->name('it.export.page');
        Route::get('/it/export/excel', [ITController::class, 'exportExcel'])->name('it.export.excel');

        // --- CUSTOM RECEIPT GENERATION (IT) ---
        Route::post('/it/sales/expense/{id}/generate-custom-receipt', [SalesController::class, 'generateCustomReceipt'])->name('it.sales.expense.generate_custom_receipt');
        Route::post('/it/sales/expense/{id}/delete-receipt', [SalesController::class, 'deleteReceipt'])->name('it.sales.expense.delete_receipt');

        // --- SETTING BAHAN BAKAR ---
        Route::get('/it/fuel-settings', [FuelSettingController::class, 'index'])->name('it.fuel_settings.index');
        Route::post('/it/fuel-settings/general', [FuelSettingController::class, 'storeGeneral'])->name('it.fuel_settings.store.general');
        Route::post('/it/fuel-settings/individual', [FuelSettingController::class, 'storeIndividual'])->name('it.fuel_settings.store.individual');
        Route::put('/it/fuel-settings/{id}', [FuelSettingController::class, 'update'])->name('it.fuel_settings.update');
        Route::post('/it/fuel-settings/{id}/deactivate', [FuelSettingController::class, 'deactivate'])->name('it.fuel_settings.deactivate');

        // --- PENGATURAN SISTEM (Auto Deletion) ---
        Route::get('/it/settings', [ITController::class, 'settings'])->name('it.settings');
        Route::post('/it/settings/update', [ITController::class, 'updateSettings'])->name('it.settings.update');
        Route::post('/it/settings/cleanup-users', [ITController::class, 'cleanupInactiveUsers'])->name('it.settings.cleanup_users');
    });

    // ==========================================
    // ROUTE UMUM (Semua Role)
    // ==========================================

    // --- PROFILE & PASSWORD (Semua Role) ---
    Route::get('/profile', [ProfileController::class, 'showProfile'])->name('profile.show');
    Route::get('/password/change', [ProfileController::class, 'editPassword'])->name('password.edit');
    Route::post('/password/update', [ProfileController::class, 'updatePassword'])->name('password.update.custom');

    // --- DETAIL HISTORY ABSEN (Semua Role dengan akses sesuai role) ---
    Route::get('/history/{id}', [SalesController::class, 'showDetail'])->name('sales.history.detail');
    Route::post('/history/{id}/verify-distance', [SalesController::class, 'storeSystemDistance'])->name('sales.history.verify_distance');

    // --- LOGOUT (Semua Role) ---
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');

    // --- SERVE STORAGE FILES (Dengan Auth) ---
    // Route ini akan serve file dari storage dengan auth check
    // Hanya akan dipanggil jika symlink tidak berfungsi
    Route::get('/file/{path}', function ($path) {
        try {
            // Pastikan path tidak mengandung .. untuk keamanan
            $path = str_replace('..', '', $path);

            // Cek apakah file ada
            if (!Storage::disk('public')->exists($path)) {
                abort(404, 'File not found');
            }

            // Ambil file dan mime type
            $file = Storage::disk('public')->get($path);
            $mimeType = Storage::disk('public')->mimeType($path);

            // Return file dengan header yang benar
            return Response::make($file, 200, [
                'Content-Type' => $mimeType ?: 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
                'Cache-Control' => 'public, max-age=31536000',
            ]);
        } catch (\Exception $e) {
            abort(404, 'File not found');
        }
    })->where('path', '.*')->name('storage.serve');

}); // <--- INI KURUNG PENUTUP MIDDLEWARE (JANGAN DIHAPUS)


// ====================================================
// AREA 2: ROUTE DARURAT (UNTUK MEMUNCULKAN FOTO)
// ====================================================
// Akses link ini sekali saja di browser untuk memperbaiki foto
Route::get('/buat-storage-link', function () {
    $target = storage_path('app/public');
    $shortcut = public_path('storage');

    if (!file_exists($target)) {
        return "Gagal: Folder target 'storage/app/public' tidak ditemukan!";
    }

    try {
        // Hapus symlink lama jika ada
        if (file_exists($shortcut) || is_link($shortcut)) {
            if (is_link($shortcut)) {
                unlink($shortcut);
            } else {
                rmdir($shortcut);
            }
        }

        symlink($target, $shortcut);
        return "BERHASIL! Jembatan Storage sudah jadi. Silakan cek foto Anda.";
    } catch (\Exception $e) {
        return "Gagal membuat symlink: " . $e->getMessage();
    }
});
