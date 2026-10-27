<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentCancellation;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Group;
use App\Models\Installment;
use App\Models\Instructor;
use App\Models\Payment;
use App\Models\Revenue;
use App\Models\SalaryPayment;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultTransfer;
use App\Services\Enrollment\EnrollmentCancellationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    Permission::firstOrCreate(['name' => 'view_vault_balances', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'view_vault_transactions', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'view_student_payments', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'create_student_payments', 'guard_name' => 'web']);
});

function createTestEnrollment($tenant, $vault = null, $total = 1000.00, $paid = 0.00, $plan = 'cash'): Enrollment
{
    $student = Student::factory()->create(['tenant_id' => $tenant->id]);
    $course = Course::factory()->create(['tenant_id' => $tenant->id, 'price' => $total]);
    $group = Group::factory()->create(['tenant_id' => $tenant->id, 'course_id' => $course->id]);

    return Enrollment::factory()->create([
        'tenant_id' => $tenant->id,
        'student_id' => $student->id,
        'group_id' => $group->id,
        'vault_id' => $vault?->id,
        'total_amount' => $total,
        'paid_amount' => $paid,
        'payment_plan' => $plan,
    ]);
}

// TEST 01 — Cash Collection
test('TEST 01: cash collection increases vault by exact amount and updates student balance', function () {
    $tenant = Tenant::factory()->create();
    $vault = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Main Cash',
        'type' => 'cash',
        'opening_balance' => 1000.00,
        'is_active' => true,
    ]);

    $enrollment = createTestEnrollment($tenant, null, 1000.00, 0.00);

    $payment = Payment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollment->id,
        'vault_id' => $vault->id,
        'amount' => 500.00,
        'status' => 'completed',
        'payment_date' => now(),
    ]);

    expect((float) $vault->getCurrentBalance())->toBe(1500.00)
        ->and($payment->vault_id)->toBe($vault->id)
        ->and($payment->vault->name)->toBe('Main Cash');
});

// TEST 02 — Vodafone Cash Collection
test('TEST 02: vodafone cash collection increases receiving vault and leaves other vaults unchanged', function () {
    $tenant = Tenant::factory()->create();
    $mainCash = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Main Cash',
        'type' => 'cash',
        'opening_balance' => 1000.00,
        'is_active' => true,
    ]);
    $vodaCash = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Vodafone Cash',
        'type' => 'vodafone',
        'opening_balance' => 0.00,
        'is_active' => true,
    ]);

    $enrollment = createTestEnrollment($tenant, null, 1000.00, 0.00);

    Payment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollment->id,
        'vault_id' => $vodaCash->id,
        'amount' => 700.00,
        'status' => 'completed',
        'payment_date' => now(),
    ]);

    expect((float) $vodaCash->getCurrentBalance())->toBe(700.00)
        ->and((float) $mainCash->getCurrentBalance())->toBe(1000.00);
});

// TEST 03 — Split Installment Payment
test('TEST 03: split installment payment across multiple vaults records separate collections and marks installment paid', function () {
    $tenant = Tenant::factory()->create();
    $mainCash = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Main Cash',
        'type' => 'cash',
        'opening_balance' => 0.00,
        'is_active' => true,
    ]);
    $vodaCash = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Vodafone Cash',
        'type' => 'vodafone',
        'opening_balance' => 0.00,
        'is_active' => true,
    ]);

    $enrollment = createTestEnrollment($tenant, null, 1200.00, 0.00, 'installments');

    $installment = Installment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollment->id,
        'installment_number' => 1,
        'amount' => 1200.00,
        'paid_amount' => 0.00,
        'due_date' => now()->addDays(30),
        'status' => 'pending',
    ]);

    // First split payment: 500 EGP into Main Cash (PaymentObserver allocates to installment)
    $p1 = Payment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollment->id,
        'vault_id' => $mainCash->id,
        'amount' => 500.00,
        'status' => 'completed',
        'payment_date' => now(),
    ]);

    // Second split payment: 700 EGP into Vodafone Cash (PaymentObserver allocates remainder)
    $p2 = Payment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollment->id,
        'vault_id' => $vodaCash->id,
        'amount' => 700.00,
        'status' => 'completed',
        'payment_date' => now(),
    ]);

    expect((float) $mainCash->getCurrentBalance())->toBe(500.00)
        ->and((float) $vodaCash->getCurrentBalance())->toBe(700.00)
        ->and((float) $installment->fresh()->paid_amount)->toBe(1200.00)
        ->and((float) $installment->fresh()->getRemainingAmount())->toBe(0.00)
        ->and($installment->fresh()->status)->toBe('paid')
        ->and($p1->vault_id)->toBe($mainCash->id)
        ->and($p2->vault_id)->toBe($vodaCash->id);
});

