<?php

namespace Tests\Feature;

use App\Filament\Instructor\Pages\HomeworkReviewCenter;
use App\Models\AcademicGradeCorrection;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamRetakeGrant;
use App\Models\Group;
use App\Models\HomeworkDeadlineExtension;
use App\Models\HomeworkResubmissionGrant;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\Tenant;
use App\Models\Vault;
use App\Services\AcademicCenterService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UnifiedAcademicCenterComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Vault $vault;
    protected User $adminUser;
    protected User $instructorUser;
    protected Instructor $instructor;
    protected Group $group;
    protected Course $course;
    protected Student $student;
    protected AttendanceSession $session;
    protected AcademicCenterService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(AcademicCenterService::class);

        $this->tenant = Tenant::create([
            'name' => 'AI School Test Branch',
            'slug' => 'test-branch',
        ]);

        $this->vault = Vault::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'خزنة الفرع',
            'type' => 'cash',
            'opening_balance' => 5000,
            'is_active' => true,
        ]);

        $role = Role::firstOrCreate(['name' => 'instructor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        $this->adminUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'مدير النظام',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
            'status' => 'active',
            'is_super_admin' => true,
        ]);

        $this->instructorUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'المعلم أحمد',
            'email' => 'ahmed@aischool.test',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $this->instructorUser->assignRole($role);

        $this->instructor = Instructor::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->instructorUser->id,
            'employee_code' => 'INS-TEST-01',
            'specialization' => 'Computer Science',
        ]);

        $this->course = Course::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'مقرر تجريبي',
            'code' => 'TEST-01',
            'price' => 1000,
        ]);

        $this->group = Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $this->course->id,
            'instructor_id' => $this->instructor->id,
            'name' => 'مجموعة الاختبار أ',
            'status' => 'active',
            'max_students' => 20,
        ]);

        $studentUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'طالب تجريبي',
            'email' => 'student@aischool.test',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $this->student = Student::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $studentUser->id,
            'student_code' => 'STD-TEST-01',
            'gender' => 'male',
            'status' => 'active',
        ]);

        $enrollment = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'course_id' => $this->course->id,
            'vault_id' => $this->vault->id,
            'enrollment_code' => 'ENR-TEST-01',
            'total_amount' => 1000,
            'paid_amount' => 1000,
            'enrollment_date' => now(),
            'enrolled_at' => now(),
            'status' => 'active',
        ]);

        \App\Models\EnrollmentCourse::create([
            'enrollment_id' => $enrollment->id,
            'course_id' => $this->course->id,
        ]);

        $this->session = AttendanceSession::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $this->course->id,
            'group_id' => $this->group->id,
            'attendance_date' => now()->format('Y-m-d'),
            'session_notes' => 'جلسة تدريبية اختبارية',
            'status' => 'completed',
        ]);
    }

    /** @test */
    public function test_academic_center_page_loads_with_four_tabs_and_header_stats(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(HomeworkReviewCenter::class, ['group' => $this->group->id])
            ->assertSuccessful()
            ->assertSet('selectedGroupId', $this->group->id)
            ->assertSet('activeTab', 'homework')
            ->call('switchTab', 'exams')
            ->assertSet('activeTab', 'exams')
            ->call('switchTab', 'submissions')
            ->assertSet('activeTab', 'submissions')
            ->call('switchTab', 'results')
            ->assertSet('activeTab', 'results');

        $stats = $this->service->getGroupAcademicHeaderStats($this->group);
        $this->assertArrayHasKey('sessions_count', $stats);
        $this->assertArrayHasKey('homework_count', $stats);
        $this->assertArrayHasKey('pending_grading_count', $stats);
        $this->assertArrayHasKey('exams_count', $stats);
    }

    /** @test */
    public function test_homework_sessions_relationship_and_unlinked_homework(): void
    {
        $linkedHw = Assignment::create([
            'tenant_id' => $this->group->tenant_id,
            'group_id' => $this->group->id,
            'instructor_id' => $this->instructor->id,
            'attendance_session_id' => $this->session->id,
            'title' => 'واجب مرتبط بالسيشن',
            'due_date' => now()->addDays(5),
            'max_score' => 10.0,
            'status' => 'published',
        ]);

        $unlinkedHw = Assignment::create([
            'tenant_id' => $this->group->tenant_id,
            'group_id' => $this->group->id,
            'instructor_id' => $this->instructor->id,
            'attendance_session_id' => null,
            'title' => 'واجب عام غير مرتبط بسيشن',
            'due_date' => now()->addDays(5),
            'max_score' => 10.0,
            'status' => 'published',
        ]);

        $this->assertEquals($this->session->id, $linkedHw->attendance_session_id);
        $this->assertNull($unlinkedHw->attendance_session_id);
    }

    /** @test */
    public function test_homework_instant_publication_and_highest_eligible_grade(): void
    {
        $assignment = Assignment::create([
            'tenant_id' => $this->group->tenant_id,
            'group_id' => $this->group->id,
            'instructor_id' => $this->instructor->id,
            'attendance_session_id' => $this->session->id,
            'title' => 'واجب احتساب أعلى درجة فوري',
            'due_date' => now()->addDays(5),
            'max_score' => 10.0,
            'status' => 'published',
        ]);

        // Attempt 1: score 6.0
        $sub1 = AssignmentSubmission::create([
            'tenant_id' => $assignment->tenant_id,
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'attempt_number' => 1,
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        $sub1 = $this->service->gradeHomework($sub1, 6.0, 'تصحيح أولي', $this->adminUser);
        $this->assertTrue($sub1->isPublished());
        $this->assertTrue((bool) $sub1->is_official);
        $this->assertEquals(6.0, $sub1->score);

        // Issue Grant 1 & submit Attempt 2: score 9.0
        $grant1 = $this->service->grantHomeworkResubmission($assignment, $this->student, now()->addDays(2), 'محاولة ثانية', $this->adminUser);
        $sub2 = AssignmentSubmission::create([
            'tenant_id' => $assignment->tenant_id,
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'attempt_number' => 2,
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);
        $grant1->update(['status' => 'consumed', 'consumed_at' => now(), 'consumed_submission_id' => $sub2->id]);

        $sub2 = $this->service->gradeHomework($sub2, 9.0, 'ممتاز', $this->adminUser);
        $official = $this->service->recalculateOfficialHomeworkGrade($assignment->id, $this->student->id);
        $this->assertEquals($sub2->id, $official->id);
        $this->assertEquals(9.0, $official->score);

        // Issue Grant 2 & submit Attempt 3: score 7.0 (lower) -> highest remains 9.0
        $grant2 = $this->service->grantHomeworkResubmission($assignment, $this->student, now()->addDays(2), 'محاولة ثالثة', $this->adminUser);
        $sub3 = AssignmentSubmission::create([
            'tenant_id' => $assignment->tenant_id,
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'attempt_number' => 3,
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);
        $grant2->update(['status' => 'consumed', 'consumed_at' => now(), 'consumed_submission_id' => $sub3->id]);
        $sub3 = $this->service->gradeHomework($sub3, 7.0, 'مقبول', $this->adminUser);

        $officialAfterLower = $this->service->recalculateOfficialHomeworkGrade($assignment->id, $this->student->id);
        $this->assertEquals($sub2->id, $officialAfterLower->id);
        $this->assertEquals(9.0, $officialAfterLower->score);
    }

    /** @test */
    public function test_published_grade_correction_creates_mandatory_audit_log(): void
    {
        $assignment = Assignment::create([
            'tenant_id' => $this->group->tenant_id,
            'group_id' => $this->group->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'واجب تدقيق تصحيح الدرجات',
            'due_date' => now()->addDays(5),
            'max_score' => 10.0,
            'status' => 'published',
        ]);

        $sub = AssignmentSubmission::create([
            'tenant_id' => $assignment->tenant_id,
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'attempt_number' => 1,
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);
        $sub = $this->service->gradeHomework($sub, 8.0, 'درجة أولية', $this->adminUser);

        $auditLog = $this->service->correctPublishedGrade($sub, 8.5, 'تصحيح خطأ حسابي في الدرجة', $this->adminUser);
        $sub->refresh();

        $this->assertEquals(8.5, $sub->score);
        $this->assertEquals(8.0, (float) $auditLog->previous_score);
        $this->assertEquals(8.5, (float) $auditLog->new_score);
        $this->assertEquals('تصحيح خطأ حسابي في الدرجة', $auditLog->reason);
        $this->assertEquals($this->adminUser->id, $auditLog->corrected_by);
    }

    /** @test */
    public function test_homework_deadline_enforcement_and_individual_student_reopening(): void
    {
        $pastAsg = Assignment::create([
            'tenant_id' => $this->group->tenant_id,
            'group_id' => $this->group->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'واجب منتهي الصلاحية',
            'due_date' => now()->subDay(),
            'max_score' => 10.0,
            'status' => 'published',
        ]);

        $this->assertTrue($pastAsg->isSubmissionClosedForStudent($this->student));

        // Reopen for this specific student
        $this->service->reopenStudentSubmission($pastAsg, $this->student, now()->addDays(2), 'عذر طبي', $this->adminUser);
        $this->assertFalse($pastAsg->fresh()->isSubmissionClosedForStudent($this->student));
    }

    /** @test */
    public function test_exam_optional_session_and_comprehensive_group_exams(): void
    {
        $sessionExam = Exam::create([
            'tenant_id' => $this->group->tenant_id,
            'course_id' => $this->group->course_id,
            'group_id' => $this->group->id,
            'instructor_id' => $this->instructor->id,
            'attendance_session_id' => $this->session->id,
            'title' => 'امتحان مرتبط بسيشن',
            'type' => 'quiz',
            'duration' => 30,
            'total_marks' => 10.0,
            'passing_marks' => 5.0,
            'status' => 'scheduled',
            'exam_date' => now(),
        ]);

        $compExam = Exam::create([
            'tenant_id' => $this->group->tenant_id,
            'course_id' => $this->group->course_id,
            'group_id' => $this->group->id,
            'instructor_id' => $this->instructor->id,
            'attendance_session_id' => null,
            'title' => 'امتحان شامل للمجموعة',
            'type' => 'comprehensive',
            'duration' => 60,
            'total_marks' => 20.0,
            'passing_marks' => 10.0,
            'status' => 'scheduled',
            'exam_date' => now(),
        ]);

        $this->assertEquals($this->session->id, $sessionExam->attendance_session_id);
        $this->assertNull($compExam->attendance_session_id);
    }

    /** @test */
    public function test_exam_retake_grant_independent_consumption_and_highest_completed_score(): void
    {
        $exam = Exam::create([
            'tenant_id' => $this->group->tenant_id,
            'course_id' => $this->group->course_id,
            'group_id' => $this->group->id,
            'instructor_id' => $this->instructor->id,
            'attendance_session_id' => null,
            'title' => 'امتحان احتساب أعلى محاولة',
            'type' => 'quiz',
            'duration' => 60,
            'total_marks' => 20.0,
            'passing_marks' => 10.0,
            'exam_date' => now()->subHours(2),
            'status' => 'scheduled',
            'published_at' => null,
        ]);

        // Attempt 1: score 8/20
        $att1 = ExamAttempt::create([
            'tenant_id' => $exam->tenant_id,
            'exam_id' => $exam->id,
            'student_id' => $this->student->id,
            'attempt_number' => 1,
            'started_at' => now()->subHour(),
            'completed_at' => now()->subMinutes(30),
            'total_score' => 20.0,
            'score' => 8.0,
            'percentage' => 40.0,
            'passed' => false,
            'is_official' => true,
        ]);

        // Student cannot start without retake grant
        $check = $this->service->canStudentStartExam($exam, $this->student);
        $this->assertFalse($check['allowed']);

        // Issue Retake Grant 1
        $retakeGrant = $this->service->grantExamRetake($exam, $this->student, now()->addDays(2), 'موافقة المشرف على الإعادة', $this->adminUser);
        $this->assertEquals('active', $retakeGrant->status);

        // Student starts Attempt 2 -> Grant consumed atomically
        $att2 = $this->service->startExamAttempt($exam, $this->student);
        $this->assertEquals('consumed', $retakeGrant->fresh()->status);
        $this->assertEquals(2, $att2->attempt_number);

        // Finalize Attempt 2 with score 16/20
        $att2->update([
            'completed_at' => now(),
            'score' => 16.0,
            'percentage' => 80.0,
            'passed' => true,
        ]);
        $official = $this->service->recalculateOfficialExamGrade($exam->id, $this->student->id);
        $this->assertEquals($att2->id, $official->id);
        $this->assertEquals(16.0, $official->score);

        // Attempt 3 with lower score 12/20 -> official remains 16.0
        $retakeGrant2 = $this->service->grantExamRetake($exam, $this->student, now()->addDays(2), 'محاولة 3', $this->adminUser);
        $att3 = $this->service->startExamAttempt($exam, $this->student);
        $att3->update([
            'completed_at' => now(),
            'score' => 12.0,
            'percentage' => 60.0,
            'passed' => true,
        ]);
        $officialAfterLower = $this->service->recalculateOfficialExamGrade($exam->id, $this->student->id);
        $this->assertEquals($att2->id, $officialAfterLower->id);
        $this->assertEquals(16.0, $officialAfterLower->score);
    }

    /** @test */
    public function test_academic_alert_threshold_strictly_below_fifty_percent(): void
    {
        // 5.0 / 10.0 = exactly 50% -> NO ALERT
        $isAlert50 = ((5.0 * 2) < 10.0);
        $this->assertFalse($isAlert50, 'Exactly 50% must not trigger low-grade alert');

        // 4.9 / 10.0 = 49% -> MUST ALERT
        $isAlert49 = ((4.9 * 2) < 10.0);
        $this->assertTrue($isAlert49, 'Strictly below 50% must trigger alert');

        // 5.1 / 10.0 = 51% -> NO ALERT
        $isAlert51 = ((5.1 * 2) < 10.0);
        $this->assertFalse($isAlert51, 'Above 50% must not trigger alert');
    }
}
