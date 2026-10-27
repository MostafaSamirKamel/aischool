<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\Enrollments\Actions\CancelSubscriptionAction;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentCancellation;
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
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SimplifiedEnrollmentCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $adminUser;
    protected User $cancelOnlyUser;
    protected User $refundOfficerUser;
    protected Vault $vault;
    protected EnrollmentCancellationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Group::unsetEventDispatcher();

        $this->tenant = Tenant::factory()->create(['name' => 'Main AI School Branch']);

        // Set up permissions
        $permissions = [
            'Cancel:Enrollment', 'cancel_subscription',
            'Settle:Enrollment', 'settle_subscription',
            'Refund:Payment', 'refund_subscription', 'execute_refunds',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // 1. Super Admin (all permissions)
        $this->adminUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => true,
        ]);

        // 2. Cancellation-only user (can cancel, but cannot execute refunds)
        $this->cancelOnlyUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => false,
        ]);
        $this->cancelOnlyUser->givePermissionTo('cancel_subscription');

        // 3. User with refund & cancel permissions
        $this->refundOfficerUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => false,
        ]);
        $this->refundOfficerUser->givePermissionTo(['cancel_subscription', 'execute_refunds']);

        // Default Vault with 10,000 EGP balance
        $this->vault = Vault::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Main Cash Vault',
            'type' => 'cash',
            'opening_balance' => 10000.00,
            'is_active' => true,
        ]);

        $this->service = app(EnrollmentCancellationService::class);
    }

    protected function createEnrollmentHelper(
        float $totalAmount = 1200.00,
        float $paidAmount = 500.00,
        string $plan = 'full',
        int $installmentsCount = 0
    ): array {
        $studentUser = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $student = Student::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $studentUser->id,
            'student_code' => 'STU-' . uniqid(),
            'gender' => 'male',
            'status' => 'active',
        ]);

        $course = Course::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Python Junior',
            'code' => 'PY-' . uniqid(),
            'price' => $totalAmount,
            'total_sessions' => 12,
            'status' => 'active',
        ]);

        $group = Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $course->id,
            'name' => 'Python Group Alpha',
            'capacity' => 15,
            'max_students' => 20,
            'status' => 'active',
        ]);

        $enrollment = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'vault_id' => $this->vault->id,
            'enrollment_code' => 'ENR-' . uniqid(),
            'enrollment_date' => now(),
            'total_amount' => $totalAmount,
            'paid_amount' => 0.00,
            'discount' => 0.00,
            'payment_plan' => $plan,
            'installments_count' => $installmentsCount,
            'status' => 'active',
        ]);

        $ec = EnrollmentCourse::create([
            'enrollment_id' => $enrollment->id,
            'course_id' => $course->id,
            'group_id' => $group->id,
            'course_price' => $totalAmount,
            'current_session' => 0,
            'total_sessions' => 12,
            'status' => 'active',
        ]);

        // If paidAmount > 0 and plan is full, create a completed payment record
        if ($paidAmount > 0 && $plan === 'full') {
            Payment::create([
                'tenant_id' => $this->tenant->id,
                'enrollment_id' => $enrollment->id,
                'payment_code' => Payment::generatePaymentCode(),
                'amount' => $paidAmount,
                'vault_id' => $this->vault->id,
                'payment_method' => 'cash',
                'payment_date' => now()->toDateString(),
                'status' => 'completed',
                'received_by' => $this->adminUser->id,
            ]);
        }

        return [$student, $course, $group, $enrollment, $ec];
    }

    /**
     * CAN-01: Cancel without refund (Case A).
     * Total: 1200, Paid: 500, Debt: 700. Refund: empty/0.
     * Expected: Canceled, remaining debt waived (0), paid 500 preserved, vault untouched.
     */
    public function test_can_01_cancel_without_refund(): void
    {
        [$student, $course, $group, $enrollment, $ec] = $this->createEnrollmentHelper(1200.00, 500.00);

        $initialVaultBalance = $this->vault->getCurrentBalance();

        $cancellation = $this->service->cancelSimplified($enrollment, $this->cancelOnlyUser, [
            'reason' => 'Student relocated to another city',
            'refund_amount' => null,
        ]);

        $enrollment->refresh();
        $this->assertEquals('cancelled', $enrollment->status);
        $this->assertEquals(0.00, $enrollment->getRemainingBalance());
        $this->assertEquals(700.00, (float) $enrollment->waived_amount);
        $this->assertEquals(500.00, (float) $enrollment->retained_amount);
        $this->assertEquals(0.00, (float) $enrollment->refunded_amount);
        $this->assertEquals('not_applicable', $enrollment->refund_status);
        $this->assertEquals('Student relocated to another city', $enrollment->cancellation_reason);

        // Vault is completely untouched
        $this->assertEquals($initialVaultBalance, $this->vault->getCurrentBalance());
        $this->assertDatabaseMissing('expenses', ['title' => 'استرداد رسوم اشتراك - ' . $student->user->name]);
    }

    /**
     * CAN-02: Cancel with partial refund (Case B).
     * Total: 1200, Paid: 500, Debt: 700. Refund: 200.
     * Expected: Canceled, remaining debt waived (700), paid 500 preserved, 200 refunded from vault. Retained: 300.
     */
    public function test_can_02_cancel_with_partial_refund(): void
    {
        [$student, $course, $group, $enrollment, $ec] = $this->createEnrollmentHelper(1200.00, 500.00);

        $initialVaultBalance = $this->vault->getCurrentBalance();

        $cancellation = $this->service->cancelSimplified($enrollment, $this->refundOfficerUser, [
            'reason' => 'Schedule mismatch, partial refund approved',
            'refund_amount' => 200.00,
            'vault_id' => $this->vault->id,
            'payment_method' => 'cash',
        ]);

        $enrollment->refresh();
        $this->assertEquals('cancelled', $enrollment->status);
        $this->assertEquals(0.00, $enrollment->getRemainingBalance());
        $this->assertEquals(700.00, (float) $enrollment->waived_amount);
        $this->assertEquals(300.00, (float) $enrollment->retained_amount);
        $this->assertEquals(200.00, (float) $enrollment->refunded_amount);
        $this->assertEquals('completed', $enrollment->refund_status);

        // Vault decreased by exactly 200 EGP
        $this->assertEquals(round($initialVaultBalance - 200.00, 2), $this->vault->fresh()->getCurrentBalance());

        // Expense record created
        $this->assertDatabaseHas('expenses', [
            'vault_id' => $this->vault->id,
            'amount' => 200.00,
            'status' => 'paid',
        ]);

        // Payment record with status refunded created
        $this->assertDatabaseHas('payments', [
            'enrollment_id' => $enrollment->id,
            'amount' => 200.00,
            'status' => 'refunded',
        ]);
    }

    /**
     * CAN-03: Cancel with full refund (Case C).
     * Total: 1200, Paid: 500. Refund: 500.
     * Expected: Canceled, debt waived (700), refunded 500 from vault, retained 0.
     */
    public function test_can_03_cancel_with_full_refund(): void
    {
        [$student, $course, $group, $enrollment, $ec] = $this->createEnrollmentHelper(1200.00, 500.00);

        $initialVaultBalance = $this->vault->getCurrentBalance();

        $cancellation = $this->service->cancelSimplified($enrollment, $this->refundOfficerUser, [
            'reason' => 'Course canceled before start',
            'refund_amount' => 500.00,
            'vault_id' => $this->vault->id,
            'payment_method' => 'cash',
        ]);

        $enrollment->refresh();
        $this->assertEquals('cancelled', $enrollment->status);
        $this->assertEquals(0.00, $enrollment->getRemainingBalance());
        $this->assertEquals(700.00, (float) $enrollment->waived_amount);
        $this->assertEquals(0.00, (float) $enrollment->retained_amount);
        $this->assertEquals(500.00, (float) $enrollment->refunded_amount);
        $this->assertEquals('completed', $enrollment->refund_status);

        $this->assertEquals(round($initialVaultBalance - 500.00, 2), $this->vault->fresh()->getCurrentBalance());
    }

    /**
     * CAN-04: Refund exceeds paid amount (Case D).
     * Paid: 500, Requested: 700.
     * Expected: ValidationException thrown, no changes to database.
     */
    public function test_can_04_refund_exceeds_paid_amount_rejected(): void
    {
        [$student, $course, $group, $enrollment, $ec] = $this->createEnrollmentHelper(1200.00, 500.00);

        $this->expectException(ValidationException::class);

        $this->service->cancelSimplified($enrollment, $this->refundOfficerUser, [
            'reason' => 'Excess refund test',
            'refund_amount' => 700.00,
            'vault_id' => $this->vault->id,
        ]);

        $enrollment->refresh();
        $this->assertEquals('active', $enrollment->status);
    }

    /**
     * CAN-05: Refund exceeds vault balance (Case E).
     * Paid: 500, Requested: 300, Vault balance: 100.
     * Expected: ValidationException thrown, entire transaction rolled back.
     */
    public function test_can_05_refund_exceeds_vault_balance_rejected(): void
    {
        [$student, $course, $group, $enrollment, $ec] = $this->createEnrollmentHelper(1200.00, 500.00);

        $poorVault = Vault::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Low Balance Vault',
            'type' => 'cash',
            'opening_balance' => 100.00,
            'is_active' => true,
        ]);

        $this->expectException(ValidationException::class);

        $this->service->cancelSimplified($enrollment, $this->refundOfficerUser, [
            'reason' => 'Insufficient vault balance test',
            'refund_amount' => 300.00,
            'vault_id' => $poorVault->id,
        ]);

        $enrollment->refresh();
        $this->assertEquals('active', $enrollment->status);
        $this->assertEquals(100.00, $poorVault->fresh()->getCurrentBalance());
    }

    /**
     * CAN-06: User lacks cancellation permission.
     * Expected: AuthorizationException.
     */
    public function test_can_06_user_lacks_cancellation_permission_rejected(): void
    {
        [$student, $course, $group, $enrollment, $ec] = $this->createEnrollmentHelper(1200.00, 500.00);

        $unauthorizedUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => false,
        ]);

        $this->expectException(AuthorizationException::class);

        $this->service->cancelSimplified($enrollment, $unauthorizedUser, [
            'reason' => 'Unauthorized cancellation attempt',
        ]);
    }

    /**
     * CAN-07: User lacks refund permission (Case F).
     * Employee has cancel_subscription but lacks execute_refunds.
     * Refund requested: 200.
     * Expected: AuthorizationException thrown.
     */
    public function test_can_07_user_lacks_refund_permission_rejected(): void
    {
        [$student, $course, $group, $enrollment, $ec] = $this->createEnrollmentHelper(1200.00, 500.00);

        $this->expectException(AuthorizationException::class);

        $this->service->cancelSimplified($enrollment, $this->cancelOnlyUser, [
            'reason' => 'Trying to refund without permission',
            'refund_amount' => 200.00,
            'vault_id' => $this->vault->id,
        ]);
    }

    /**
     * CAN-08: Student has multiple enrollments (Case G).
     * Cancel Enrollment 1 only.
     * Expected: Enrollment 1 cancelled, Enrollment 2 remains active and unaffected.
     */
    public function test_can_08_multiple_enrollments_independence(): void
    {
        [$student, $course1, $group1, $enrollment1, $ec1] = $this->createEnrollmentHelper(1200.00, 500.00);

        // Second course & enrollment for same student
        $course2 = Course::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Scratch Programming',
            'code' => 'SCR-' . uniqid(),
            'price' => 800.00,
            'total_sessions' => 8,
            'status' => 'active',
        ]);

        $group2 = Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $course2->id,
            'name' => 'Scratch Group Beta',
            'capacity' => 15,
            'max_students' => 20,
            'status' => 'active',
        ]);

        $enrollment2 = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $student->id,
            'group_id' => $group2->id,
            'enrollment_code' => 'ENR-' . uniqid(),
            'enrollment_date' => now(),
            'total_amount' => 800.00,
            'paid_amount' => 0.00,
            'discount' => 0.00,
            'payment_plan' => 'full',
            'status' => 'active',
        ]);

        $ec2 = EnrollmentCourse::create([
            'enrollment_id' => $enrollment2->id,
            'course_id' => $course2->id,
            'group_id' => $group2->id,
            'course_price' => 800.00,
            'current_session' => 2,
            'total_sessions' => 8,
            'status' => 'active',
        ]);

        // Cancel enrollment 1
        $this->service->cancelSimplified($enrollment1, $this->cancelOnlyUser, [
            'reason' => 'Canceling course 1 only',
        ]);

        $enrollment1->refresh();
        $enrollment2->refresh();
        $ec1->refresh();
        $ec2->refresh();

        $this->assertEquals('cancelled', $enrollment1->status);
        $this->assertEquals('inactive', $ec1->status);

        // Course 2 remains active and untouched
        $this->assertEquals('active', $enrollment2->status);
        $this->assertEquals('active', $ec2->status);
        $this->assertEquals(800.00, $enrollment2->getRemainingBalance(), 'Scratch outstanding balance remains unchanged');
    }

    /**
     * CAN-09: Cancel already canceled enrollment (Prevent duplicate cancellation).
     * Expected: ValidationException thrown.
     */
    public function test_can_09_cancel_already_canceled_enrollment_prevented(): void
    {
        [$student, $course, $group, $enrollment, $ec] = $this->createEnrollmentHelper(1200.00, 500.00);

        // First cancellation
        $this->service->cancelSimplified($enrollment, $this->cancelOnlyUser, [
            'reason' => 'First cancellation',
        ]);

        $this->expectException(ValidationException::class);

        // Attempt second cancellation
        $this->service->cancelSimplified($enrollment, $this->cancelOnlyUser, [
            'reason' => 'Second cancellation attempt',
        ]);
    }

    /**
     * CAN-10: Student belongs to a group -> Active membership updated and freed.
     * Expected: activeStudents() excludes the student after cancellation.
     */
    public function test_can_10_active_group_membership_updated(): void
    {
        [$student, $course, $group, $enrollment, $ec] = $this->createEnrollmentHelper(1200.00, 500.00);

        // Student is active in group
        $this->assertEquals(1, $group->activeStudents()->count());

        // Cancel enrollment
        $this->service->cancelSimplified($enrollment, $this->cancelOnlyUser, [
            'reason' => 'Student leaving group',
        ]);

        // Student is no longer active in group
        $this->assertEquals(0, $group->activeStudents()->count());
    }

    /**
     * CAN-11: Historical attendance exists -> Preserved.
     * Expected: AttendanceRecord records remain untouched.
     */
    public function test_can_11_historical_attendance_preserved(): void
    {
        [$student, $course, $group, $enrollment, $ec] = $this->createEnrollmentHelper(1200.00, 500.00);

        $session = AttendanceSession::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $course->id,
            'group_id' => $group->id,
            'attendance_date' => now()->subDays(5)->toDateString(),
        ]);

        $attendance = AttendanceRecord::create([
            'tenant_id' => $this->tenant->id,
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => 'present',
        ]);

        $this->service->cancelSimplified($enrollment, $this->cancelOnlyUser, [
            'reason' => 'Cancellation after session 1',
        ]);

        $this->assertDatabaseHas('attendance_records', [
            'id' => $attendance->id,
            'student_id' => $student->id,
            'status' => 'present',
        ]);
    }

    /**
     * CAN-12: Outstanding installments exist -> Debt waived correctly.
     * Total: 1200. Installment 1: 400 (paid). Installment 2: 400 (unpaid). Installment 3: 400 (unpaid).
     * Expected: Unpaid installments marked cancelled, remaining balance becomes 0.
     */
    public function test_can_12_outstanding_installments_waived_correctly(): void
    {
        [$student, $course, $group, $enrollment, $ec] = $this->createEnrollmentHelper(1200.00, 400.00, 'installments', 3);

        $inst1 = Installment::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $enrollment->id,
            'installment_number' => 1,
            'amount' => 400.00,
            'paid_amount' => 400.00,
            'due_date' => now()->subMonth(),
            'status' => 'paid',
        ]);

        $inst2 = Installment::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $enrollment->id,
            'installment_number' => 2,
            'amount' => 400.00,
            'paid_amount' => 0.00,
            'due_date' => now()->addDays(10),
            'status' => 'pending',
        ]);

        $inst3 = Installment::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $enrollment->id,
            'installment_number' => 3,
            'amount' => 400.00,
            'paid_amount' => 0.00,
            'due_date' => now()->addMonth(),
            'status' => 'pending',
        ]);

        $this->assertEquals(800.00, $enrollment->getRemainingBalance());

        $this->service->cancelSimplified($enrollment, $this->cancelOnlyUser, [
            'reason' => 'Waiving remaining installments',
        ]);

        $enrollment->refresh();
        $inst1->refresh();
        $inst2->refresh();
        $inst3->refresh();

        $this->assertEquals(0.00, $enrollment->getRemainingBalance());
        $this->assertEquals('paid', $inst1->status);
        $this->assertEquals('cancelled', $inst2->status);
        $this->assertEquals('cancelled', $inst3->status);
        $this->assertEquals(800.00, (float) $enrollment->waived_amount);
    }

    /**
     * CAN-13: Refund field empty -> No refund or vault deduction.
     */
    public function test_can_13_refund_field_empty_means_no_refund(): void
    {
        [$student, $course, $group, $enrollment, $ec] = $this->createEnrollmentHelper(1200.00, 500.00);

        $initialBalance = $this->vault->getCurrentBalance();

        $this->service->cancelSimplified($enrollment, $this->cancelOnlyUser, [
            'reason' => 'Empty refund field test',
            'refund_amount' => '',
        ]);

        $enrollment->refresh();
        $this->assertEquals(0.00, (float) $enrollment->refunded_amount);
        $this->assertEquals($initialBalance, $this->vault->getCurrentBalance());
    }

    /**
     * CAN-14: Double-click Save simulation -> Exactly one cancellation created.
     */
    public function test_can_14_double_click_simulation(): void
    {
        [$student, $course, $group, $enrollment, $ec] = $this->createEnrollmentHelper(1200.00, 500.00);

        // Click 1
        $this->service->cancelSimplified($enrollment, $this->cancelOnlyUser, [
            'reason' => 'First click',
        ]);

        // Click 2 immediate follow-up
        try {
            $this->service->cancelSimplified($enrollment, $this->cancelOnlyUser, [
                'reason' => 'Second click duplicate',
            ]);
        } catch (\Exception $e) {
            // Expected duplicate exception
        }

        $this->assertEquals(1, EnrollmentCancellation::where('enrollment_id', $enrollment->id)->count());
    }

    /**
     * CAN-15: Concurrent refund requests -> No duplicate payout.
     */
    public function test_can_15_concurrent_refund_requests_prevention(): void
    {
        [$student, $course, $group, $enrollment, $ec] = $this->createEnrollmentHelper(1200.00, 500.00);

        $this->service->cancelSimplified($enrollment, $this->refundOfficerUser, [
            'reason' => 'First refund',
            'refund_amount' => 200.00,
            'vault_id' => $this->vault->id,
        ]);

        // Second refund attempt
        try {
            $this->service->cancelSimplified($enrollment, $this->refundOfficerUser, [
                'reason' => 'Second refund attempt',
                'refund_amount' => 200.00,
                'vault_id' => $this->vault->id,
            ]);
        } catch (ValidationException $e) {
            // Expected: already cancelled
        }

        $this->assertEquals(1, Expense::where('vault_id', $this->vault->id)->count());
    }

    /**
     * CAN-16: Transaction fails halfway -> Full rollback.
     */
    public function test_can_16_transaction_fails_halfway_rolls_back(): void
    {
        [$student, $course, $group, $enrollment, $ec] = $this->createEnrollmentHelper(1200.00, 500.00);

        try {
            $this->service->cancelSimplified($enrollment, $this->refundOfficerUser, [
                'reason' => 'Will fail due to invalid vault',
                'refund_amount' => 200.00,
                'vault_id' => 999999, // non-existent vault
            ]);
        } catch (ValidationException $e) {
            // Expected validation failure
        }

        $enrollment->refresh();
        $this->assertEquals('active', $enrollment->status);
        $this->assertEquals(0, EnrollmentCancellation::where('enrollment_id', $enrollment->id)->count());
    }

    /**
     * CAN-17: Another tenant's enrollment -> Unauthorized.
     */
    public function test_can_17_cross_tenant_enrollment_unauthorized(): void
    {
        $otherTenant = Tenant::factory()->create(['name' => 'Other Branch']);
        $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id, 'is_super_admin' => false]);
        $otherUser->givePermissionTo('cancel_subscription');

        [$student, $course, $group, $enrollment, $ec] = $this->createEnrollmentHelper(1200.00, 500.00);

        $this->expectException(AuthorizationException::class);

        $this->service->cancelSimplified($enrollment, $otherUser, [
            'reason' => 'Attempting cross tenant cancel',
        ]);
    }

    /**
     * CAN-18: Cancel after several sessions -> Correct history and balance.
     */
    public function test_can_18_cancel_after_several_sessions(): void
    {
        [$student, $course, $group, $enrollment, $ec] = $this->createEnrollmentHelper(1200.00, 500.00);
        $ec->update(['current_session' => 4]);

        $this->service->cancelSimplified($enrollment, $this->cancelOnlyUser, [
            'reason' => 'Cancelled at session 4',
        ]);

        $enrollment->refresh();
        $ec->refresh();

        $this->assertEquals('cancelled', $enrollment->status);
        $this->assertEquals('inactive', $ec->status);
        $this->assertEquals(4, $ec->current_session);
        $this->assertEquals(0.00, $enrollment->getRemainingBalance());
    }

    /**
     * CAN-19: Student has previous refunds -> Refund maximum calculated correctly.
     * Paid: 500. Previous refund: 100.
     * Max eligible refund should be 400.
     */
    public function test_can_19_student_has_previous_refunds_maximum_calculated_correctly(): void
    {
        [$student, $course, $group, $enrollment, $ec] = $this->createEnrollmentHelper(1200.00, 500.00);
        $enrollment->update(['refunded_amount' => 100.00]);

        $this->expectException(ValidationException::class);

        // Requesting 450 when remaining refundable is only 400 (500 - 100)
        $this->service->cancelSimplified($enrollment, $this->refundOfficerUser, [
            'reason' => 'Exceeds remaining refundable test',
            'refund_amount' => 450.00,
            'vault_id' => $this->vault->id,
        ]);
    }

    /**
     * CAN-20: Dashboard and reports after cancellation -> Accurate statistics.
     */
    public function test_can_20_dashboard_and_reports_accurate_statistics(): void
    {
        [$student, $course, $group, $enrollment, $ec] = $this->createEnrollmentHelper(1200.00, 500.00);

        $this->service->cancelSimplified($enrollment, $this->refundOfficerUser, [
            'reason' => 'Reporting accuracy test',
            'refund_amount' => 200.00,
            'vault_id' => $this->vault->id,
        ]);

        $enrollment->refresh();

        $this->assertEquals('cancelled', $enrollment->status);
        $this->assertEquals(500.00, (float) $enrollment->payments()->where('status', 'completed')->sum('amount'));
        $this->assertEquals(200.00, (float) $enrollment->payments()->where('status', 'refunded')->sum('amount'));
        $this->assertEquals(0.00, $enrollment->getRemainingBalance());
        $this->assertEquals(700.00, (float) $enrollment->waived_amount);
    }

    /**
     * CAN-21: Livewire / Filament action mounting and execution.
     */
    public function test_can_21_filament_livewire_action_execution(): void
    {
        [$student, $course, $group, $enrollment, $ec] = $this->createEnrollmentHelper(1200.00, 500.00);

        \Livewire\Livewire::actingAs($this->adminUser)
            ->test(\App\Filament\Admin\Resources\Enrollments\Pages\ViewEnrollment::class, ['record' => $enrollment->id])
            ->assertActionVisible('cancel_subscription')
            ->callAction('cancel_subscription', [
                'cancellation_reason' => 'Livewire submission test',
                'refund_amount' => null,
            ])
            ->assertHasNoActionErrors();

        $enrollment->refresh();
        $this->assertEquals('cancelled', $enrollment->status);
        $this->assertEquals('Livewire submission test', $enrollment->cancellation_reason);
    }
}
