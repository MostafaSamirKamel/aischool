<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentCourse;
use App\Models\Group;
use App\Models\Instructor;
use App\Models\InstructorPayment;
use App\Models\SalaryPayment;
use App\Models\SessionSchedule;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vault;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SessionAttendancePayrollIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Tenant $otherTenant;
    protected User $adminUser;
    protected User $instructorUser;
    protected Instructor $instructor;
    protected Course $pythonCourse;
    protected Course $scratchCourse;
    protected Classroom $classroomA;
    protected Classroom $classroomB;
    protected Group $pythonGroup;
    protected Group $scratchGroup;
    protected Vault $vault;
    protected array $students = [];

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Tenants
        $this->tenant = Tenant::create(['name' => 'Main Campus', 'domain' => 'main.ai-school.test']);
        $this->otherTenant = Tenant::create(['name' => 'Alex Branch', 'domain' => 'alex.ai-school.test']);

        // 2. Setup Permissions
        $permissions = [
            'view_attendance_sessions', 'manage_attendance',
            'view_session_schedules', 'manage_session_schedules',
            'view_instructor_payments', 'manage_instructor_payments',
            'view_salary_payments', 'manage_salary_payments',
            'view_vault_balances', 'view_vault_transactions',
        ];
        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // 3. Setup Users
        $this->adminUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => true,
        ]);

        $this->instructorUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'م. أحمد خالد',
            'email' => 'ahmed.khaled@ai-school.test',
        ]);

        $this->instructor = Instructor::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->instructorUser->id,
            'employee_code' => 'INS-001',
            'hourly_rate' => 150.00,
            'base_salary' => 2000.00,
            'status' => 'active',
        ]);

        // 4. Setup Classrooms
        $this->classroomA = Classroom::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'قاعة الخوارزمي',
            'code' => 'LAB-A',
            'capacity' => 20,
            'is_active' => true,
        ]);

        $this->classroomB = Classroom::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'معمل ابن الهيثم',
            'code' => 'LAB-B',
            'capacity' => 15,
            'is_active' => true,
        ]);

        // 5. Setup Vault
        $this->vault = Vault::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'خزنة الفرع الرئيسية',
            'type' => 'cash',
            'opening_balance' => 15000.00,
            'is_active' => true,
        ]);

        // 6. Setup Courses
        $this->pythonCourse = Course::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'بايثون للذكاء الاصطناعي',
            'price' => 1200.00,
            'sessions_count' => 8,
        ]);

        $this->scratchCourse = Course::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'سكراتش للأطفال',
            'price' => 800.00,
            'sessions_count' => 6,
        ]);

        // 7. Setup Groups
        $this->pythonGroup = Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $this->pythonCourse->id,
            'instructor_id' => $this->instructor->id,
            'classroom_id' => $this->classroomA->id,
            'name' => 'مجموعة بايثون المتقدمة',
            'status' => 'active',
            'total_sessions' => 8,
            'completed_sessions' => 0,
        ]);

        $this->scratchGroup = Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $this->scratchCourse->id,
            'instructor_id' => $this->instructor->id,
            'classroom_id' => $this->classroomB->id,
            'name' => 'مجموعة سكراتش - أطفال',
            'status' => 'active',
            'total_sessions' => 6,
            'completed_sessions' => 0,
        ]);

        // 8. Setup 4 Synthetic Students with Enrollments in Python Group
        for ($i = 1; $i <= 4; $i++) {
            $studentUser = User::factory()->create([
                'tenant_id' => $this->tenant->id,
                'name' => "طالب تجريبي {$i}",
                'phone' => "0100000000{$i}",
            ]);

            $student = Student::create([
                'tenant_id' => $this->tenant->id,
                'user_id' => $studentUser->id,
                'student_code' => "STU-E2E-0{$i}",
                'gender' => 'male',
                'status' => 'active',
            ]);

            $enrollment = Enrollment::create([
                'tenant_id' => $this->tenant->id,
                'student_id' => $student->id,
                'group_id' => $this->pythonGroup->id,
                'vault_id' => $this->vault->id,
                'enrollment_code' => "ENR-P-0{$i}",
                'enrollment_date' => now()->toDateString(),
                'total_amount' => 1200.00,
                'paid_amount' => 1200.00,
                'payment_plan' => 'full',
                'status' => 'active',
            ]);

            EnrollmentCourse::create([
                'tenant_id' => $this->tenant->id,
                'enrollment_id' => $enrollment->id,
                'course_id' => $this->pythonCourse->id,
                'group_id' => $this->pythonGroup->id,
                'course_price' => 1200.00,
                'total_sessions' => 8,
                'current_session' => 0,
                'status' => 'active',
            ]);

            $this->students[$i] = $student;
        }

        $this->actingAs($this->adminUser);
    }

    /**
     * Phase 1 — Scenario A: Normal Session Creation
     */
    public function test_scenario_a_normal_session_creation(): void
    {
        $session = SessionSchedule::create([
            'tenant_id' => $this->tenant->id,
            'group_id' => $this->pythonGroup->id,
            'instructor_id' => $this->instructor->id,
            'course_id' => $this->pythonCourse->id,
            'classroom_id' => $this->classroomA->id,
            'date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'title' => 'السيشن 1: مقدمة في بايثون',
            'type' => 'regular',
            'status' => 'scheduled',
        ]);

        $this->assertDatabaseHas('session_schedules', [
            'id' => $session->id,
            'group_id' => $this->pythonGroup->id,
            'instructor_id' => $this->instructor->id,
            'course_id' => $this->pythonCourse->id,
            'classroom_id' => $this->classroomA->id,
            'duration_minutes' => 120,
            'status' => 'scheduled',
        ]);

        $this->assertEquals(120, $session->duration_minutes);
        $this->assertFalse($session->has_conflicts);
    }

    /**
     * Phase 1 — Scenario B: Session Postponement & Rescheduling
     */
    public function test_scenario_b_session_postponement_and_rescheduling(): void
    {
        $original = SessionSchedule::create([
            'tenant_id' => $this->tenant->id,
            'group_id' => $this->pythonGroup->id,
            'instructor_id' => $this->instructor->id,
            'course_id' => $this->pythonCourse->id,
            'classroom_id' => $this->classroomA->id,
            'date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'title' => 'سيشن يتم تأجيله',
            'type' => 'regular',
            'status' => 'scheduled',
        ]);

        $newDate = now()->addDays(3)->toDateString();
        $makeup = $original->reschedule($newDate, '14:00:00', '16:00:00', $this->adminUser->id, 'عذر طارئ للمدرب');

        $original->refresh();
        $this->assertEquals('rescheduled', $original->status);
        $this->assertEquals('عذر طارئ للمدرب', $original->cancellation_reason);
        $this->assertNotNull($original->rescheduled_at);

        $this->assertEquals('makeup', $makeup->type);
        $this->assertEquals('scheduled', $makeup->status);
        $this->assertEquals($original->id, $makeup->original_session_id);
        $this->assertEquals($newDate, $makeup->date->toDateString());

        // Zero completed session progress added
        $this->assertEquals(0, $this->pythonGroup->fresh()->completed_sessions);
    }

    /**
     * Phase 1 — Scenario C: Makeup Session Execution
     */
    public function test_scenario_c_makeup_session_execution(): void
    {
        $original = SessionSchedule::create([
            'tenant_id' => $this->tenant->id,
            'group_id' => $this->pythonGroup->id,
            'instructor_id' => $this->instructor->id,
            'course_id' => $this->pythonCourse->id,
            'date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'status' => 'scheduled',
        ]);

        $makeup = $original->reschedule(now()->addDay()->toDateString(), '10:00:00', '12:00:00', $this->adminUser->id, 'تعويض');
        $makeup->markCompleted();

        $this->assertEquals('completed', $makeup->fresh()->status);
        $this->assertEquals('rescheduled', $original->fresh()->status);
    }

    /**
     * Phase 1 — Scenario D: Session Cancellation
     */
    public function test_scenario_d_session_cancellation(): void
    {
        $session = SessionSchedule::create([
            'tenant_id' => $this->tenant->id,
            'group_id' => $this->pythonGroup->id,
            'instructor_id' => $this->instructor->id,
            'course_id' => $this->pythonCourse->id,
            'date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'status' => 'scheduled',
        ]);

        $session->cancel($this->adminUser->id, 'إجازة رسمية غير مجدولة');

        $this->assertEquals('cancelled', $session->fresh()->status);
        $this->assertEquals('إجازة رسمية غير مجدولة', $session->fresh()->cancellation_reason);
        $this->assertNotNull($session->fresh()->cancelled_at);
        $this->assertEquals(0, $this->pythonGroup->fresh()->completed_sessions);
    }

    /**
     * Phase 1 — Scenario E: Scheduling Conflicts Detection
     */
    public function test_scenario_e_scheduling_conflicts_detection(): void
    {
        // First session: Instructor Ahmed in Classroom A from 10:00 to 12:00
        SessionSchedule::create([
            'tenant_id' => $this->tenant->id,
            'group_id' => $this->pythonGroup->id,
            'instructor_id' => $this->instructor->id,
            'course_id' => $this->pythonCourse->id,
            'classroom_id' => $this->classroomA->id,
            'date' => '2026-11-01',
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'status' => 'scheduled',
        ]);

        // Second session: Same instructor overlapping from 11:00 to 13:00 in Classroom B
        $conflictingSession = SessionSchedule::create([
            'tenant_id' => $this->tenant->id,
            'group_id' => $this->scratchGroup->id,
            'instructor_id' => $this->instructor->id,
            'course_id' => $this->scratchCourse->id,
            'classroom_id' => $this->classroomB->id,
            'date' => '2026-11-01',
            'start_time' => '11:00:00',
            'end_time' => '13:00:00',
            'status' => 'scheduled',
        ]);

        $this->assertTrue($conflictingSession->has_conflicts);
        $this->assertArrayHasKey('instructor', $conflictingSession->conflicts_data);
    }

    /**
     * Phase 2 — Complete Attendance Workflow (3 Present, 1 Absent)
     */
    public function test_attendance_workflow_three_present_one_absent(): void
    {
        $this->actingAs($this->instructorUser);

        // Create AttendanceSession for group on today's date
        $attendanceSession = AttendanceSession::create([
            'tenant_id' => $this->tenant->id,
            'group_id' => $this->pythonGroup->id,
            'course_id' => $this->pythonCourse->id,
            'attendance_date' => now()->toDateString(),
            'recorded_by' => $this->instructorUser->id,
            'session_notes' => 'حضور الجلسة الأولى - تم تدريس الدوال والـ Loops',
        ]);

        // Record: Students 1, 2, 3 Present, Student 4 Absent
        for ($i = 1; $i <= 3; $i++) {
            AttendanceRecord::create([
                'attendance_session_id' => $attendanceSession->id,
                'student_id' => $this->students[$i]->id,
                'status' => 'present',
                'check_in_time' => '10:05',
            ]);
        }

        AttendanceRecord::create([
            'attendance_session_id' => $attendanceSession->id,
            'student_id' => $this->students[4]->id,
            'status' => 'absent',
            'notes' => 'غياب بدون إذن مسبق',
        ]);

        $attendanceSession->refresh();
        $this->assertEquals(4, $attendanceSession->total_students);
        $this->assertEquals(3, $attendanceSession->present_count);
        $this->assertEquals(1, $attendanceSession->absent_count);
        $this->assertEquals(0, $attendanceSession->late_count);
        $this->assertEquals(75.00, $attendanceSession->attendance_rate);

        // Group completed sessions count is updated to 1
        $this->assertEquals(1, $this->pythonGroup->fresh()->completed_sessions);
    }

    /**
     * Phase 2 — Critical Rule: Unrecorded attendance does NOT mean absent
     */
    public function test_unrecorded_attendance_does_not_mean_absent(): void
    {
        // Scheduled session exists
        $schedule = SessionSchedule::create([
            'tenant_id' => $this->tenant->id,
            'group_id' => $this->pythonGroup->id,
            'instructor_id' => $this->instructor->id,
            'course_id' => $this->pythonCourse->id,
            'date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'status' => 'scheduled',
            'attendance_taken' => false,
        ]);

        // Student 1 has 0 attendance records
        $this->assertEquals(0, AttendanceRecord::where('student_id', $this->students[1]->id)->count());

        // Absence count is strictly 0 (not unrecorded)
        $absentCount = AttendanceRecord::where('student_id', $this->students[1]->id)
            ->where('status', 'absent')
            ->count();
        $this->assertEquals(0, $absentCount);
    }

    /**
     * Phase 2 — Attendance Modification and Audit
     */
    public function test_attendance_modification(): void
    {
        $attendanceSession = AttendanceSession::create([
            'tenant_id' => $this->tenant->id,
            'group_id' => $this->pythonGroup->id,
            'course_id' => $this->pythonCourse->id,
            'attendance_date' => now()->toDateString(),
            'recorded_by' => $this->instructorUser->id,
        ]);

        $record = AttendanceRecord::create([
            'attendance_session_id' => $attendanceSession->id,
            'student_id' => $this->students[4]->id,
            'status' => 'absent',
        ]);

        $this->assertEquals('absent', $record->status);

        // Correction: Student arrived late with excuse
        $record->update([
            'status' => 'late',
            'check_in_time' => '10:45',
            'notes' => 'حضر متأخراً بعذر الازدحام المروري',
        ]);

        $record->refresh();
        $this->assertEquals('late', $record->status);
        $this->assertEquals('حضر متأخراً بعذر الازدحام المروري', $record->notes);
        $this->assertEquals(1, $attendanceSession->fresh()->late_count);
    }

    /**
     * Phase 3 — Student Progress and Course Auto-Completion
     */
    public function test_student_progress_and_course_auto_completion(): void
    {
        $student = $this->students[1];
        $enrollmentCourse = EnrollmentCourse::where('course_id', $this->pythonCourse->id)
            ->whereHas('enrollment', fn($q) => $q->where('student_id', $student->id))
            ->first();

        $this->assertEquals(0, $enrollmentCourse->current_session);
        $this->assertEquals('active', $enrollmentCourse->status);

        // Complete 8 sessions for python group
        for ($i = 1; $i <= 8; $i++) {
            $sess = AttendanceSession::create([
                'tenant_id' => $this->tenant->id,
                'group_id' => $this->pythonGroup->id,
                'course_id' => $this->pythonCourse->id,
                'attendance_date' => now()->subDays(10 - $i)->toDateString(),
                'recorded_by' => $this->instructorUser->id,
            ]);

            AttendanceRecord::create([
                'attendance_session_id' => $sess->id,
                'student_id' => $student->id,
                'status' => 'present',
            ]);
        }

        $enrollmentCourse->refresh();
        $this->assertEquals(8, $enrollmentCourse->current_session);
        $this->assertEquals('completed', $enrollmentCourse->status);
    }

    /**
     * Phase 3 — Multi-Course Progress Independence
     */
    public function test_multi_course_progress_independence(): void
    {
        $student = $this->students[1];

        // Enroll same student in Scratch Course as well
        $scratchEnrollment = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $student->id,
            'group_id' => $this->scratchGroup->id,
            'vault_id' => $this->vault->id,
            'enrollment_code' => 'ENR-S-MULTI',
            'enrollment_date' => now()->toDateString(),
            'total_amount' => 800.00,
            'paid_amount' => 800.00,
            'status' => 'active',
        ]);

        $scratchEC = EnrollmentCourse::create([
            'tenant_id' => $this->tenant->id,
            'enrollment_id' => $scratchEnrollment->id,
            'course_id' => $this->scratchCourse->id,
            'group_id' => $this->scratchGroup->id,
            'course_price' => 800.00,
            'total_sessions' => 6,
            'current_session' => 0,
            'status' => 'active',
        ]);

        // Deliver a Python session
        $pySession = AttendanceSession::create([
            'tenant_id' => $this->tenant->id,
            'group_id' => $this->pythonGroup->id,
            'course_id' => $this->pythonCourse->id,
            'attendance_date' => now()->toDateString(),
            'recorded_by' => $this->instructorUser->id,
        ]);
        AttendanceRecord::create([
            'attendance_session_id' => $pySession->id,
            'student_id' => $student->id,
            'status' => 'present',
        ]);

        $pythonEC = EnrollmentCourse::where('course_id', $this->pythonCourse->id)
            ->whereHas('enrollment', fn($q) => $q->where('student_id', $student->id))
            ->first();

        $this->assertEquals(1, $pythonEC->current_session);
        $this->assertEquals(0, $scratchEC->fresh()->current_session);
    }

    /**
     * Phase 4 — Instructor Entitlement Calculation
     */
    public function test_instructor_earnings_calculation(): void
    {
        // 2 completed sessions in current month
        for ($i = 1; $i <= 2; $i++) {
            AttendanceSession::create([
                'tenant_id' => $this->tenant->id,
                'group_id' => $this->pythonGroup->id,
                'course_id' => $this->pythonCourse->id,
                'attendance_date' => now()->startOfMonth()->addDays($i)->toDateString(),
                'recorded_by' => $this->instructorUser->id,
            ]);
        }

        $payment = InstructorPayment::where('instructor_id', $this->instructor->id)
            ->where('tenant_id', $this->tenant->id)
            ->where('period_start', now()->startOfMonth()->toDateString())
            ->first();

        $this->assertNotNull($payment);
        $this->assertEquals(2, $payment->completed_sessions);
        $this->assertEquals(300.00, (float) $payment->sessions_amount); // 2 * 150
        $this->assertEquals(2000.00, (float) $payment->base_amount);
        $this->assertEquals(2300.00, (float) $payment->total_amount); // 2000 + 300
    }

    /**
     * Phase 5 — Salary Payment and Vault Integration
     */
    public function test_salary_payment_disbursement_and_vault_deduction(): void
    {
        $this->actingAs($this->adminUser);

        $initialBalance = (float) $this->vault->getCurrentBalance();
        $this->assertGreaterThanOrEqual(15000.00, $initialBalance);

        // Deliver 2 sessions
        for ($i = 1; $i <= 2; $i++) {
            AttendanceSession::create([
                'tenant_id' => $this->tenant->id,
                'group_id' => $this->pythonGroup->id,
                'course_id' => $this->pythonCourse->id,
                'attendance_date' => now()->startOfMonth()->addDays($i)->toDateString(),
                'recorded_by' => $this->instructorUser->id,
            ]);
        }

        // Accountant pays salary (2,300 EGP) from Vault
        $salaryPayment = SalaryPayment::create([
            'tenant_id' => $this->tenant->id,
            'employee_type' => 'instructor',
            'instructor_id' => $this->instructor->id,
            'amount' => 2300.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'vault_id' => $this->vault->id,
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'status' => 'paid',
            'notes' => 'صرف راتب شهر معتمد مع السيشنات',
        ]);

        // Vault is decremented by 2,300 EGP
        $this->assertEquals($initialBalance - 2300.00, (float) $this->vault->fresh()->getCurrentBalance());

        // InstructorPayment status is updated to 'paid'
        $instructorPayment = InstructorPayment::where('instructor_id', $this->instructor->id)
            ->where('period_start', now()->startOfMonth()->toDateString())
            ->first();

        $this->assertEquals('paid', $instructorPayment->status);

        // Sessions are linked to salary_payment_id
        $sessions = AttendanceSession::where('group_id', $this->pythonGroup->id)->get();
        foreach ($sessions as $s) {
            $this->assertEquals($salaryPayment->id, $s->salary_payment_id);
        }
    }

    /**
     * Phase 5 — Insufficient Vault Balance Blocks Salary Payment
     */
    public function test_insufficient_vault_balance_blocks_salary_payment(): void
    {
        $poorVault = Vault::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'خزنة برصيد قليل',
            'type' => 'cash',
            'opening_balance' => 100.00,
            'is_active' => true,
        ]);

        $this->expectException(\Exception::class);

        SalaryPayment::create([
            'tenant_id' => $this->tenant->id,
            'employee_type' => 'instructor',
            'instructor_id' => $this->instructor->id,
            'amount' => 2500.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'vault_id' => $poorVault->id,
            'status' => 'paid',
        ]);
    }

    /**
     * Phase 7 — Multi-Tenant Isolation
     */
    public function test_multi_tenant_isolation_on_sessions(): void
    {
        $session = SessionSchedule::create([
            'tenant_id' => $this->tenant->id,
            'group_id' => $this->pythonGroup->id,
            'instructor_id' => $this->instructor->id,
            'course_id' => $this->pythonCourse->id,
            'date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'status' => 'scheduled',
        ]);

        // Querying from another tenant
        $otherTenantSessions = SessionSchedule::where('tenant_id', $this->otherTenant->id)->get();
        $this->assertFalse($otherTenantSessions->contains($session));
    }

    /**
     * Phase 4 — Multiple Groups Taught by Same Instructor
     */
    public function test_multiple_groups_taught_by_same_instructor(): void
    {
        // 2 sessions for Python group
        AttendanceSession::create([
            'tenant_id' => $this->tenant->id,
            'group_id' => $this->pythonGroup->id,
            'course_id' => $this->pythonCourse->id,
            'attendance_date' => now()->startOfMonth()->addDays(1)->toDateString(),
            'recorded_by' => $this->instructorUser->id,
        ]);
        AttendanceSession::create([
            'tenant_id' => $this->tenant->id,
            'group_id' => $this->pythonGroup->id,
            'course_id' => $this->pythonCourse->id,
            'attendance_date' => now()->startOfMonth()->addDays(2)->toDateString(),
            'recorded_by' => $this->instructorUser->id,
        ]);

        // 1 session for Scratch group
        AttendanceSession::create([
            'tenant_id' => $this->tenant->id,
            'group_id' => $this->scratchGroup->id,
            'course_id' => $this->scratchCourse->id,
            'attendance_date' => now()->startOfMonth()->addDays(3)->toDateString(),
            'recorded_by' => $this->instructorUser->id,
        ]);

        $payment = InstructorPayment::where('instructor_id', $this->instructor->id)
            ->where('period_start', now()->startOfMonth()->toDateString())
            ->first();

        $this->assertNotNull($payment);
        $this->assertEquals(3, $payment->completed_sessions); // 2 Python + 1 Scratch
        $this->assertEquals(450.00, (float) $payment->sessions_amount); // 3 * 150
        $this->assertEquals(2450.00, (float) $payment->total_amount); // 2000 + 450
    }

    /**
     * Phase 3 — Group Transfer Preserves Historical Attendance Records
     */
    public function test_group_transfer_preserves_historical_attendance_records(): void
    {
        $student = $this->students[1];

        // Student attends 2 sessions in pythonGroup
        $sess1 = AttendanceSession::create([
            'tenant_id' => $this->tenant->id,
            'group_id' => $this->pythonGroup->id,
            'course_id' => $this->pythonCourse->id,
            'attendance_date' => now()->subDays(5)->toDateString(),
            'recorded_by' => $this->instructorUser->id,
        ]);
        AttendanceRecord::create([
            'attendance_session_id' => $sess1->id,
            'student_id' => $student->id,
            'status' => 'present',
        ]);

        $sess2 = AttendanceSession::create([
            'tenant_id' => $this->tenant->id,
            'group_id' => $this->pythonGroup->id,
            'course_id' => $this->pythonCourse->id,
            'attendance_date' => now()->subDays(3)->toDateString(),
            'recorded_by' => $this->instructorUser->id,
        ]);
        AttendanceRecord::create([
            'attendance_session_id' => $sess2->id,
            'student_id' => $student->id,
            'status' => 'present',
        ]);

        // Create new target group
        $newGroup = Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $this->pythonCourse->id,
            'instructor_id' => $this->instructor->id,
            'name' => 'مجموعة بايثون - المسار المسائي',
            'status' => 'active',
            'total_sessions' => 8,
        ]);

        // Transfer student
        $enrollment = Enrollment::where('student_id', $student->id)->where('group_id', $this->pythonGroup->id)->first();
        $enrollment->update(['group_id' => $newGroup->id]);
        EnrollmentCourse::where('enrollment_id', $enrollment->id)->update(['group_id' => $newGroup->id]);

        // Verify historical attendance records are 100% preserved
        $historicalRecords = AttendanceRecord::where('student_id', $student->id)->get();
        $this->assertCount(2, $historicalRecords);
        $this->assertEquals($sess1->id, $historicalRecords[0]->attendance_session_id);
        $this->assertEquals($sess2->id, $historicalRecords[1]->attendance_session_id);
    }

    /**
     * Phase 5 — Already Paid Sessions Are Not Recalculated Into New Unpaid Earnings
     */
    public function test_cannot_double_count_paid_sessions_for_instructor(): void
    {
        // Deliver 2 sessions
        $s1 = AttendanceSession::create([
            'tenant_id' => $this->tenant->id,
            'group_id' => $this->pythonGroup->id,
            'course_id' => $this->pythonCourse->id,
            'attendance_date' => now()->startOfMonth()->addDays(1)->toDateString(),
            'recorded_by' => $this->instructorUser->id,
        ]);
        $s2 = AttendanceSession::create([
            'tenant_id' => $this->tenant->id,
            'group_id' => $this->pythonGroup->id,
            'course_id' => $this->pythonCourse->id,
            'attendance_date' => now()->startOfMonth()->addDays(2)->toDateString(),
            'recorded_by' => $this->instructorUser->id,
        ]);

        // Pay salary for this period
        SalaryPayment::create([
            'tenant_id' => $this->tenant->id,
            'employee_type' => 'instructor',
            'instructor_id' => $this->instructor->id,
            'amount' => 2300.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'vault_id' => $this->vault->id,
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'status' => 'paid',
        ]);

        // Deliver a 3rd session in the same month
        $s3 = AttendanceSession::create([
            'tenant_id' => $this->tenant->id,
            'group_id' => $this->pythonGroup->id,
            'course_id' => $this->pythonCourse->id,
            'attendance_date' => now()->startOfMonth()->addDays(5)->toDateString(),
            'recorded_by' => $this->instructorUser->id,
        ]);

        // S1 and S2 have salary_payment_id set, S3 does not
        $this->assertNotNull($s1->fresh()->salary_payment_id);
        $this->assertNotNull($s2->fresh()->salary_payment_id);
        $this->assertNull($s3->fresh()->salary_payment_id);
    }
}