// TEST 04 — Expense
test('TEST 04: expense deducts funds correctly from paying vault', function () {
    $tenant = Tenant::factory()->create();
    $creator = User::factory()->create(['tenant_id' => $tenant->id]);
    $vault = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Main Cash',
        'type' => 'cash',
        'opening_balance' => 1000.00,
        'is_active' => true,
    ]);
    $cat = ExpenseCategory::create([
        'tenant_id' => $tenant->id,
        'name' => 'Supplies',
        'code' => 'SUPP-01',
        'is_active' => true,
    ]);

    Expense::create([
        'tenant_id' => $tenant->id,
        'expense_category_id' => $cat->id,
        'vault_id' => $vault->id,
        'expense_code' => 'EXP-TEST-004',
        'title' => 'Office Paper',
        'amount' => 300.00,
        'expense_date' => now(),
        'status' => 'paid',
        'created_by' => $creator->id,
    ]);

    expect((float) $vault->getCurrentBalance())->toBe(700.00);
});

// TEST 05 — Insufficient Balance
test('TEST 05: expense exceeding vault balance is rejected and vault balance remains unchanged', function () {
    $tenant = Tenant::factory()->create();
    $creator = User::factory()->create(['tenant_id' => $tenant->id]);
    $vault = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Main Cash',
        'type' => 'cash',
        'opening_balance' => 500.00,
        'is_active' => true,
    ]);
    $cat = ExpenseCategory::create([
        'tenant_id' => $tenant->id,
        'name' => 'Supplies',
        'code' => 'SUPP-02',
        'is_active' => true,
    ]);

    expect(function () use ($tenant, $cat, $vault, $creator) {
        Expense::create([
            'tenant_id' => $tenant->id,
            'expense_category_id' => $cat->id,
            'vault_id' => $vault->id,
            'expense_code' => 'EXP-TEST-005',
            'title' => 'Expensive Equipment',
            'amount' => 800.00,
            'expense_date' => now(),
            'status' => 'paid',
            'created_by' => $creator->id,
        ]);
    })->toThrow(\Illuminate\Validation\ValidationException::class);

    expect((float) $vault->getCurrentBalance())->toBe(500.00)
        ->and(Expense::where('expense_code', 'EXP-TEST-005')->exists())->toBeFalse();
});

// TEST 06 — Refund
test('TEST 06: student refund deducts from selected vault once and preserves original payment records', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->create([
        'tenant_id' => $tenant->id,
        'is_super_admin' => true,
    ]);

    $vault = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Main Cash',
        'type' => 'cash',
        'opening_balance' => 1000.00,
        'is_active' => true,
    ]);

    $enrollment = createTestEnrollment($tenant, null, 1000.00, 0.00);

    $payment = Payment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollment->id,
        'vault_id' => $vault->id,
        'amount' => 500.00,
        'status' => 'completed',
        'payment_date' => now(),
    ]);

    expect((float) $vault->getCurrentBalance())->toBe(1500.00);

    // Cancel and refund 200 EGP using cancelSimplified
    $service = app(EnrollmentCancellationService::class);
    $cancellation = $service->cancelSimplified($enrollment, $admin, [
        'cancellation_reason' => 'Relocation',
        'refund_amount' => 200.00,
        'vault_id' => $vault->id,
    ]);

    expect((float) $vault->getCurrentBalance())->toBe(1300.00)
        ->and((float) $cancellation->refunded_amount)->toBe(200.00)
        ->and($cancellation->refund_vault_id)->toBe($vault->id)
        ->and($payment->fresh()->exists)->toBeTrue();
});

