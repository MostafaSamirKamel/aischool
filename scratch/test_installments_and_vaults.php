<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Installment;
use App\Models\Payment;
use App\Models\Vault;
use Illuminate\Support\Facades\DB;

echo "================================================================" . PHP_EOL;
echo "  TEST STUDENT PAYMENTS, INSTALLMENTS & VAULT RECONCILIATION     " . PHP_EOL;
echo "================================================================" . PHP_EOL;

$vault = Vault::find(1);
$initialVaultBalance = (float) $vault->getCurrentBalance();
echo "Initial Vault 1 Balance: {$initialVaultBalance} EGP" . PHP_EOL;

// 1. Full Installment Payment Test
// Student 2 (يوسف أحمد محمود - 01007287002)
$student2 = Student::where('phone', '01007287002')->firstOrFail();
$enrollment2 = Enrollment::where('student_id', $student2->id)->firstOrFail();
$openInst2 = Installment::where('enrollment_id', $enrollment2->id)
    ->where('status', '!=', 'paid')
    ->first();

if ($openInst2) {
    echo PHP_EOL . "Test 1: Full Payment of Installment ID {$openInst2->id} (Amount: {$openInst2->amount} EGP)..." . PHP_EOL;

    DB::beginTransaction();

    $payment1 = Payment::create([
        'tenant_id' => 1,
        'enrollment_id' => $enrollment2->id,
        'student_id' => $student2->id,
        'installment_id' => $openInst2->id,
        'amount' => (float) $openInst2->amount,
        'payment_date' => now()->toDateString(),
        'payment_method' => 'cash',
        'vault_id' => $vault->id,
        'receipt_number' => 'REC-INST-' . uniqid(),
        'status' => 'completed',
        'notes' => 'سداد القسط بالكامل نقداً بالخزينة',
    ]);

    $openInst2->paid_amount = (float) $openInst2->amount;
    $openInst2->paid_date = now()->toDateString();
    $openInst2->status = 'paid';
    $openInst2->save();

    $enrollment2->paid_amount = (float) $enrollment2->paid_amount + (float) $openInst2->amount;
    $enrollment2->save();

    DB::commit();

    $vaultBal1 = (float) $vault->fresh()->getCurrentBalance();
    echo " - Payment Recorded: ID {$payment1->id}, Receipt: {$payment1->receipt_number}" . PHP_EOL;
    echo " - Installment Status: {$openInst2->status}, Paid Amount: {$openInst2->paid_amount} EGP" . PHP_EOL;
    echo " - Vault Balance After Payment 1: {$vaultBal1} EGP (Diff: +" . ($vaultBal1 - $initialVaultBalance) . " EGP)" . PHP_EOL;
} else {
    echo PHP_EOL . "Test 1: Installment for Student 2 is already paid (Status: paid)" . PHP_EOL;
    $vaultBal1 = $initialVaultBalance;
}

// 2. Partial Installment Payment Test
// Student 3 (عمر خالد عبد الرحمن - 01007287003)
$student3 = Student::where('phone', '01007287003')->firstOrFail();
$enrollment3 = Enrollment::where('student_id', $student3->id)->firstOrFail();
$openInst3 = Installment::where('enrollment_id', $enrollment3->id)
    ->where('status', '!=', 'paid')
    ->where('status', '!=', 'partial')
    ->first();

if ($openInst3) {
    $partialAmount = 250.00; // Half of 500
    echo PHP_EOL . "Test 2: Partial Payment of Installment ID {$openInst3->id} (Paying: {$partialAmount} of {$openInst3->amount} EGP)..." . PHP_EOL;

    DB::beginTransaction();

    $payment2 = Payment::create([
        'tenant_id' => 1,
        'enrollment_id' => $enrollment3->id,
        'student_id' => $student3->id,
        'installment_id' => $openInst3->id,
        'amount' => $partialAmount,
        'payment_date' => now()->toDateString(),
        'payment_method' => 'cash',
        'vault_id' => $vault->id,
        'receipt_number' => 'REC-PART-' . uniqid(),
        'status' => 'completed',
        'notes' => 'سداد جزء من القسط نقداً',
    ]);

    $openInst3->paid_amount = (float) $openInst3->paid_amount + $partialAmount;
    $openInst3->status = 'partial';
    $openInst3->save();

    $enrollment3->paid_amount = (float) $enrollment3->paid_amount + $partialAmount;
    $enrollment3->save();

    DB::commit();

    $vaultBal2 = (float) $vault->fresh()->getCurrentBalance();
    echo " - Payment Recorded: ID {$payment2->id}, Receipt: {$payment2->receipt_number}" . PHP_EOL;
    echo " - Installment Status: {$openInst3->status}, Remaining: " . ($openInst3->amount - $openInst3->paid_amount) . " EGP" . PHP_EOL;
    echo " - Vault Balance After Payment 2: {$vaultBal2} EGP (Diff: +" . ($vaultBal2 - $vaultBal1) . " EGP)" . PHP_EOL;
} else {
    echo PHP_EOL . "Test 2: Partial installment for Student 3 is already executed (Status: partial)" . PHP_EOL;
}

