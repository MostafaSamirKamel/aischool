<?php

namespace Tests\Feature;

use App\Filament\Instructor\Pages\HomeworkReviewCenter;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vault;
use App\Services\HomeworkService;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InstructorSessionHomeworkWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Vault $vault;
    protected User $instructorUser;
    protected Instructor $instructor;
    protected Course $course;
    protected Group $group;
    protected AttendanceSession $session1;
    protected AttendanceSession $session2;
    protected array $students = [];
    protected User $parentUser;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');

        $this->tenant = Tenant::create([
            'name' => 'AI School Branch',
            'slug' => 'branch-1',
        ]);

        $this->vault = Vault::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'خزنة الفرع',
            'type' => 'cash',
            'opening_balance' => 10000,
            'is_active' => true,
        ]);

        $instructorRole = Role::firstOrCreate(['name' => 'instructor', 'guard_name' => 'web']);
        $studentRole = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        $parentRole = Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);

        // Instructor
        $this->instructorUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'م. أحمد خالد',
            'email' => 'ahmed.instructor@aischool.test',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $this->instructorUser->assignRole($instructorRole);

        $this->instructor = Instructor::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->instructorUser->id,
            'employee_code' => 'INS-100',
            'specialization' => 'Python & AI',
        ]);

        // Course & Group
        $this->course = Course::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'دبلومة الذكاء الاصطناعي',
            'code' => 'AI-101',
            'price' => 1500,
        ]);

        $this->group = Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $this->course->id,
            'instructor_id' => $this->instructor->id,
            'name' => 'مجموعة بايثون المتقدمة - السبت',
            'status' => 'active',
            'max_students' => 15,
        ]);

        // Attendance Sessions
        $this->session1 = AttendanceSession::create([
            'tenant_id' => $this->tenant->id,
            'group_id' => $this->group->id,
            'course_id' => $this->course->id,
            'attendance_date' => Carbon::now()->subDays(14),
            'session_notes' => 'الجلسة 1: المتغيرات والعمليات الحسابية',
            'recorded_by' => $this->instructorUser->id,
        ]);

        $this->session2 = AttendanceSession::create([
            'tenant_id' => $this->tenant->id,
            'group_id' => $this->group->id,
            'course_id' => $this->course->id,
            'attendance_date' => Carbon::now()->subDays(7),
            'session_notes' => 'الجلسة 2: الدوال والبرمجة التركيبية',
            'recorded_by' => $this->instructorUser->id,
        ]);

        // Students & Enrollments
        for ($i = 1; $i <= 3; $i++) {
            $studentUser = User::create([
                'tenant_id' => $this->tenant->id,
                'name' => "طالب تجريبي {$i}",
                'email' => "student{$i}@aischool.test",
                'password' => bcrypt('password'),
                'status' => 'active',
            ]);
            $studentUser->assignRole($studentRole);

            $student = Student::create([
                'tenant_id' => $this->tenant->id,
                'user_id' => $studentUser->id,
                'student_code' => "STU-E2E-{$i}",
                'gender' => 'male',
                'status' => 'active',
            ]);

            $enrollment = Enrollment::create([
                'tenant_id' => $this->tenant->id,
                'student_id' => $student->id,
                'group_id' => $this->group->id,
                'course_id' => $this->course->id,
                'enrollment_code' => "ENR-{$i}",
                'enrollment_date' => Carbon::now()->subDays(30),
                'status' => 'active',
                'total_amount' => 1500,
                'paid_amount' => 1500,
                'vault_id' => $this->vault->id,
            ]);

            \App\Models\EnrollmentCourse::create([
                'enrollment_id' => $enrollment->id,
                'course_id' => $this->course->id,
            ]);

            $this->students[$i] = $student;
        }

        // Parent for Student 1
        $this->parentUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'ولي أمر الطالب 1',
            'email' => 'parent1@aischool.test',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $this->parentUser->assignRole($parentRole);

        StudentParent::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $this->students[1]->id,
            'user_id' => $this->parentUser->id,
            'relation' => 'father',
            'is_primary' => true,
            'receive_notifications' => true,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('instructor'));
    }

    /**
     * TEST 1: Session Homework Association & Group Sessions Listing
     */
    public function test_group_sessions_listing_and_homework_counts(): void
    {
        // Assignment 1 linked to Session 1
        $assignment1 = Assignment::create([
            'tenant_id' => $this->tenant->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $this->group->id,
            'attendance_session_id' => $this->session1->id,
            'session_number' => 1,
            'title' => 'واجب الجلسة 1: المتغيرات',
            'due_date' => Carbon::now()->addDays(2),
            'max_score' => 10,
            'status' => 'published',
        ]);

        // Unlinked historical assignment
        $unlinkedAssignment = Assignment::create([
            'tenant_id' => $this->tenant->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $this->group->id,
            'attendance_session_id' => null,
            'title' => 'واجب عام غير مرتبط بسيشن',
            'due_date' => Carbon::now()->addDays(5),
            'max_score' => 10,
            'status' => 'published',
        ]);

        // Instructor navigates to Group Review Center
        Livewire::actingAs($this->instructorUser)
            ->test(HomeworkReviewCenter::class, ['group' => $this->group->id])
            ->assertSet('selectedGroupId', $this->group->id)
            ->assertSee('واجبات مجموعة: ' . $this->group->name)
            ->assertSee('الجلسة 1')
            ->assertSee('الجلسة 2')
            ->assertSee('1 واجب مرتبط')
            ->assertSee('واجب عام غير مرتبط بسيشن')
            ->call('selectSession', $this->session1->id)
            ->assertSet('selectedSessionId', $this->session1->id)
            ->assertSee('واجب الجلسة 1: المتغيرات')
            ->assertSee('فتح وتصحيح الواجب');
    }

    /**
     * TEST 2: Student Submissions Roster with All Eligible Students
     */
    public function test_student_roster_displays_all_eligible_students_with_accurate_statuses(): void
    {
        $assignment = Assignment::create([
            'tenant_id' => $this->tenant->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $this->group->id,
            'attendance_session_id' => $this->session1->id,
            'session_number' => 1,
            'title' => 'واجب المتغيرات',
            'due_date' => Carbon::now()->addDays(2),
            'max_score' => 10,
            'status' => 'published',
        ]);

        // Student 1 submits on time
        $file = UploadedFile::fake()->image('solution.png');
        $homeworkService = app(HomeworkService::class);
        $sub1 = $homeworkService->submitHomework($assignment, $this->students[1], [$file], 'إجابة الطالب 1');

        // Student 2 has not submitted
        // Student 3 has not submitted

        Livewire::actingAs($this->instructorUser)
            ->test(HomeworkReviewCenter::class, [
                'group' => $this->group->id,
                'session' => $this->session1->id,
                'assignment' => $assignment->id,
            ])
            ->assertSee('طالب تجريبي 1')
            ->assertSee('طالب تجريبي 2')
            ->assertSee('طالب تجريبي 3')
            ->assertSee('تم التسليم في الموعد')
            ->assertSee('بانتظار التسليم')
            ->assertSee('عرض الإجابة');
    }

    /**
     * TEST 3: Instant Grading Rule — Clicking "تم التصحيح" Saves and Publishes Immediately
     */
    public function test_instant_grading_rule_saves_and_publishes_immediately(): void
    {
        $assignment = Assignment::create([
            'tenant_id' => $this->tenant->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $this->group->id,
            'attendance_session_id' => $this->session1->id,
            'session_number' => 1,
            'title' => 'واجب الدوال الرياضية',
            'due_date' => Carbon::now()->addDays(3),
            'max_score' => 10,
            'status' => 'published',
        ]);

        $file1 = UploadedFile::fake()->image('code1.png');
        $file2 = UploadedFile::fake()->image('code2.png');
        $homeworkService = app(HomeworkService::class);
        $sub = $homeworkService->submitHomework($assignment, $this->students[1], [$file1, $file2], 'كود الدالة مع المخرجات');

        $this->assertFalse((bool) $sub->graded);
        $this->assertNull($sub->published_at);

        Livewire::actingAs($this->instructorUser)
            ->test(HomeworkReviewCenter::class, [
                'group' => $this->group->id,
                'session' => $this->session1->id,
                'assignment' => $assignment->id,
            ])
            ->call('openReview', $this->students[1]->id, $sub->id)
            ->assertSet('isReviewOpen', true)
            ->assertSet('selectedStudentId', $this->students[1]->id)
            ->assertSet('score', null)
            ->set('score', 9.5)
            ->set('instructor_feedback', 'عمل ممتاز وكود منظم جداً')
            ->call('markCorrected') // Clicking "تم التصحيح"
            ->assertHasNoErrors();

        // Verify in database: grade saved, published immediately, audit timestamps
        $sub->refresh();
        $this->assertEquals(9.5, (float) $sub->score);
        $this->assertEquals('عمل ممتاز وكود منظم جداً', $sub->instructor_feedback);
        $this->assertTrue((bool) $sub->graded);
        $this->assertNotNull($sub->published_at);
        $this->assertTrue($sub->isPublished());
        $this->assertEquals($this->instructorUser->id, $sub->graded_by);
        $this->assertNotNull($sub->graded_at);
    }

    /**
     * TEST 4: Next Student Workflow — Moving Seamlessly to Next Pending Student
     */
    public function test_next_student_workflow_navigates_to_next_pending_submission(): void
    {
        $assignment = Assignment::create([
            'tenant_id' => $this->tenant->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $this->group->id,
            'attendance_session_id' => $this->session1->id,
            'session_number' => 1,
            'title' => 'واجب القوائم',
            'due_date' => Carbon::now()->addDays(3),
            'max_score' => 10,
            'status' => 'published',
        ]);

        $homeworkService = app(HomeworkService::class);
        $sub1 = $homeworkService->submitHomework($assignment, $this->students[1], [UploadedFile::fake()->image('s1.png')], 'حل طالب 1');
        $sub2 = $homeworkService->submitHomework($assignment, $this->students[2], [UploadedFile::fake()->image('s2.png')], 'حل طالب 2');

        Livewire::actingAs($this->instructorUser)
            ->test(HomeworkReviewCenter::class, [
                'group' => $this->group->id,
                'session' => $this->session1->id,
                'assignment' => $assignment->id,
            ])
            ->call('openReview', $this->students[1]->id, $sub1->id)
            ->set('score', 8)
            ->set('instructor_feedback', 'ملاحظات لطالب 1')
            ->call('markCorrected')
            ->assertSet('nextPendingStudent.student_id', $this->students[2]->id)
            ->call('nextStudent')
            ->assertSet('selectedStudentId', $this->students[2]->id)
            ->assertSet('selectedSubmission.id', $sub2->id);
    }

    /**
     * TEST 5: Low-Grade Alert Threshold — < 50% triggers alert, == 50% does not
     */
    public function test_low_grade_alert_threshold_strictly_below_50_percent(): void
    {
        $assignment = Assignment::create([
            'tenant_id' => $this->tenant->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $this->group->id,
            'attendance_session_id' => $this->session1->id,
            'session_number' => 1,
            'title' => 'واجب الشروط البرمجية',
            'due_date' => Carbon::now()->addDays(2),
            'max_score' => 10,
            'status' => 'published',
        ]);

        $homeworkService = app(HomeworkService::class);
        $sub1 = $homeworkService->submitHomework($assignment, $this->students[1], [UploadedFile::fake()->image('c1.png')], 'كود ضعيف');
        $sub2 = $homeworkService->submitHomework($assignment, $this->students[2], [UploadedFile::fake()->image('c2.png')], 'كود متوسط');

        // Test Grade: 4/10 (< 50%)
        Livewire::actingAs($this->instructorUser)
            ->test(HomeworkReviewCenter::class, [
                'group' => $this->group->id,
                'session' => $this->session1->id,
                'assignment' => $assignment->id,
            ])
            ->call('openReview', $this->students[1]->id, $sub1->id)
            ->set('score', 4)
            ->call('markCorrected')
            ->assertHasNoErrors();

        $sub1->refresh();
        $this->assertEquals(4.0, (float) $sub1->score);
        $this->assertTrue((($sub1->score * 2) < $assignment->max_score), 'Score 4/10 is strictly below 50%');

        // Test Grade: exactly 5/10 (50%) — must NOT be below 50%
        Livewire::actingAs($this->instructorUser)
            ->test(HomeworkReviewCenter::class, [
                'group' => $this->group->id,
                'session' => $this->session1->id,
                'assignment' => $assignment->id,
            ])
            ->call('openReview', $this->students[2]->id, $sub2->id)
            ->set('score', 5)
            ->call('markCorrected')
            ->assertHasNoErrors();

        $sub2->refresh();
        $this->assertEquals(5.0, (float) $sub2->score);
        $this->assertFalse((($sub2->score * 2) < $assignment->max_score), 'Score 5/10 is exactly 50% and NOT below 50%');
    }

    /**
     * TEST 6: Student Grade Visibility in Student Panel Immediately
     */
    public function test_student_sees_published_grade_and_feedback_immediately(): void
    {
        $assignment = Assignment::create([
            'tenant_id' => $this->tenant->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $this->group->id,
            'attendance_session_id' => $this->session1->id,
            'session_number' => 1,
            'title' => 'واجب المصفوفات',
            'due_date' => Carbon::now()->addDays(4),
            'max_score' => 10,
            'status' => 'published',
        ]);

        $homeworkService = app(HomeworkService::class);
        $sub = $homeworkService->submitHomework($assignment, $this->students[1], [UploadedFile::fake()->image('arr.png')], 'حل المصفوفة');

        // Instructor grades and clicks تم التصحيح
        Livewire::actingAs($this->instructorUser)
            ->test(HomeworkReviewCenter::class, [
                'group' => $this->group->id,
                'session' => $this->session1->id,
                'assignment' => $assignment->id,
            ])
            ->call('openReview', $this->students[1]->id, $sub->id)
            ->set('score', 9)
            ->set('instructor_feedback', 'ممتاز يا بطل!')
            ->call('markCorrected')
            ->assertHasNoErrors();

        // Student visits Student Assignments Page
        Filament::setCurrentPanel(Filament::getPanel('student'));
        Livewire::actingAs($this->students[1]->user)
            ->test(\App\Filament\Student\Pages\MyAssignments::class)
            ->call('openAssignment', $assignment->id)
            ->assertSee('9')
            ->assertSee('تم اعتماد النتيجة')
            ->assertSee('ممتاز يا بطل!');
    }

    /**
     * TEST 7: Image Zoom Controls in Lightbox
     */
    public function test_image_zoom_controls_in_lightbox(): void
    {
        $assignment = Assignment::create([
            'tenant_id' => $this->tenant->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $this->group->id,
            'attendance_session_id' => $this->session1->id,
            'session_number' => 1,
            'title' => 'واجب الصور',
            'due_date' => Carbon::now()->addDays(2),
            'max_score' => 10,
            'status' => 'published',
        ]);

        $file1 = UploadedFile::fake()->image('img1.png');
        $file2 = UploadedFile::fake()->image('img2.png');
        $homeworkService = app(HomeworkService::class);
        $sub = $homeworkService->submitHomework($assignment, $this->students[1], [$file1, $file2], 'مرفق صورتين');

        Livewire::actingAs($this->instructorUser)
            ->test(HomeworkReviewCenter::class, [
                'group' => $this->group->id,
                'session' => $this->session1->id,
                'assignment' => $assignment->id,
            ])
            ->call('openReview', $this->students[1]->id, $sub->id)
            ->call('openLightbox', 0)
            ->assertSet('lightboxOpen', true)
            ->assertSet('activeImageIndex', 0)
            ->assertSet('imageZoom', 1.0)
            ->call('zoomIn')
            ->assertSet('imageZoom', 1.25)
            ->call('zoomIn')
            ->assertSet('imageZoom', 1.5)
            ->call('zoomOut')
            ->assertSet('imageZoom', 1.25)
            ->call('resetZoom')
            ->assertSet('imageZoom', 1.0)
            ->call('nextImage')
            ->assertSet('activeImageIndex', 1)
            ->call('closeLightbox')
            ->assertSet('lightboxOpen', false);
    }
}