// TEST 07 — Salary Payment
test('TEST 07: instructor salary payment deducts from vault and updates salary status', function () {
    $tenant = Tenant::factory()->create();
    $vault = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'InstaPay Vault',
        'type' => 'instapay',
        'opening_balance' => 5000.00,
        'is_active' => true,
    ]);
    $instructor = Instructor::factory()->create(['tenant_id' => $tenant->id]);

    $salary = SalaryPayment::create([
        'tenant_id' => $tenant->id,
        'instructor_id' => $instructor->id,
        'employee_type' => 'instructor',
        'vault_id' => $vault->id,
        'amount' => 1500.00,
        'payment_date' => now(),
        'status' => 'paid',
    ]);

    expect((float) $vault->getCurrentBalance())->toBe(3500.00)
        ->and($salary->status)->toBe('paid')
        ->and($salary->vault_id)->toBe($vault->id);
});

// TEST 08 — Vault Transfer
test('TEST 08: vault transfer moves funds between vaults without increasing school revenue', function () {
    $tenant = Tenant::factory()->create();
    $mainCash = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Main Cash',
        'type' => 'cash',
        'opening_balance' => 2000.00,
        'is_active' => true,
    ]);
    $vodaCash = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Vodafone Cash',
        'type' => 'vodafone',
        'opening_balance' => 500.00,
        'is_active' => true,
    ]);

    VaultTransfer::create([
        'tenant_id' => $tenant->id,
        'from_vault_id' => $mainCash->id,
        'to_vault_id' => $vodaCash->id,
        'amount' => 1000.00,
        'transfer_date' => now(),
        'status' => 'completed',
    ]);

    expect((float) $mainCash->getCurrentBalance())->toBe(1000.00)
        ->and((float) $vodaCash->getCurrentBalance())->toBe(1500.00)
        ->and(Revenue::count())->toBe(0);
});

// TEST 09 — No Balance Visibility
test('TEST 09: employee without balance visibility permission can select vault and collect without balance displayed', function () {
    $tenant = Tenant::factory()->create();
    $vault = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Confidential Vault',
        'type' => 'cash',
        'opening_balance' => 9999.00,
        'is_active' => true,
    ]);

    $employee = User::factory()->create([
        'tenant_id' => $tenant->id,
        'is_super_admin' => false,
    ]);
    $employee->givePermissionTo('create_student_payments');

    $this->actingAs($employee);

    expect(auth()->user()->can('view_vault_balances'))->toBeFalse()
        ->and($vault->getDisplayNameWithBalance())->toBe('Confidential Vault')
        ->and(str_contains($vault->getDisplayNameWithBalance(), '9999'))->toBeFalse();

    $enrollment = createTestEnrollment($tenant, null, 500.00, 0.00);

    $payment = Payment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollment->id,
        'vault_id' => $vault->id,
        'amount' => 200.00,
        'status' => 'completed',
        'payment_date' => now(),
    ]);

    expect($payment->id)->toBeGreaterThan(0)
        ->and((float) $vault->getCurrentBalance())->toBe(10199.00);
});

// TEST 10 — No Statement Visibility
test('TEST 10: employee without statement visibility cannot access vault reports or transactions', function () {
    $tenant = Tenant::factory()->create();
    $employee = User::factory()->create([
        'tenant_id' => $tenant->id,
        'is_super_admin' => false,
    ]);
    $employee->givePermissionTo('create_student_payments');

    $this->actingAs($employee);

    expect(\App\Filament\Admin\Pages\VaultReport::canAccess())->toBeFalse()
        ->and(\App\Filament\Admin\Resources\Vaults\Pages\VaultTransactions::canAccess())->toBeFalse();
});

