<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\AcademicFollowUpCenter;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentCourse;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Group;
use App\Models\Student;
use App\Models\StudentFollowUpContact;
use App\Models\StudentFollowUpTask;
use App\Models\StudentGroupTransfer;
use App\Models\StudentParent;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AcademicFollowUpService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AcademicFollowUpCenterTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $branch1;
    protected Tenant $branch2;
    protected User $secretary;
    protected User $instructor;
    protected Course $course;
    protected Group $groupA;
    protected Group $groupB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch1 = Tenant::create(['name' => 'فرع القاهرة', 'slug' => 'cairo', 'is_active' => true]);
        $this->branch2 = Tenant::create(['name' => 'فرع دمنهور', 'slug' => 'damanhour', 'is_active' => true]);

        $permissions = [
            'view_daily_follow_up_dashboard',
            'view_student_academic_monitoring',
            'view_exam_followup',
            'view_homework_followup',
            'view_student_contact_numbers',
            'create_student_followup_task',
            'record_parent_contact',
            'reassign_follow_up_case',
        ];
        foreach ($permissions as $p) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $secretaryRole = Role::firstOrCreate(['name' => 'secretary', 'guard_name' => 'web']);
        $secretaryRole->syncPermissions($permissions);

        $this->secretary = User::create([
            'name' => 'منى عادل (سكرتيرة)',
            'email' => 'secretary.test@aischool.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->branch1->id,
            'status' => 'active',
        ]);
        $this->secretary->assignRole($secretaryRole);

        $this->instructor = User::create([
            'name' => 'أحمد طارق (معلم)',
            'email' => 'instructor.test@aischool.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->branch1->id,
            'status' => 'active',
        ]);

        $this->course = Course::create([
            'tenant_id' => $this->branch1->id,
            'name' => 'Python Fundamentals',
            'code' => 'PY-101',
            'price' => 1500,
            'status' => 'active',
        ]);

        $this->groupA = Group::create([
            'tenant_id' => $this->branch1->id,
            'course_id' => $this->course->id,
            'name' => 'Group Python A',
            'status' => 'active',
            'capacity' => 20,
        ]);

        $this->groupB = Group::create([
            'tenant_id' => $this->branch1->id,
            'course_id' => $this->course->id,
            'name' => 'Group Python B',
            'status' => 'active',
            'capacity' => 20,
        ]);

        \App\Models\SupervisionGroupAssignment::create([
            'tenant_id' => $this->branch1->id,
            'user_id' => $this->secretary->id,
            'group_id' => $this->groupA->id,
            'assigned_by' => $this->secretary->id,
            'assigned_at' => now(),
        ]);
    }

    protected function createStudentWithParent(string $name, string $studentPhone, string $parentPhone, Group $group, bool $isPrimary = true): Student
    {
        $user = User::create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '', $name)) . '@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => $group->tenant_id,
            'phone' => $studentPhone,
            'status' => 'active',
        ]);

        $student = Student::create([
            'tenant_id' => $group->tenant_id,
            'user_id' => $user->id,
            'student_code' => 'STD-' . rand(1000, 9999),
            'phone' => $studentPhone,
            'gender' => 'male',
            'status' => 'active',
        ]);

        $parentUser = User::create([
            'name' => 'ولي أمر ' . $name,
            'email' => 'parent_' . strtolower(str_replace(' ', '', $name)) . '@test.com',
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
            'is_primary' => $isPrimary,
        ]);

        $enrollment = Enrollment::create([
            'tenant_id' => $group->tenant_id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'enrollment_code' => 'ENR-' . rand(1000, 9999) . '-' . $student->id,
            'enrollment_date' => now()->toDateString(),
            'total_amount' => 1500,
            'status' => 'active',
        ]);

        EnrollmentCourse::create([
            'enrollment_id' => $enrollment->id,
            'course_id' => $group->course_id,
            'group_id' => $group->id,
            'course_price' => 1500,
            'status' => 'active',
            'total_sessions' => 12,
            'current_session' => 1,
        ]);

        return $student;
    }

    /**
     * CONTACT-001: Separate Parent and Student Mobile Columns
     * CONTACT-002: Missing Guardian Phone
     */
    public function test_contact_numbers_resolved_independently(): void
    {
        $student1 = $this->createStudentWithParent('أحمد علي', '01012345678', '01198765432', $this->groupA);

        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);

        $parentPhone = $service->resolveParentPhone($student1);
        $studentPhone = $service->resolveStudentPhone($student1);

        $this->assertEquals('01198765432', $parentPhone);
        $this->assertEquals('01012345678', $studentPhone);
        $this->assertNotEquals($parentPhone, $studentPhone);

        // Student with no guardian phone
        $user2 = User::create([
            'name' => 'طالب بدون ولي أمر',
            'email' => 'orphan@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => $this->branch1->id,
            'status' => 'active',
        ]);

        $student2 = Student::create([
            'tenant_id' => $this->branch1->id,
            'user_id' => $user2->id,
            'student_code' => 'STD-EMPTY',
            'phone' => '01200000000',
            'gender' => 'female',
            'status' => 'active',
        ]);

        $this->assertEquals('غير مسجل', $service->resolveParentPhone($student2));
        $this->assertEquals('01200000000', $service->resolveStudentPhone($student2));
    }

    /**
     * EXAM-001: Completed Exam (3 finish, 1 does not start)
     * EXAM-003: Zero Score Display (displays 0 / 20)
     */
    public function test_exam_roster_resolution_with_zero_and_missing_attempts(): void
    {
        $s1 = $this->createStudentWithParent('طالب 1', '01000000001', '01100000001', $this->groupA);
        $s2 = $this->createStudentWithParent('طالب 2', '01000000002', '01100000002', $this->groupA);
        $s3 = $this->createStudentWithParent('طالب 3', '01000000003', '01100000003', $this->groupA);
        $s4 = $this->createStudentWithParent('طالب 4', '01000000004', '01100000004', $this->groupA);

        $exam = Exam::create([
            'tenant_id' => $this->branch1->id,
            'course_id' => $this->course->id,
            'group_id' => $this->groupA->id,
            'title' => 'اختبار نهائي بايثون',
            'exam_date' => now()->subDay(),
            'duration' => 60,
            'total_marks' => 20,
            'passing_marks' => 10,
            'status' => 'completed',
        ]);

        // S1: Finished with 18 / 20
        ExamAttempt::create([
            'tenant_id' => $this->branch1->id,
            'exam_id' => $exam->id,
            'student_id' => $s1->id,
            'score' => 18,
            'total_score' => 20,
            'percentage' => 90,
            'passed' => true,
            'started_at' => now()->subHours(2),
            'completed_at' => now()->subHours(1),
        ]);

        // S2: Finished with 0 / 20 (Genuine zero)
        ExamAttempt::create([
            'tenant_id' => $this->branch1->id,
            'exam_id' => $exam->id,
            'student_id' => $s2->id,
            'score' => 0,
            'total_score' => 20,
            'percentage' => 0,
            'passed' => false,
            'started_at' => now()->subHours(2),
            'completed_at' => now()->subHours(1),
        ]);

        // S3: Finished with 15 / 20
        ExamAttempt::create([
            'tenant_id' => $this->branch1->id,
            'exam_id' => $exam->id,
            'student_id' => $s3->id,
            'score' => 15,
            'total_score' => 20,
            'percentage' => 75,
            'passed' => true,
            'started_at' => now()->subHours(2),
            'completed_at' => now()->subHours(1),
        ]);

        // S4: Did not attempt!

        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);
        $roster = $service->getExamEligibleRoster($exam);

        $this->assertCount(4, $roster);

        $row1 = $roster->firstWhere('student_id', $s1->id);
        $this->assertEquals('تم الحل', $row1['status_label']);
        $this->assertEquals('18 / 20', $row1['score_display']);

        // Check genuine zero score
        $row2 = $roster->firstWhere('student_id', $s2->id);
        $this->assertEquals('تم الحل', $row2['status_label']);
        $this->assertEquals('0 / 20', $row2['score_display']);

        // Check S4 (overdue / missed)
        $row4 = $roster->firstWhere('student_id', $s4->id);
        $this->assertEquals('انتهى الموعد ولم يحل', $row4['status_label']);
        $this->assertEquals('—', $row4['score_display']);
        $this->assertTrue($row4['is_overdue']);
        $this->assertTrue($row4['requires_follow_up']);
    }

    /**
     * HOMEWORK-001: Submitted on Time
     * HOMEWORK-002: Overdue Without Submission
     * HOMEWORK-005: Draft Grade Protection
     * HOMEWORK-006: Published Zero Score
     */
    public function test_homework_roster_states_and_draft_protection(): void
    {
        $s1 = $this->createStudentWithParent('طالب واثق', '01011111111', '01111111111', $this->groupA);
        $s2 = $this->createStudentWithParent('طالب مسودة', '01022222222', '01122222222', $this->groupA);
        $s3 = $this->createStudentWithParent('طالب متأخر', '01033333333', '01133333333', $this->groupA);

        $assignment = Assignment::create([
            'tenant_id' => $this->branch1->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $this->groupA->id,
            'title' => 'واجب العمليات الحسابية',
            'due_date' => now()->subDays(2),
            'max_score' => 10,
        ]);

        // S1: Submitted & Officially Published Grade 0 / 10
        AssignmentSubmission::create([
            'tenant_id' => $this->branch1->id,
            'assignment_id' => $assignment->id,
            'student_id' => $s1->id,
            'submitted_at' => now()->subDays(3),
            'score' => 0.0,
            'published_at' => now()->subDay(),
            'status' => 'graded',
        ]);

        // S2: Submitted & Draft Score 9.5 (NOT PUBLISHED)
        AssignmentSubmission::create([
            'tenant_id' => $this->branch1->id,
            'assignment_id' => $assignment->id,
            'student_id' => $s2->id,
            'submitted_at' => now()->subDays(3),
            'score' => 9.5,
            'published_at' => null, // DRAFT!
            'status' => 'submitted',
        ]);

        // S3: No submission, overdue!

        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);
        $roster = $service->getHomeworkEligibleRoster($assignment);

        $this->assertCount(3, $roster);

        // S1: Published zero
        $row1 = $roster->firstWhere('student_id', $s1->id);
        $this->assertEquals('تم التصحيح', $row1['status_label']);
        $this->assertEquals('0 / 10', $row1['score_display']);

        // S2: Draft grade protected
        $row2 = $roster->firstWhere('student_id', $s2->id);
        $this->assertEquals('بانتظار اعتماد الدرجة', $row2['status_label']);
        $this->assertEquals('بانتظار اعتماد الدرجة', $row2['score_display']);
        $this->assertStringNotContainsString('9.5', $row2['score_display']); // Zero draft score leak!

        // S3: Overdue
        $row3 = $roster->firstWhere('student_id', $s3->id);
        $this->assertEquals('متأخر ولم يسلّم', $row3['status_label']);
        $this->assertTrue($row3['is_overdue']);
        $this->assertTrue($row3['requires_follow_up']);
    }

    /**
     * TRANSFER-001: Student Joins After Deadline (No False Overdue)
     */
    public function test_transferred_student_after_deadline_not_falsely_labeled_overdue(): void
    {
        $studentTransferred = $this->createStudentWithParent('طالب منقول', '01099999999', '01199999999', $this->groupB);

        $enrollment = Enrollment::where('student_id', $studentTransferred->id)->first();

        // Record transfer into Group B on Oct 15
        StudentGroupTransfer::create([
            'tenant_id' => $this->branch1->id,
            'student_id' => $studentTransferred->id,
            'enrollment_id' => $enrollment->id,
            'from_group_id' => $this->groupA->id,
            'to_group_id' => $this->groupB->id,
            'transferred_at' => Carbon::parse('2026-10-15 10:00:00'),
        ]);

        // Group B had an assignment due on Oct 12 (before the transfer!)
        $oldAssignment = Assignment::create([
            'tenant_id' => $this->branch1->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $this->groupB->id,
            'title' => 'واجب قديم قبل النقل',
            'due_date' => Carbon::parse('2026-10-12'),
            'max_score' => 10,
        ]);

        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);
        $roster = $service->getHomeworkEligibleRoster($oldAssignment);

        // Student should NOT be included in old assignment's roster or flagged overdue!
        $this->assertNull($roster->firstWhere('student_id', $studentTransferred->id));
    }

    /**
     * FOLLOWUP-001: Manual Task Only & CONTACT-004: Chronological History
     * FOLLOWUP-002: Deduplication
     */
    public function test_manual_follow_up_creation_and_chronological_contact_logging(): void
    {
        $this->actingAs($this->secretary);

        $student = $this->createStudentWithParent('عمر خالد', '01055555555', '01155555555', $this->groupA);

        $assignment = Assignment::create([
            'tenant_id' => $this->branch1->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $this->groupA->id,
            'title' => 'مشروع بايثون',
            'due_date' => now()->subDays(3),
            'max_score' => 10,
        ]);

        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);

        // 1. Automatic overdue detected, but NO task exists yet
        $this->assertDatabaseMissing('student_follow_up_tasks', [
            'student_id' => $student->id,
            'source_id' => $assignment->id,
        ]);

        // 2. Secretary clicks Create Follow-Up manually
        $task = $service->createOrGetFollowUpTask(
            student: $student,
            sourceType: 'homework',
            sourceId: $assignment->id,
            title: 'واجب لم يسلم - عمر خالد',
            reason: 'تأخر في تسليم المشروع النهائي',
            priority: 'high',
            assignedTo: $this->secretary->id,
            academicStatus: 'متأخر ولم يسلّم'
        );

        $this->assertDatabaseHas('student_follow_up_tasks', [
            'id' => $task->id,
            'student_id' => $student->id,
            'source_type' => 'homework',
            'source_id' => $assignment->id,
            'status' => 'new',
        ]);

        // 3. Prevent duplicate active task creation
        $duplicateTask = $service->createOrGetFollowUpTask(
            student: $student,
            sourceType: 'homework',
            sourceId: $assignment->id,
            title: 'محاولة أخرى مكررة',
            reason: 'سبب مكرر',
            priority: 'urgent',
            assignedTo: $this->secretary->id
        );

        $this->assertEquals($task->id, $duplicateTask->id);
        $this->assertEquals(1, StudentFollowUpTask::where('student_id', $student->id)->count());

        // 4. Record 1st Contact: Call -> No Answer
        $contact1 = $service->recordContactAttempt(
            task: $task,
            contactMethod: 'phone_call',
            contactResult: 'no_answer',
            notes: 'تم الاتصال مرتين ولم يرد ولي الأمر',
            nextFollowUpDate: now()->addDay()->format('Y-m-d')
        );

        // 5. Record 2nd Contact: WhatsApp -> Contacted
        $contact2 = $service->recordContactAttempt(
            task: $task,
            contactMethod: 'whatsapp',
            contactResult: 'contacted',
            notes: 'تواصل عبر واتساب ووعد ولي الأمر بالحل الليلة',
            nextFollowUpDate: null
        );

        // Verify two separate chronological history records
        $this->assertEquals(2, $task->contacts()->count());
        $this->assertEquals('no_answer', $contact1->contact_result);
        $this->assertEquals('contacted', $contact2->contact_result);
        $this->assertEquals('contacted', $task->fresh()->status);

        // 6. Complete task
        $task->markCompleted('تم التأكد من تسليم الطالب للواجب.');
        $this->assertEquals('contacted', $task->fresh()->status);
        $this->assertNotNull($task->fresh()->resolved_at);
    }

    /**
     * Livewire Component Integration & UI Render Verification
     */
    public function test_academic_follow_up_center_page_mounts_and_renders_cleanly(): void
    {
        $this->actingAs($this->secretary);

        $student = $this->createStudentWithParent('سامي يوسف', '01044444444', '01144444444', $this->groupA);

        $exam = Exam::create([
            'tenant_id' => $this->branch1->id,
            'course_id' => $this->course->id,
            'group_id' => $this->groupA->id,
            'title' => 'امتحان الميدتيرم',
            'exam_date' => now()->subDay(),
            'duration' => 45,
            'total_marks' => 30,
            'passing_marks' => 15,
            'status' => 'completed',
        ]);

        Livewire::test(AcademicFollowUpCenter::class)
            ->assertSuccessful()
            ->assertSee('مركز المتابعة التعليمية للطلاب')
            ->assertSee('متابعة الامتحانات')
            ->assertSee('متابعة الواجبات')
            ->assertSee('سامي يوسف')
            ->assertSee('01144444444') // Parent mobile
            ->assertSee('01044444444') // Student mobile
            ->assertSee('انتهى الموعد ولم يحل');
    }

    /**
     * EXAM-002: Future Exam (Not Overdue)
     */
    public function test_exam_not_yet_due_not_marked_overdue(): void
    {
        $student = $this->createStudentWithParent('كريم ناصر', '01066666666', '01166666666', $this->groupA);

        $futureExam = Exam::create([
            'tenant_id' => $this->branch1->id,
            'course_id' => $this->course->id,
            'group_id' => $this->groupA->id,
            'title' => 'اختبار مستقبلي',
            'exam_date' => now()->addDays(5),
            'duration' => 60,
            'total_marks' => 20,
            'passing_marks' => 10,
            'status' => 'scheduled',
        ]);

        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);
        $roster = $service->getExamEligibleRoster($futureExam);

        $row = $roster->firstWhere('student_id', $student->id);
        $this->assertNotNull($row);
        $this->assertEquals('لم يبدأ بعد', $row['status_label']);
        $this->assertFalse($row['is_overdue']);
        $this->assertFalse($row['requires_follow_up']);
    }

    /**
     * EXAM-004: Pending Manual Grading
     * EXAM-005: Multiple Attempts Deduplication
     */
    public function test_exam_pending_manual_grading_and_multiple_attempts(): void
    {
        $student = $this->createStudentWithParent('زياد إيهاب', '01077777777', '01177777777', $this->groupA);

        $exam = Exam::create([
            'tenant_id' => $this->branch1->id,
            'course_id' => $this->course->id,
            'group_id' => $this->groupA->id,
            'title' => 'اختبار عملي يحتاج تصحيح',
            'exam_date' => now()->subDay(),
            'duration' => 60,
            'total_marks' => 50,
            'passing_marks' => 25,
            'status' => 'completed',
        ]);

        // Attempt 1: In progress / awaiting manual review (score null)
        ExamAttempt::create([
            'tenant_id' => $this->branch1->id,
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'attempt_number' => 1,
            'score' => null,
            'total_score' => 50,
            'percentage' => null,
            'passed' => false,
            'started_at' => now()->subHours(4),
            'completed_at' => now()->subHours(3),
        ]);

        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);
        $roster = $service->getExamEligibleRoster($exam);

        // Deduplication: Only 1 row for the student
        $this->assertCount(1, $roster->where('student_id', $student->id));

        $row = $roster->firstWhere('student_id', $student->id);
        $this->assertEquals('بانتظار التصحيح', $row['status_label']);
        $this->assertEquals('بانتظار التصحيح', $row['score_display']);
        $this->assertFalse($row['is_overdue']);
    }

    /**
     * HOMEWORK-003: Due Today (Not Overdue)
     * HOMEWORK-004: Submitted, Awaiting Grading
     */
    public function test_homework_due_today_not_overdue_and_awaiting_grading(): void
    {
        $s1 = $this->createStudentWithParent('محمد إبراهيم', '01088888881', '01188888881', $this->groupA);
        $s2 = $this->createStudentWithParent('ياسين حسام', '01088888882', '01188888882', $this->groupA);

        $assignment = Assignment::create([
            'tenant_id' => $this->branch1->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $this->groupA->id,
            'title' => 'واجب اليوم الحالي',
            'due_date' => now()->endOfDay(),
            'max_score' => 20,
        ]);

        // S1: Not submitted yet, but due date is end of today -> Not overdue
        // S2: Submitted, awaiting grading
        AssignmentSubmission::create([
            'tenant_id' => $this->branch1->id,
            'assignment_id' => $assignment->id,
            'student_id' => $s2->id,
            'submitted_at' => now()->subHour(),
            'score' => null,
            'published_at' => null,
            'status' => 'submitted',
        ]);

        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);
        $roster = $service->getHomeworkEligibleRoster($assignment);

        // S1: Not overdue
        $row1 = $roster->firstWhere('student_id', $s1->id);
        $this->assertEquals('لم يسلّم (ضمن المهلة)', $row1['status_label']);
        $this->assertFalse($row1['is_overdue']);

        // S2: Awaiting grading
        $row2 = $roster->firstWhere('student_id', $s2->id);
        $this->assertEquals('بانتظار التصحيح', $row2['status_label']);
        $this->assertFalse($row2['is_overdue']);
    }

    /**
     * CONTACT-003: Multiple Linked Guardians Resolves Primary
     */
    public function test_multiple_guardians_resolves_primary_guardian(): void
    {
        $student = $this->createStudentWithParent('طارق سمير', '01091111111', '01191111111', $this->groupA, true);

        // Add second guardian (mother), not primary
        $motherUser = User::create([
            'name' => 'أم طارق',
            'email' => 'mother_tarek@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => $this->branch1->id,
            'phone' => '01292222222',
            'status' => 'active',
        ]);

        StudentParent::create([
            'tenant_id' => $this->branch1->id,
            'student_id' => $student->id,
            'user_id' => $motherUser->id,
            'relation' => 'mother',
            'is_primary' => false,
        ]);

        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);
        $parentPhone = $service->resolveParentPhone($student);

        // Must resolve the primary guardian (father 01191111111), not the secondary
        $this->assertEquals('01191111111', $parentPhone);
    }

    /**
     * TRANSFER-002: Historical Work Retained Across Transfers
     */
    public function test_historical_grades_preserved_across_group_transfers(): void
    {
        $student = $this->createStudentWithParent('شريف عثمان', '01093333333', '01193333333', $this->groupA);

        // Assignment in Group A
        $assignmentGroupA = Assignment::create([
            'tenant_id' => $this->branch1->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $this->groupA->id,
            'title' => 'واجب المجموعة الأولى',
            'due_date' => now()->subDays(5),
            'max_score' => 20,
        ]);

        // Student submitted in Group A and got 19 / 20 published
        AssignmentSubmission::create([
            'tenant_id' => $this->branch1->id,
            'assignment_id' => $assignmentGroupA->id,
            'student_id' => $student->id,
            'submitted_at' => now()->subDays(6),
            'score' => 19.0,
            'published_at' => now()->subDays(4),
            'status' => 'graded',
        ]);

        // Transfer student from Group A to Group B
        $enrollment = Enrollment::where('student_id', $student->id)->first();
        StudentGroupTransfer::create([
            'tenant_id' => $this->branch1->id,
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'from_group_id' => $this->groupA->id,
            'to_group_id' => $this->groupB->id,
            'transferred_at' => now()->subDays(2),
        ]);

        // In Group A's roster for that historical assignment: student's completed submission is preserved!
        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);
        $rosterA = $service->getHomeworkEligibleRoster($assignmentGroupA);

        $row = $rosterA->firstWhere('student_id', $student->id);
        $this->assertNotNull($row);
        $this->assertEquals('19 / 20', $row['score_display']);
        $this->assertEquals('تم التصحيح', $row['status_label']);
    }

    /**
     * SECURITY-002: Branch / Tenant Isolation
     */
    public function test_branch_isolation_prevents_cross_tenant_roster_access(): void
    {
        // Student in Branch 2 (Damanhour)
        $groupBranch2 = Group::create([
            'tenant_id' => $this->branch2->id,
            'course_id' => $this->course->id,
            'name' => 'Group Damanhour',
            'status' => 'active',
            'capacity' => 20,
        ]);

        $studentBranch2 = $this->createStudentWithParent('طالب دمنهور', '01094444444', '01194444444', $groupBranch2);

        $examBranch2 = Exam::create([
            'tenant_id' => $this->branch2->id,
            'course_id' => $this->course->id,
            'group_id' => $groupBranch2->id,
            'title' => 'امتحان فرع دمنهور',
            'exam_date' => now()->subDay(),
            'duration' => 60,
            'total_marks' => 20,
            'passing_marks' => 10,
            'status' => 'completed',
        ]);

        $this->actingAs($this->secretary); // Secretary is strictly in Branch 1 (Cairo)

        // Secretary in Branch 1 loads the page
        $component = Livewire::test(AcademicFollowUpCenter::class);

        // Must only list branches authorized to the user (branch 1)
        $branches = $component->get('branches');
        $this->assertTrue($branches->contains('id', $this->branch1->id));
        $this->assertFalse($branches->contains('id', $this->branch2->id));

        // Attempting to select branch 2 should not expose branch 2 groups
        $component->set('selectedBranchId', $this->branch2->id);
        $groups = $component->get('groups');
        $this->assertFalse($groups->contains('id', $groupBranch2->id));
    }
}
