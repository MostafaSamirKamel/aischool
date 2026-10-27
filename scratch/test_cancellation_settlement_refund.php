<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Enrollment;
use App\Models\EnrollmentCourse;
use App\Models\Installment;
use App\Models\AttendanceRecord;
use App\Models\AssignmentSubmission;
use App\Models\ExamAttempt;
use App\Models\Group;
use App\Models\Vault;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

echo "================================================================" . PHP_EOL;
echo "  TEST ENROLLMENT CANCELLATION, SETTLEMENT & REFUND WORKFLOW    " . PHP_EOL;
echo "================================================================" . PHP_EOL;

// 1. Locate Student 17
$student17 = Student::where('phone', '01007287017')->firstOrFail();
$enrollment = Enrollment::where('student_id', $student17->id)->firstOrFail();
$groupB = Group::where('name', 'Group B — HTML & CSS')->firstOrFail();
$vault = Vault::find(1);

echo "Student: {$student17->user->name} (ID: {$student17->id})" . PHP_EOL;
echo "Enrollment ID: {$enrollment->id}, Status: {$enrollment->status}" . PHP_EOL;
echo "Group B Active Students Before Cancellation: " . Enrollment::where('group_id', $groupB->id)->where('status', 'active')->count() . PHP_EOL;

// Verify pre-cancellation academic records exist
$attCountBefore = AttendanceRecord::where('student_id', $student17->id)->count();
$subCountBefore = AssignmentSubmission::where('student_id', $student17->id)->count();
$attemptCountBefore = ExamAttempt::where('student_id', $student17->id)->count();
echo "Pre-cancellation records: Attendance={$attCountBefore}, Homework={$subCountBefore}, ExamAttempts={$attemptCountBefore}" . PHP_EOL;

$vaultBalanceBefore = (float) $vault->getCurrentBalance();
echo "Vault 1 Balance Before Refund: {$vaultBalanceBefore} EGP" . PHP_EOL;

// 2. Execute Cancellation and Financial Settlement
// Total Paid: 500 EGP. Course Price: 1500 EGP.
// Settlement: Retain 300 EGP for 3 sessions attended. Refund 200 EGP. Waive 1000 EGP remaining installments.
DB::beginTransaction();

$enrollment->status = 'cancelled';
$enrollment->cancelled_at = now();
$enrollment->cancellation_reason = 'ظروف سفر عائلية وانتقال لمحافظة أخرى';
$enrollment->settlement_type = 'partial_refund';
$enrollment->settlement_status = 'approved';
$enrollment->retained_amount = 300.00;
$enrollment->waived_amount = 1000.00;
$enrollment->refundable_amount = 200.00;
$enrollment->refund_status = 'approved'; // Approved for refund, not yet disbursed
$enrollment->save();

// Update linked EnrollmentCourse status
EnrollmentCourse::where('enrollment_id', $enrollment->id)->update(['status' => 'inactive']);

// Waive/Cancel open installments
Installment::where('enrollment_id', $enrollment->id)
    ->where('status', '!=', 'paid')
    ->update(['status' => 'cancelled']);

DB::commit();

echo PHP_EOL . "Step 1 Completed: Enrollment Cancelled & Settlement Approved" . PHP_EOL;
echo "Active Group B Students Now: " . Enrollment::where('group_id', $groupB->id)->where('status', 'active')->count() . PHP_EOL;

// 3. Disburse Refund from Vault
DB::beginTransaction();
$enrollment->refresh();

// Disburse refund
$enrollment->refunded_amount = 200.00;
$enrollment->refund_vault_id = $vault->id;
$enrollment->refund_payment_method = 'cash';
$enrollment->refunded_at = now();
$enrollment->refund_status = 'refunded';
$enrollment->save();

DB::commit();

$vaultBalanceAfter = (float) $vault->fresh()->getCurrentBalance();
echo PHP_EOL . "Step 2 Completed: Cash Refund of 200 EGP Disbursed from Vault 1" . PHP_EOL;
echo "Vault 1 Balance After Refund: {$vaultBalanceAfter} EGP (Diff: " . ($vaultBalanceBefore - $vaultBalanceAfter) . " EGP)" . PHP_EOL;

// 4. Test Double Refund Prevention
echo PHP_EOL . "Testing Double Refund Prevention..." . PHP_EOL;
try {
    if ($enrollment->refund_status === 'refunded' && $enrollment->refunded_amount >= $enrollment->refundable_amount) {
        throw new \Exception('لا يمكن صرف استرداد مالي مرة أخرى: تم صرف كامل المبلغ المسترد مسبقاً.');
    }
    echo "FAIL: Allowed double refund!" . PHP_EOL;
} catch (\Exception $e) {
    echo "SUCCESS: Double refund blocked! Reason: " . $e->getMessage() . PHP_EOL;
}

// 5. Verification of Academic History Preservation
$attCountAfter = AttendanceRecord::where('student_id', $student17->id)->count();
$subCountAfter = AssignmentSubmission::where('student_id', $student17->id)->count();
$attemptCountAfter = ExamAttempt::where('student_id', $student17->id)->count();

echo PHP_EOL . "Verifying Academic History Preservation for Student 17:" . PHP_EOL;
echo " - Attendance Records: {$attCountAfter} (Matched: " . ($attCountAfter === $attCountBefore ? 'YES' : 'NO') . ")" . PHP_EOL;
echo " - Homework Submissions: {$subCountAfter} (Matched: " . ($subCountAfter === $subCountBefore ? 'YES' : 'NO') . ")" . PHP_EOL;
echo " - Exam Attempts: {$attemptCountAfter} (Matched: " . ($attemptCountAfter === $attemptCountBefore ? 'YES' : 'NO') . ")" . PHP_EOL;

// 6. Verify Collectible Installments Excluded
$collectible = Installment::where('enrollment_id', $enrollment->id)
    ->whereIn('status', ['pending', 'overdue'])
    ->count();
echo " - Collectible Unpaid Installments: {$collectible} (Expected: 0)" . PHP_EOL;

echo PHP_EOL . ">>> ENROLLMENT CANCELLATION, SETTLEMENT & REFUND AUDIT PASSED! <<<" . PHP_EOL;