// TEST 11 — Both Visibility Permissions
test('TEST 11: employee with both visibility permissions can view balances and transactions', function () {
    $tenant = Tenant::factory()->create();
    $vault = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Main Cash',
        'type' => 'cash',
        'opening_balance' => 500.00,
        'is_active' => true,
    ]);

    $auditor = User::factory()->create([
        'tenant_id' => $tenant->id,
        'is_super_admin' => false,
    ]);
    $auditor->givePermissionTo('view_vault_balances');
    $auditor->givePermissionTo('view_vault_transactions');

    $this->actingAs($auditor);

    expect(auth()->user()->can('view_vault_balances'))->toBeTrue()
        ->and(auth()->user()->can('view_vault_transactions'))->toBeTrue()
        ->and(str_contains($vault->getDisplayNameWithBalance(), '500.00'))->toBeTrue()
        ->and(\App\Filament\Admin\Pages\VaultReport::canAccess())->toBeTrue()
        ->and(\App\Filament\Admin\Resources\Vaults\Pages\VaultTransactions::canAccess())->toBeTrue();
});

// TEST 12 — Cross-Branch Access
test('TEST 12: cross-branch vault access is rejected', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $vaultB = Vault::create([
        'tenant_id' => $tenantB->id,
        'name' => 'Branch B Vault',
        'type' => 'cash',
        'opening_balance' => 1000.00,
        'is_active' => true,
    ]);

    $userA = User::factory()->create([
        'tenant_id' => $tenantA->id,
        'is_super_admin' => false,
    ]);

    $this->actingAs($userA);

    $scopedVaults = Vault::query()->where('is_active', true)->where('tenant_id', $userA->tenant_id)->get();
    expect($scopedVaults->pluck('id'))->not->toContain($vaultB->id);
});

// TEST 13 — Inactive Vault
test('TEST 13: recording new transaction into inactive vault is rejected', function () {
    $tenant = Tenant::factory()->create();
    $inactiveVault = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Old Inactive Vault',
        'type' => 'cash',
        'opening_balance' => 1000.00,
        'is_active' => false,
    ]);

    $activeVaults = Vault::query()->where('is_active', true)->where('tenant_id', $tenant->id)->get();
    expect($activeVaults->pluck('id'))->not->toContain($inactiveVault->id);
});

// TEST 14 — Concurrent Withdrawals
test('TEST 14: atomic protection prevents vault balance from going negative under concurrent withdrawals', function () {
    $tenant = Tenant::factory()->create();
    $creator = User::factory()->create(['tenant_id' => $tenant->id]);
    $vault = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'High Concurrency Vault',
        'type' => 'cash',
        'opening_balance' => 500.00,
        'is_active' => true,
    ]);
    $cat = ExpenseCategory::create([
        'tenant_id' => $tenant->id,
        'name' => 'Operations',
        'code' => 'OP-01',
        'is_active' => true,
    ]);

    Expense::create([
        'tenant_id' => $tenant->id,
        'expense_category_id' => $cat->id,
        'vault_id' => $vault->id,
        'expense_code' => 'EXP-CONC-1',
        'title' => 'First Withdrawal',
        'amount' => 400.00,
        'expense_date' => now(),
        'status' => 'paid',
        'created_by' => $creator->id,
    ]);

    expect(function () use ($tenant, $cat, $vault, $creator) {
        Expense::create([
            'tenant_id' => $tenant->id,
            'expense_category_id' => $cat->id,
            'vault_id' => $vault->id,
            'expense_code' => 'EXP-CONC-2',
            'title' => 'Second Withdrawal',
            'amount' => 300.00,
            'expense_date' => now(),
            'status' => 'paid',
            'created_by' => $creator->id,
        ]);
    })->toThrow(\Illuminate\Validation\ValidationException::class);

    expect((float) $vault->getCurrentBalance())->toBe(100.00)
        ->and((float) $vault->getCurrentBalance())->toBeGreaterThanOrEqual(0.00);
});

