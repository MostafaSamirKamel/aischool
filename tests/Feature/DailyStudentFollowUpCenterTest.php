<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentCourse;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Group;
use App\Models\Installment;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentFollowUpContact;
use App\Models\StudentFollowUpTask;
use App\Models\StudentFollowUpTaskReassignment;
use App\Models\StudentGroupTransfer;
use App\Models\StudentParent;
use App\Models\SupervisionGroupAssignment;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vault;
use App\Services\AcademicFollowUpService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DailyStudentFollowUpCenterTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $branch;
    protected User $admin;
    protected User $secretary;
    protected User $supervisor;
    protected User $financialSecretary;
    protected User $instructor;
    protected Course $course1;
    protected Course $course2;
    protected Group $groupA;
    protected Group $groupB;
    protected Group $groupC;
    protected AcademicFollowUpService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Tenant::create(['name' => 'فرع القاهرة التجريبي', 'slug' => 'cairo-e2e', 'is_active' => true]);

        // Permissions
        $permissions = [
            'view_daily_follow_up_dashboard',
            'view_student_academic_monitoring',
            'view_student_contact_numbers',
            'create_student_followup_task',
            'record_parent_contact',
            'reassign_follow_up_case',
            'view_daily_follow_up_reports',
            'view_student_financial_follow_up',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        $secretaryRole = Role::firstOrCreate(['name' => 'secretary', 'guard_name' => 'web']);
        $secretaryRole->syncPermissions([
            'view_daily_follow_up_dashboard',
            'view_student_academic_monitoring',
            'view_student_contact_numbers',
            'create_student_followup_task',
            'record_parent_contact',
            'reassign_follow_up_case',
            'view_daily_follow_up_reports',
        ]);

        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'web']);
        $supervisorRole->syncPermissions([
            'view_daily_follow_up_dashboard',
            'view_student_academic_monitoring',
            'view_student_contact_numbers',
            'create_student_followup_task',
            'record_parent_contact',
            'reassign_follow_up_case',
            'view_daily_follow_up_reports',
        ]);

        $this->admin = User::create([
            'name' => 'مدير النظام المعتمد',
            'email' => 'admin.e2e@aischool.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->branch->id,
            'status' => 'active',
        ]);
        $this->admin->assignRole($adminRole);

        $this->instructor = User::create([
            'name' => 'أحمد طارق (معلم)',
            'email' => 'instructor.e2e@aischool.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->branch->id,
            'status' => 'active',
        ]);

        $this->secretary = User::create([
            'name' => 'سارة السكرتيرة',
            'email' => 'secretary.sara@aischool.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->branch->id,
            'status' => 'active',
        ]);
        $this->secretary->assignRole($secretaryRole);

        $this->supervisor = User::create([
            'name' => 'خالد المشرف التربوي',
            'email' => 'supervisor.khaled@aischool.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->branch->id,
            'status' => 'active',
        ]);
        $this->supervisor->assignRole($supervisorRole);

        $this->financialSecretary = User::create([
            'name' => 'منى المالية',
            'email' => 'secretary.financial@aischool.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->branch->id,
            'status' => 'active',
        ]);
        $this->financialSecretary->assignRole($secretaryRole);
        $this->financialSecretary->givePermissionTo('view_student_financial_follow_up');

        // Courses
        $this->course1 = Course::create([
            'tenant_id' => $this->branch->id,
            'name' => 'Python Junior',
            'code' => 'PY-JR',
            'price' => 1200,
            'status' => 'active',
        ]);

        $this->course2 = Course::create([
            'tenant_id' => $this->branch->id,
            'name' => 'Scratch Champions',
            'code' => 'SC-CH',
            'price' => 1000,
            'status' => 'active',
        ]);

        // Groups
        $this->groupA = Group::create([
            'tenant_id' => $this->branch->id,
            'course_id' => $this->course1->id,
            'instructor_id' => $this->instructor->id,
            'name' => 'مجموعة بايثون (أ)',
            'status' => 'active',
            'capacity' => 20,
        ]);

        $this->groupB = Group::create([
            'tenant_id' => $this->branch->id,
            'course_id' => $this->course1->id,
            'instructor_id' => $this->instructor->id,
            'name' => 'مجموعة بايثون (ب)',
            'status' => 'active',
            'capacity' => 20,
        ]);

        $this->groupC = Group::create([
            'tenant_id' => $this->branch->id,
            'course_id' => $this->course2->id,
            'instructor_id' => $this->instructor->id,
            'name' => 'مجموعة سكراتش (ج)',
            'status' => 'active',
            'capacity' => 20,
        ]);

        // Scope assignments: Sara assigned to Group A, Khaled assigned to Group A
        SupervisionGroupAssignment::create([
            'tenant_id' => $this->branch->id,
            'user_id' => $this->secretary->id,
            'group_id' => $this->groupA->id,
            'assigned_by' => $this->admin->id,
            'assigned_at' => now(),
        ]);

        SupervisionGroupAssignment::create([
            'tenant_id' => $this->branch->id,
            'user_id' => $this->supervisor->id,
            'group_id' => $this->groupA->id,
            'assigned_by' => $this->admin->id,
            'assigned_at' => now(),
        ]);

        SupervisionGroupAssignment::create([
            'tenant_id' => $this->branch->id,
            'user_id' => $this->financialSecretary->id,
            'group_id' => $this->groupA->id,
            'assigned_by' => $this->admin->id,
            'assigned_at' => now(),
        ]);

        $this->service = app(AcademicFollowUpService::class);
    }

    protected function createStudent(string $name, string $studentPhone, string $parentPhone, Group $group): Student
    {
        $user = User::create([
            'name' => $name,
            'email' => 'student.' . rand(1000, 99999) . '@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => $group->tenant_id,
            'phone' => $studentPhone,
            'status' => 'active',
        ]);

        $student = Student::create([
            'tenant_id' => $group->tenant_id,
            'user_id' => $user->id,
            'student_code' => 'STD-' . rand(10000, 99999),
            'phone' => $studentPhone,
            'gender' => 'male',
            'status' => 'active',
        ]);

        $parentUser = User::create([
            'name' => 'ولي أمر ' . $name,
            'email' => 'parent.' . rand(1000, 99999) . '@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => $group->tenant_id,
            'phone' => $parentPhone,
            'status' => 'active',
        ]);

        StudentParent::create([
            'tenant_id' => $group->tenant_id,
            'student_id' => $student->id,
            'user_id' => $parentUser->id,
            'relation' => 'father',
            'is_primary' => true,
        ]);

        $enrollment = Enrollment::create([
            'tenant_id' => $group->tenant_id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'enrollment_code' => 'ENR-' . rand(1000, 9999) . '-' . $student->id,
            'enrollment_date' => now()->toDateString(),
            'total_amount' => 1200,
            'status' => 'active',
            'enrolled_at' => now()->subMonth(),
        ]);

        EnrollmentCourse::create([
            'tenant_id' => $group->tenant_id,
            'enrollment_id' => $enrollment->id,
            'course_id' => $group->course_id,
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        return $student;
    }

    /**
     * TEST 01 — Low Exam Score (< 50% published triggers alert, no auto-task)
     */
    public function test_01_low_exam_score_below_50_percent_generates_alert_without_auto_task(): void
    {
        $student = $this->createStudent('أحمد طه', '01011111111', '01211111111', $this->groupA);

        $exam = Exam::create([
            'tenant_id' => $this->branch->id,
            'course_id' => $this->course1->id,
            'group_id' => $this->groupA->id,
            'title' => 'امتحان منتصف الفصل',
            'exam_date' => now()->subDays(2),
            'duration' => 60,
            'total_marks' => 20,
            'passing_marks' => 10,
            'status' => 'completed',
        ]);

        // Student score: 9 out of 20 (strictly below 50%)
        ExamAttempt::create([
            'tenant_id' => $this->branch->id,
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'score' => 9,
            'total_score' => 20,
            'percentage' => 45.0,
            'passed' => false,
            'started_at' => now()->subHours(5),
            'completed_at' => now()->subHours(4),
        ]);

        // 1. Detect alerts
        $alerts = $this->service->detectAllAcademicAlerts($this->secretary);
        $examAlert = $alerts->firstWhere('student_id', $student->id);

        $this->assertNotNull($examAlert);
        $this->assertEquals('low_exam_score', $examAlert['issue_type']);
        $this->assertStringContainsString('9/20', $examAlert['issue_label']);

        // 2. Strict Rule: Automatic detection does NOT automatically create task
        $this->assertEquals(0, StudentFollowUpTask::where('student_id', $student->id)->count());

        // 3. Employee can manually create a case
        $task = $this->service->createOrGetFollowUpTask(
            student: $student,
            sourceType: 'exam',
            sourceId: $exam->id,
            title: 'متابعة درجة امتحان ضعيفة',
            reason: 'درجة امتحان ضعيفة 9/20',
            priority: 'high',
            assignedTo: $this->secretary->id
        );

        $this->assertNotNull($task);
        $this->assertEquals('new', $task->status);
        $this->assertEquals($this->secretary->id, $task->assigned_to);
    }

    /**
     * TEST 02 — Exactly Half Exam Score (No low-grade alert)
     */
    public function test_02_exam_score_exactly_half_does_not_generate_low_grade_alert(): void
    {
        $student = $this->createStudent('يوسف خالد', '01022222222', '01222222222', $this->groupA);

        $exam = Exam::create([
            'tenant_id' => $this->branch->id,
            'course_id' => $this->course1->id,
            'group_id' => $this->groupA->id,
            'title' => 'امتحان البرمجة 1',
            'exam_date' => now()->subDays(2),
            'duration' => 60,
            'total_marks' => 20,
            'passing_marks' => 10,
            'status' => 'completed',
        ]);

        // Score: exactly 10 out of 20 (50%) -> NOT a low grade
        ExamAttempt::create([
            'tenant_id' => $this->branch->id,
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'score' => 10,
            'total_score' => 20,
            'percentage' => 50.0,
            'passed' => true,
            'started_at' => now()->subHours(5),
            'completed_at' => now()->subHours(4),
        ]);

        $alerts = $this->service->detectAllAcademicAlerts($this->secretary);
        $examAlert = $alerts->firstWhere('student_id', $student->id);

        $this->assertNull($examAlert, 'A score of exactly 50% must not trigger a low-grade alert.');
    }

    /**
     * TEST 03 — Low Homework Score (< 50% published triggers alert)
     */
    public function test_03_low_homework_score_below_50_percent_generates_alert(): void
    {
        $student = $this->createStudent('كريم حسام', '01033333333', '01233333333', $this->groupA);

        $assignment = Assignment::create([
            'tenant_id' => $this->branch->id,
            'group_id' => $this->groupA->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'واجب الشروط والحلقات',
            'due_date' => now()->subDays(3),
            'max_score' => 10,
            'status' => 'active',
        ]);

        // Published score: 4 out of 10 (< 50%)
        AssignmentSubmission::create([
            'tenant_id' => $this->branch->id,
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'submitted_at' => now()->subDays(4),
            'score' => 4,
            'published_at' => now()->subDays(2),
            'graded' => true,
            'status' => 'graded',
        ]);

        $alerts = $this->service->detectAllAcademicAlerts($this->secretary);
        $hwAlert = $alerts->firstWhere('student_id', $student->id);

        $this->assertNotNull($hwAlert);
        $this->assertEquals('low_homework_score', $hwAlert['issue_type']);
        $this->assertStringContainsString('4/10', $hwAlert['issue_label']);
    }

    /**
     * TEST 04 — Exactly Half Homework Score (No low-grade alert)
     */
    public function test_04_homework_score_exactly_half_does_not_generate_low_grade_alert(): void
    {
        $student = $this->createStudent('طارق محمود', '01044444444', '01244444444', $this->groupA);

        $assignment = Assignment::create([
            'tenant_id' => $this->branch->id,
            'group_id' => $this->groupA->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'واجب المتغيرات',
            'due_date' => now()->subDays(3),
            'max_score' => 10,
            'status' => 'active',
        ]);

        // Published score: 5 out of 10 (exactly 50%) -> NOT low grade
        AssignmentSubmission::create([
            'tenant_id' => $this->branch->id,
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'submitted_at' => now()->subDays(4),
            'score' => 5,
            'published_at' => now()->subDays(2),
            'graded' => true,
            'status' => 'graded',
        ]);

        $alerts = $this->service->detectAllAcademicAlerts($this->secretary);
        $hwAlert = $alerts->firstWhere('student_id', $student->id);

        $this->assertNull($hwAlert, 'A homework score of exactly 50% must not trigger a low-grade alert.');
    }

    /**
     * TEST 05 — Zero Grade (Genuine zero 0/10 triggers alert and displays correctly)
     */
    public function test_05_zero_score_generates_low_grade_alert_and_displays_genuine_zero(): void
    {
        $student = $this->createStudent('سامح فريد', '01055555555', '01255555555', $this->groupA);

        $assignment = Assignment::create([
            'tenant_id' => $this->branch->id,
            'group_id' => $this->groupA->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'مشروع بايثون النهائي',
            'due_date' => now()->subDays(3),
            'max_score' => 10,
            'status' => 'active',
        ]);

        // Published score: 0 out of 10
        AssignmentSubmission::create([
            'tenant_id' => $this->branch->id,
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'submitted_at' => now()->subDays(4),
            'score' => 0,
            'published_at' => now()->subDays(2),
            'graded' => true,
            'status' => 'graded',
        ]);

        $alerts = $this->service->detectAllAcademicAlerts($this->secretary);
        $hwAlert = $alerts->firstWhere('student_id', $student->id);

        $this->assertNotNull($hwAlert);
        $this->assertEquals('low_homework_score', $hwAlert['issue_type']);
        $this->assertStringContainsString('0/10', $hwAlert['issue_label']);

        // Check roster view format
        $roster = $this->service->getHomeworkEligibleRoster($assignment);
        $row = $roster->firstWhere('student_id', $student->id);
        $this->assertEquals('0 / 10', $row['score_display']);
    }

    /**
     * TEST 06 — Unpublished Draft (Instructor saves draft below 50% -> No alert, no leak)
     */
    public function test_06_unpublished_draft_score_does_not_generate_follow_up_alert(): void
    {
        $student = $this->createStudent('نادر كمال', '01066666666', '01266666666', $this->groupA);

        $assignment = Assignment::create([
            'tenant_id' => $this->branch->id,
            'group_id' => $this->groupA->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'واجب القوائم',
            'due_date' => now()->subDays(3),
            'max_score' => 10,
            'status' => 'active',
        ]);

        // Draft grading: score 2, but published_at is NULL
        AssignmentSubmission::create([
            'tenant_id' => $this->branch->id,
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'submitted_at' => now()->subDays(4),
            'score' => 2,
            'published_at' => null, // Draft!
            'graded' => false,
            'status' => 'submitted',
        ]);

        $alerts = $this->service->detectAllAcademicAlerts($this->secretary);
        $hwAlert = $alerts->firstWhere('student_id', $student->id);

        $this->assertNull($hwAlert, 'Unpublished draft grades must never trigger follow-up alerts.');
    }

    /**
     * TEST 07 — Homework Missing after deadline (Alert generated, no auto-task)
     */
    public function test_07_homework_missing_after_deadline_generates_alert_without_auto_task(): void
    {
        $student = $this->createStudent('مازن عمرو', '01077777771', '01277777771', $this->groupA);

        $assignment = Assignment::create([
            'tenant_id' => $this->branch->id,
            'group_id' => $this->groupA->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'واجب الدوال',
            'due_date' => now()->subDays(2),
            'max_score' => 10,
            'status' => 'active',
        ]);

        // No submission created
        $alerts = $this->service->detectAllAcademicAlerts($this->secretary);
        $hwAlert = $alerts->firstWhere('student_id', $student->id);

        $this->assertNotNull($hwAlert);
        $this->assertEquals('missing_homework', $hwAlert['issue_type']);
        $this->assertEquals(0, StudentFollowUpTask::where('student_id', $student->id)->count());
    }

    /**
     * TEST 08 — Late Submission (Accepted, marked late, alert updated, contact history preserved)
     */
    public function test_08_late_homework_submission_accepted_marked_late_and_preserves_contact_history(): void
    {
        $student = $this->createStudent('أيمن رامي', '01088888881', '01288888881', $this->groupA);

        $dueDate = now()->subDays(2);
        $assignment = Assignment::create([
            'tenant_id' => $this->branch->id,
            'group_id' => $this->groupA->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'واجب القواميس',
            'due_date' => $dueDate,
            'max_score' => 10,
            'status' => 'active',
        ]);

        // Step 1: Missing homework alert exists
        $alerts1 = $this->service->detectAllAcademicAlerts($this->secretary);
        $this->assertNotNull($alerts1->firstWhere('student_id', $student->id));

        // Step 2: Employee manually creates case and logs contact attempt
        $task = $this->service->createOrGetFollowUpTask(
            student: $student,
            sourceType: 'homework',
            sourceId: $assignment->id,
            title: 'واجب لم يُسلّم',
            reason: 'تأخر في تسليم واجب القواميس',
            priority: 'medium',
            assignedTo: $this->secretary->id
        );

        $this->service->recordContactAttempt(
            task: $task,
            contactMethod: 'phone_call',
            contactResult: 'no_answer',
            notes: 'لم يرد ولي الأمر'
        );

        // Step 3: Student submits late
        $submission = AssignmentSubmission::create([
            'tenant_id' => $this->branch->id,
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'submitted_at' => now(), // Late!
            'status' => 'submitted',
        ]);

        $this->assertTrue($submission->isLate());

        // Step 4: Missing homework alert disappears (updated)
        $alerts2 = $this->service->detectAllAcademicAlerts($this->secretary);
        $missingAlert = $alerts2->where('student_id', $student->id)->firstWhere('issue_type', 'missing_homework');
        $this->assertNull($missingAlert);

        // Step 5: Contact history is preserved
        $this->assertEquals(1, $task->fresh()->contacts()->count());
        $this->assertEquals('no_answer', $task->fresh()->contacts()->first()->contact_result);
    }

    /**
     * TEST 09 — Missed Examination (Alert after deadline without fabricating attempt)
     */
    public function test_09_missed_exam_after_deadline_generates_alert_without_fake_attempt(): void
    {
        $student = $this->createStudent('حمزة وليد', '01099999991', '01299999991', $this->groupA);

        $exam = Exam::create([
            'tenant_id' => $this->branch->id,
            'course_id' => $this->course1->id,
            'group_id' => $this->groupA->id,
            'title' => 'امتحان عملي 1',
            'exam_date' => now()->subDay(),
            'duration' => 60,
            'total_marks' => 30,
            'passing_marks' => 15,
            'status' => 'completed',
        ]);

        // No attempt created
        $alerts = $this->service->detectAllAcademicAlerts($this->secretary);
        $examAlert = $alerts->firstWhere('student_id', $student->id);

        $this->assertNotNull($examAlert);
        $this->assertEquals('missed_exam', $examAlert['issue_type']);

        // No fake attempt in DB
        $this->assertEquals(0, ExamAttempt::where('student_id', $student->id)->count());
    }

    /**
     * TEST 10 — Unrecorded Attendance (No false student absence)
     */
    public function test_10_unrecorded_attendance_does_not_generate_false_student_absence(): void
    {
        $student = $this->createStudent('ياسين حسني', '01012345671', '01212345671', $this->groupA);

        // Session created but no AttendanceRecord entered for this student
        AttendanceSession::create([
            'tenant_id' => $this->branch->id,
            'attendance_date' => now()->subDay()->toDateString(),
            'course_id' => $this->course1->id,
            'group_id' => $this->groupA->id,
            'recorded_by' => $this->admin->id,
        ]);

        $alerts = $this->service->detectAllAcademicAlerts($this->secretary);
        $absenceAlert = $alerts->where('student_id', $student->id)
            ->filter(fn($a) => in_array($a['issue_type'], ['confirmed_absence', 'repeated_absence']))
            ->first();

        $this->assertNull($absenceAlert, 'Unrecorded attendance session must not generate student absence alert.');
    }

    /**
     * TEST 11 — New Case (Manual creation, status is 'new', exactly one assigned employee)
     */
    public function test_11_manual_case_creation_starts_as_new_with_exactly_one_assigned_employee(): void
    {
        $student = $this->createStudent('هشام سليم', '01023456781', '01223456781', $this->groupA);

        $task = $this->service->createOrGetFollowUpTask(
            student: $student,
            sourceType: 'attendance',
            sourceId: 101,
            title: 'غياب بدون إذن',
            reason: 'تغيب عن جلسة السبت',
            priority: 'medium',
            assignedTo: $this->secretary->id
        );

        $this->assertEquals('new', $task->status);
        $this->assertEquals('جديدة', $task->status_label);
        $this->assertEquals($this->secretary->id, $task->assigned_to);
        $this->assertEquals(1, StudentFollowUpTask::where('student_id', $student->id)->count());
    }

    /**
     * TEST 12 — Unanswered Call (Attempt logged as 'no_answer', case remains 'new')
     */
    public function test_12_unanswered_call_records_attempt_and_status_remains_new(): void
    {
        $student = $this->createStudent('رامي أشرف', '01034567891', '01234567891', $this->groupA);

        $task = $this->service->createOrGetFollowUpTask(
            student: $student,
            sourceType: 'general',
            sourceId: null,
            title: 'متابعة عامة',
            reason: 'متابعة المستوى الأكاديمي',
            priority: 'medium',
            assignedTo: $this->secretary->id
        );

        $this->assertEquals('new', $task->status);

        // Record unanswered call
        $contact = $this->service->recordContactAttempt(
            task: $task,
            contactMethod: 'phone_call',
            contactResult: 'no_answer',
            notes: 'تم الاتصال مرتين ولم يرد ولي الأمر',
            recordedByUser: $this->secretary
        );

        $this->assertNotNull($contact);
        $this->assertEquals('no_answer', $contact->contact_result);

        // Strict rule: Status REMAINS 'new'
        $this->assertEquals('new', $task->fresh()->status);
        $this->assertEquals('جديدة', $task->fresh()->status_label);
    }

    /**
     * TEST 13 — Successful Contact (Attempt logged as 'contacted', case becomes 'contacted')
     */
    public function test_13_successful_contact_records_attempt_and_updates_status_to_contacted(): void
    {
        $student = $this->createStudent('مصطفى كمال', '01045678901', '01245678901', $this->groupA);

        $task = $this->service->createOrGetFollowUpTask(
            student: $student,
            sourceType: 'general',
            sourceId: null,
            title: 'متابعة المستوى',
            reason: 'استفسار عن مستوى الطالب',
            priority: 'medium',
            assignedTo: $this->secretary->id
        );

        // Successful communication
        $contact = $this->service->recordContactAttempt(
            task: $task,
            contactMethod: 'phone_call',
            contactResult: 'contacted',
            notes: 'تم التواصل مع الأب وتم التوضيح بالكامل',
            recordedByUser: $this->secretary
        );

        $this->assertNotNull($contact);
        $this->assertEquals('contacted', $contact->contact_result);

        // Strict rule: Status BECOMES 'contacted'
        $this->assertEquals('contacted', $task->fresh()->status);
        $this->assertEquals('تم التواصل', $task->fresh()->status_label);
    }

    /**
     * TEST 14 — Case Reassignment (Audit history preserved, no duplicate task)
     */
    public function test_14_case_reassignment_updates_employee_and_preserves_audit_history(): void
    {
        $student = $this->createStudent('علاء عبد الرحمن', '01056789012', '01256789012', $this->groupA);

        $task = $this->service->createOrGetFollowUpTask(
            student: $student,
            sourceType: 'homework',
            sourceId: 202,
            title: 'متابعة واجب',
            reason: 'واجب لم يسلّم',
            priority: 'high',
            assignedTo: $this->secretary->id
        );

        $this->assertEquals($this->secretary->id, $task->assigned_to);

        // Admin reassigns to Supervisor
        $this->service->reassignTask(
            task: $task,
            newEmployee: $this->supervisor,
            reassignedBy: $this->admin,
            reason: 'تحويل للمشرف التربوي لعدم التجاوب'
        );

        $freshTask = $task->fresh();
        $this->assertEquals($this->supervisor->id, $freshTask->assigned_to);

        // Verify audit log
        $this->assertEquals(1, $freshTask->reassignments()->count());
        $reassignment = $freshTask->reassignments()->first();

        $this->assertEquals($this->secretary->id, $reassignment->previous_assigned_to);
        $this->assertEquals($this->supervisor->id, $reassignment->new_assigned_to);
        $this->assertEquals($this->admin->id, $reassignment->reassigned_by);
        $this->assertEquals('تحويل للمشرف التربوي لعدم التجاوب', $reassignment->reason);

        // Only one task exists
        $this->assertEquals(1, StudentFollowUpTask::where('student_id', $student->id)->count());
    }

    /**
     * TEST 15 — Multiple Courses (Student in 2 courses, academic records isolated)
     */
    public function test_15_multiple_courses_per_student_academic_records_and_alerts_remain_separated(): void
    {
        $student = $this->createStudent('عماد سامي', '01067890123', '01267890123', $this->groupA);

        // Also enroll in Group C (Scratch)
        Enrollment::create([
            'tenant_id' => $this->branch->id,
            'student_id' => $student->id,
            'group_id' => $this->groupC->id,
            'enrollment_code' => 'ENR-' . rand(1000, 9999) . '-' . $student->id,
            'enrollment_date' => now()->toDateString(),
            'total_amount' => 1000,
            'status' => 'active',
            'enrolled_at' => now()->subMonth(),
        ]);

        // Group A assignment: Low grade (3/10)
        $assignmentA = Assignment::create([
            'tenant_id' => $this->branch->id,
            'group_id' => $this->groupA->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'واجب بايثون',
            'due_date' => now()->subDays(2),
            'max_score' => 10,
            'status' => 'active',
        ]);

        AssignmentSubmission::create([
            'tenant_id' => $this->branch->id,
            'assignment_id' => $assignmentA->id,
            'student_id' => $student->id,
            'submitted_at' => now()->subDays(3),
            'score' => 3,
            'published_at' => now()->subDay(),
            'graded' => true,
            'status' => 'graded',
        ]);

        // Group C assignment: Good grade (9/10)
        $assignmentC = Assignment::create([
            'tenant_id' => $this->branch->id,
            'group_id' => $this->groupC->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'واجب سكراتش',
            'due_date' => now()->subDays(2),
            'max_score' => 10,
            'status' => 'active',
        ]);

        AssignmentSubmission::create([
            'tenant_id' => $this->branch->id,
            'assignment_id' => $assignmentC->id,
            'student_id' => $student->id,
            'submitted_at' => now()->subDays(3),
            'score' => 9,
            'published_at' => now()->subDay(),
            'graded' => true,
            'status' => 'graded',
        ]);

        // Detect alerts as admin (who has access to all groups)
        $alerts = $this->service->detectAllAcademicAlerts($this->admin);

        $studentAlerts = $alerts->where('student_id', $student->id);
        $this->assertCount(1, $studentAlerts);

        $alert = $studentAlerts->first();
        $this->assertEquals($this->course1->id, $alert['course_id']);
        $this->assertEquals($this->groupA->id, $alert['group_id']);
        $this->assertEquals('low_homework_score', $alert['issue_type']);
    }

    /**
     * TEST 16 — Group Transfer (Student transfers after deadline, no false overdue)
     */
    public function test_16_transferred_student_after_deadline_does_not_get_false_overdue_issue(): void
    {
        $student = $this->createStudent('إبراهيم فؤاد', '01078901234', '01278901234', $this->groupA);

        // Old assignment in Group A due 10 days ago
        $oldAssignment = Assignment::create([
            'tenant_id' => $this->branch->id,
            'group_id' => $this->groupA->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'واجب تم طرحه قديماً',
            'due_date' => now()->subDays(10),
            'max_score' => 10,
            'status' => 'active',
        ]);

        // Student joined Group A only 2 days ago via transfer
        StudentGroupTransfer::create([
            'tenant_id' => $this->branch->id,
            'student_id' => $student->id,
            'enrollment_id' => $student->enrollments->first()->id,
            'from_group_id' => $this->groupB->id,
            'to_group_id' => $this->groupA->id,
            'transferred_at' => now()->subDays(2),
        ]);

        $roster = $this->service->getHomeworkEligibleRoster($oldAssignment);
        $studentRow = $roster->firstWhere('student_id', $student->id);

        $this->assertNull($studentRow, 'A student transferred after the deadline must not receive a false overdue obligation.');
    }

    /**
     * TEST 17 — Financial Read-Only (Permitted employee sees installment details without edit/vault access)
     */
    public function test_17_financial_read_only_authorized_employee_sees_overdue_installments(): void
    {
        $student = $this->createStudent('شريف محمود', '01089012345', '01289012345', $this->groupA);

        $enrollment = $student->enrollments->first();

        // Overdue installment
        $inst = Installment::create([
            'tenant_id' => $this->branch->id,
            'enrollment_id' => $enrollment->id,
            'installment_number' => 1,
            'amount' => 500,
            'paid_amount' => 0,
            'due_date' => now()->subDays(5),
            'status' => 'overdue',
        ]);

        // Financial secretary has 'view_student_financial_follow_up'
        $alerts = $this->service->detectAllAcademicAlerts($this->financialSecretary);
        $financialAlert = $alerts->firstWhere('student_id', $student->id);

        $this->assertNotNull($financialAlert);
        $this->assertEquals('overdue_installment', $financialAlert['issue_type']);
        $this->assertStringContainsString('500', $financialAlert['academic_status']);

        // Profile test: installments present
        $profile = $this->service->getStudentProfile($student, $this->financialSecretary);
        $this->assertTrue($profile['can_view_financial']);
        $this->assertCount(1, $profile['installments']);
        $this->assertEquals(500, $profile['installments']->first()['amount']);
    }

    /**
     * TEST 18 — Unauthorized Financial View (Rejected/hidden for unpermitted employee)
     */
    public function test_18_unauthorized_financial_view_rejected_for_user_without_permission(): void
    {
        $student = $this->createStudent('وائل سمير', '01090123456', '01290123456', $this->groupA);

        $enrollment = $student->enrollments->first();

        Installment::create([
            'tenant_id' => $this->branch->id,
            'enrollment_id' => $enrollment->id,
            'installment_number' => 1,
            'amount' => 600,
            'paid_amount' => 0,
            'due_date' => now()->subDays(5),
            'status' => 'overdue',
        ]);

        // Sara does NOT have 'view_student_financial_follow_up'
        $alerts = $this->service->detectAllAcademicAlerts($this->secretary);
        $financialAlert = $alerts->where('student_id', $student->id)->firstWhere('issue_type', 'overdue_installment');

        $this->assertNull($financialAlert, 'Financial alerts must be completely hidden from users without financial permission.');

        // Profile test: installments empty
        $profile = $this->service->getStudentProfile($student, $this->secretary);
        $this->assertFalse($profile['can_view_financial']);
        $this->assertEmpty($profile['installments']);
    }

    /**
     * TEST 19 — Unauthorized Group Access (Rejected for unassigned groups)
     */
    public function test_19_unauthorized_group_access_rejected_for_unassigned_group(): void
    {
        // Sara is assigned to Group A only. Group B is unassigned to her.
        $alerts = $this->service->detectAllAcademicAlerts(
            user: $this->secretary,
            branchId: $this->branch->id,
            groupId: $this->groupB->id
        );

        $this->assertEmpty($alerts, 'Access to unassigned groups must be strictly rejected.');
    }

    /**
     * TEST 20 — Daily Report Accuracy (Cases, contact attempts, and alert counts)
     */
    public function test_20_daily_report_accuracy_for_cases_contact_attempts_and_alerts(): void
    {
        $s1 = $this->createStudent('طالب 1', '01011112222', '01211112222', $this->groupA);
        $s2 = $this->createStudent('طالب 2', '01022223333', '01222223333', $this->groupA);

        // Task 1: Contacted
        $task1 = $this->service->createOrGetFollowUpTask(
            student: $s1,
            sourceType: 'general',
            sourceId: null,
            title: 'متابعة 1',
            reason: 'سبب 1',
            priority: 'medium',
            assignedTo: $this->secretary->id
        );

        $this->service->recordContactAttempt(
            task: $task1,
            contactMethod: 'phone_call',
            contactResult: 'contacted',
            notes: 'تم التواصل بنجاح',
            recordedByUser: $this->secretary
        );

        // Task 2: New (Unanswered)
        $task2 = $this->service->createOrGetFollowUpTask(
            student: $s2,
            sourceType: 'general',
            sourceId: null,
            title: 'متابعة 2',
            reason: 'سبب 2',
            priority: 'medium',
            assignedTo: $this->secretary->id
        );

        $this->service->recordContactAttempt(
            task: $task2,
            contactMethod: 'phone_call',
            contactResult: 'no_answer',
            notes: 'لم يرد',
            recordedByUser: $this->secretary
        );

        $report = $this->service->getDailyReportData($this->secretary);

        $this->assertEquals(1, $report['new_cases_count']);
        $this->assertEquals(1, $report['contacted_cases_count']);
        $this->assertEquals(2, $report['total_contact_attempts']);
        $this->assertEquals(1, $report['successful_contacts']);
        $this->assertEquals(1, $report['unsuccessful_contacts']);
    }

    /**
     * TEST 21 — Existing Academic Regression Intact
     */
    public function test_21_existing_academic_regression_intact(): void
    {
        $student = $this->createStudent('ماجد نبيل', '01033334444', '01233334444', $this->groupA);

        // 1. Session attendance recording
        $session = AttendanceSession::create([
            'tenant_id' => $this->branch->id,
            'attendance_date' => now()->toDateString(),
            'course_id' => $this->course1->id,
            'group_id' => $this->groupA->id,
            'recorded_by' => $this->admin->id,
        ]);

        $record = AttendanceRecord::create([
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => 'present',
            'check_in_time' => now(),
        ]);

        $this->assertEquals('present', $record->status);

        // 2. Homework submission and grading
        $assignment = Assignment::create([
            'tenant_id' => $this->branch->id,
            'group_id' => $this->groupA->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'واجب اعتيادي',
            'due_date' => now()->addDays(2),
            'max_score' => 20,
            'status' => 'active',
        ]);

        $submission = AssignmentSubmission::create([
            'tenant_id' => $this->branch->id,
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'submitted_at' => now(),
            'score' => 18,
            'published_at' => now(),
            'graded' => true,
            'status' => 'graded',
        ]);

        $this->assertTrue($submission->isPublished());
        $this->assertEquals(18, $submission->score);

        // 3. Exam attempt
        $exam = Exam::create([
            'tenant_id' => $this->branch->id,
            'course_id' => $this->course1->id,
            'group_id' => $this->groupA->id,
            'title' => 'امتحان اعتيادي',
            'exam_date' => now()->addDays(3),
            'duration' => 60,
            'total_marks' => 100,
            'passing_marks' => 50,
            'status' => 'scheduled',
        ]);

        $attempt = ExamAttempt::create([
            'tenant_id' => $this->branch->id,
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'score' => 85,
            'total_score' => 100,
            'percentage' => 85.0,
            'passed' => true,
            'started_at' => now(),
            'completed_at' => now()->addMinutes(45),
        ]);

        $this->assertTrue($attempt->isCompleted());
        $this->assertTrue($attempt->passed);
    }

    /**
     * TEST 22 — Financial Regression (Vaults, payments, installments intact)
     */
    public function test_22_financial_regression_vault_balances_and_payments_intact(): void
    {
        $student = $this->createStudent('حسام حسن', '01044445555', '01244445555', $this->groupA);
        $enrollment = $student->enrollments->first();

        // Create vault
        $vault = Vault::create([
            'tenant_id' => $this->branch->id,
            'name' => 'الخزينة الرئيسية',
            'type' => 'cash',
            'opening_balance' => 10000.00,
            'is_active' => true,
        ]);

        $this->assertEquals(10000.00, (float) $vault->opening_balance);

        // Installment
        $inst = Installment::create([
            'tenant_id' => $this->branch->id,
            'enrollment_id' => $enrollment->id,
            'installment_number' => 1,
            'amount' => 1000.00,
            'paid_amount' => 0.00,
            'due_date' => now()->addMonth(),
            'status' => 'pending',
        ]);

        $this->assertEquals('pending', $inst->getActualStatus());

        // Make payment
        $payment = Payment::create([
            'tenant_id' => $this->branch->id,
            'enrollment_id' => $enrollment->id,
            'vault_id' => $vault->id,
            'amount' => 1000.00,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $inst->update([
            'paid_amount' => 1000.00,
            'paid_date' => now(),
            'status' => 'paid',
        ]);

        $this->assertEquals('paid', $inst->fresh()->getActualStatus());
        $this->assertEquals(10000.00, (float) $vault->fresh()->opening_balance); // Follow-up operations did not touch vaults!
    }
}
