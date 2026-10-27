<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\Enrollments\Pages\ViewEnrollment;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentCourse;
use App\Models\Expense;
use App\Models\Group;
use App\Models\Installment;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vault;
use App\Services\Enrollment\EnrollmentCancellationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExecuteRefundActionLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $adminUser;
    protected User $refundOfficer;
    protected User $secretaryWithoutRefund;
    protected Vault $vault;
    protected Course $pythonCourse;
    protected Course $scratchCourse;
    protected Student $student;
    protected Enrollment $pythonEnrollment;
    protected Enrollment $scratchEnrollment;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Tenant
        $this->tenant = Tenant::create([
            'name' => 'Main Campus',
            'domain' => 'main.ai-school.test',
        ]);

        // 2. Setup Permissions
        $permissions = [
            'view_any_enrollments', 'ViewAny:Enrollment',
            'view_enrollments', 'View:Enrollment',
            'cancel_subscription', 'Cancel:Enrollment',
            'settle_subscription', 'Settle:Enrollment',
            'execute_refunds', 'refund_subscription', 'Refund:Payment',
            'view_vault_balances',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // Admin User (Full Access)
        $this->adminUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => true,
        ]);

        // Refund Officer (Can view and execute refunds)
        $this->refundOfficer = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => false,
        ]);
        $this->refundOfficer->givePermissionTo([
            'view_any_enrollments', 'ViewAny:Enrollment',
            'view_enrollments', 'View:Enrollment',
            'execute_refunds', 'refund_subscription',
            'view_vault_balances',
        ]);

        // Secretary (Can cancel and view, but CANNOT execute refunds)
        $this->secretaryWithoutRefund = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => false,
        ]);
        $this->secretaryWithoutRefund->givePermissionTo([
            'view_any_enrollments', 'ViewAny:Enrollment',
            'view_enrollments', 'View:Enrollment',
            'cancel_subscription', 'Cancel:Enrollment',
        ]);

        // 3. Setup Vault with 10,000 EGP balance
        $this->vault = Vault::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'خزنة الفرع الرئيسية',
            'type' => 'cash',
            'opening_balance' => 10000.00,
            'is_active' => true,
        ]);

        // 4. Setup Courses
        $this->pythonCourse = Course::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'بايثون المتقدم',
            'price' => 1200.00,
        ]);

        $this->scratchCourse = Course::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'سكراتش للمبتدئين',
            'price' => 800.00,
        ]);

        // 5. Setup Groups
        $pythonGroup = Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $this->pythonCourse->id,
            'name' => 'مجموعة بايثون',
            'status' => 'active',
        ]);

        $scratchGroup = Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $this->scratchCourse->id,
            'name' => 'مجموعة سكراتش',
            'status' => 'active',
        ]);

        // 6. Setup Student
        $studentUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'يوسف محمود',
            'phone' => '01055566677',
        ]);
        $this->student = Student::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $studentUser->id,
            'student_code' => 'STU-REFUND-01',
            'gender' => 'male',
            'status' => 'active',
        ]);

        // 7. Setup Enrollments
        // Python Enrollment (Paid 500 EGP upfront)
        $this->pythonEnrollment = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $this->student->id,
            'group_id' => $pythonGroup->id,
            'vault_id' => $this->vault->id,
            'enrollment_code' => 'ENR-PYTHON-01',
            'enrollment_date' => now()->toDateString(),
            'total_amount' => 1200.00,
            'paid_amount' => 500.00,
            'discount' => 0.00,
            'payment_plan' => 'installments',
            'status' => 'active',
        ]);

        EnrollmentCourse::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $this->pythonEnrollment->id,
            'course_id' => $this->pythonCourse->id,
            'group_id' => $pythonGroup->id,
            'course_price' => 1200.00,
            'total_sessions' => 8,
            'current_session' => 2,
            'status' => 'active',
        ]);

        // Scratch Enrollment (Unrelated course, fully active)
        $this->scratchEnrollment = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $this->student->id,
            'group_id' => $scratchGroup->id,
            'vault_id' => $this->vault->id,
            'enrollment_code' => 'ENR-SCRATCH-01',
            'enrollment_date' => now()->toDateString(),
            'total_amount' => 800.00,
            'paid_amount' => 800.00,
            'discount' => 0.00,
            'payment_plan' => 'full',
            'status' => 'active',
        ]);

        EnrollmentCourse::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $this->scratchEnrollment->id,
            'course_id' => $this->scratchCourse->id,
            'group_id' => $scratchGroup->id,
            'course_price' => 800.00,
            'total_sessions' => 6,
            'current_session' => 1,
            'status' => 'active',
        ]);
    }

    /**
     * Test: Cancel subscription and then execute pending refund via ExecuteRefundAction.
     */
    public function test_two_step_cancel_and_execute_refund_action(): void
    {
        $service = app(EnrollmentCancellationService::class);

        // Step 1: Cancel Python subscription with settlement (Refund Entitlement = 300 EGP, Debt Waived = 700 EGP)
        // Settlement alone does NOT disburse money yet
        $service->cancelSubscription($this->pythonEnrollment, $this->adminUser, [
            'settlement_type' => 'partial_refund',
            'refund_amount' => 300.00,
            'reason' => 'ظروف خاصة ونقل سكن الطالب',
            'execute_refund_now' => false, // Entitlement only!
        ]);

        $this->pythonEnrollment->refresh();
        $this->assertEquals('cancelled', $this->pythonEnrollment->status);
        $this->assertEquals(300.00, (float) $this->pythonEnrollment->refundable_amount);
        $this->assertEquals(0.00, (float) $this->pythonEnrollment->refunded_amount);
        $this->assertEquals('pending', $this->pythonEnrollment->refund_status);

        // Vault is untouched so far
        $initialBalance = (float) $this->vault->getCurrentBalance();
        $this->assertEquals($initialBalance, (float) $this->vault->getCurrentBalance());

        // Step 2: Secretary without refund permission cannot mount refund action
        Livewire::actingAs($this->secretaryWithoutRefund)
            ->test(ViewEnrollment::class, ['record' => $this->pythonEnrollment->id])
            ->assertActionHidden('execute_refund');

        // Step 3: Refund Officer opens ViewEnrollment, mounts execute_refund and executes it
        Livewire::actingAs($this->refundOfficer)
            ->test(ViewEnrollment::class, ['record' => $this->pythonEnrollment->id])
            ->assertActionVisible('execute_refund')
            ->callAction('execute_refund', [
                'amount' => 300.00,
                'vault_id' => $this->vault->id,
                'notes' => 'سند صرف رسمي للطالب',
            ])
            ->assertHasNoActionErrors();

        // Step 4: Verify Database State
        $this->pythonEnrollment->refresh();
        $this->assertEquals(300.00, (float) $this->pythonEnrollment->refunded_amount);
        $this->assertEquals('completed', $this->pythonEnrollment->refund_status);
        $this->assertEquals('completed', $this->pythonEnrollment->settlement_status);

        // Verify Expense record created
        $this->assertDatabaseHas('expenses', [
            'tenant_id' => $this->tenant->id,
            'vault_id' => $this->vault->id,
            'amount' => 300.00,
            'status' => 'paid',
        ]);

        // Verify Vault balance decreased by exactly 300 EGP
        $this->assertEquals($initialBalance - 300.00, (float) $this->vault->fresh()->getCurrentBalance());

        // Step 5: Verify Multi-Course Isolation: Scratch enrollment is 100% unaffected
        $this->scratchEnrollment->refresh();
        $this->assertEquals('active', $this->scratchEnrollment->status);
        $this->assertEquals(800.00, (float) $this->scratchEnrollment->paid_amount);
        $this->assertEquals(0.00, (float) $this->scratchEnrollment->getRemainingBalance());
        $this->assertEquals(0.00, (float) $this->scratchEnrollment->refunded_amount);

        // Step 6: Refund Action is now hidden because pending refund is 0
        Livewire::actingAs($this->refundOfficer)
            ->test(ViewEnrollment::class, ['record' => $this->pythonEnrollment->id])
            ->assertActionHidden('execute_refund');
    }

    /**
     * Test: Insufficient vault balance blocks refund execution.
     */
    public function test_insufficient_vault_balance_blocks_refund(): void
    {
        $service = app(EnrollmentCancellationService::class);

        $service->cancelSubscription($this->pythonEnrollment, $this->adminUser, [
            'settlement_type' => 'full_refund',
            'reason' => 'استرداد كامل',
            'execute_refund_now' => false,
        ]);

        // Create poor vault with only 50 EGP
        $poorVault = Vault::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'خزنة فرعية فارغة',
            'type' => 'cash',
            'opening_balance' => 50.00,
            'is_active' => true,
        ]);

        Livewire::actingAs($this->refundOfficer)
            ->test(ViewEnrollment::class, ['record' => $this->pythonEnrollment->id])
            ->callAction('execute_refund', [
                'amount' => 500.00,
                'vault_id' => $poorVault->id,
            ])
            ->assertHasActionErrors(['vault_id']);

        // Poor vault balance is unchanged
        $this->assertEquals(50.00, (float) $poorVault->getCurrentBalance());
        $this->assertEquals(0.00, (float) $this->pythonEnrollment->fresh()->refunded_amount);
    }
}
