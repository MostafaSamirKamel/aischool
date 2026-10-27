<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Enrollment;

$e = Enrollment::where('paid_amount', '>', 0)->whereNull('vault_id')->first();
if (!$e) {
    echo "No matching enrollment found\n";
    exit(0);
}

echo "Found legacy enrollment ID: {$e->id}, code: {$e->enrollment_code}, paid: {$e->paid_amount}\n";

try {
    $e->notes = 'Test update notes';
    $e->save();
    echo "SAVE SUCCESSFUL\n";
} catch (\Exception $ex) {
    echo "CAUGHT EXCEPTION: " . $ex->getMessage() . "\n";
    if (method_exists($ex, 'errors')) {
        print_r($ex->errors());
    }
}