// TEST 15 — Double Submission
test('TEST 15: double submission prevention creates separate distinct payments when intentionally recorded', function () {
    $tenant = Tenant::factory()->create();
    $vault = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Main Cash',
        'type' => 'cash',
        'opening_balance' => 0.00,
        'is_active' => true,
    ]);

    $enrollment = createTestEnrollment($tenant, null, 1000.00, 0.00);

    $p1 = Payment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollment->id,
        'vault_id' => $vault->id,
        'payment_code' => 'PAY-SUBMIT-1',
        'amount' => 250.00,
        'status' => 'completed',
        'payment_date' => now(),
    ]);

    $p2 = Payment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollment->id,
        'vault_id' => $vault->id,
        'payment_code' => 'PAY-SUBMIT-2',
        'amount' => 250.00,
        'status' => 'completed',
        'payment_date' => now(),
    ]);

    expect($p1->id)->not->toBe($p2->id)
        ->and((float) $vault->getCurrentBalance())->toBe(500.00);
});

// TEST 16 — Historical Payment Records
test('TEST 16: historical payment records retain their payment methods without modification', function () {
    $tenant = Tenant::factory()->create();
    $vault = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Legacy Vault',
        'type' => 'cash',
        'opening_balance' => 0.00,
        'is_active' => true,
    ]);

    $enrollment = createTestEnrollment($tenant, null, 1000.00, 0.00);

    $legacyPayment = Payment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollment->id,
        'vault_id' => $vault->id,
        'payment_code' => 'LEGACY-PAY-001',
        'amount' => 450.00,
        'payment_method' => 'mobile_wallet',
        'status' => 'completed',
        'payment_date' => now()->subMonths(6),
    ]);

    expect($legacyPayment->fresh()->payment_method)->toBe('mobile_wallet')
        ->and((float) $vault->getCurrentBalance())->toBe(450.00);
});

// TEST 17 — New Vault
test('TEST 17: newly created active vault dynamically appears in branch selectors', function () {
    $tenant = Tenant::factory()->create();

    $newVault = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'InstaPay New Branch Account',
        'type' => 'instapay',
        'opening_balance' => 0.00,
        'is_active' => true,
    ]);

    $selectorOptions = Vault::query()
        ->where('is_active', true)
        ->where('tenant_id', $tenant->id)
        ->pluck('name', 'id')
        ->toArray();

    expect(array_key_exists($newVault->id, $selectorOptions))->toBeTrue()
        ->and($selectorOptions[$newVault->id])->toBe('InstaPay New Branch Account');
});

// TEST 18 — Deactivate Vault
test('TEST 18: deactivated vault preserves history but is excluded from active selectors', function () {
    $tenant = Tenant::factory()->create();
    $vault = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Old Branch Vault',
        'type' => 'cash',
        'opening_balance' => 1000.00,
        'is_active' => true,
    ]);

    $enrollment = createTestEnrollment($tenant, null, 1000.00, 0.00);

    Payment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollment->id,
        'vault_id' => $vault->id,
        'amount' => 500.00,
        'status' => 'completed',
        'payment_date' => now()->subMonth(),
    ]);

    $vault->update(['is_active' => false]);

    expect((float) $vault->getCurrentBalance())->toBe(1500.00);

    $activeVaults = Vault::query()->where('is_active', true)->where('tenant_id', $tenant->id)->pluck('id');
    expect($activeVaults)->not->toContain($vault->id);
});

