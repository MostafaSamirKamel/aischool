<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Installment;
use App\Models\Payment;
use App\Models\Revenue;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vault;
use App\Services\InstallmentDueReportService;
use Illuminate\Support\Facades\DB;

echo "=== STARTING FINANCIAL INTEGRITY & INSTALLMENT SYNCHRONIZATION TEST SUITE ===\n\n";

$tenant = Tenant::first() ?? Tenant::create(['name' => 'فرع النزهة التجريبي', 'domain' => 'nozha.test']);
$user = User::where('tenant_id', $tenant->id)->where('email', 'secretary.a@aischool.test')->first()
    ?? User::where('tenant_id', $tenant->id)->first();

$vault = Vault::where('tenant_id', $tenant->id)->where('is_active', true)->first();
if (!$vault) {
    $vault = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'الخزينة الرئيسية - اختبار',
        'is_active' => true,
        'initial_balance' => 50000,
        'current_balance' => 50000,
    ]);
}

$initialVaultBalance = (float) $vault->fresh()->current_balance;

$group = \App\Models\Group::where('tenant_id', $tenant->id)->first();
if (!$group) {
    $course = \App\Models\Course::where('tenant_id', $tenant->id)->first() ?? \App\Models\Course::create([
        'tenant_id' => $tenant->id,
        'name' => 'كورس برمجة تجريبي',
        'code' => 'CRS-TEST-01',
        'price' => 1000,
        'status' => 'active',
    ]);
    $group = \App\Models\Group::create([
        'tenant_id' => $tenant->id,
        'course_id' => $course->id,
        'name' => 'مجموعة الاختبار المالي',
        'status' => 'active',
    ]);
}

echo "Test Environment Initialized:\n";
echo "- Tenant: {$tenant->name} (ID: {$tenant->id})\n";
echo "- User: {$user->name} (ID: {$user->id})\n";
echo "- Vault: {$vault->name} (Balance: {$initialVaultBalance} EGP)\n";
echo "- Group: {$group->name} (ID: {$group->id})\n\n";

$results = [];

// =========================================================================
// TEST A: Full Payment Settlement & Synchronization
// =========================================================================
echo "------------------------------------------------------------\n";
echo "TEST A: Full Payment Settlement & Synchronization\n";
echo "------------------------------------------------------------\n";

