<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Expense;

$username = $argv[1] ?? 'budi';

$user = User::where('username', $username)->orWhere('name', $username)->first();
if (!$user) {
    echo "User not found: {$username}\n";
    exit(1);
}

$expenses = Expense::where('user_id', $user->id)->orderBy('date', 'desc')->orderBy('created_at', 'desc')->limit(20)->get();

$result = [
    'user' => ['id' => $user->id, 'name' => $user->name, 'username' => $user->username, 'role' => $user->role, 'supervisor_id' => $user->supervisor_id],
    'expenses' => $expenses->map(function($e){
        return [
            'id' => $e->id,
            'date' => $e->date?->toDateString(),
            'type' => $e->type,
            'amount' => (string)$e->amount,
            'status' => $e->status,
            'approved_by_spv_id' => $e->approved_by_spv_id,
            'approved_by_spv_at' => $e->approved_by_spv_at?->toDateTimeString(),
            'submitted_by' => $e->submitted_by,
            'daily_log_id' => $e->daily_log_id,
            'created_at' => $e->created_at?->toDateTimeString(),
        ];
    })->toArray(),
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
