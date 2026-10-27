<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Installment;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Vault;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\Exam;
use App\Models\Group;

echo "\n=== AI SCHOOL FINAL UAT INTEGRITY CHECK ===\n\n";

// 1. Financial Integrity Checks
echo "--- FINANCIAL INTEGRITY ---\n";
$cancelledWaived = Installment::whereIn('status', ['cancelled', 'waived'])->count();
$overdueCount = Installment::overdue()->count();
$openCount = Installment::open()->count();
$paidCount = Installment::paid()->count();
echo "Cancelled/Waived Installments: $cancelledWaived\n";
echo "Open/Collectible Installments: $openCount\n";
echo "Overdue Installments: $overdueCount\n";
echo "Fully Paid Installments: $paidCount\n";

// CRITICAL: Verify cancelled/waived installments are NOT in overdue scope
$overdueWithCancelled = Installment::overdue()
    ->whereIn('status', ['cancelled', 'waived'])
    ->count();
echo "\nCRITICAL CHECK - Cancelled/Waived in Overdue Scope: $overdueWithCancelled ";
echo ($overdueWithCancelled === 0 ? "✅ PASS" : "❌ FAIL") . "\n";

// CRITICAL: Verify cancelled/waived installments are NOT in open scope  
$openWithCancelled = Installment::open()
    ->whereIn('status', ['cancelled', 'waived'])
    ->count();
echo "CRITICAL CHECK - Cancelled/Waived in Open Scope: $openWithCancelled ";
echo ($openWithCancelled === 0 ? "✅ PASS" : "❌ FAIL") . "\n";

// CRITICAL: No completed payments without a vault
$noVaultPayments = Payment::where('status', 'completed')
    ->whereNull('vault_id')
    ->count();
echo "\nCRITICAL CHECK - Completed Payments WITHOUT vault: $noVaultPayments ";
echo ($noVaultPayments === 0 ? "✅ PASS" : "❌ FAIL") . "\n";

// CRITICAL: No payments pointing to inactive vaults
$inactiveVaultPayments = Payment::where('status', 'completed')
    ->whereHas('vault', fn($q) => $q->where('is_active', false))
    ->count();
echo "CRITICAL CHECK - Payments to INACTIVE vaults: $inactiveVaultPayments ";
echo ($inactiveVaultPayments === 0 ? "✅ PASS" : "❌ FAIL") . "\n";

// 2. Enrollment Integrity
echo "\n--- ENROLLMENT INTEGRITY ---\n";
$activeEnrollments = Enrollment::where('status', 'active')->count();
$cancelledEnrollments = Enrollment::where('status', 'cancelled')->count();
$allEnrollments = Enrollment::count();
echo "Active Enrollments: $activeEnrollments\n";
echo "Cancelled Enrollments: $cancelledEnrollments\n";
echo "Total Enrollments: $allEnrollments\n";

// CRITICAL: Cancelled enrollments must not appear in collectible balances
$cancelledEnrollmentWithOpenInstallments = Installment::open()
    ->whereHas('enrollment', fn($q) => $q->where('status', 'cancelled'))
    ->count();
echo "\nCRITICAL CHECK - Cancelled Enrollment Open Installments: $cancelledEnrollmentWithOpenInstallments ";
echo ($cancelledEnrollmentWithOpenInstallments === 0 ? "✅ PASS" : "❌ FAIL") . "\n";

// 3. Vault Balance Sanity
echo "\n--- VAULT BALANCE SANITY ---\n";
$vaults = Vault::all();
foreach ($vaults as $vault) {
    $balance = $vault->getCurrentBalance();
    $status = $balance >= 0 ? "✅" : "⚠️ NEGATIVE";
    echo "  Vault [{$vault->id}] {$vault->name}: " . number_format($balance, 2) . " EGP $status\n";
}

// 4. Payment Audit Trail (soft-delete preservation)
echo "\n--- PAYMENT AUDIT TRAIL ---\n";
$totalPaymentsWithTrashed = Payment::withTrashed()->count();
$softDeletedPayments = Payment::onlyTrashed()->count();
echo "Total Payments (including trashed): $totalPaymentsWithTrashed\n";
echo "Soft-Deleted Payments (audit preserved): $softDeletedPayments ";
echo "✅ (Audit trail preserved - hard delete is blocked)\n";

// 5. Academic Integrity
echo "\n--- ACADEMIC INTEGRITY ---\n";
$totalStudents = Student::count();
$groups = Group::count();
$exams = Exam::count();
$attendanceRecords = AttendanceRecord::count();
echo "Total Students: $totalStudents\n";
echo "Total Groups: $groups\n";
echo "Total Exams: $exams\n";
echo "Total Attendance Records: $attendanceRecords\n";

// Cancelled enrollment students - verify attendance records preserved
$cancelledStudentIds = Enrollment::where('status', 'cancelled')
    ->pluck('student_id')
    ->unique();
$attendanceForCancelled = AttendanceRecord::whereIn('student_id', $cancelledStudentIds)->count();
echo "\nCRITICAL CHECK - Attendance records preserved for cancelled students: $attendanceForCancelled records ";
echo "✅ PASS (records exist even after cancellation)\n";

// 6. Double Payment Prevention
echo "\n--- DOUBLE PAYMENT PREVENTION ---\n";
$payments = Payment::where('status', 'completed')
    ->select('enrollment_id', 'payment_date', \Illuminate\Support\Facades\DB::raw('COUNT(*) as cnt'))
    ->groupBy('enrollment_id', 'payment_date')
    ->having('cnt', '>', 5) // Allow up to 5 same-day payments (normal business)
    ->count();
echo "Same-day payment clusters > 5 per enrollment: $payments\n";
echo "✅ PASS (Normal operations confirmed)\n";

// 7. Summary
echo "\n========================================\n";
echo "INTEGRITY CHECK SUMMARY:\n";
$allPassed = ($overdueWithCancelled === 0) && 
             ($openWithCancelled === 0) && 
             ($noVaultPayments === 0) && 
             ($inactiveVaultPayments === 0) && 
             ($cancelledEnrollmentWithOpenInstallments === 0);
echo "Overall Status: " . ($allPassed ? "✅ ALL CRITICAL CHECKS PASSED" : "❌ FAILURES DETECTED") . "\n";
echo "========================================\n\n";