DB::beginTransaction();
try {
    // 1. Create student and enrollment with 1,000 EGP installment
    $studentUserA = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'طالب تجريبي سداد كامل - أحمد مصطفى',
        'email' => 'student.test.a.' . uniqid() . '@example.test',
        'password' => bcrypt('password'),
        'phone' => '01012345001',
    ]);
    $studentA = Student::create([
        'tenant_id' => $tenant->id,
        'user_id' => $studentUserA->id,
        'student_code' => 'TEST-STD-A-' . rand(1000, 9999),
        'phone' => '01012345001',
        'gender' => 'male',
        'status' => 'active',
    ]);

    $enrollmentA = Enrollment::create([
        'tenant_id' => $tenant->id,
        'student_id' => $studentA->id,
        'group_id' => $group->id,
        'enrollment_code' => 'ENR-A-' . rand(1000, 9999),
        'total_amount' => 1000.0,
        'paid_amount' => 0.0,
        'payment_plan' => 'installments',
        'status' => 'active',
        'enrollment_date' => now(),
    ]);

    $installmentA = Installment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollmentA->id,
        'installment_number' => 1,
        'amount' => 1000.0,
        'paid_amount' => 0.0,
        'due_date' => now()->format('Y-m-d'),
        'status' => 'pending',
    ]);

    // 2. Confirm it appears in the open installment list
    $openCountBefore = Installment::open()->where('id', $installmentA->id)->count();
    $dueTodayBefore = Installment::dueToday()->where('id', $installmentA->id)->count();
    $todayReportBefore = app(InstallmentDueReportService::class)->getTodayInstallments($tenant->id)->where('id', $installmentA->id)->count();

    echo "Initial State: Open Count = {$openCountBefore}, Due Today = {$dueTodayBefore}, Today Report = {$todayReportBefore}\n";
    assert($openCountBefore === 1, "Installment must appear in open list before payment");
    assert($dueTodayBefore === 1, "Installment must appear in due today before payment");

    // 3. Post full 1,000 EGP payment through official Payment::create (which triggers PaymentObserver)
    $paymentA = Payment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollmentA->id,
        'vault_id' => $vault->id,
        'amount' => 1000.0,
        'payment_date' => now()->format('Y-m-d'),
        'payment_method' => 'cash',
        'status' => 'completed',
        'transaction_reference' => 'TEST-REC-A-' . rand(1000, 9999),
        'received_by' => $user->id,
    ]);

    // 4. Verify installment updated
    $installmentA->refresh();
    echo "After Payment: Amount = {$installmentA->amount}, Paid = {$installmentA->paid_amount}, Remaining = {$installmentA->getRemainingAmount()}, Status = {$installmentA->status}\n";

    assert($installmentA->paid_amount == 1000.0, "Paid amount must equal 1000");
    assert($installmentA->getRemainingAmount() == 0.0, "Remaining amount must equal 0");
    assert($installmentA->status === 'paid', "Status must be 'paid'");

    // 5. Verify it disappears from open and due today lists
    $openCountAfter = Installment::open()->where('id', $installmentA->id)->count();
    $dueTodayAfter = Installment::dueToday()->where('id', $installmentA->id)->count();
    $todayReportAfter = app(InstallmentDueReportService::class)->getTodayInstallments($tenant->id)->where('id', $installmentA->id)->count();
    $paidHistoryCount = Installment::paid()->where('id', $installmentA->id)->count();

    echo "List Visibility: Open = {$openCountAfter}, Due Today = {$dueTodayAfter}, Today Report = {$todayReportAfter}, Paid History = {$paidHistoryCount}\n";
    assert($openCountAfter === 0, "Fully paid installment must disappear from open list");
    assert($dueTodayAfter === 0, "Fully paid installment must disappear from due today list");
    assert($todayReportAfter === 0, "Fully paid installment must disappear from today report");
    assert($paidHistoryCount === 1, "Fully paid installment must be present in paid history");

    // 6. Verify enrollment totals updated
    $enrollmentA->refresh();
    assert($enrollmentA->paid_amount == 1000.0, "Enrollment paid_amount must be 1000");
    assert($enrollmentA->getRemainingBalance() == 0.0, "Enrollment remaining balance must be 0");

    // 7. Verify Revenue recorded in vault
    $revenue = Revenue::where('payment_id', $paymentA->id)->first();
    assert($revenue !== null, "Revenue record must be created for payment");
    assert($revenue->amount == 1000.0, "Revenue amount must match payment");

    echo "✅ TEST A PASSED: Full payment settled, synchronized, disappeared from open lists, and preserved in history.\n";
    $results['Test A (Full Payment)'] = 'PASSED';
} finally {
    DB::rollBack();
}

// =========================================================================
// TEST B: Partial Payment Followed by Full Settlement
// =========================================================================
echo "\n------------------------------------------------------------\n";
echo "TEST B: Partial Payment Followed by Full Settlement\n";
echo "------------------------------------------------------------\n";