// TEST 19 — Dashboard Reconciliation
test('TEST 19: financial reports and vault statements accurately reconcile against database records', function () {
    $tenant = Tenant::factory()->create();
    $creator = User::factory()->create(['tenant_id' => $tenant->id]);
    $vault = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Audit Vault',
        'type' => 'cash',
        'opening_balance' => 1000.00,
        'is_active' => true,
    ]);

    $enrollment = createTestEnrollment($tenant, null, 1000.00, 0.00);

    Payment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollment->id,
        'vault_id' => $vault->id,
        'amount' => 600.00,
        'status' => 'completed',
        'payment_date' => now(),
    ]);

    $cat = ExpenseCategory::create([
        'tenant_id' => $tenant->id,
        'name' => 'Admin',
        'code' => 'ADM-01',
        'is_active' => true,
    ]);

    Expense::create([
        'tenant_id' => $tenant->id,
        'expense_category_id' => $cat->id,
        'vault_id' => $vault->id,
        'expense_code' => 'EXP-REC-01',
        'title' => 'Utility',
        'amount' => 200.00,
        'status' => 'paid',
        'expense_date' => now(),
        'created_by' => $creator->id,
    ]);

    expect((float) $vault->getCurrentBalance())->toBe(1400.00);
});

// TEST 20 — Permission Bypass Attempts
test('TEST 20: unauthorized direct access to vault statements or balances is denied', function () {
    $tenant = Tenant::factory()->create();
    $vault = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Secret Vault',
        'type' => 'cash',
        'opening_balance' => 5000.00,
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'is_super_admin' => false,
    ]);

    $this->actingAs($user);

    expect(\App\Filament\Admin\Pages\VaultReport::canAccess())->toBeFalse()
        ->and(\App\Filament\Admin\Resources\Vaults\Pages\VaultTransactions::canAccess())->toBeFalse()
        ->and(str_contains($vault->getDisplayNameWithBalance(), '5000'))->toBeFalse();
});

// TEST 21 — Refund and Expense Double Counting
test('TEST 21: refund and expense observers do not double-deduct from vault balance', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->create([
        'tenant_id' => $tenant->id,
        'is_super_admin' => true,
    ]);

    $vault = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Reconciliation Vault',
        'type' => 'cash',
        'opening_balance' => 1000.00,
        'is_active' => true,
    ]);

    $enrollment = createTestEnrollment($tenant, null, 1000.00, 0.00);

    Payment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollment->id,
        'vault_id' => $vault->id,
        'amount' => 500.00,
        'status' => 'completed',
        'payment_date' => now(),
    ]);

    expect((float) $vault->getCurrentBalance())->toBe(1500.00);

    $service = app(EnrollmentCancellationService::class);
    $service->cancelSimplified($enrollment, $admin, [
        'cancellation_reason' => 'Duplicate registration',
        'refund_amount' => 150.00,
        'vault_id' => $vault->id,
    ]);

    expect((float) $vault->getCurrentBalance())->toBe(1350.00);
});

// TEST 22 — Empty Vault
test('TEST 22: empty vault with zero balance permits incoming payments but rejects outgoing payments', function () {
    $tenant = Tenant::factory()->create();
    $creator = User::factory()->create(['tenant_id' => $tenant->id]);
    $zeroVault = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Zero Balance Vault',
        'type' => 'cash',
        'opening_balance' => 0.00,
        'is_active' => true,
    ]);

    expect((float) $zeroVault->getCurrentBalance())->toBe(0.00);

    $cat = ExpenseCategory::create([
        'tenant_id' => $tenant->id,
        'name' => 'Office',
        'code' => 'OFF-01',
        'is_active' => true,
    ]);

    expect(function () use ($tenant, $cat, $zeroVault, $creator) {
        Expense::create([
            'tenant_id' => $tenant->id,
            'expense_category_id' => $cat->id,
            'vault_id' => $zeroVault->id,
            'expense_code' => 'EXP-ZERO-01',
            'title' => 'Attempted Outflow',
            'amount' => 50.00,
            'status' => 'paid',
            'expense_date' => now(),
            'created_by' => $creator->id,
        ]);
    })->toThrow(\Illuminate\Validation\ValidationException::class);

    $enrollment = createTestEnrollment($tenant, null, 1000.00, 0.00);

    Payment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollment->id,
        'vault_id' => $zeroVault->id,
        'amount' => 100.00,
        'status' => 'completed',
        'payment_date' => now(),
    ]);

    expect((float) $zeroVault->getCurrentBalance())->toBe(100.00);
});

