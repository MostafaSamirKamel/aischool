<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\InstallmentsDueMonth;
use App\Filament\Admin\Pages\InstallmentsDueToday;
use App\Filament\Admin\Pages\InstallmentsDueWeek;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Installment;
use App\Models\Payment;
use App\Models\Revenue;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vault;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InstallmentDueReportIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $adminUser;
    protected User $reportingUser;
    protected User $unauthorizedUser;
    protected Vault $vault;
    protected Student $student;
    protected Enrollment $enrollment;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Tenant
        $this->tenant = Tenant::create([
            'name' => 'Main Branch Tenant',
            'domain' => 'main.aischool.test',
        ]);

        // 2. Setup Roles & Permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $secretaryRole = Role::firstOrCreate(['name' => 'secretary', 'guard_name' => 'web']);

        $viewReportsPerm = Permission::firstOrCreate(['name' => 'view_reports', 'guard_name' => 'web']);
        $viewFinancialReportsPerm = Permission::firstOrCreate(['name' => 'view_financial_reports', 'guard_name' => 'web']);
        $createPaymentsPerm = Permission::firstOrCreate(['name' => 'create_student_payments', 'guard_name' => 'web']);
        $viewVaultBalancesPerm = Permission::firstOrCreate(['name' => 'view_vault_balances', 'guard_name' => 'web']);

        $adminRole->givePermissionTo([$viewReportsPerm, $viewFinancialReportsPerm, $createPaymentsPerm, $viewVaultBalancesPerm]);

        // 3. Setup Users
        $this->adminUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
            'is_super_admin' => false,
        ]);
        $this->adminUser->assignRole($adminRole);

        // Reporting-only user (can view reports, cannot collect payments)
        $this->reportingUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
            'is_super_admin' => false,
        ]);
        $this->reportingUser->givePermissionTo([$viewReportsPerm, $viewFinancialReportsPerm]);

        // Completely unauthorized user
        $this->unauthorizedUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
            'is_super_admin' => false,
        ]);

        // 4. Setup Vault
        $this->vault = Vault::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'الخزنة الرئيسية',
            'type' => 'cash',
            'opening_balance' => 5000.00,
            'is_active' => true,
        ]);

        // 5. Setup Student & Group & Enrollment
        $studentUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'أحمد إبراهيم',
            'phone' => '01012345678',
        ]);

        $this->student = Student::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $studentUser->id,
            'student_code' => 'STU-999',
            'gender' => 'male',
        ]);

        $group = Group::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->enrollment = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $this->student->id,
            'group_id' => $group->id,
            'enrollment_code' => 'ENR-999',
            'enrollment_date' => now()->startOfMonth()->toDateString(),
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
            'payment_plan' => 'installments',
            'installments_count' => 1,
            'status' => 'active',
        ]);
    }

    /**
     * Test A: Today's Report loads and lists installments due today.
     */
    public function test_a_today_report_displays_installments_due_today(): void
    {
        $this->actingAs($this->adminUser);

        $instToday = Installment::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $this->enrollment->id,
            'installment_number' => 1,
            'amount' => 1000.00,
            'paid_amount' => 0.00,
            'due_date' => now()->format('Y-m-d'),
            'status' => 'pending',
        ]);

        Livewire::test(InstallmentsDueToday::class)
            ->assertSuccessful()
            ->assertSee('أقساط اليوم')
            ->assertSee('أحمد إبراهيم')
            ->assertSee('1,000.00');
    }

    /**
     * Test B: Weekly Report loads with grouping, period stats and filter.
     */
    public function test_b_weekly_report_structure_and_stats(): void
    {
        $this->actingAs($this->adminUser);

        $today = now()->format('Y-m-d');
        $startOfWeek = now()->startOfWeek()->format('Y-m-d');

        Installment::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $this->enrollment->id,
            'installment_number' => 1,
            'amount' => 1000.00,
            'paid_amount' => 0.00,
            'due_date' => $today,
            'status' => 'pending',
        ]);

        Livewire::test(InstallmentsDueWeek::class)
            ->assertSuccessful()
            ->assertSee('تقرير أقساط الأسبوع')
            ->assertSet('installmentFilter', 'all')
            ->assertSee('أحمد إبراهيم')
            ->set('installmentFilter', 'today')
            ->assertSee('أحمد إبراهيم');
    }

    /**
     * Test C: Monthly Report loads with stats and filters.
     */
    public function test_c_monthly_report_structure_and_stats(): void
    {
        $this->actingAs($this->adminUser);

        Installment::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $this->enrollment->id,
            'installment_number' => 1,
            'amount' => 1000.00,
            'paid_amount' => 0.00,
            'due_date' => now()->format('Y-m-d'),
            'status' => 'pending',
        ]);

        Livewire::test(InstallmentsDueMonth::class)
            ->assertSuccessful()
            ->assertSee('تقرير أقساط الشهر')
            ->assertSet('installmentFilter', 'all')
            ->assertSee('أحمد إبراهيم');
    }

    /**
     * Test D: Cross-Report Consistency: installment details match identically in today, week, and month reports.
     */
    public function test_d_cross_report_consistency(): void
    {
        $this->actingAs($this->adminUser);

        $today = now()->format('Y-m-d');

        $installment = Installment::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $this->enrollment->id,
            'installment_number' => 1,
            'amount' => 1000.00,
            'paid_amount' => 400.00,
            'due_date' => $today,
            'status' => 'partial',
        ]);

        // 1. Today's report
        $todayComp = Livewire::test(InstallmentsDueToday::class)->assertSuccessful();
        $this->assertEquals(1, $todayComp->get('installments')->count());
        $inst = $todayComp->get('installments')->first();
        $this->assertEquals(1000.00, (float) $inst->amount);
        $this->assertEquals(400.00, (float) $inst->paid_amount);
        $this->assertEquals(600.00, (float) $inst->getRemainingAmount());
        $this->assertEquals('partial', $inst->status);

        // 2. Weekly report
        $weekComp = Livewire::test(InstallmentsDueWeek::class)->assertSuccessful();
        $weekDays = $weekComp->get('installmentsByDay');
        $this->assertNotEmpty($weekDays);

        // 3. Monthly report
        $monthComp = Livewire::test(InstallmentsDueMonth::class)->assertSuccessful();
        $monthInsts = $monthComp->get('installments');
        $this->assertEquals(1, $monthInsts->count());
        $mInst = $monthInsts->first();
        $this->assertEquals(1000.00, (float) $mInst->amount);
        $this->assertEquals(400.00, (float) $mInst->paid_amount);
        $this->assertEquals(600.00, (float) $mInst->getRemainingAmount());
        $this->assertEquals('partial', $mInst->status);
    }

    /**
     * Test E: Partial Collection via Report UI.
     * Collect 400 EGP on a 1000 EGP installment.
     */
    public function test_e_partial_collection_through_report_action(): void
    {
        $this->actingAs($this->adminUser);

        $initialVaultBalance = $this->vault->getCurrentBalance();

        $installment = Installment::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $this->enrollment->id,
            'installment_number' => 1,
            'amount' => 1000.00,
            'paid_amount' => 0.00,
            'due_date' => now()->format('Y-m-d'),
            'status' => 'pending',
        ]);

        Livewire::test(InstallmentsDueToday::class)
            ->call('openPaymentModal', $installment->id)
            ->assertSet('showPaymentModal', true)
            ->assertSet('paymentAmount', '1000')
            ->set('paymentAmount', 400.00)
            ->set('vaultId', $this->vault->id)
            ->set('paymentNotes', 'سداد دفعة أولى')
            ->call('savePayment')
            ->assertHasNoErrors()
            ->assertSet('showPaymentModal', false);

        // Verify Installment state
        $installment->refresh();
        $this->assertEquals(400.00, (float) $installment->paid_amount);
        $this->assertEquals(600.00, (float) $installment->getRemainingAmount());
        $this->assertEquals('partial', $installment->status);

        // Verify Payment record
        $this->assertDatabaseHas('payments', [
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $this->enrollment->id,
            'vault_id' => $this->vault->id,
            'amount' => 400.00,
            'status' => 'completed',
            'received_by' => $this->adminUser->id,
        ]);

        // Verify Revenue record created by PaymentObserver
        $payment = Payment::where('enrollment_id', $this->enrollment->id)->first();
        $this->assertNotNull($payment);
        $this->assertDatabaseHas('revenues', [
            'payment_id' => $payment->id,
            'vault_id' => $this->vault->id,
            'amount' => 400.00,
            'status' => 'received',
        ]);

        // Verify Vault balance increased by exactly 400 EGP without double counting
        $this->assertEquals($initialVaultBalance + 400.00, $this->vault->getCurrentBalance());

        // Verify Enrollment financial sync
        $this->enrollment->refresh();
        $this->assertEquals(400.00, (float) $this->enrollment->paid_amount);
    }

    /**
     * Test F: Full Collection.
     */
    public function test_f_full_collection_settles_installment_completely(): void
    {
        $this->actingAs($this->adminUser);

        $installment = Installment::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $this->enrollment->id,
            'installment_number' => 1,
            'amount' => 1000.00,
            'paid_amount' => 0.00,
            'due_date' => now()->format('Y-m-d'),
            'status' => 'pending',
        ]);

        Livewire::test(InstallmentsDueToday::class)
            ->call('openPaymentModal', $installment->id)
            ->set('paymentAmount', 1000.00)
            ->set('vaultId', $this->vault->id)
            ->call('savePayment')
            ->assertHasNoErrors();

        $installment->refresh();
        $this->assertEquals(1000.00, (float) $installment->paid_amount);
        $this->assertEquals(0.00, (float) $installment->getRemainingAmount());
        $this->assertEquals('paid', $installment->status);
        $this->assertTrue($installment->isFullyPaid());
    }

    /**
     * Test G: Oldest-First Allocation Rule.
     * Student has an older unpaid installment (last month) and a newer one (today).
     * When paying from the report, official allocation applies money to the older installment first.
     * The report reflects the REAL resulting balances.
     */
    public function test_g_oldest_first_allocation_priority_respected(): void
    {
        $this->actingAs($this->adminUser);

        $this->enrollment->update(['total_amount' => 2000.00, 'installments_count' => 2]);

        // Older installment (due 30 days ago)
        $oldInstallment = Installment::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $this->enrollment->id,
            'installment_number' => 1,
            'amount' => 1000.00,
            'paid_amount' => 0.00,
            'due_date' => now()->subDays(30)->toDateString(),
            'status' => 'overdue',
        ]);

        // Newer installment (due today)
        $newInstallment = Installment::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $this->enrollment->id,
            'installment_number' => 2,
            'amount' => 1000.00,
            'paid_amount' => 0.00,
            'due_date' => now()->format('Y-m-d'),
            'status' => 'pending',
        ]);

        // Employee clicks "سداد" on newInstallment from Today's report and collects 1000 EGP
        Livewire::test(InstallmentsDueToday::class)
            ->call('openPaymentModal', $newInstallment->id)
            ->set('paymentAmount', 1000.00)
            ->set('vaultId', $this->vault->id)
            ->call('savePayment')
            ->assertHasNoErrors();

        $oldInstallment->refresh();
        $newInstallment->refresh();

        // Older installment was satisfied first!
        $this->assertEquals(1000.00, (float) $oldInstallment->paid_amount);
        $this->assertEquals('paid', $oldInstallment->status);

        // Newer installment remains pending (money went to older debt)
        $this->assertEquals(0.00, (float) $newInstallment->paid_amount);
        $this->assertEquals('pending', $newInstallment->status);

        // Today's report reflects the true database state
        $comp = Livewire::test(InstallmentsDueToday::class);
        $displayedInst = $comp->get('installments')->where('id', $newInstallment->id)->first();
        $this->assertEquals(0.00, (float) $displayedInst->paid_amount);
    }

    /**
     * Test H: Duplicate submission prevention.
     */
    public function test_h_duplicate_payment_submission_safely_handled(): void
    {
        $this->actingAs($this->adminUser);

        $installment = Installment::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $this->enrollment->id,
            'installment_number' => 1,
            'amount' => 1000.00,
            'paid_amount' => 0.00,
            'due_date' => now()->format('Y-m-d'),
            'status' => 'pending',
        ]);

        $component = Livewire::test(InstallmentsDueToday::class)
            ->call('openPaymentModal', $installment->id)
            ->set('paymentAmount', 1000.00)
            ->set('vaultId', $this->vault->id)
            ->call('savePayment');

        $this->assertDatabaseCount('payments', 1);

        // Attempting to pay on now fully paid installment will be rejected
        $component->call('openPaymentModal', $installment->id);
        $component->assertSet('showPaymentModal', false);
    }

    /**
     * Test I: Unauthorized payment collection rejected.
     * A user with report-view permission only cannot collect payments.
     */
    public function test_i_reporting_only_user_cannot_collect_payments(): void
    {
        $this->actingAs($this->reportingUser);

        $installment = Installment::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $this->enrollment->id,
            'installment_number' => 1,
            'amount' => 1000.00,
            'paid_amount' => 0.00,
            'due_date' => now()->format('Y-m-d'),
            'status' => 'pending',
        ]);

        // Can view the report page
        Livewire::test(InstallmentsDueToday::class)
            ->assertSuccessful()
            ->assertSee('أقساط اليوم');

        // Cannot open modal
        Livewire::test(InstallmentsDueToday::class)
            ->call('openPaymentModal', $installment->id)
            ->assertSet('showPaymentModal', false);

        // Cannot call savePayment directly (aborted 403)
        Livewire::test(InstallmentsDueToday::class)
            ->set('selectedInstallment', $installment)
            ->set('paymentAmount', 500)
            ->set('vaultId', $this->vault->id)
            ->set('paymentDate', now()->format('Y-m-d'))
            ->call('savePayment')
            ->assertForbidden();

        $this->assertDatabaseCount('payments', 0);
    }

    /**
     * Test J: Cross-tenant / cross-branch isolation.
     */
    public function test_j_cross_tenant_installment_access_denied(): void
    {
        $otherTenant = Tenant::create([
            'name' => 'Other Branch Tenant',
            'domain' => 'other.aischool.test',
        ]);

        $otherGroup = Group::factory()->create(['tenant_id' => $otherTenant->id]);

        $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id, 'name' => 'طالب الفرع الآخر']);
        $otherStudent = Student::create([
            'tenant_id' => $otherTenant->id,
            'user_id' => $otherUser->id,
            'student_code' => 'STU-OTHER',
            'gender' => 'male',
        ]);

        $otherEnrollment = Enrollment::create([
            'tenant_id' => $otherTenant->id,
            'student_id' => $otherStudent->id,
            'group_id' => $otherGroup->id,
            'enrollment_code' => 'ENR-OTHER',
            'enrollment_date' => now()->toDateString(),
            'total_amount' => 1000,
            'paid_amount' => 0,
            'status' => 'active',
        ]);

        $foreignInstallment = Installment::create([
            'tenant_id' => $otherTenant->id,
            'enrollment_id' => $otherEnrollment->id,
            'installment_number' => 1,
            'amount' => 1000.00,
            'paid_amount' => 0.00,
            'due_date' => now()->format('Y-m-d'),
            'status' => 'pending',
        ]);

        $this->actingAs($this->adminUser);

        // Foreign installment does not appear in current tenant report
        $comp = Livewire::test(InstallmentsDueToday::class)->assertSuccessful();
        $this->assertFalse($comp->get('installments')->contains('id', $foreignInstallment->id));
        $comp->assertDontSee('طالب الفرع الآخر');

        // Cannot open payment modal for foreign installment
        Livewire::test(InstallmentsDueToday::class)
            ->call('openPaymentModal', $foreignInstallment->id)
            ->assertSet('showPaymentModal', false);
    }

    /**
     * Test K: Early payment keeps original due date and updates accounting.
     */
    public function test_k_early_payment_preserves_due_date(): void
    {
        $this->actingAs($this->adminUser);

        $futureDate = now()->addMonths(1)->toDateString();

        $installment = Installment::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $this->enrollment->id,
            'installment_number' => 1,
            'amount' => 1000.00,
            'paid_amount' => 0.00,
            'due_date' => $futureDate,
            'status' => 'pending',
        ]);

        Livewire::test(InstallmentsDueMonth::class)
            ->call('openPaymentModal', $installment->id)
            ->set('paymentAmount', 1000.00)
            ->set('vaultId', $this->vault->id)
            ->call('savePayment')
            ->assertHasNoErrors();

        $installment->refresh();
        $this->assertEquals($futureDate, $installment->due_date->format('Y-m-d'));
        $this->assertEquals('paid', $installment->status);
        $this->assertEquals(now()->format('Y-m-d'), $installment->paid_date->format('Y-m-d'));
    }

    /**
     * Test L: Cancelled installments excluded from active reports.
     */
    public function test_l_cancelled_installment_not_shown_or_payable(): void
    {
        $this->actingAs($this->adminUser);

        $cancelledUser = User::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'طالب ملغي']);
        $cancelledStudent = Student::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $cancelledUser->id,
            'student_code' => 'STU-CAN',
            'gender' => 'male',
        ]);
        $group = Group::factory()->create(['tenant_id' => $this->tenant->id]);
        $cancelledEnr = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $cancelledStudent->id,
            'group_id' => $group->id,
            'enrollment_code' => 'ENR-CAN',
            'enrollment_date' => now()->toDateString(),
            'total_amount' => 1000,
            'paid_amount' => 0,
            'status' => 'active',
        ]);

        $cancelledInst = Installment::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $cancelledEnr->id,
            'installment_number' => 1,
            'amount' => 1000.00,
            'paid_amount' => 0.00,
            'due_date' => now()->format('Y-m-d'),
            'status' => 'cancelled',
        ]);

        $comp = Livewire::test(InstallmentsDueToday::class);
        $this->assertFalse($comp->get('installments')->contains('id', $cancelledInst->id));
        $comp->assertDontSee('طالب ملغي');

        $comp->call('openPaymentModal', $cancelledInst->id)
            ->assertSet('showPaymentModal', false);
    }

    /**
     * Test M: Totals and Financial Reconciliation.
     */
    public function test_m_totals_and_financial_reconciliation(): void
    {
        $this->actingAs($this->adminUser);

        $this->enrollment->update(['total_amount' => 2000.00, 'installments_count' => 2]);

        $today = now()->format('Y-m-d');

        Installment::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $this->enrollment->id,
            'installment_number' => 1,
            'amount' => 1000.00,
            'paid_amount' => 400.00,
            'due_date' => $today,
            'status' => 'partial',
        ]);

        Installment::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $this->enrollment->id,
            'installment_number' => 2,
            'amount' => 1000.00,
            'paid_amount' => 1000.00,
            'due_date' => $today,
            'status' => 'paid',
        ]);

        $comp = Livewire::test(InstallmentsDueToday::class)->assertSuccessful();
        $installments = $comp->get('installments');

        $this->assertEquals(2, $installments->count());
        $this->assertEquals(2000.00, (float) $installments->sum('amount'));
        $this->assertEquals(1400.00, (float) $installments->sum('paid_amount'));
        $this->assertEquals(600.00, (float) ($installments->sum('amount') - $installments->sum('paid_amount')));
    }

    /**
     * Test N: Failed payment operation rolls back completely with no partial accounting entries.
     */
    public function test_n_failed_payment_rolls_back_cleanly(): void
    {
        $this->actingAs($this->adminUser);

        $initialVaultBalance = $this->vault->getCurrentBalance();

        $installment = Installment::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $this->enrollment->id,
            'installment_number' => 1,
            'amount' => 1000.00,
            'paid_amount' => 0.00,
            'due_date' => now()->format('Y-m-d'),
            'status' => 'pending',
        ]);

        // Attempting to pay with non-existent vault ID
        Livewire::test(InstallmentsDueToday::class)
            ->call('openPaymentModal', $installment->id)
            ->set('paymentAmount', 500.00)
            ->set('vaultId', 999999)
            ->call('savePayment');

        // Verify zero payments and zero revenues created
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('revenues', 0);

        // Verify installment untouched
        $installment->refresh();
        $this->assertEquals(0.00, (float) $installment->paid_amount);
        $this->assertEquals('pending', $installment->status);

        // Verify vault balance untouched
        $this->assertEquals($initialVaultBalance, $this->vault->getCurrentBalance());
    }
}