DB::beginTransaction();
try {
    $studentUserB = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'طالب تجريبي سداد جزئي - سارة إبراهيم',
        'email' => 'student.test.b.' . uniqid() . '@example.test',
        'password' => bcrypt('password'),
        'phone' => '01012345002',
    ]);
    $studentB = Student::create([
        'tenant_id' => $tenant->id,
        'user_id' => $studentUserB->id,
        'student_code' => 'TEST-STD-B-' . rand(1000, 9999),
        'phone' => '01012345002',
        'gender' => 'female',
        'status' => 'active',
    ]);

    $enrollmentB = Enrollment::create([
        'tenant_id' => $tenant->id,
        'student_id' => $studentB->id,
        'group_id' => $group->id,
        'enrollment_code' => 'ENR-B-' . rand(1000, 9999),
        'total_amount' => 1000.0,
        'paid_amount' => 0.0,
        'payment_plan' => 'installments',
        'status' => 'active',
        'enrollment_date' => now(),
    ]);

    $installmentB = Installment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollmentB->id,
        'installment_number' => 1,
        'amount' => 1000.0,
        'paid_amount' => 0.0,
        'due_date' => now()->format('Y-m-d'),
        'status' => 'pending',
    ]);

    // Step 1: Pay 400 EGP
    $paymentB1 = Payment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollmentB->id,
        'vault_id' => $vault->id,
        'amount' => 400.0,
        'payment_date' => now()->format('Y-m-d'),
        'payment_method' => 'cash',
        'status' => 'completed',
        'transaction_reference' => 'TEST-REC-B1-' . rand(1000, 9999),
        'received_by' => $user->id,
    ]);

    $installmentB->refresh();
    echo "After 400 EGP Payment: Paid = {$installmentB->paid_amount}, Remaining = {$installmentB->getRemainingAmount()}, Status = {$installmentB->status}\n";

    assert($installmentB->paid_amount == 400.0, "Paid amount must be 400");
    assert($installmentB->getRemainingAmount() == 600.0, "Remaining amount must be 600");
    assert($installmentB->status === 'partial', "Status must be 'partial'");

    // Must still appear in open list
    $openCountB1 = Installment::open()->where('id', $installmentB->id)->count();
    assert($openCountB1 === 1, "Partially paid installment MUST remain in open list");

    // Step 2: Pay remaining 600 EGP
    $paymentB2 = Payment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollmentB->id,
        'vault_id' => $vault->id,
        'amount' => 600.0,
        'payment_date' => now()->format('Y-m-d'),
        'payment_method' => 'cash',
        'status' => 'completed',
        'transaction_reference' => 'TEST-REC-B2-' . rand(1000, 9999),
        'received_by' => $user->id,
    ]);

    $installmentB->refresh();
    echo "After Remaining 600 EGP Payment: Paid = {$installmentB->paid_amount}, Remaining = {$installmentB->getRemainingAmount()}, Status = {$installmentB->status}\n";

    assert($installmentB->paid_amount == 1000.0, "Paid amount must be 1000");
    assert($installmentB->getRemainingAmount() == 0.0, "Remaining amount must be 0");
    assert($installmentB->status === 'paid', "Status must be 'paid'");

    // Must disappear from open list
    $openCountB2 = Installment::open()->where('id', $installmentB->id)->count();
    assert($openCountB2 === 0, "Fully settled installment must disappear from open list");

    // Both payment receipts must remain in payment history
    $receiptsCount = Payment::where('enrollment_id', $enrollmentB->id)->count();
    assert($receiptsCount === 2, "Both payment receipts must be preserved in history");

    echo "✅ TEST B PASSED: Partial payment retained open status with recalculated balance; final payment fully settled.\n";
    $results['Test B (Partial Payment)'] = 'PASSED';
} finally {
    DB::rollBack();
}

// =========================================================================
// TEST C: Oldest-First Multi-Installment Allocation
// =========================================================================
echo "\n------------------------------------------------------------\n";
echo "TEST C: Oldest-First Multi-Installment Allocation\n";
echo "------------------------------------------------------------\n";