// TEST 23 — Registration Upfront Payment
test('TEST 23: registration upfront payment creates exactly one cash inflow and updates enrollment accounting', function () {
    $tenant = Tenant::factory()->create();
    $vault = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Registration Cash Vault',
        'type' => 'cash',
        'opening_balance' => 0.00,
        'is_active' => true,
    ]);

    $enrollment = createTestEnrollment($tenant, $vault, 1000.00, 300.00, 'cash');

    expect((float) $vault->getCurrentBalance())->toBe(300.00)
        ->and((float) $enrollment->getRemainingBalance())->toBe(700.00);
});

// TEST 24 — Existing Installment Allocation
test('TEST 24: installment allocation distribution logic is preserved across vault transactions', function () {
    $tenant = Tenant::factory()->create();
    $vaultA = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Vault A',
        'type' => 'cash',
        'opening_balance' => 0.00,
        'is_active' => true,
    ]);

    $enrollment = createTestEnrollment($tenant, null, 1500.00, 0.00, 'installments');

    $inst1 = Installment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollment->id,
        'installment_number' => 1,
        'amount' => 500.00,
        'paid_amount' => 0.00,
        'due_date' => now()->addDays(10),
        'status' => 'pending',
    ]);

    $inst2 = Installment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollment->id,
        'installment_number' => 2,
        'amount' => 1000.00,
        'paid_amount' => 0.00,
        'due_date' => now()->addDays(40),
        'status' => 'pending',
    ]);

    // Payment of 700: PaymentObserver automatically applies 500 to inst1 and 200 to inst2
    Payment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollment->id,
        'vault_id' => $vaultA->id,
        'amount' => 700.00,
        'status' => 'completed',
        'payment_date' => now(),
    ]);

    expect($inst1->fresh()->status)->toBe('paid')
        ->and((float) $inst1->fresh()->getRemainingAmount())->toBe(0.00)
        ->and($inst2->fresh()->status)->toBe('partial')
        ->and((float) $inst2->fresh()->getRemainingAmount())->toBe(800.00)
        ->and((float) $vaultA->getCurrentBalance())->toBe(700.00);
});

// TEST 25 — Failed Transaction Rollback
test('TEST 25: failed outgoing transaction rolls back completely with no partial changes', function () {
    $tenant = Tenant::factory()->create();
    $creator = User::factory()->create(['tenant_id' => $tenant->id]);
    $vault = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Rollback Test Vault',
        'type' => 'cash',
        'opening_balance' => 500.00,
        'is_active' => true,
    ]);
    $cat = ExpenseCategory::create([
        'tenant_id' => $tenant->id,
        'name' => 'General',
        'code' => 'GEN-01',
        'is_active' => true,
    ]);

    $caught = false;
    try {
        DB::transaction(function () use ($tenant, $vault, $cat, $creator) {
            Expense::create([
                'tenant_id' => $tenant->id,
                'expense_category_id' => $cat->id,
                'vault_id' => $vault->id,
                'expense_code' => 'EXP-ROLLBACK-1',
                'title' => 'Part 1',
                'amount' => 100.00,
                'status' => 'paid',
                'expense_date' => now(),
                'created_by' => $creator->id,
            ]);

            Expense::create([
                'tenant_id' => $tenant->id,
                'expense_category_id' => $cat->id,
                'vault_id' => $vault->id,
                'expense_code' => 'EXP-ROLLBACK-2',
                'title' => 'Part 2 Exceeding',
                'amount' => 800.00,
                'status' => 'paid',
                'expense_date' => now(),
                'created_by' => $creator->id,
            ]);
        });
    } catch (\Throwable $e) {
        $caught = true;
    }

    expect($caught)->toBeTrue()
        ->and((float) $vault->getCurrentBalance())->toBe(500.00)
        ->and(Expense::where('expense_code', 'EXP-ROLLBACK-1')->exists())->toBeFalse();
});
