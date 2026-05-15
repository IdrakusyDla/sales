<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\DailyLog;
use App\Models\Visit;
use App\Models\Expense;
use App\Models\ExpenseHistory;
use App\Models\FuelSetting;
use App\Models\ApplicationSetting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks for clean wipe
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate semua tabel (urutan bebas karena FK check dimatikan)
        DB::table('expense_histories')->truncate();
        DB::table('expenses')->truncate();
        DB::table('visits')->truncate();
        DB::table('daily_logs')->truncate();
        DB::table('supervisor_sales')->truncate();
        DB::table('fuel_settings')->truncate();
        DB::table('application_settings')->truncate();
        DB::table('users')->truncate();
        DB::table('roles')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Semua tabel berhasil dikosongkan.');

        // ==========================================
        // 1. ROLES
        // ==========================================
        $roles = [
            ['name' => 'IT System', 'slug' => 'it', 'permissions' => json_encode(['all']), 'dashboard_url' => 'it.dashboard', 'is_system_role' => true, 'hrd_can_create' => false],
            ['name' => 'Human Resources (HRD)', 'slug' => 'hrd', 'permissions' => json_encode(['manage_users', 'view_reports', 'approve_reimburse']), 'dashboard_url' => 'hrd.dashboard', 'is_system_role' => true, 'hrd_can_create' => false],
            ['name' => 'Finance', 'slug' => 'finance', 'permissions' => json_encode(['approve_reimburse', 'view_reports']), 'dashboard_url' => 'finance.dashboard', 'is_system_role' => true, 'hrd_can_create' => false],
            ['name' => 'Supervisor', 'slug' => 'supervisor', 'permissions' => json_encode(['approve_reimburse', 'view_sales']), 'dashboard_url' => 'supervisor.dashboard', 'is_system_role' => true, 'hrd_can_create' => false],
            ['name' => 'Sales', 'slug' => 'sales', 'permissions' => json_encode(['create_visits', 'create_reimburse']), 'dashboard_url' => 'sales.dashboard', 'is_system_role' => true, 'hrd_can_create' => false],
        ];
        DB::table('roles')->insert($roles);

        // ==========================================
        // 2. USERS (1 per role)
        // ==========================================
        $it = User::create([
            'name' => 'Admin IT',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'role' => 'it',
            'is_active' => true,
        ]);

        $hrd = User::create([
            'name' => 'Rina HRD',
            'username' => 'hrd',
            'password' => Hash::make('hrd123'),
            'role' => 'hrd',
            'is_active' => true,
        ]);

        $finance = User::create([
            'name' => 'Dewi Finance',
            'username' => 'finance',
            'password' => Hash::make('finance123'),
            'role' => 'finance',
            'is_active' => true,
        ]);

        $spv = User::create([
            'name' => 'Budi Supervisor',
            'username' => 'supervisor',
            'password' => Hash::make('supervisor123'),
            'role' => 'supervisor',
            'is_active' => true,
        ]);

        $sales = User::create([
            'name' => 'Andi Sales',
            'username' => 'sales',
            'password' => Hash::make('sales123'),
            'role' => 'sales',
            'supervisor_id' => $spv->id,
            'is_active' => true,
        ]);

        // Supervisor-Sales pivot
        DB::table('supervisor_sales')->insert([
            'supervisor_id' => $spv->id,
            'sales_id' => $sales->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ==========================================
        // 3. APPLICATION SETTINGS
        // ==========================================
        ApplicationSetting::set('retention_months', '3');

        // ==========================================
        // 4. FUEL SETTINGS
        // ==========================================
        FuelSetting::create([
            'user_id' => null,
            'role' => null,
            'km_per_liter' => 12.00,
            'fuel_price' => 10000.00,
            'is_active' => true,
        ]);

        // ==========================================
        // 5. DAILY LOGS & VISITS & EXPENSES
        // ==========================================

        // --- HARI INI (Sales sedang aktif) ---
        $logToday = DailyLog::create([
            'user_id' => $sales->id,
            'date' => Carbon::today(),
            'start_time' => Carbon::today()->setTime(8, 0),
            'start_photo' => 'photos/start_today.jpg',
            'start_odo_value' => 45200.00,
            'start_odo_photo' => 'photos/odo_start_today.jpg',
            'lat' => -6.2087634,
            'long' => 106.8455990,
            'daily_plan' => 'Visit 3 customer di area Jakarta Selatan',
            'system_calculated_distance' => null,
        ]);

        // Visit pending hari ini (belum dikunjungi)
        Visit::create([
            'daily_log_id' => $logToday->id,
            'client_name' => 'PT Maju Jaya',
            'time' => Carbon::today()->setTime(10, 0),
            'status' => 'pending',
            'is_planned' => true,
        ]);
        Visit::create([
            'daily_log_id' => $logToday->id,
            'client_name' => 'CV Berkah Mandiri',
            'time' => Carbon::today()->setTime(13, 0),
            'status' => 'pending',
            'is_planned' => true,
        ]);
        Visit::create([
            'daily_log_id' => $logToday->id,
            'client_name' => 'Toko Sentosa (Dadakan)',
            'time' => Carbon::today()->setTime(15, 30),
            'status' => 'pending',
            'is_planned' => false,
        ]);

        // --- KEMARIN (Sales sudah selesai, belum absen keluar - simulate) ---
        $logYesterday = DailyLog::create([
            'user_id' => $sales->id,
            'date' => Carbon::yesterday(),
            'start_time' => Carbon::yesterday()->setTime(7, 45),
            'start_photo' => 'photos/start_yesterday.jpg',
            'start_odo_value' => 45120.00,
            'start_odo_photo' => 'photos/odo_start_yesterday.jpg',
            'lat' => -6.2087634,
            'long' => 106.8455990,
            'daily_plan' => 'Visit customer di area Bekasi',
            'end_time' => Carbon::yesterday()->setTime(17, 30),
            'end_photo' => 'photos/end_yesterday.jpg',
            'end_odo_value' => 45200.00,
            'end_odo_photo' => 'photos/odo_end_yesterday.jpg',
            'end_lat' => -6.2087634,
            'end_long' => 106.8455990,
            'end_type' => 'home',
            'end_notes' => 'Semua customer sudah dikunjungi',
            'system_calculated_distance' => 80.00,
        ]);

        Visit::create([
            'daily_log_id' => $logYesterday->id,
            'client_name' => 'PT Karya Utama',
            'time' => Carbon::yesterday()->setTime(9, 0),
            'status' => 'completed',
            'notes' => 'Order 50 karton produk A',
            'is_planned' => true,
        ]);
        Visit::create([
            'daily_log_id' => $logYesterday->id,
            'client_name' => 'UD Makmur Sejahtera',
            'time' => Carbon::yesterday()->setTime(11, 30),
            'status' => 'completed',
            'notes' => 'Minta sample produk baru',
            'is_planned' => true,
        ]);
        Visit::create([
            'daily_log_id' => $logYesterday->id,
            'client_name' => 'Toko Abadi',
            'time' => Carbon::yesterday()->setTime(14, 0),
            'status' => 'failed',
            'reason' => 'Customer tidak ada di tempat',
            'is_planned' => true,
        ]);

        // --- 3 HARI LALU (Lengkap dengan expenses di berbagai status) ---
        $log3DaysAgo = DailyLog::create([
            'user_id' => $sales->id,
            'date' => Carbon::today()->subDays(3),
            'start_time' => Carbon::today()->subDays(3)->setTime(8, 15),
            'start_photo' => 'photos/start_3d.jpg',
            'start_odo_value' => 45050.00,
            'start_odo_photo' => 'photos/odo_start_3d.jpg',
            'lat' => -6.2087634,
            'long' => 106.8455990,
            'daily_plan' => 'Visit customer di area Tangerang',
            'end_time' => Carbon::today()->subDays(3)->setTime(16, 45),
            'end_photo' => 'photos/end_3d.jpg',
            'end_odo_value' => 45120.00,
            'end_odo_photo' => 'photos/odo_end_3d.jpg',
            'end_lat' => -6.2087634,
            'end_long' => 106.8455990,
            'end_type' => 'home',
            'end_notes' => null,
            'system_calculated_distance' => 70.00,
        ]);

        Visit::create([
            'daily_log_id' => $log3DaysAgo->id,
            'client_name' => 'PT Global Teknik',
            'time' => Carbon::today()->subDays(3)->setTime(9, 30),
            'status' => 'completed',
            'notes' => 'Deal kontrak 6 bulan',
            'is_planned' => true,
        ]);
        Visit::create([
            'daily_log_id' => $log3DaysAgo->id,
            'client_name' => 'CV Indo Perkasa',
            'time' => Carbon::today()->subDays(3)->setTime(13, 0),
            'status' => 'completed',
            'notes' => 'Follow up PO bulan lalu',
            'is_planned' => true,
        ]);

        // --- 5 HARI LALU ---
        $log5DaysAgo = DailyLog::create([
            'user_id' => $sales->id,
            'date' => Carbon::today()->subDays(5),
            'start_time' => Carbon::today()->subDays(5)->setTime(7, 30),
            'start_photo' => 'photos/start_5d.jpg',
            'start_odo_value' => 44950.00,
            'start_odo_photo' => 'photos/odo_start_5d.jpg',
            'lat' => -6.2087634,
            'long' => 106.8455990,
            'daily_plan' => 'Visit customer di area Bogor',
            'end_time' => Carbon::today()->subDays(5)->setTime(17, 0),
            'end_photo' => 'photos/end_5d.jpg',
            'end_odo_value' => 45050.00,
            'end_odo_photo' => 'photos/odo_end_5d.jpg',
            'end_lat' => -6.2087634,
            'end_long' => 106.8455990,
            'end_type' => 'last_store',
            'end_notes' => 'Pulang dari toko terakhir',
            'system_calculated_distance' => 100.00,
        ]);

        Visit::create([
            'daily_log_id' => $log5DaysAgo->id,
            'client_name' => 'PT Sumber Makmur',
            'time' => Carbon::today()->subDays(5)->setTime(10, 0),
            'status' => 'completed',
            'notes' => 'Order besar 100 karton',
            'is_planned' => true,
        ]);
        Visit::create([
            'daily_log_id' => $log5DaysAgo->id,
            'client_name' => 'Toko Jaya Abadi',
            'time' => Carbon::today()->subDays(5)->setTime(14, 30),
            'status' => 'completed',
            'notes' => 'Negosiasi harga untuk Q2',
            'is_planned' => true,
        ]);

        // ==========================================
        // 6. EXPENSES - Simulasi semua status workflow
        // ==========================================

        // --- A. APPROVED (Full workflow selesai) ---
        $expApproved = Expense::create([
            'user_id' => $sales->id,
            'daily_log_id' => $log5DaysAgo->id,
            'date' => Carbon::today()->subDays(5),
            'type' => 'fuel',
            'amount' => 83333.33,
            'note' => 'Bensin Bogor PP',
            'photo_receipt' => 'receipts/fuel_5d.jpg',
            'km_total' => 100.00,
            'is_auto_calculated' => true,
            'deadline_date' => Carbon::today()->subDays(5)->copy()->addDays(7),
            'status' => 'approved',
            'approved_by_spv_at' => Carbon::today()->subDays(4)->setTime(9, 0),
            'approved_by_spv_id' => $spv->id,
            'approved_by_hrd_at' => Carbon::today()->subDays(4)->setTime(11, 0),
            'approved_by_hrd_id' => $hrd->id,
            'approved_by_finance_at' => Carbon::today()->subDays(3)->setTime(10, 0),
            'approved_by_finance_id' => $finance->id,
            'submitted_by' => $sales->id,
            'revision_count' => 0,
        ]);

        ExpenseHistory::create(['expense_id' => $expApproved->id, 'status' => 'pending_spv', 'changed_by' => $sales->id, 'notes' => 'Submit reimburse bensin', 'created_at' => Carbon::today()->subDays(5)->setTime(17, 30)]);
        ExpenseHistory::create(['expense_id' => $expApproved->id, 'status' => 'pending_hrd', 'changed_by' => $spv->id, 'notes' => 'Approved SPV', 'created_at' => Carbon::today()->subDays(4)->setTime(9, 0)]);
        ExpenseHistory::create(['expense_id' => $expApproved->id, 'status' => 'pending_finance', 'changed_by' => $hrd->id, 'notes' => 'Approved HRD', 'created_at' => Carbon::today()->subDays(4)->setTime(11, 0)]);
        ExpenseHistory::create(['expense_id' => $expApproved->id, 'status' => 'approved', 'changed_by' => $finance->id, 'notes' => 'Approved Finance, akan diproses', 'created_at' => Carbon::today()->subDays(3)->setTime(10, 0)]);

        // Toll yang juga approved
        $expTollApproved = Expense::create([
            'user_id' => $sales->id,
            'daily_log_id' => $log5DaysAgo->id,
            'date' => Carbon::today()->subDays(5),
            'type' => 'toll',
            'amount' => 25000.00,
            'note' => 'Tol Bogor - Jakarta',
            'photo_receipt' => 'receipts/toll_5d.jpg',
            'deadline_date' => Carbon::today()->subDays(5)->copy()->addDays(7),
            'status' => 'approved',
            'approved_by_spv_at' => Carbon::today()->subDays(4)->setTime(9, 5),
            'approved_by_spv_id' => $spv->id,
            'approved_by_hrd_at' => Carbon::today()->subDays(4)->setTime(11, 5),
            'approved_by_hrd_id' => $hrd->id,
            'approved_by_finance_at' => Carbon::today()->subDays(3)->setTime(10, 5),
            'approved_by_finance_id' => $finance->id,
            'submitted_by' => $sales->id,
            'revision_count' => 0,
        ]);

        ExpenseHistory::create(['expense_id' => $expTollApproved->id, 'status' => 'pending_spv', 'changed_by' => $sales->id, 'notes' => null, 'created_at' => Carbon::today()->subDays(5)->setTime(17, 35)]);
        ExpenseHistory::create(['expense_id' => $expTollApproved->id, 'status' => 'pending_hrd', 'changed_by' => $spv->id, 'notes' => 'OK', 'created_at' => Carbon::today()->subDays(4)->setTime(9, 5)]);
        ExpenseHistory::create(['expense_id' => $expTollApproved->id, 'status' => 'pending_finance', 'changed_by' => $hrd->id, 'notes' => null, 'created_at' => Carbon::today()->subDays(4)->setTime(11, 5)]);
        ExpenseHistory::create(['expense_id' => $expTollApproved->id, 'status' => 'approved', 'changed_by' => $finance->id, 'notes' => null, 'created_at' => Carbon::today()->subDays(3)->setTime(10, 5)]);

        // --- B. PENDING FINANCE (Sudah approve SPV + HRD) ---
        $expPendingFinance = Expense::create([
            'user_id' => $sales->id,
            'daily_log_id' => $log3DaysAgo->id,
            'date' => Carbon::today()->subDays(3),
            'type' => 'fuel',
            'amount' => 58333.33,
            'note' => 'Bensin Tangerang PP',
            'photo_receipt' => 'receipts/fuel_3d.jpg',
            'km_total' => 70.00,
            'is_auto_calculated' => true,
            'deadline_date' => Carbon::today()->subDays(3)->copy()->addDays(7),
            'status' => 'pending_finance',
            'approved_by_spv_at' => Carbon::today()->subDays(2)->setTime(8, 30),
            'approved_by_spv_id' => $spv->id,
            'approved_by_hrd_at' => Carbon::today()->subDays(2)->setTime(10, 0),
            'approved_by_hrd_id' => $hrd->id,
            'submitted_by' => $sales->id,
            'revision_count' => 0,
        ]);

        ExpenseHistory::create(['expense_id' => $expPendingFinance->id, 'status' => 'pending_spv', 'changed_by' => $sales->id, 'notes' => null, 'created_at' => Carbon::today()->subDays(3)->setTime(17, 0)]);
        ExpenseHistory::create(['expense_id' => $expPendingFinance->id, 'status' => 'pending_hrd', 'changed_by' => $spv->id, 'notes' => 'Approved', 'created_at' => Carbon::today()->subDays(2)->setTime(8, 30)]);
        ExpenseHistory::create(['expense_id' => $expPendingFinance->id, 'status' => 'pending_finance', 'changed_by' => $hrd->id, 'notes' => 'Lengkap', 'created_at' => Carbon::today()->subDays(2)->setTime(10, 0)]);

        // Parking yang pending finance
        $expParkingPending = Expense::create([
            'user_id' => $sales->id,
            'daily_log_id' => $log3DaysAgo->id,
            'date' => Carbon::today()->subDays(3),
            'type' => 'parking',
            'amount' => 15000.00,
            'note' => 'Parkir di mal Tangerang',
            'photo_receipt' => 'receipts/parking_3d.jpg',
            'deadline_date' => Carbon::today()->subDays(3)->copy()->addDays(7),
            'status' => 'pending_finance',
            'approved_by_spv_at' => Carbon::today()->subDays(2)->setTime(8, 35),
            'approved_by_spv_id' => $spv->id,
            'approved_by_hrd_at' => Carbon::today()->subDays(2)->setTime(10, 5),
            'approved_by_hrd_id' => $hrd->id,
            'submitted_by' => $sales->id,
            'revision_count' => 0,
        ]);

        ExpenseHistory::create(['expense_id' => $expParkingPending->id, 'status' => 'pending_spv', 'changed_by' => $sales->id, 'notes' => null, 'created_at' => Carbon::today()->subDays(3)->setTime(17, 5)]);
        ExpenseHistory::create(['expense_id' => $expParkingPending->id, 'status' => 'pending_hrd', 'changed_by' => $spv->id, 'notes' => null, 'created_at' => Carbon::today()->subDays(2)->setTime(8, 35)]);
        ExpenseHistory::create(['expense_id' => $expParkingPending->id, 'status' => 'pending_finance', 'changed_by' => $hrd->id, 'notes' => null, 'created_at' => Carbon::today()->subDays(2)->setTime(10, 5)]);

        // --- C. PENDING HRD (Sudah approve SPV) ---
        $expPendingHrd = Expense::create([
            'user_id' => $sales->id,
            'daily_log_id' => $logYesterday->id,
            'date' => Carbon::yesterday(),
            'type' => 'fuel',
            'amount' => 66666.67,
            'note' => 'Bensin Bekasi PP',
            'photo_receipt' => 'receipts/fuel_yesterday.jpg',
            'km_total' => 80.00,
            'is_auto_calculated' => true,
            'deadline_date' => Carbon::yesterday()->copy()->addDays(7),
            'status' => 'pending_hrd',
            'approved_by_spv_at' => Carbon::today()->setTime(8, 0),
            'approved_by_spv_id' => $spv->id,
            'submitted_by' => $sales->id,
            'revision_count' => 0,
        ]);

        ExpenseHistory::create(['expense_id' => $expPendingHrd->id, 'status' => 'pending_spv', 'changed_by' => $sales->id, 'notes' => null, 'created_at' => Carbon::yesterday()->setTime(17, 45)]);
        ExpenseHistory::create(['expense_id' => $expPendingHrd->id, 'status' => 'pending_hrd', 'changed_by' => $spv->id, 'notes' => 'KM sesuai rute', 'created_at' => Carbon::today()->setTime(8, 0)]);

        // --- D. PENDING SPV (Baru submit, menunggu supervisor) ---
        $expPendingSpv = Expense::create([
            'user_id' => $sales->id,
            'daily_log_id' => $logYesterday->id,
            'date' => Carbon::yesterday(),
            'type' => 'toll',
            'amount' => 35000.00,
            'note' => 'Tol Bekasi - Jakarta via Cikampek',
            'photo_receipt' => 'receipts/toll_yesterday.jpg',
            'deadline_date' => Carbon::yesterday()->copy()->addDays(7),
            'status' => 'pending_spv',
            'submitted_by' => $sales->id,
            'revision_count' => 0,
        ]);

        ExpenseHistory::create(['expense_id' => $expPendingSpv->id, 'status' => 'pending_spv', 'changed_by' => $sales->id, 'notes' => null, 'created_at' => Carbon::yesterday()->setTime(17, 50)]);

        // Gas manual yang juga pending SPV
        $expGasPending = Expense::create([
            'user_id' => $sales->id,
            'daily_log_id' => $logYesterday->id,
            'date' => Carbon::yesterday(),
            'type' => 'gas',
            'amount' => 50000.00,
            'note' => 'Isi gas kendaraan',
            'photo_receipt' => 'receipts/gas_yesterday.jpg',
            'deadline_date' => Carbon::yesterday()->copy()->addDays(7),
            'status' => 'pending_spv',
            'submitted_by' => $sales->id,
            'revision_count' => 0,
        ]);

        ExpenseHistory::create(['expense_id' => $expGasPending->id, 'status' => 'pending_spv', 'changed_by' => $sales->id, 'notes' => null, 'created_at' => Carbon::yesterday()->setTime(17, 55)]);

        // --- E. NEEDS REVISION SALES (SPV minta revisi) ---
        $expRevision = Expense::create([
            'user_id' => $sales->id,
            'daily_log_id' => $log3DaysAgo->id,
            'date' => Carbon::today()->subDays(3),
            'type' => 'other',
            'amount' => 200000.00,
            'note' => 'Makan siang dengan client',
            'photo_receipt' => 'receipts/meal_3d.jpg',
            'deadline_date' => Carbon::today()->subDays(3)->copy()->addDays(7),
            'status' => 'needs_revision_sales',
            'rejection_note' => 'Foto struk tidak jelas, tolong upload ulang dengan resolusi lebih tinggi',
            'rejection_type' => 'revisi',
            'approved_by_spv_at' => null,
            'submitted_by' => $sales->id,
            'revision_count' => 1,
            'revised_at' => Carbon::today()->subDays(2)->setTime(14, 0),
        ]);

        ExpenseHistory::create(['expense_id' => $expRevision->id, 'status' => 'pending_spv', 'changed_by' => $sales->id, 'notes' => null, 'created_at' => Carbon::today()->subDays(3)->setTime(17, 10)]);
        ExpenseHistory::create(['expense_id' => $expRevision->id, 'status' => 'needs_revision_sales', 'changed_by' => $spv->id, 'notes' => 'Foto struk tidak jelas, tolong upload ulang', 'created_at' => Carbon::today()->subDays(2)->setTime(14, 0)]);

        // --- F. REJECTED PERMANENT ---
        $expRejected = Expense::create([
            'user_id' => $sales->id,
            'daily_log_id' => $log3DaysAgo->id,
            'date' => Carbon::today()->subDays(3),
            'type' => 'other',
            'amount' => 500000.00,
            'note' => 'Entertainment client',
            'photo_receipt' => 'receipts/entertainment_3d.jpg',
            'deadline_date' => Carbon::today()->subDays(3)->copy()->addDays(7),
            'status' => 'rejected_permanent',
            'rejection_note' => 'Kategori expense tidak sesuai kebijakan perusahaan. Entertainment tidak termasuk reimbursable.',
            'rejection_type' => 'permanent',
            'approved_by_spv_at' => null,
            'submitted_by' => $sales->id,
            'revision_count' => 0,
        ]);

        ExpenseHistory::create(['expense_id' => $expRejected->id, 'status' => 'pending_spv', 'changed_by' => $sales->id, 'notes' => null, 'created_at' => Carbon::today()->subDays(3)->setTime(17, 15)]);
        ExpenseHistory::create(['expense_id' => $expRejected->id, 'status' => 'rejected_permanent', 'changed_by' => $spv->id, 'notes' => 'Kategori tidak reimbursable sesuai kebijakan', 'created_at' => Carbon::today()->subDays(2)->setTime(9, 0)]);

        // ==========================================
        // 7. DAILY LOG UNTUK SUPERVISOR (absensi SPV)
        // ==========================================
        DailyLog::create([
            'user_id' => $spv->id,
            'date' => Carbon::today(),
            'start_time' => Carbon::today()->setTime(8, 30),
            'start_photo' => 'photos/spv_start_today.jpg',
            'start_odo_value' => 32000.00,
            'start_odo_photo' => 'photos/spv_odo_start_today.jpg',
            'lat' => -6.2087634,
            'long' => 106.8455990,
            'daily_plan' => 'Monitoring tim sales, approve reimburse',
        ]);

        DailyLog::create([
            'user_id' => $spv->id,
            'date' => Carbon::yesterday(),
            'start_time' => Carbon::yesterday()->setTime(8, 0),
            'start_photo' => 'photos/spv_start_yesterday.jpg',
            'start_odo_value' => 31950.00,
            'start_odo_photo' => 'photos/spv_odo_start_yesterday.jpg',
            'lat' => -6.2087634,
            'long' => 106.8455990,
            'daily_plan' => 'Review expense tim, visit beberapa customer',
            'end_time' => Carbon::yesterday()->setTime(17, 0),
            'end_photo' => 'photos/spv_end_yesterday.jpg',
            'end_odo_value' => 32000.00,
            'end_odo_photo' => 'photos/spv_odo_end_yesterday.jpg',
            'end_lat' => -6.2087634,
            'end_long' => 106.8455990,
            'end_type' => 'home',
            'system_calculated_distance' => 50.00,
        ]);

        // ==========================================
        // OUTPUT INFO
        // ==========================================
        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('   SEEDER BERHASIL DIJALANKAN');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info('--- AKUN LOGIN ---');
        $this->command->info('IT         | username: admin      | password: admin123');
        $this->command->info('HRD        | username: hrd        | password: hrd123');
        $this->command->info('Finance    | username: finance    | password: finance123');
        $this->command->info('Supervisor | username: supervisor | password: supervisor123');
        $this->command->info('Sales      | username: sales      | password: sales123');
        $this->command->info('');
        $this->command->info('--- DATA DUMMY ---');
        $this->command->info('Daily Logs  : 5 (Sales) + 2 (Supervisor)');
        $this->command->info('Visits      : 9 (3 pending, 4 completed, 1 failed, 1 dadakan)');
        $this->command->info('Expenses    : 9');
        $this->command->info('  - 2 Approved (fuel + toll)');
        $this->command->info('  - 2 Pending Finance (fuel + parking)');
        $this->command->info('  - 1 Pending HRD (fuel)');
        $this->command->info('  - 2 Pending SPV (toll + gas)');
        $this->command->info('  - 1 Needs Revision (other - foto tidak jelas)');
        $this->command->info('  - 1 Rejected Permanent (other - tidak reimbursable)');
        $this->command->info('');
        $this->command->info('Fuel Setting: General (12 km/l, Rp 10.000/liter)');
        $this->command->info('========================================');
    }
}
