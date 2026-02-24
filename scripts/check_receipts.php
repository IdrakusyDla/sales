<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Expense;
use Illuminate\Support\Facades\Storage;

$expense = Expense::whereNotNull('photo_receipt')->first();
if (!$expense) {
    echo "No expense with photo_receipt found.\n";
    exit;
}
$path = $expense->photo_receipt;
$publicPath = public_path('storage/' . $path);
$storageExists = Storage::disk('public')->exists($path);

echo "Expense ID: {$expense->id}\n";
echo "photo_receipt: {$path}\n";
echo "public_path: {$publicPath}\n";
echo "Storage::disk('public')->exists: " . ($storageExists ? 'yes' : 'no') . "\n";

if ($storageExists) {
    echo "File size: " . Storage::disk('public')->size($path) . " bytes\n";
}

echo "Asset URL: " . asset('storage/' . $path) . "\n";