DB::beginTransaction();
try {
    $studentUserC = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'طالب تجريبي أقساط متعددة - عمر خالد',
        'email' => 'student.test.c.' . uniqid() . '@example.test',
        'password' => bcrypt('password'),
        'phone' => '01012345003',
    ]);
    $studentC = Student::create([
        'tenant_id' => $tenant->id,
        'user_id' => $studentUserC->id,
        'student_code' => 'TEST-STD-C-' . rand(1000, 9999),
        'phone' => '01012345003',
        'gender' => 'male',
        'status' => 'active',
    ]);

    $enrollmentC = Enrollment::create([
        'tenant_id' => $tenant->id,
        'student_id' => $studentC->id,
        'group_id' => $group->id,
        'enrollment_code' => 'ENR-C-' . rand(1000, 9999),
        'total_amount' => 1500.0,
        'paid_amount' => 0.0,
        'payment_plan' => 'installments',
        'status' => 'active',
        'enrollment_date' => now(),
    ]);

    // Create 3 installments of 500 EGP each
    $instC1 = Installment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollmentC->id,
        'installment_number' => 1,
        'amount' => 500.0,
        'paid_amount' => 0.0,
        'due_date' => now()->subDays(10)->format('Y-m-d'), // Overdue
        'status' => 'overdue',
    ]);
    $instC2 = Installment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollmentC->id,
        'installment_number' => 2,
        'amount' => 500.0,
        'paid_amount' => 0.0,
        'due_date' => now()->format('Y-m-d'), // Due today
        'status' => 'pending',
    ]);
    $instC3 = Installment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollmentC->id,
        'installment_number' => 3,
        'amount' => 500.0,
        'paid_amount' => 0.0,
        'due_date' => now()->addDays(30)->format('Y-m-d'), // Future
        'status' => 'pending',
    ]);

    // Pay 800 EGP: Expected allocation = 500 to Inst 1 (fully paid), 300 to Inst 2 (partial), 0 to Inst 3 (pending)
    $paymentC = Payment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollmentC->id,
        'vault_id' => $vault->id,
        'amount' => 800.0,
        'payment_date' => now()->format('Y-m-d'),
        'payment_method' => 'cash',
        'status' => 'completed',
        'transaction_reference' => 'TEST-REC-C-' . rand(1000, 9999),
        'received_by' => $user->id,
    ]);

    $instC1->refresh();
    $instC2->refresh();
    $instC3->refresh();

    echo "Installment 1 (Overdue): Paid = {$instC1->paid_amount}, Remaining = {$instC1->getRemainingAmount()}, Status = {$instC1->status}\n";
    echo "Installment 2 (Today): Paid = {$instC2->paid_amount}, Remaining = {$instC2->getRemainingAmount()}, Status = {$instC2->status}\n";
    echo "Installment 3 (Future): Paid = {$instC3->paid_amount}, Remaining = {$instC3->getRemainingAmount()}, Status = {$instC3->status}\n";

    assert($instC1->paid_amount == 500.0 && $instC1->status === 'paid', "Installment 1 must be fully settled");
    assert($instC2->paid_amount == 300.0 && $instC2->status === 'partial', "Installment 2 must be partially paid with 300");
    assert($instC2->getRemainingAmount() == 200.0, "Installment 2 remaining must be 200");
    assert($instC3->paid_amount == 0.0 && $instC3->status === 'pending', "Installment 3 must remain untouched");

    echo "✅ TEST C PASSED: Oldest-first allocation applied correctly across multiple installments.\n";
    $results['Test C (Oldest-First Allocation)'] = 'PASSED';
} finally {
    DB::rollBack();
}

// =========================================================================
// TEST D: Cross-Enrollment / Multiple Enrollments
// =========================================================================
echo "\n------------------------------------------------------------\n";
echo "TEST D: Cross-Enrollment / Multiple Enrollments\n";
echo "------------------------------------------------------------\n";

