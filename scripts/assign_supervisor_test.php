<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$budi = User::where('username', 'budi')->first();
$supervisors = User::where('role', 'supervisor')->get();

echo "Budi before:\n";
echo json_encode(['id'=>$budi->id,'name'=>$budi->name,'supervisor_id'=>$budi->supervisor_id,'supervisors'=> $budi->supervisors->pluck('id')], JSON_PRETTY_PRINT), "\n\n";

if ($supervisors->isEmpty()) {
    echo "No supervisors available to assign.\n";
    exit(0);
}

$spv = $supervisors->first();

// Attach if not exists
if (!$budi->supervisors()->where('supervisor_sales.supervisor_id', $spv->id)->exists()) {
    $budi->supervisors()->attach($spv->id);
}

if (is_null($budi->supervisor_id)) {
    $budi->supervisor_id = $spv->id;
    $budi->save();
}

$budi->refresh();

echo "Budi after:\n";
echo json_encode(['id'=>$budi->id,'name'=>$budi->name,'supervisor_id'=>$budi->supervisor_id,'supervisors'=> $budi->supervisors->pluck('id')], JSON_PRETTY_PRINT), "\n";
