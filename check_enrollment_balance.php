<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== All Enrollments ===\n";
$enrollments = \App\Models\Enrollment::withoutGlobalScopes()->with('student.user')->get();

foreach ($enrollments as $enrollment) {
    echo "\n--- Enrollment #{$enrollment->id} ---\n";
    echo "Code: " . $enrollment->enrollment_code . "\n";
    echo "Student: " . $enrollment->student->user->name . "\n";
    echo "Total Amount: " . $enrollment->total_amount . "\n";
    echo "Paid Amount: " . $enrollment->paid_amount . "\n";
    echo "Discount: " . $enrollment->discount . "\n";
    echo "Remaining: " . $enrollment->getRemainingBalance() . "\n";
    echo "Formula: {$enrollment->total_amount} - {$enrollment->paid_amount} - {$enrollment->discount} = " . ($enrollment->total_amount - $enrollment->paid_amount - $enrollment->discount) . "\n";
}

if ($enrollments->isEmpty()) {
    echo "No enrollments found\n";
}