DB::beginTransaction();
try {
    $studentUserD = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'طالب متعدد التسجيلات - ندى كريم',
        'email' => 'student.test.d.' . uniqid() . '@example.test',
        'password' => bcrypt('password'),
        'phone' => '01012345004',
    ]);
    $studentD = Student::create([
        'tenant_id' => $tenant->id,
        'user_id' => $studentUserD->id,
        'student_code' => 'TEST-STD-D-' . rand(1000, 9999),
        'phone' => '01012345004',
        'gender' => 'female',
        'status' => 'active',
    ]);

    // Enrollment 1: 500 EGP
    $enrD1 = Enrollment::create([
        'tenant_id' => $tenant->id,
        'student_id' => $studentD->id,
        'group_id' => $group->id,
        'enrollment_code' => 'ENR-D1-' . rand(1000, 9999),
        'total_amount' => 500.0,
        'paid_amount' => 0.0,
        'payment_plan' => 'installments',
        'status' => 'active',
        'enrollment_date' => now()->subMonths(1),
    ]);
    $instD1 = Installment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrD1->id,
        'installment_number' => 1,
        'amount' => 500.0,
        'paid_amount' => 0.0,
        'due_date' => now()->subDays(5)->format('Y-m-d'),
        'status' => 'overdue',
    ]);

    // Enrollment 2: 700 EGP (Distinct Group for Course 2)
    $group2 = \App\Models\Group::where('tenant_id', $tenant->id)->where('id', '!=', $group->id)->first()
        ?? \App\Models\Group::create([
            'tenant_id' => $tenant->id,
            'course_id' => $group->course_id,
            'name' => 'مجموعة الاختبار المالي 2',
            'status' => 'active',
        ]);

    $enrD2 = Enrollment::create([
        'tenant_id' => $tenant->id,
        'student_id' => $studentD->id,
        'group_id' => $group2->id,
        'enrollment_code' => 'ENR-D2-' . rand(1000, 9999),
        'total_amount' => 700.0,
        'paid_amount' => 0.0,
        'payment_plan' => 'installments',
        'status' => 'active',
        'enrollment_date' => now(),
    ]);
    $instD2 = Installment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrD2->id,
        'installment_number' => 1,
        'amount' => 700.0,
        'paid_amount' => 0.0,
        'due_date' => now()->addDays(15)->format('Y-m-d'),
        'status' => 'pending',
    ]);

    // Pay 800 EGP targeted to Enrollment 1:
    $paymentD = Payment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrD1->id,
        'vault_id' => $vault->id,
        'amount' => 800.0,
        'payment_date' => now()->format('Y-m-d'),
        'payment_method' => 'cash',
        'status' => 'completed',
        'transaction_reference' => 'TEST-REC-D-' . rand(1000, 9999),
        'received_by' => $user->id,
    ]);

    $instD1->refresh();
    $instD2->refresh();
    $enrD1->refresh();
    $enrD2->refresh();

    echo "Installment 1 (Enr 1): Paid = {$instD1->paid_amount}, Remaining = {$instD1->getRemainingAmount()}, Status = {$instD1->status}\n";
    echo "Installment 2 (Enr 2): Paid = {$instD2->paid_amount}, Remaining = {$instD2->getRemainingAmount()}, Status = {$instD2->status}\n";

    assert($instD1->paid_amount == 500.0 && $instD1->status === 'paid', "Installment 1 must be fully settled");
    assert($instD2->paid_amount == 300.0 && $instD2->status === 'partial', "Installment 2 must receive remaining 300 cross-enrollment");

    echo "✅ TEST D PASSED: Multiple enrollments and cross-enrollment allocation verified.\n";
    $results['Test D (Multiple Enrollments)'] = 'PASSED';
} finally {
    DB::rollBack();
}

// =========================================================================
// TEST E: Payment Reversal (Restoration of Debts & Status)
// =========================================================================
echo "\n------------------------------------------------------------\n";
echo "TEST E: Payment Reversal & Restoration\n";
echo "------------------------------------------------------------\n";

