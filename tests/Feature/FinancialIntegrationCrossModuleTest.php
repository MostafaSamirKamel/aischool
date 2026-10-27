<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\InstallmentsDueMonth;
use App\Filament\Admin\Pages\InstallmentsDueToday;
use App\Filament\Admin\Pages\InstallmentsDueWeek;
use App\Filament\Admin\Resources\Enrollments\Pages\ViewEnrollment;
use App\Filament\Admin\Resources\Enrollments\Widgets\EnrollmentInstallmentsWidget;
use App\Filament\Admin\Resources\Payments\Pages\CreatePayment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Installment;
use App\Models\Payment;
use App\Models\Revenue;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vault;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FinancialIntegrationCrossModuleTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $adminUser;
    protected User $readOnlyUser;
    protected Vault $vault;
    protected Student $student;
    protected Course $course;
    protected Group $group;
    protected Enrollment $enrollment;
    protected StudentParent $parent;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Tenant
        $this->tenant = Tenant::create([
            'name' => 'Main Test Campus',
            'domain' => 'campus.aischool.test',
        ]);

        // 2. Setup Permissions & Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $viewReportsPerm = Permission::firstOrCreate(['name' => 'view_reports', 'guard_name' => 'web']);
        $viewFinancialPerm = Permission::firstOrCreate(['name' => 'view_financial_reports', 'guard_name' => 'web']);
        $collectPaymentsPerm = Permission::firstOrCreate(['name' => 'create_student_payments', 'guard_name' => 'web']);
        $viewVaultPerm = Permission::firstOrCreate(['name' => 'view_vault_balances', 'guard_name' => 'web']);

        $adminRole->givePermissionTo([$viewReportsPerm, $viewFinancialPerm, $collectPaymentsPerm, $viewVaultPerm]);

        $this->adminUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
            'is_super_admin' => false,
        ]);
        $this->adminUser->assignRole($adminRole);

        // Read-only user (cannot collect payments)
        $this->readOnlyUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
            'is_super_admin' => false,
        ]);
        $this->readOnlyUser->givePermissionTo([$viewReportsPerm, $viewFinancialPerm]);

        // 3. Setup Vault
        $this->vault = Vault::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'خزنة الفرع الرئيسية',
            'type' => 'cash',
            'opening_balance' => 10000.00,
            'is_active' => true,
        ]);

        // 4. Setup Course & Group
        $this->course = Course::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'دورة بايثون المتقدمة',
            'price' => 2000.00,
        ]);

        $this->group = Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $this->course->id,
            'name' => 'مجموعة بايثون 101',
            'status' => 'active',
        ]);

        // 5. Setup Student & Parent
        $studentUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'عمر إبراهيم (طالب)',
            'phone' => '01122334455',
        ]);
        $this->student = Student::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $studentUser->id,
            'student_code' => 'STU-CROSS-01',
            'gender' => 'male',
            'status' => 'active',
        ]);

        $parentUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'إبراهيم علي (ولي أمر)',
            'phone' => '01099887766',
        ]);
        $this->parent = StudentParent::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $parentUser->id,
            'student_id' => $this->student->id,
            'relation' => 'father',
        ]);

        // 6. Setup Enrollment with 2 Installments of 1,000 EGP each
        $this->enrollment = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'enrollment_code' => 'ENR-2026-CROSS',
            'enrollment_date' => now()->toDateString(),
            'total_amount' => 2000.00,
            'paid_amount' => 0.00,
            'discount' => 0.00,
            'payment_plan' => 'installments',
            'installments_count' => 2,
            'status' => 'active',
        ]);

        // Installment #1: Due Today (1,000 EGP)
        Installment::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $this->enrollment->id,
            'installment_number' => 1,
            'amount' => 1000.00,
            'paid_amount' => 0.00,
            'due_date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        // Installment #2: Due in 30 days (1,000 EGP)
        Installment::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $this->enrollment->id,
            'installment_number' => 2,
            'amount' => 1000.00,
            'paid_amount' => 0.00,
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => 'pending',
        ]);
    }

    /**
     * Cross-Module Acceptance Test:
     * Full scenario executing steps 1 through 20 as required by prompt Section 8.
     */
    public function test_complete_cross_module_financial_workflow(): void
    {
        $this->actingAs($this->adminUser);

        // Step 6: Verify student financial profile / enrollment view
        $widget = new EnrollmentInstallmentsWidget();
        $widget->record = $this->enrollment;
        $stats = $widget->getStats();
        $this->assertEquals(2, $stats['total_count']);
        $this->assertEquals(2000.00, (float) $stats['total_amount']);
        $this->assertEquals(0.00, (float) $stats['total_paid']);
        $this->assertEquals(2000.00, (float) $stats['total_remaining']);

        // Step 7: Open daily, weekly, and monthly installment reports
        $todayComp = Livewire::test(InstallmentsDueToday::class)->assertSuccessful();
        $this->assertEquals(1, $todayComp->get('installments')->count());

        $weekComp = Livewire::test(InstallmentsDueWeek::class)->assertSuccessful();
        $monthComp = Livewire::test(InstallmentsDueMonth::class)->assertSuccessful();

        // Step 8: Collect partial installment payment (400 EGP) through report UI
        $inst1 = Installment::where('enrollment_id', $this->enrollment->id)->where('installment_number', 1)->first();
        $todayComp->call('openPaymentModal', $inst1->id)
            ->set('paymentAmount', 400.00)
            ->set('vaultId', $this->vault->id)
            ->set('paymentNotes', 'دفعة جزئية من تقرير اليوم')
            ->call('savePayment')
            ->assertHasNoErrors();

        // Step 9 & 10: Verify official Payment record & allocation
        $payment1 = Payment::where('enrollment_id', $this->enrollment->id)->first();
        $this->assertNotNull($payment1);
        $this->assertEquals(400.00, (float) $payment1->amount);
        $this->assertEquals('completed', $payment1->status);

        $inst1->refresh();
        $this->assertEquals(400.00, (float) $inst1->paid_amount);
        $this->assertEquals(600.00, (float) $inst1->getRemainingAmount());
        $this->assertEquals('partial', $inst1->status);

        // Step 11: Verify enrollment outstanding balance
        $this->enrollment->refresh();
        $this->assertEquals(400.00, (float) $this->enrollment->paid_amount);
        $this->assertEquals(1600.00, (float) $this->enrollment->getRemainingBalance());

        // Step 12: Verify vault accounting
        $this->assertEquals(10400.00, (float) $this->vault->getCurrentBalance());
        $revenue1 = Revenue::where('payment_id', $payment1->id)->first();
        $this->assertNotNull($revenue1);
        $this->assertEquals(400.00, (float) $revenue1->amount);

        // Step 13 & 14: Confirm transaction appears in student financial profile
        $widgetStatsAfter = $widget->getStats();
        $this->assertEquals(400.00, (float) $widgetStatsAfter['total_paid']);
        $this->assertEquals(1600.00, (float) $widgetStatsAfter['total_remaining']);

        // Step 15: Collect another authorized payment (600 EGP to complete Installment 1) via Payment model / Primary collection
        $payment2 = Payment::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->tenant->id,
            'enrollment_id' => $this->enrollment->id,
            'vault_id' => $this->vault->id,
            'amount' => 600.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'status' => 'completed',
            'received_by' => $this->adminUser->id,
            'payment_code' => Payment::generatePaymentCode(),
        ]);

        // Step 16 & 17: Reopen all applicable reports and verify remaining balances and statuses
        $inst1->refresh();
        $this->assertEquals(1000.00, (float) $inst1->paid_amount);
        $this->assertEquals(0.00, (float) $inst1->getRemainingAmount());
        $this->assertEquals('paid', $inst1->status);

        $this->enrollment->refresh();
        $this->assertEquals(1000.00, (float) $this->enrollment->paid_amount);
        $this->assertEquals(1000.00, (float) $this->enrollment->getRemainingBalance());

        // Check vault balance (10000 + 400 + 600 = 11000)
        $this->assertEquals(11000.00, (float) $this->vault->getCurrentBalance());

        // Check widget sync
        $widgetStatsFinal = $widget->getStats();
        $this->assertEquals(1000.00, (float) $widgetStatsFinal['total_paid']);
        $this->assertEquals(1000.00, (float) $widgetStatsFinal['total_remaining']);

        // Step 18 & 19: Attempt unauthorized collection as read-only user
        $this->actingAs($this->readOnlyUser);
        $inst2 = Installment::where('enrollment_id', $this->enrollment->id)->where('installment_number', 2)->first();
        
        // Cannot open payment modal
        Livewire::test(InstallmentsDueToday::class)
            ->call('openPaymentModal', $inst2->id)
            ->assertSet('showPaymentModal', false);

        // Cannot bypass modal to execute savePayment directly
        Livewire::test(InstallmentsDueToday::class)
            ->set('selectedInstallment', $inst2)
            ->set('paymentAmount', 500)
            ->set('vaultId', $this->vault->id)
            ->set('paymentDate', now()->format('Y-m-d'))
            ->call('savePayment')
            ->assertForbidden();

        // Verify zero database mutation occurred on unauthorized attempt
        $this->assertEquals(2, Payment::count());
        $this->assertEquals(11000.00, (float) $this->vault->getCurrentBalance());

        // Step 20: Reconcile all effective installment balances and financial records
        $totalOriginalInstallments = Installment::where('enrollment_id', $this->enrollment->id)->sum('amount');
        $totalPaidInstallments = Installment::where('enrollment_id', $this->enrollment->id)->sum('paid_amount');
        $totalRemainingInstallments = Installment::where('enrollment_id', $this->enrollment->id)->get()->sum(fn($i) => $i->getRemainingAmount());
        $totalPaymentsSum = Payment::where('enrollment_id', $this->enrollment->id)->where('status', 'completed')->sum('amount');

        $this->assertEquals(2000.00, (float) $totalOriginalInstallments);
        $this->assertEquals(1000.00, (float) $totalPaidInstallments);
        $this->assertEquals(1000.00, (float) $totalRemainingInstallments);
        $this->assertEquals($totalPaidInstallments, $totalPaymentsSum);
        $this->assertEquals($this->enrollment->paid_amount, $totalPaidInstallments);
    }
}
