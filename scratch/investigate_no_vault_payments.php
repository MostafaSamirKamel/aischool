<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Payment;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;

echo "=== INVESTIGATING: Completed Payments WITHOUT Vault ===\n\n";

$payments = Payment::where('status', 'completed')
    ->whereNull('vault_id')
    ->with(['enrollment.student'])
    ->orderBy('id')
    ->get();

echo "Total: " . $payments->count() . " payments\n\n";

foreach ($payments as $p) {
    $studentName = $p->enrollment?->student?->name ?? 'Unknown';
    $enrollmentId = $p->enrollment_id ?? 'NULL';
    echo "Payment ID: {$p->id} | Code: {$p->payment_code} | Amount: {$p->amount} EGP | Date: {$p->payment_date} | Enrollment: {$enrollmentId} | Student: {$studentName} | Method: {$p->payment_method} | Created: {$p->created_at}\n";
}

echo "\n--- Grouping by Enrollment ---\n";
$byEnrollment = $payments->groupBy('enrollment_id');
foreach ($byEnrollment as $enrollmentId => $grouped) {
    $total = $grouped->sum('amount');
    echo "Enrollment #{$enrollmentId}: " . $grouped->count() . " payments totaling {$total} EGP\n";
}

// Check if these are old/orphaned records from before vault enforcement
echo "\n--- Checking Created At dates ---\n";
$earliest = $payments->sortBy('created_at')->first();
$latest = $payments->sortByDesc('created_at')->first();
echo "Earliest no-vault payment: {$earliest?->created_at}\n";
echo "Latest no-vault payment: {$latest?->created_at}\n";

// Check if these payments have a corresponding vault via enrollment
echo "\n--- Checking related enrollment vault_id ---\n";
foreach ($payments->take(5) as $p) {
    $enrollment = Enrollment::find($p->enrollment_id);
    $enrollmentVaultId = $enrollment?->vault_id;
    echo "Payment {$p->id} → Enrollment vault_id: " . ($enrollmentVaultId ?? 'NULL') . "\n";
}
