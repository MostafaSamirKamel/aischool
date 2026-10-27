<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;

$st = Student::where('tenant_id', 1)
    ->where('status', 'active')
    ->whereHas('enrollments.installments', function($q) {
        $q->where('status', '!=', 'cancelled')->whereColumn('paid_amount', '<', 'amount');
    })
    ->with(['user', 'enrollments.installments'])
    ->first();

if ($st) {
    echo "Found Student: {$st->user->name} ({$st->student_code}) ID: {$st->id}\n";
    foreach ($st->enrollments as $enr) {
        echo " Enrollment: {$enr->enrollment_code} (Remaining: {$enr->getRemainingBalance()})\n";
        foreach ($enr->installments as $inst) {
            echo "   Inst #{$inst->installment_number}: Amount={$inst->amount}, Paid={$inst->paid_amount}, Due=" . ($inst->due_date ? $inst->due_date->format('Y-m-d') : '-') . ", Status={$inst->status}\n";
        }
    }
} else {
    echo "No student found with open installments.\n";
}