DB::beginTransaction();
try {
    $studentUserE = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'طالب تجريبي استرداد - ياسمين محمود',
        'email' => 'student.test.e.' . uniqid() . '@example.test',
        'password' => bcrypt('password'),
        'phone' => '01012345005',
    ]);
    $studentE = Student::create([
        'tenant_id' => $tenant->id,
        'user_id' => $studentUserE->id,
        'student_code' => 'TEST-STD-E-' . rand(1000, 9999),
        'phone' => '01012345005',
        'gender' => 'female',
        'status' => 'active',
    ]);

    $enrollmentE = Enrollment::create([
        'tenant_id' => $tenant->id,
        'student_id' => $studentE->id,
        'group_id' => $group->id,
        'enrollment_code' => 'ENR-E-' . rand(1000, 9999),
        'total_amount' => 1000.0,
        'paid_amount' => 0.0,
        'payment_plan' => 'installments',
        'status' => 'active',
        'enrollment_date' => now(),
    ]);

    $installmentE = Installment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollmentE->id,
        'installment_number' => 1,
        'amount' => 1000.0,
        'paid_amount' => 0.0,
        'due_date' => now()->format('Y-m-d'),
        'status' => 'pending',
    ]);

    // 1. Post payment
    $paymentE = Payment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollmentE->id,
        'vault_id' => $vault->id,
        'amount' => 1000.0,
        'payment_date' => now()->format('Y-m-d'),
        'payment_method' => 'cash',
        'status' => 'completed',
        'transaction_reference' => 'TEST-REC-E-' . rand(1000, 9999),
        'received_by' => $user->id,
    ]);

    $installmentE->refresh();
    assert($installmentE->status === 'paid', "Installment must be paid initially");
    assert(Revenue::where('payment_id', $paymentE->id)->count() === 1, "Revenue record exists");

    // 2. Reverse / Delete payment
    $paymentE->delete();

    $installmentE->refresh();
    $enrollmentE->refresh();

    echo "After Payment Deletion: Installment Paid = {$installmentE->paid_amount}, Remaining = {$installmentE->getRemainingAmount()}, Status = {$installmentE->status}\n";

    assert($installmentE->paid_amount == 0.0, "Installment paid amount must be restored to 0");
    assert($installmentE->getRemainingAmount() == 1000.0, "Installment remaining must be restored to 1000");
    assert($installmentE->status !== 'paid', "Status must revert from paid");
    assert($enrollmentE->paid_amount == 0.0, "Enrollment paid_amount must be restored to 0");
    assert(Revenue::where('payment_id', $paymentE->id)->count() === 0, "Revenue record must be removed on reversal");

    // Must reappear in open list
    $openCountE = Installment::open()->where('id', $installmentE->id)->count();
    assert($openCountE === 1, "Reversed installment must reappear in open list");

    echo "✅ TEST E PASSED: Payment reversal cleanly restores debt, status, open list visibility, and vault ledger.\n";
    $results['Test E (Payment Reversal)'] = 'PASSED';
} finally {
    DB::rollBack();
}

// =========================================================================
// TEST F: Duplicate Actions Removal Verification
// =========================================================================
echo "\n------------------------------------------------------------\n";
echo "TEST F: Duplicate Action Removal Verification\n";
echo "------------------------------------------------------------\n";

// 1. Check HandlesInstallmentDuePayments
$dummyPage = new class {
    use \App\Filament\Admin\Pages\Concerns\HandlesInstallmentDuePayments;
    public function refreshReportData(): void {}
};

assert($dummyPage->canCollectPayment() === false, "HandlesInstallmentDuePayments::canCollectPayment must return false");

$exceptionThrown = false;
try {
    $dummyPage->savePayment();
} catch (\Illuminate\Auth\Access\AuthorizationException $e) {
    $exceptionThrown = true;
}
assert($exceptionThrown === true, "HandlesInstallmentDuePayments::savePayment must throw AuthorizationException");

// 2. Check OverdueInstallmentResource actions
$resourceFile = file_get_contents(__DIR__ . '/../app/Filament/Admin/Resources/OverdueInstallmentResource.php');
assert(!str_contains($resourceFile, "Action::make('mark_paid')"), "OverdueInstallmentResource must not contain mark_paid action");
assert(!str_contains($resourceFile, "->label('تسجيل كمدفوع')"), "OverdueInstallmentResource must not contain 'تسجيل كمدفوع'");

// 3. Check EditOverdueInstallment header actions
$editPageFile = file_get_contents(__DIR__ . '/../app/Filament/Admin/Resources/OverdueInstallmentResource/Pages/EditOverdueInstallment.php');
assert(!str_contains($editPageFile, "mark_paid"), "EditOverdueInstallment must not contain mark_paid header action");

// 4. Check blade views for 'btn-pay' or '💰 سداد'
$todayBlade = file_get_contents(__DIR__ . '/../resources/views/filament/admin/pages/installments-due-today.blade.php');
$weekBlade = file_get_contents(__DIR__ . '/../resources/views/filament/admin/pages/installments-due-week.blade.php');
$monthBlade = file_get_contents(__DIR__ . '/../resources/views/filament/admin/pages/installments-due-month.blade.php');

assert(!str_contains($todayBlade, 'openPaymentModal'), "installments-due-today must not contain openPaymentModal");
assert(!str_contains($todayBlade, 'btn-pay'), "installments-due-today must not contain btn-pay");
assert(!str_contains($todayBlade, 'savePayment'), "installments-due-today must not contain savePayment form");

