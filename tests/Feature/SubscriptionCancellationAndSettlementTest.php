<?php

namespace Tests\Feature;

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
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubscriptionCancellationAndSettlementTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $superAdmin;
    protected User $cancelOnlyUser;
    protected User $settleUser;
    protected User $refundUser;
    protected Vault $vault;
    protected EnrollmentCancellationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['name' => 'Main Campus', 'domain' => 'main.ai-school.test']);

        // Setup permissions
        $permissions = [
            'Cancel:Enrollment', 'cancel_subscription',
            'Settle:Enrollment', 'settle_subscription',
            'Refund:Payment', 'refund_subscription', 'execute_refunds',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // 1. Super Admin
        $this->superAdmin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => true,
        ]);

        // 2. Cancel-only Employee (cannot waive debt or disburse refunds)
        $this->cancelOnlyUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => false,
        ]);
        $this->cancelOnlyUser->givePermissionTo('cancel_subscription');

        // 3. Settlement Employee (can cancel and waive debt/adjust settlement)
        $this->settleUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => false,
        ]);
        $this->settleUser->givePermissionTo(['cancel_subscription', 'settle_subscription']);

        // 4. Refund Officer (can disburse refunds)
        $this->refundUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => false,
        ]);
        $this->refundUser->givePermissionTo(['refund_subscription', 'execute_refunds']);

        // Vault
        $this->vault = Vault::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Main Safe',
            'type' => 'cash',
            'opening_balance' => 5000.00,
            'is_active' => true,
        ]);

        $this->service = app(EnrollmentCancellationService::class);
    }

    protected function createStudentWithCourse(string $code = 'STU-001'): array
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $student = Student::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $user->id,
            'student_code' => $code,
            'gender' => 'male',
            'status' => 'active',
        ]);

        $course = Course::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Python Level 1',
            'code' => 'PY-101-' . uniqid(),
            'price' => 1200.00,
            'total_sessions' => 12,
            'status' => 'active',
        ]);

        $group = Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $course->id,
            'name' => 'Group A',
            'capacity' => 20,
            'status' => 'active',
        ]);

        return [$student, $course, $group];
    }

    /**
     * Scenario 1: Student enrolled but has made no payments.
     * Expectation: Cancel without corrupting balances, debt can be kept or waived.
     */
    public function test_scenario_1_cancel_enrollment_with_no_payments(): void
    {
        [$student, $course, $group] = $this->createStudentWithCourse('STU-101');

        $enrollment = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'enrollment_code' => 'ENR-101',
            'enrollment_date' => now(),
            'total_amount' => 1200.00,
            'paid_amount' => 0.00,
            'discount' => 0.00,
            'payment_plan' => 'full',
            'status' => 'active',
        ]);

        $this->assertEquals(1200.00, $enrollment->getRemainingBalance());

        // Cancel with Keep Debt
        $cancellation = $this->service->cancelSubscription($enrollment, $this->cancelOnlyUser, [
            'reason' => 'Student relocated',
            'settlement_type' => 'keep_debt',
        ]);

        $enrollment->refresh();
        $this->assertEquals('cancelled', $enrollment->status);
        $this->assertEquals(1200.00, $enrollment->getRemainingBalance());
        $this->assertEquals(0.00, $enrollment->waived_amount);
        $this->assertEquals(0.00, $enrollment->refundable_amount);
        $this->assertEquals($this->cancelOnlyUser->id, $enrollment->cancelled_by);
        $this->assertEquals('Student relocated', $enrollment->cancellation_reason);
    }

    /**
     * Scenario 2: Student paid advance but has not started the course.
     * Expectation: Full refund settlement records pending refund obligation without touching vault balance until execution.
     */
    public function test_scenario_2_advance_paid_full_refund_settlement_and_disbursement(): void
    {
        [$student, $course, $group] = $this->createStudentWithCourse('STU-102');

        $enrollment = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'vault_id' => $this->vault->id,
            'enrollment_code' => 'ENR-102',
            'enrollment_date' => now(),
            'total_amount' => 1200.00,
            'paid_amount' => 300.00, // advance payment
            'discount' => 0.00,
            'payment_plan' => 'full',
            'status' => 'active',
        ]);

        // Vault includes opening 5000 + 300 upfront = 5300
        $this->assertEquals(5300.00, $this->vault->getCurrentBalance());

        // Cancel with Full Refund
        $cancellation = $this->service->cancelSubscription($enrollment, $this->settleUser, [
            'reason' => 'Course cancelled before start',
            'settlement_type' => 'full_refund',
        ]);

        $enrollment->refresh();
        $this->assertEquals('cancelled', $enrollment->status);
        $this->assertEquals(300.00, (float) $enrollment->refundable_amount);
        $this->assertEquals(0.00, (float) $enrollment->refunded_amount);
        $this->assertEquals('pending', $enrollment->refund_status);

        // CRITICAL: Vault must remain 5300 while refund is pending!
        $this->assertEquals(5300.00, $this->vault->getCurrentBalance());

        // Now authorized refund officer executes refund disbursement
        $result = $this->service->executeRefund($enrollment, $this->refundUser, 300.00, $this->vault->id, 'cash');

        $enrollment->refresh();
        $this->assertEquals('completed', $enrollment->refund_status);
        $this->assertEquals(300.00, (float) $enrollment->refunded_amount);

        // Vault is now 5300 - 300 = 5000
        $this->assertEquals(5000.00, $this->vault->getCurrentBalance());

        // Historical payment of status 'refunded' exists for reporting
        $refundPayment = Payment::where('enrollment_id', $enrollment->id)->where('status', 'refunded')->first();
        $this->assertNotNull($refundPayment);
        $this->assertEquals(300.00, (float) $refundPayment->amount);
    }

    /**
     * Scenario 3: Student attended some sessions before cancellation.
     * Expectation: Correct session-based earned amount and refund/debt calculation.
     */
    public function test_scenario_3_session_based_settlement(): void
    {
        [$student, $course, $group] = $this->createStudentWithCourse('STU-103');

        // Course is 1200 EGP for 12 sessions = 100 EGP / session
        $enrollment = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'vault_id' => $this->vault->id,
            'enrollment_code' => 'ENR-103',
            'enrollment_date' => now(),
            'total_amount' => 1200.00,
            'paid_amount' => 600.00, // student paid 600 EGP
            'discount' => 0.00,
            'payment_plan' => 'full',
            'status' => 'active',
        ]);

        $enrollmentCourse = EnrollmentCourse::create([
            'enrollment_id' => $enrollment->id,
            'course_id' => $course->id,
            'group_id' => $group->id,
            'course_price' => 1200.00,
            'total_sessions' => 12,
            'current_session' => 4, // 4 sessions attended
            'status' => 'active',
        ]);

        // Student attended 4 sessions = 400 EGP earned by school
        // Student paid 600 EGP. Refundable credit = 600 - 400 = 200 EGP
        $preview = $this->service->previewSettlement($enrollment, 'session_based', [
            'chargeable_sessions' => 4,
        ]);

        $this->assertEquals(400.00, $preview['retained_amount']);
        $this->assertEquals(200.00, $preview['refundable_amount']);
        $this->assertEquals(0.00, $preview['remaining_debt']);

        // Execute session-based cancellation
        $this->service->cancelSubscription($enrollment, $this->settleUser, [
            'reason' => 'Student completed 4 sessions and requested settlement',
            'settlement_type' => 'session_based',
            'chargeable_sessions' => 4,
        ]);

        $enrollment->refresh();
        $this->assertEquals('cancelled', $enrollment->status);
        $this->assertEquals(400.00, (float) $enrollment->retained_amount);
        $this->assertEquals(200.00, (float) $enrollment->refundable_amount);
        $this->assertEquals('pending', $enrollment->refund_status);
        $this->assertEquals('inactive', $enrollmentCourse->fresh()->status);
    }

    /**
     * Scenario 4, 5, 6, 7: Installments paid, remaining unpaid, partial, overdue.
     * Expectation: Historical paid installments preserved, unpaid installments handled according to settlement.
     */
    public function test_scenario_4_to_7_installments_history_preserved_and_waived_correctly(): void
    {
        [$student, $course, $group] = $this->createStudentWithCourse('STU-104');

        $enrollment = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'vault_id' => $this->vault->id,
            'enrollment_code' => 'ENR-104',
            'enrollment_date' => now()->subMonths(3),
            'total_amount' => 1200.00,
            'paid_amount' => 400.00,
            'discount' => 0.00,
            'payment_plan' => 'installments',
            'installments_count' => 3,
            'status' => 'active',
        ]);

        // Installment 1: Fully paid (400)
        $inst1 = Installment::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $enrollment->id,
            'installment_number' => 1,
            'amount' => 400.00,
            'paid_amount' => 400.00,
            'due_date' => now()->subMonths(2),
            'paid_date' => now()->subMonths(2),
            'status' => 'paid',
        ]);

        // Installment 2: Overdue (400)
        $inst2 = Installment::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $enrollment->id,
            'installment_number' => 2,
            'amount' => 400.00,
            'paid_amount' => 0.00,
            'due_date' => now()->subDays(10),
            'status' => 'overdue',
        ]);

        // Installment 3: Upcoming Pending (400)
        $inst3 = Installment::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $enrollment->id,
            'installment_number' => 3,
            'amount' => 400.00,
            'paid_amount' => 0.00,
            'due_date' => now()->addMonth(),
            'status' => 'pending',
        ]);

        // Outstanding before cancellation: 800 EGP
        $this->assertEquals(800.00, $enrollment->getRemainingBalance());

        // Cancel with Waive Debt (Option B)
        $this->service->cancelSubscription($enrollment, $this->settleUser, [
            'reason' => 'Financial hardship waiver',
            'settlement_type' => 'waive_debt',
        ]);

        $enrollment->refresh();
        $this->assertEquals('cancelled', $enrollment->status);
        $this->assertEquals(800.00, (float) $enrollment->waived_amount);
        $this->assertEquals(0.00, $enrollment->getRemainingBalance());

        // CRITICAL: Paid installment 1 remains intact with status 'paid' and 400 paid!
        $this->assertEquals('paid', $inst1->fresh()->status);
        $this->assertEquals(400.00, (float) $inst1->fresh()->paid_amount);

        // Unpaid installments 2 and 3 are marked 'cancelled'
        $this->assertEquals('cancelled', $inst2->fresh()->status);
        $this->assertEquals('cancelled', $inst3->fresh()->status);
    }

    /**
     * Scenario 8: Fully paid subscription cancellation.
     * Expectation: Cancel without losing payment receipts or records.
     */
    public function test_scenario_8_fully_paid_subscription_cancellation(): void
    {
        [$student, $course, $group] = $this->createStudentWithCourse('STU-108');

        $enrollment = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'vault_id' => $this->vault->id,
            'enrollment_code' => 'ENR-108',
            'enrollment_date' => now(),
            'total_amount' => 1200.00,
            'paid_amount' => 1200.00,
            'discount' => 0.00,
            'payment_plan' => 'full',
            'status' => 'active',
        ]);

        $payment = Payment::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $enrollment->id,
            'vault_id' => $this->vault->id,
            'payment_code' => 'PAY-108',
            'amount' => 1200.00,
            'payment_method' => 'cash',
            'payment_date' => now(),
            'status' => 'completed',
            'received_by' => $this->cancelOnlyUser->id,
        ]);

        $this->service->cancelSubscription($enrollment, $this->cancelOnlyUser, [
            'reason' => 'Student decided not to proceed',
            'settlement_type' => 'keep_debt',
        ]);

        $enrollment->refresh();
        $this->assertEquals('cancelled', $enrollment->status);

        // Payment record is fully preserved
        $this->assertEquals('completed', $payment->fresh()->status);
        $this->assertEquals(1200.00, (float) $payment->fresh()->amount);
    }

    /**
     * Scenario 9: Multiple courses/subscriptions for one student.
     * Expectation: Canceling one subscription must NOT cancel or affect the other subscription.
     */
    public function test_scenario_9_multiple_subscriptions_independence(): void
    {
        [$student, $courseA, $groupA] = $this->createStudentWithCourse('STU-109');

        $courseB = Course::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Scratch Advanced',
            'code' => 'SC-201',
            'price' => 800.00,
            'total_sessions' => 8,
            'status' => 'active',
        ]);

        $groupB = Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $courseB->id,
            'name' => 'Group B',
            'capacity' => 15,
            'status' => 'active',
        ]);

        // Subscription A
        $enrollmentA = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $student->id,
            'group_id' => $groupA->id,
            'vault_id' => $this->vault->id,
            'enrollment_code' => 'ENR-A',
            'enrollment_date' => now(),
            'total_amount' => 1200.00,
            'paid_amount' => 600.00,
            'status' => 'active',
        ]);

        EnrollmentCourse::create([
            'enrollment_id' => $enrollmentA->id,
            'course_id' => $courseA->id,
            'group_id' => $groupA->id,
            'course_price' => 1200.00,
            'total_sessions' => 12,
            'status' => 'active',
        ]);

        // Subscription B
        $enrollmentB = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $student->id,
            'group_id' => $groupB->id,
            'vault_id' => $this->vault->id,
            'enrollment_code' => 'ENR-B',
            'enrollment_date' => now(),
            'total_amount' => 800.00,
            'paid_amount' => 800.00,
            'status' => 'active',
        ]);

        EnrollmentCourse::create([
            'enrollment_id' => $enrollmentB->id,
            'course_id' => $courseB->id,
            'group_id' => $groupB->id,
            'course_price' => 800.00,
            'total_sessions' => 8,
            'status' => 'active',
        ]);

        // Cancel Subscription A only
        $this->service->cancelSubscription($enrollmentA, $this->cancelOnlyUser, [
            'reason' => 'Cancelled Course A',
            'settlement_type' => 'keep_debt',
        ]);

        $enrollmentA->refresh();
        $enrollmentB->refresh();

        $this->assertEquals('cancelled', $enrollmentA->status);
        $this->assertEquals('active', $enrollmentB->status); // B remains active!
        $this->assertEquals(800.00, (float) $enrollmentB->paid_amount);

        // Group B active students still includes student
        $this->assertTrue($groupB->activeStudents()->where('students.id', $student->id)->exists());
    }

    /**
     * Scenario 10: Student already canceled.
     * Expectation: Duplicate cancellation request rejected with exception.
     */
    public function test_scenario_10_duplicate_cancellation_rejected(): void
    {
        [$student, $course, $group] = $this->createStudentWithCourse('STU-110');

        $enrollment = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'enrollment_code' => 'ENR-110',
            'enrollment_date' => now(),
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
            'status' => 'active',
        ]);

        $this->service->cancelSubscription($enrollment, $this->cancelOnlyUser, [
            'reason' => 'First cancellation',
            'settlement_type' => 'keep_debt',
        ]);

        $this->expectException(ValidationException::class);

        // Second cancellation attempt on already cancelled enrollment must throw exception
        $this->service->cancelSubscription($enrollment, $this->cancelOnlyUser, [
            'reason' => 'Second cancellation',
            'settlement_type' => 'keep_debt',
        ]);
    }

    /**
     * Scenario 11: Employee without settlement permission attempts to waive debt.
     * Expectation: Unauthorized waiver rejected with AuthorizationException.
     */
    public function test_scenario_11_unauthorized_debt_waiver_rejected(): void
    {
        [$student, $course, $group] = $this->createStudentWithCourse('STU-111');

        $enrollment = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'enrollment_code' => 'ENR-111',
            'enrollment_date' => now(),
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
            'status' => 'active',
        ]);

        $this->expectException(AuthorizationException::class);

        // cancelOnlyUser does NOT have settle_subscription permission -> must be rejected
        $this->service->cancelSubscription($enrollment, $this->cancelOnlyUser, [
            'reason' => 'Attempted waiver without permission',
            'settlement_type' => 'waive_debt',
        ]);
    }

    /**
     * Scenario 12: Employee without refund permission attempts refund disbursement.
     * Expectation: Refund execution rejected with AuthorizationException.
     */
    public function test_scenario_12_unauthorized_refund_disbursement_rejected(): void
    {
        [$student, $course, $group] = $this->createStudentWithCourse('STU-112');

        $enrollment = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'vault_id' => $this->vault->id,
            'enrollment_code' => 'ENR-112',
            'enrollment_date' => now(),
            'total_amount' => 1000.00,
            'paid_amount' => 500.00,
            'refundable_amount' => 500.00,
            'refund_status' => 'pending',
            'status' => 'cancelled',
        ]);

        $this->expectException(AuthorizationException::class);

        // cancelOnlyUser lacks refund permission -> must fail
        $this->service->executeRefund($enrollment, $this->cancelOnlyUser, 500.00, $this->vault->id);
    }

    /**
     * Scenario 14: Repeated refund request exceeding refundable balance.
     * Expectation: Duplicate payout prevented.
     */
    public function test_scenario_14_duplicate_refund_payout_prevented(): void
    {
        [$student, $course, $group] = $this->createStudentWithCourse('STU-114');

        $enrollment = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'vault_id' => $this->vault->id,
            'enrollment_code' => 'ENR-114',
            'enrollment_date' => now(),
            'total_amount' => 1000.00,
            'paid_amount' => 400.00,
            'refundable_amount' => 400.00,
            'refunded_amount' => 0.00,
            'refund_status' => 'pending',
            'status' => 'cancelled',
        ]);

        // First payout: 400 EGP
        $this->service->executeRefund($enrollment, $this->refundUser, 400.00, $this->vault->id);

        $enrollment->refresh();
        $this->assertEquals('completed', $enrollment->refund_status);
        $this->assertEquals(400.00, (float) $enrollment->refunded_amount);

        $this->expectException(ValidationException::class);

        // Attempting another payout when already fully refunded must throw ValidationException
        $this->service->executeRefund($enrollment, $this->refundUser, 400.00, $this->vault->id);
    }

    /**
     * Scenario 15: Cross-tenant isolation.
     * Expectation: User from Tenant A cannot cancel an enrollment from Tenant B.
     */
    public function test_scenario_15_cross_tenant_isolation_enforced(): void
    {
        $tenantB = Tenant::create(['name' => 'Branch B', 'domain' => 'b.ai-school.test']);
        $userB = User::factory()->create(['tenant_id' => $tenantB->id]);
        $userB->givePermissionTo('cancel_subscription');

        [$student, $course, $group] = $this->createStudentWithCourse('STU-115');

        $enrollmentA = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'enrollment_code' => 'ENR-115',
            'enrollment_date' => now(),
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
            'status' => 'active',
        ]);

        $this->expectException(AuthorizationException::class);

        // User from Tenant B cannot cancel Enrollment from Tenant A
        $this->service->cancelSubscription($enrollmentA, $userB, [
            'reason' => 'Illegal cross-tenant attempt',
            'settlement_type' => 'keep_debt',
        ]);
    }

    /**
     * Scenario: Partial refund settlement with complete audit trail.
     */
    public function test_scenario_partial_refund_and_audit_trail(): void
    {
        [$student, $course, $group] = $this->createStudentWithCourse('STU-116');

        $enrollment = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'vault_id' => $this->vault->id,
            'enrollment_code' => 'ENR-116',
            'enrollment_date' => now(),
            'total_amount' => 1000.00,
            'paid_amount' => 600.00,
            'discount' => 0.00,
            'status' => 'active',
        ]);

        // Cancel with Partial Refund: refund 250, retain 350, waive 400
        $cancellation = $this->service->cancelSubscription($enrollment, $this->settleUser, [
            'reason' => 'Partial refund agreed upon',
            'settlement_type' => 'partial_refund',
            'refund_amount' => 250.00,
        ]);

        $enrollment->refresh();
        $this->assertEquals(250.00, (float) $enrollment->refundable_amount);
        $this->assertEquals(350.00, (float) $enrollment->retained_amount);
        $this->assertEquals(400.00, (float) $enrollment->waived_amount);
        $this->assertEquals('pending', $enrollment->refund_status);

        // Verify ActivityLog was created for cancellation and debt waiver
        $cancellationLog = \App\Models\ActivityLog::where('event', 'subscription_cancelled')
            ->where('subject_id', $enrollment->id)
            ->first();
        $this->assertNotNull($cancellationLog);
        $this->assertEquals($enrollment->id, $cancellationLog->subject_id);

        $waiverLog = \App\Models\ActivityLog::where('event', 'debt_waived')
            ->where('subject_id', $enrollment->id)
            ->first();
        $this->assertNotNull($waiverLog);

        // Now execute the refund of 250
        $this->service->executeRefund($enrollment, $this->refundUser, 250.00, $this->vault->id, 'cash');

        $refundLog = \App\Models\ActivityLog::where('event', 'refund_executed')
            ->where('subject_id', $enrollment->id)
            ->first();
        $this->assertNotNull($refundLog);
    }

    /**
     * Scenario: Custom financial settlement mathematics.
     */
    public function test_scenario_custom_settlement_mathematics(): void
    {
        [$student, $course, $group] = $this->createStudentWithCourse('STU-117');

        $enrollment = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'vault_id' => $this->vault->id,
            'enrollment_code' => 'ENR-117',
            'enrollment_date' => now(),
            'total_amount' => 1500.00,
            'paid_amount' => 900.00,
            'status' => 'active',
        ]);

        // Outstanding is 600. Paid is 900.
        // Custom: retain 700, refund 200 (700+200=900), waive 400, keep debt 200 (400+200=600).
        $this->service->cancelSubscription($enrollment, $this->settleUser, [
            'reason' => 'Exceptional custom settlement',
            'settlement_type' => 'custom',
            'retained_amount' => 700.00,
            'refundable_amount' => 200.00,
            'waived_amount' => 400.00,
            'remaining_debt' => 200.00,
        ]);

        $enrollment->refresh();
        $this->assertEquals(700.00, (float) $enrollment->retained_amount);
        $this->assertEquals(200.00, (float) $enrollment->refundable_amount);
        $this->assertEquals(400.00, (float) $enrollment->waived_amount);
        $this->assertEquals('pending', $enrollment->refund_status);

        // Check relationship in student profile
        $this->assertTrue($student->canceledEnrollments()->where('enrollments.id', $enrollment->id)->exists());
    }
}
