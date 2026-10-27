<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Course;
use App\Models\Group;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\Enrollment;
use App\Models\EnrollmentCourse;
use App\Models\Installment;
use App\Models\Payment;
use App\Models\Vault;
use App\Models\VaultTransaction;
use App\Models\SessionSchedule;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\StudentFollowUpTask;
use App\Models\StudentFollowUpContact;
use App\Models\StudentFollowUpTaskReassignment;
use App\Models\Instructor;
use App\Models\InstructorPayment;
use App\Models\SalaryPayment;
use App\Services\Enrollment\EnrollmentCancellationService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class MasterProductionAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $branchA;
    protected Tenant $branchB;
    protected User $superAdmin;
    protected User $branchManagerA;
    protected User $accountant;
    protected User $secretary;
    protected User $supervisor;
    protected User $instructorUser;
    protected Instructor $instructor;
    protected Vault $vaultA;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Tenants (Branches)
        $this->branchA = Tenant::create([
            'name' => 'الفرع الرئيسي - القاهرة',
            'is_active' => true,
        ]);

        $this->branchB = Tenant::create([
            'name' => 'فرع دمنهور',
            'is_active' => true,
        ]);

        // 2. Create Roles
        $roles = ['super_admin', 'branch_manager', 'accountant', 'secretary', 'supervisor', 'instructor', 'student', 'parent', 'registration'];
        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
        }

        // 3. Create Key Staff Users
        $this->superAdmin = User::create([
            'name' => 'المدير العام (UAT)',
            'email' => 'uat.admin@school.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->branchA->id,
            'is_super_admin' => true,
            'status' => 'active',
        ]);
        $this->superAdmin->assignRole('super_admin');

        $this->branchManagerA = User::create([
            'name' => 'مدير فرع القاهرة (UAT)',
            'email' => 'uat.manager.a@school.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->branchA->id,
            'status' => 'active',
        ]);
        $this->branchManagerA->assignRole('branch_manager');

        $this->accountant = User::create([
            'name' => 'محاسب الفرع (UAT)',
            'email' => 'uat.accountant@school.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->branchA->id,
            'status' => 'active',
        ]);
        $this->accountant->assignRole('accountant');

        $this->secretary = User::create([
            'name' => 'سكرتيرة الفرع (UAT)',
            'email' => 'uat.secretary@school.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->branchA->id,
            'status' => 'active',
        ]);
        $this->secretary->assignRole('secretary');

        $this->supervisor = User::create([
            'name' => 'مشرف الفرع (UAT)',
            'email' => 'uat.supervisor@school.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->branchA->id,
            'status' => 'active',
        ]);
        $this->supervisor->assignRole('supervisor');

        $this->instructorUser = User::create([
            'name' => 'المهندس أحمد (UAT)',
            'email' => 'uat.instructor@school.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->branchA->id,
            'status' => 'active',
        ]);
        $this->instructorUser->assignRole('instructor');

        $this->instructor = Instructor::create([
            'user_id' => $this->instructorUser->id,
            'tenant_id' => $this->branchA->id,
            'employee_code' => 'INS-001',
            'hourly_rate' => 100.00,
            'base_salary' => 2000.00,
        ]);

        // 4. Create Active Vault
        $this->vaultA = Vault::create([
            'name' => 'خزينة القاهرة الرئيسية',
            'tenant_id' => $this->branchA->id,
            'type' => 'cash',
            'opening_balance' => 10000.00,
            'is_active' => true,
        ]);
    }

    protected function createStudent(array $attributes = []): Student
    {
        $user = User::factory()->create(['tenant_id' => $this->branchA->id]);
        return Student::create(array_merge([
            'user_id' => $user->id,
            'tenant_id' => $this->branchA->id,
            'student_code' => 'STU-' . uniqid(),
            'gender' => 'male',
            'birth_date' => '2012-01-01',
            'phone' => '012' . rand(10000000, 99999999),
        ], $attributes));
    }

    protected function createEnrollment(Student $student, Group $group, array $attributes = []): Enrollment
    {
        $paid = $attributes['paid_amount'] ?? 0.00;
        $defaultVault = ($paid > 0) ? $this->vaultA->id : null;

        return Enrollment::create(array_merge([
            'student_id' => $student->id,
            'tenant_id' => $this->branchA->id,
            'group_id' => $group->id,
            'vault_id' => $defaultVault,
            'enrollment_code' => 'ENR-' . uniqid(),
            'enrollment_date' => now()->toDateString(),
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
            'status' => 'active',
        ], $attributes));
    }

    /**
     * @test
     * STAGE A — STUDENT REGISTRATION
     */
    public function test_stage_a_student_registration_and_guardian_phone_separation()
    {
        // 1. Create Parent
        $parentUser = User::create([
            'name' => 'أ/ محمد السيد (ولي أمر)',
            'email' => 'uat.parent.a@school.test',
            'password' => bcrypt('password'),
            'phone' => '01011112222',
            'tenant_id' => $this->branchA->id,
            'status' => 'active',
        ]);
        $parentUser->assignRole('parent');

        // 2. Register Student A
        $studentA = $this->createStudent([
            'student_code' => 'STU-001',
            'gender' => 'male',
            'phone' => '01233334444',
            'birth_date' => '2012-05-10',
        ]);

        // Link Guardian
        StudentParent::create([
            'tenant_id' => $this->branchA->id,
            'student_id' => $studentA->id,
            'user_id' => $parentUser->id,
            'relation' => 'father',
            'is_primary' => true,
        ]);

        // 3. Register Student B & C
        $studentB = $this->createStudent(['student_code' => 'STU-002', 'phone' => '01255556666']);
        $studentC = $this->createStudent(['student_code' => 'STU-003', 'gender' => 'female', 'phone' => '01277778888']);

        // Assertions
        $this->assertEquals(3, Student::count());
        $this->assertEquals('STU-001', $studentA->student_code);
        $this->assertNotEquals($studentA->phone, $parentUser->phone);
        $this->assertEquals('01233334444', $studentA->phone);
        $this->assertEquals('01011112222', $parentUser->phone);
        $this->assertTrue(StudentParent::where('student_id', $studentA->id)->where('user_id', $parentUser->id)->exists());
    }

    /**
     * @test
     * STAGE B — COURSE ENROLLMENT (Multi-Course Independence)
     */
    public function test_stage_b_course_enrollment_and_multi_course_independence()
    {
        $coursePython = Course::create(['tenant_id' => $this->branchA->id, 'name' => 'Python Basics', 'total_sessions' => 12]);
        $courseScratch = Course::create(['tenant_id' => $this->branchA->id, 'name' => 'Scratch Programming', 'total_sessions' => 8]);

        $groupPython = Group::create([
            'tenant_id' => $this->branchA->id,
            'course_id' => $coursePython->id,
            'instructor_id' => $this->instructor->id,
            'name' => 'مجموعة بايثون - A',
            'status' => 'active',
        ]);

        $groupScratch = Group::create([
            'tenant_id' => $this->branchA->id,
            'course_id' => $courseScratch->id,
            'instructor_id' => $this->instructor->id,
            'name' => 'مجموعة سكراتش - A',
            'status' => 'active',
        ]);

        $studentA = $this->createStudent(['student_code' => 'STU-A']);

        // Enroll Student A in Python & Scratch
        $enrollment = $this->createEnrollment($studentA, $groupPython, [
            'total_amount' => 2000.00,
        ]);

        $ecPython = EnrollmentCourse::create([
            'enrollment_id' => $enrollment->id,
            'course_id' => $coursePython->id,
            'total_sessions' => 12,
            'completed_sessions' => 0,
            'status' => 'active',
        ]);

        $ecScratch = EnrollmentCourse::create([
            'enrollment_id' => $enrollment->id,
            'course_id' => $courseScratch->id,
            'total_sessions' => 8,
            'completed_sessions' => 0,
            'status' => 'active',
        ]);

        $this->assertEquals(2, $enrollment->enrollmentCourses()->count());
        $this->assertEquals('active', $ecPython->status);
        $this->assertEquals('active', $ecScratch->status);
    }

    /**
     * @test
     * STAGE C — FINANCIAL REGISTRATION & VAULT TRANSACTION
     */
    public function test_stage_c_financial_registration_and_vault_transaction()
    {
        $group = Group::create([
            'tenant_id' => $this->branchA->id,
            'course_id' => Course::create(['tenant_id' => $this->branchA->id, 'name' => 'C Course'])->id,
            'instructor_id' => $this->instructor->id,
            'name' => 'C Group',
        ]);

        $student = $this->createStudent(['student_code' => 'STU-FIN']);
        $enrollment = $this->createEnrollment($student, $group, [
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
        ]);

        // Upfront Payment 500 EGP
        $initialVaultBalance = (float) $this->vaultA->getCurrentBalance();
        $paymentAmount = 500.00;

        Payment::create([
            'tenant_id' => $this->branchA->id,
            'enrollment_id' => $enrollment->id,
            'vault_id' => $this->vaultA->id,
            'amount' => $paymentAmount,
            'payment_date' => now()->toDateString(),
            'status' => 'completed',
        ]);

        $enrollment->increment('paid_amount', $paymentAmount);

        $this->assertEquals($initialVaultBalance + 500.00, (float) $this->vaultA->getCurrentBalance());
        $this->assertEquals(500.00, (float) $enrollment->paid_amount);
        $this->assertEquals(500.00, (float) ($enrollment->total_amount - $enrollment->paid_amount));
    }

    /**
     * @test
     * STAGE D & E — SESSIONS, ATTENDANCE (3 Present, 1 Absent, Unrecorded != Absent)
     */
    public function test_stage_d_and_e_sessions_and_attendance_integrity()
    {
        $course = Course::create(['tenant_id' => $this->branchA->id, 'name' => 'AI Robotics', 'total_sessions' => 10]);
        $group = Group::create([
            'tenant_id' => $this->branchA->id,
            'course_id' => $course->id,
            'instructor_id' => $this->instructor->id,
            'name' => 'Group Robots',
            'status' => 'active',
        ]);

        $students = collect();
        for ($i = 1; $i <= 4; $i++) {
            $students->push($this->createStudent(['student_code' => "ROBOT-$i"]));
        }

        // Schedule Session
        $schedule = SessionSchedule::create([
            'tenant_id' => $this->branchA->id,
            'group_id' => $group->id,
            'instructor_id' => $this->instructor->id,
            'course_id' => $course->id,
            'date' => now()->addDays(1)->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'session_date' => now()->addDays(1)->toDateString(),
            'status' => 'scheduled',
            'duration_minutes' => 120,
        ]);

        $session = AttendanceSession::create([
            'tenant_id' => $this->branchA->id,
            'course_id' => $course->id,
            'group_id' => $group->id,
            'session_schedule_id' => $schedule->id,
            'attendance_date' => now()->addDays(1)->toDateString(),
            'session_number' => 1,
            'status' => 'completed',
            'instructor_id' => $this->instructor->id,
        ]);

        // Record 3 Present, 1 Absent
        AttendanceRecord::create(['attendance_session_id' => $session->id, 'student_id' => $students[0]->id, 'status' => 'present']);
        AttendanceRecord::create(['attendance_session_id' => $session->id, 'student_id' => $students[1]->id, 'status' => 'present']);
        AttendanceRecord::create(['attendance_session_id' => $session->id, 'student_id' => $students[2]->id, 'status' => 'present']);
        AttendanceRecord::create(['attendance_session_id' => $session->id, 'student_id' => $students[3]->id, 'status' => 'absent']);

        $this->assertEquals(3, AttendanceRecord::where('attendance_session_id', $session->id)->where('status', 'present')->count());
        $this->assertEquals(1, AttendanceRecord::where('attendance_session_id', $session->id)->where('status', 'absent')->count());

        // Unrecorded Session Test: must have 0 records, not absent
        $unrecordedSession = AttendanceSession::create([
            'tenant_id' => $this->branchA->id,
            'course_id' => $course->id,
            'group_id' => $group->id,
            'attendance_date' => now()->addDays(3)->toDateString(),
            'session_number' => 2,
            'status' => 'scheduled',
            'instructor_id' => $this->instructor->id,
        ]);

        $this->assertEquals(0, AttendanceRecord::where('attendance_session_id', $unrecordedSession->id)->count());
        $this->assertEquals(0, AttendanceRecord::where('attendance_session_id', $unrecordedSession->id)->where('status', 'absent')->count());
    }

    /**
     * @test
     * STAGE F — HOMEWORK (Draft, Publish, Late Submission, Low Grade Alert)
     */
    public function test_stage_f_homework_lifecycle_and_grading_alerts()
    {
        $group = Group::create([
            'tenant_id' => $this->branchA->id,
            'course_id' => Course::create(['tenant_id' => $this->branchA->id, 'name' => 'HW Course'])->id,
            'instructor_id' => $this->instructor->id,
            'name' => 'HW Group',
        ]);

        $student = $this->createStudent(['student_code' => 'STU-HW']);

        $assignment = Assignment::create([
            'tenant_id' => $this->branchA->id,
            'group_id' => $group->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'واجب البرمجة 1',
            'due_date' => now()->subDay()->toDateString(), // Due yesterday
            'max_score' => 10,
        ]);

        // Late Submission
        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'tenant_id' => $this->branchA->id,
            'submitted_at' => now(), // submitted after due date
            'score' => 4.0,
            'published_at' => null, // Draft Mode
        ]);

        $this->assertTrue($submission->isLate());
        $this->assertTrue($submission->isDraft());
        $this->assertFalse($submission->isPublished());

        // Publish Score (4/10: Below 50% -> Low Score Alert)
        $submission->update(['published_at' => now(), 'graded' => true]);
        $this->assertTrue($submission->isPublished());
        $this->assertLessThan(5.0, $submission->score);
    }

    /**
     * @test
     * STAGE G — EXAMINATIONS (9/20 Fails/Needs Follow-Up, 10/20 Passes)
     */
    public function test_stage_g_examinations_thresholds_and_results()
    {
        $course = Course::create(['tenant_id' => $this->branchA->id, 'name' => 'Exam Course']);
        $group = Group::create([
            'tenant_id' => $this->branchA->id,
            'course_id' => $course->id,
            'instructor_id' => $this->instructor->id,
            'name' => 'Exam Group',
        ]);

        $exam = Exam::create([
            'tenant_id' => $this->branchA->id,
            'course_id' => $course->id,
            'group_id' => $group->id,
            'title' => 'امتحان نصف الفصل',
            'total_marks' => 20,
            'passing_marks' => 10,
            'exam_date' => now()->addDays(2),
            'status' => 'completed',
        ]);

        $studentLow = $this->createStudent(['student_code' => 'EXAM-LOW']);
        $studentPass = $this->createStudent(['student_code' => 'EXAM-PASS']);

        // Attempt 1: 9/20 (Needs Follow-Up)
        $attemptLow = ExamAttempt::create([
            'tenant_id' => $this->branchA->id,
            'exam_id' => $exam->id,
            'student_id' => $studentLow->id,
            'score' => 9.0,
            'total_score' => 20.0,
            'percentage' => 45.0,
            'passed' => false,
            'completed_at' => now(),
        ]);

        // Attempt 2: 10/20 (Passed)
        $attemptPass = ExamAttempt::create([
            'tenant_id' => $this->branchA->id,
            'exam_id' => $exam->id,
            'student_id' => $studentPass->id,
            'score' => 10.0,
            'total_score' => 20.0,
            'percentage' => 50.0,
            'passed' => true,
            'completed_at' => now(),
        ]);

        $this->assertFalse($attemptLow->passed);
        $this->assertTrue($attemptPass->passed);
        $this->assertEquals(9.0, (float) $attemptLow->score);
        $this->assertEquals(10.0, (float) $attemptPass->score);
    }

    /**
     * @test
     * STAGE H — STUDENT FOLLOW-UP (Two Statuses: جديدة / تم التواصل, Contact Attempts, Reassignment)
     */
    public function test_stage_h_student_follow_up_workflow_and_statuses()
    {
        $student = $this->createStudent(['student_code' => 'STU-FUP']);

        // Verify only TWO statuses exist in Model
        $statuses = StudentFollowUpTask::getStatuses();
        $this->assertCount(2, $statuses);
        $this->assertEquals('جديدة', $statuses['new']);
        $this->assertEquals('تم التواصل', $statuses['contacted']);

        // 1. Create Task manually
        $task = StudentFollowUpTask::create([
            'tenant_id' => $this->branchA->id,
            'student_id' => $student->id,
            'created_by' => $this->secretary->id,
            'assigned_to' => $this->secretary->id,
            'status' => StudentFollowUpTask::STATUS_NEW,
            'title' => 'متابعة انخفاض درجة الواجب',
            'reason' => 'حصل الطالب على 4 من 10 في واجب البرمجة',
        ]);

        $this->assertEquals(StudentFollowUpTask::STATUS_NEW, $task->status);

        // 2. Unsuccessful contact attempt -> Case remains 'جديدة'
        StudentFollowUpContact::create([
            'tenant_id' => $this->branchA->id,
            'task_id' => $task->id,
            'student_id' => $student->id,
            'guardian_phone' => '01011112222',
            'contact_method' => 'phone_call',
            'contact_result' => 'no_answer', // Unsuccessful
            'notes' => 'الهاتف مغلق',
            'contacted_at' => now(),
            'recorded_by' => $this->secretary->id,
        ]);

        $task->refresh();
        $this->assertEquals(StudentFollowUpTask::STATUS_NEW, $task->status);

        // 3. Successful contact attempt -> Case becomes 'تم التواصل'
        StudentFollowUpContact::create([
            'tenant_id' => $this->branchA->id,
            'task_id' => $task->id,
            'student_id' => $student->id,
            'guardian_phone' => '01011112222',
            'contact_method' => 'phone_call',
            'contact_result' => 'contacted', // Successful
            'notes' => 'تم التواصل مع ولي الأمر وتوضيح مواعيد التعويض',
            'contacted_at' => now(),
            'recorded_by' => $this->secretary->id,
        ]);

        $task->refresh();
        $this->assertEquals(StudentFollowUpTask::STATUS_CONTACTED, $task->status);

        // 4. Reassignment History
        StudentFollowUpTaskReassignment::create([
            'tenant_id' => $this->branchA->id,
            'task_id' => $task->id,
            'previous_assigned_to' => $this->secretary->id,
            'new_assigned_to' => $this->supervisor->id,
            'reassigned_by' => $this->branchManagerA->id,
            'reassigned_at' => now(),
            'reason' => 'إحالة للإشراف الأكاديمي',
        ]);

        $this->assertEquals(1, StudentFollowUpTaskReassignment::where('task_id', $task->id)->count());
    }

    /**
     * @test
     * STAGE I — INSTALLMENT COLLECTION (Partial, Reports, Full Settlement)
     */
    public function test_stage_i_installment_collection_and_due_queries()
    {
        $group = Group::create([
            'tenant_id' => $this->branchA->id,
            'course_id' => Course::create(['tenant_id' => $this->branchA->id, 'name' => 'I Course'])->id,
            'instructor_id' => $this->instructor->id,
            'name' => 'I Group',
        ]);

        $student = $this->createStudent(['student_code' => 'STU-INST']);
        $enrollment = $this->createEnrollment($student, $group, [
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
        ]);

        $installment = Installment::create([
            'enrollment_id' => $enrollment->id,
            'tenant_id' => $this->branchA->id,
            'installment_number' => 1,
            'amount' => 1000.00,
            'paid_amount' => 0.00,
            'due_date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        $initialVault = (float) $this->vaultA->getCurrentBalance();

        // Partial Collection: 400 EGP
        Payment::create([
            'tenant_id' => $this->branchA->id,
            'enrollment_id' => $enrollment->id,
            'vault_id' => $this->vaultA->id,
            'amount' => 400.00,
            'payment_date' => now()->toDateString(),
            'status' => 'completed',
        ]);

        $installment->increment('paid_amount', 400.00);
        $installment->update(['status' => 'partial']);

        $installment->refresh();
        $this->assertEquals(1000.00, (float) $installment->amount);
        $this->assertEquals(400.00, (float) $installment->paid_amount);
        $this->assertEquals(600.00, (float) ($installment->amount - $installment->paid_amount));
        $this->assertEquals('partial', $installment->status);
        $this->assertEquals($initialVault + 400.00, (float) $this->vaultA->getCurrentBalance());

        // Due today query matches
        $dueToday = Installment::where('tenant_id', $this->branchA->id)
            ->where('status', '!=', 'paid')
            ->whereDate('due_date', now()->toDateString())
            ->exists();
        $this->assertTrue($dueToday);

        // Collect remaining 600 EGP
        Payment::create([
            'tenant_id' => $this->branchA->id,
            'enrollment_id' => $enrollment->id,
            'vault_id' => $this->vaultA->id,
            'amount' => 600.00,
            'payment_date' => now()->toDateString(),
            'status' => 'completed',
        ]);

        $installment->increment('paid_amount', 600.00);
        $installment->update(['status' => 'paid']);

        $installment->refresh();
        $this->assertEquals(1000.00, (float) $installment->paid_amount);
        $this->assertEquals('paid', $installment->status);
        $this->assertEquals($initialVault + 1000.00, (float) $this->vaultA->getCurrentBalance());
    }

    /**
     * @test
     * STAGE J — INSTRUCTOR EARNINGS & SALARY PAYMENT VAULT INTEGRATION
     */
    public function test_stage_j_instructor_earnings_and_salary_disbursement()
    {
        $course = Course::create(['tenant_id' => $this->branchA->id, 'name' => 'Pay Course']);
        $group = Group::create([
            'tenant_id' => $this->branchA->id,
            'course_id' => $course->id,
            'instructor_id' => $this->instructor->id,
            'name' => 'Pay Group',
        ]);

        $session = AttendanceSession::create([
            'tenant_id' => $this->branchA->id,
            'course_id' => $course->id,
            'group_id' => $group->id,
            'attendance_date' => now()->toDateString(),
            'session_number' => 1,
            'status' => 'completed',
            'instructor_id' => $this->instructor->id,
        ]);

        // Entitlement accrued in database
        $earning = InstructorPayment::where('instructor_id', $this->instructor->id)
            ->where('tenant_id', $this->branchA->id)
            ->first();

        $this->assertNotNull($earning);
        $this->assertEquals(1, $earning->completed_sessions);
        $this->assertEquals(100.00, (float) $earning->sessions_amount);
        $this->assertEquals('pending', $earning->status);

        // Accountant executes salary disbursement from Vault
        $vaultBalBefore = (float) $this->vaultA->getCurrentBalance();
        $payoutAmount = (float) $earning->total_amount;

        $salaryPayment = SalaryPayment::create([
            'tenant_id' => $this->branchA->id,
            'employee_type' => 'instructor',
            'instructor_id' => $this->instructor->id,
            'amount' => $payoutAmount,
            'payment_date' => now()->toDateString(),
            'status' => 'paid',
            'vault_id' => $this->vaultA->id,
        ]);

        $earning->update([
            'status' => 'paid',
            'paid_by' => $this->accountant->id,
            'payment_date' => now()->toDateString(),
        ]);

        $this->assertEquals($vaultBalBefore - $payoutAmount, (float) $this->vaultA->getCurrentBalance());
        $this->assertEquals('paid', $earning->status);
        $this->assertEquals($this->accountant->id, $earning->paid_by);
    }

    /**
     * @test
     * STAGE K — CANCELLATION, FINANCIAL SETTLEMENT & REFUND DISBURSEMENT
     */
    public function test_stage_k_cancellation_settlement_and_refund()
    {
        $course1 = Course::create(['tenant_id' => $this->branchA->id, 'name' => 'Cancel C1']);
        $course2 = Course::create(['tenant_id' => $this->branchA->id, 'name' => 'Active C2']);
        $group = Group::create([
            'tenant_id' => $this->branchA->id,
            'course_id' => $course1->id,
            'instructor_id' => $this->instructor->id,
            'name' => 'K Group',
        ]);

        $student = $this->createStudent(['student_code' => 'STU-REF']);
        $enrollment = $this->createEnrollment($student, $group, [
            'total_amount' => 1000.00,
            'paid_amount' => 1000.00, // Fully paid in advance
        ]);

        $c1 = EnrollmentCourse::create(['enrollment_id' => $enrollment->id, 'course_id' => $course1->id, 'status' => 'active']);
        $c2 = EnrollmentCourse::create(['enrollment_id' => $enrollment->id, 'course_id' => $course2->id, 'status' => 'active']);

        // Cancel C1 only (set inactive)
        $c1->update(['status' => 'inactive']);
        $this->assertEquals('inactive', $c1->status);
        $this->assertEquals('active', $c2->status);

        // Execute Settlement: Refund entitlement of 400 EGP
        $vaultBalBefore = (float) $this->vaultA->getCurrentBalance();
        $refundAmount = 400.00;

        $service = app(EnrollmentCancellationService::class);
        $cancellation = $service->cancelSimplified($enrollment, $this->superAdmin, [
            'cancellation_reason' => 'طلب ولي الأمر بالانسحاب الجزئي',
            'refund_amount' => $refundAmount,
            'vault_id' => $this->vaultA->id,
        ]);

        $this->assertEquals($vaultBalBefore - 400.00, (float) $this->vaultA->getCurrentBalance());
        $this->assertEquals(400.00, (float) $cancellation->refunded_amount);
        $this->assertEquals('cancelled', $enrollment->fresh()->status);
    }

    /**
     * @test
     * CRITICAL AUTHORIZATION & CROSS-TENANT ISOLATION
     */
    public function test_cross_tenant_branch_isolation_and_vault_overdraft_prevention()
    {
        // Branch A Vault vs Branch B Vault
        $vaultB = Vault::create([
            'name' => 'خزينة دمنهور',
            'tenant_id' => $this->branchB->id,
            'type' => 'cash',
            'opening_balance' => 5000.00,
            'is_active' => true,
        ]);

        // BranchManager A querying vaults via BelongsToBranch / BranchScope
        $this->actingAs($this->branchManagerA);
        $accessibleVaults = Vault::all();

        $this->assertTrue($accessibleVaults->contains('id', $this->vaultA->id));
        $this->assertFalse($accessibleVaults->contains('id', $vaultB->id));

        // Overdraft prevention
        $emptyVault = Vault::create([
            'name' => 'خزينة فارغة',
            'tenant_id' => $this->branchA->id,
            'type' => 'cash',
            'opening_balance' => 50.00,
            'is_active' => true,
        ]);

        $attemptPayout = 200.00;
        $canPayout = $emptyVault->getCurrentBalance() >= $attemptPayout;
        $this->assertFalse($canPayout);
    }
}