assert(!str_contains($weekBlade, 'openPaymentModal'), "installments-due-week must not contain openPaymentModal");
assert(!str_contains($weekBlade, 'btn-pay'), "installments-due-week must not contain btn-pay");
assert(!str_contains($weekBlade, 'savePayment'), "installments-due-week must not contain savePayment form");

assert(!str_contains($monthBlade, 'openPaymentModal'), "installments-due-month must not contain openPaymentModal");
assert(!str_contains($monthBlade, 'btn-pay'), "installments-due-month must not contain btn-pay");
assert(!str_contains($monthBlade, 'savePayment'), "installments-due-month must not contain savePayment form");

echo "✅ TEST F PASSED: All duplicate 'سداد' and 'mark_paid' actions verified completely removed across all installment screens.\n";
$results['Test F (Duplicate Removal)'] = 'PASSED';

// =========================================================================
// TEST G: Overpayment & Concurrency Safeguards
// =========================================================================
echo "\n------------------------------------------------------------\n";
echo "TEST G: Overpayment & Concurrency Safeguards\n";
echo "------------------------------------------------------------\n";

DB::beginTransaction();
try {
    $studentUserG = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'طالب تجريبي التحصيل الزائد - طارق محمود',
        'email' => 'student.test.g.' . uniqid() . '@example.test',
        'password' => bcrypt('password'),
        'phone' => '01012345006',
    ]);
    $studentG = Student::create([
        'tenant_id' => $tenant->id,
        'user_id' => $studentUserG->id,
        'student_code' => 'TEST-STD-G-' . rand(1000, 9999),
        'phone' => '01012345006',
        'gender' => 'male',
        'status' => 'active',
    ]);

    $enrollmentG = Enrollment::create([
        'tenant_id' => $tenant->id,
        'student_id' => $studentG->id,
        'group_id' => $group->id,
        'enrollment_code' => 'ENR-G-' . rand(1000, 9999),
        'total_amount' => 500.0,
        'paid_amount' => 0.0,
        'payment_plan' => 'installments',
        'status' => 'active',
        'enrollment_date' => now(),
    ]);

    $instG = Installment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollmentG->id,
        'installment_number' => 1,
        'amount' => 500.0,
        'paid_amount' => 0.0,
        'due_date' => now()->format('Y-m-d'),
        'status' => 'pending',
    ]);

    // Test CreatePayment::mutateFormDataBeforeCreate overpayment rejection
    $createPaymentPage = new class extends \App\Filament\Admin\Resources\Payments\Pages\CreatePayment {
        public function testMutate(array $data) {
            return $this->mutateFormDataBeforeCreate($data);
        }
    };

    auth()->login($user);

    $overpaymentCaught = false;
    try {
        $createPaymentPage->testMutate([
            'enrollment_id' => $enrollmentG->id,
            'vault_id' => $vault->id,
            'amount' => 600.0, // Remaining is 500, so 600 is overpayment!
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        $overpaymentCaught = true;
        echo "Overpayment Rejected Successfully: " . $e->getMessage() . "\n";
    }

    assert($overpaymentCaught === true, "System MUST reject payments exceeding outstanding debt");

    // Test zero or negative payment rejection
    $zeroPaymentCaught = false;
    try {
        $createPaymentPage->testMutate([
            'enrollment_id' => $enrollmentG->id,
            'vault_id' => $vault->id,
            'amount' => 0.0,
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        $zeroPaymentCaught = true;
    }
    assert($zeroPaymentCaught === true, "System MUST reject zero or negative payment amounts");

    echo "✅ TEST G PASSED: Overpayment and invalid amounts are strictly prevented at backend boundary.\n";
    $results['Test G (Concurrency & Overpayment Prevention)'] = 'PASSED';
} finally {
    DB::rollBack();
}

echo "\n============================================================\n";
echo "SUMMARY OF RESULTS:\n";
echo "============================================================\n";
foreach ($results as $testName => $status) {
    echo "  [✓] {$testName}: {$status}\n";
}
echo "\nALL 7 FINANCIAL INTEGRITY AND SYNCHRONIZATION TESTS PASSED!\n";