// 3. Vault Complete Mathematical Reconciliation
echo PHP_EOL . "=== VAULT 1 COMPLETE FINANCIAL RECONCILIATION ===" . PHP_EOL;
$openingBal = (float) $vault->opening_balance;
$paymentsTotal = (float) $vault->payments()->where('status', 'completed')->sum('amount');
$today = now()->toDateString();
$upfrontsTotal = 0.0;
foreach ($vault->enrollments as $enr) {
    if ($enr->payment_plan === 'installments' && $enr->installments()->exists()) {
        $upfrontsTotal += $enr->getCalculatedUpfrontAmount();
    } else {
        $upfrontsTotal += (float) $enr->paid_amount;
    }
}
$expensesTotal = (float) $vault->expenses()->whereIn('status', ['paid', 'completed'])->sum('amount');
$salariesTotal = (float) $vault->salaryPayments()->where('status', 'paid')->sum('amount');
$transfersIn = (float) $vault->transfersTo()->where('status', 'completed')->sum('amount');
$transfersOut = (float) $vault->transfersFrom()->where('status', 'completed')->sum('amount');
$refundsTotal = (float) $vault->refundedEnrollments()->where('refund_status', 'refunded')->sum('refunded_amount');

$standaloneRevenues = (float) $vault->revenues()->whereNull('payment_id')->sum('amount');
$studentPayments = \Schema::hasTable('student_payments') ? (float) $vault->studentPayments()->where('status', 'completed')->sum('net_amount') : 0.0;

$expectedBalance = round($openingBal + $standaloneRevenues + $paymentsTotal + $upfrontsTotal + $studentPayments + $transfersIn - $expensesTotal - $salariesTotal - $transfersOut - $refundsTotal, 2);
$actualBalance = (float) $vault->getCurrentBalance();

echo "Opening Balance:        " . number_format($openingBal, 2) . " EGP" . PHP_EOL;
echo "(+) Standalone Revenues:" . number_format($standaloneRevenues, 2) . " EGP" . PHP_EOL;
echo "(+) Payments Collected: " . number_format($paymentsTotal, 2) . " EGP" . PHP_EOL;
echo "(+) Upfront Collections:" . number_format($upfrontsTotal, 2) . " EGP" . PHP_EOL;
echo "(+) Student Payments:   " . number_format($studentPayments, 2) . " EGP" . PHP_EOL;
echo "(+) Transfers In:       " . number_format($transfersIn, 2) . " EGP" . PHP_EOL;
echo "(-) Expenses Paid:      " . number_format($expensesTotal, 2) . " EGP" . PHP_EOL;
echo "(-) Salaries Disbursed: " . number_format($salariesTotal, 2) . " EGP" . PHP_EOL;
echo "(-) Transfers Out:      " . number_format($transfersOut, 2) . " EGP" . PHP_EOL;
echo "(-) Refunds Disbursed:  " . number_format($refundsTotal, 2) . " EGP" . PHP_EOL;
echo "----------------------------------------------------" . PHP_EOL;
echo "Expected Reconciled:    " . number_format($expectedBalance, 2) . " EGP" . PHP_EOL;
echo "Actual Vault Balance:   " . number_format($actualBalance, 2) . " EGP" . PHP_EOL;
echo "Difference:             " . number_format(abs($expectedBalance - $actualBalance), 2) . " EGP" . PHP_EOL;

if (abs($expectedBalance - $actualBalance) < 0.01) {
    echo ">>> RECONCILIATION STATUS: 100% RECONCILED & AUDITED! <<<" . PHP_EOL;
} else {
    echo ">>> RECONCILIATION MISMATCH DETECTED! <<<" . PHP_EOL;
}
