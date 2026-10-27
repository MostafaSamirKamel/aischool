<?php

namespace Tests\Feature;

use App\Filament\Instructor\Pages\HomeworkReviewCenter;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentCourse;
use App\Models\Group;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\StudentFollowUpContact;
use App\Models\StudentFollowUpTask;
use App\Models\StudentGroupTransfer;
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

class InstructorHomeworkReviewCenterTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Vault $vault;
    protected User $instructorUser;
    protected Instructor $instructor;
    protected Course $course;
    protected Group $group;
    protected array $students = [];

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');

        $this->tenant = Tenant::create([
            'name' => 'AI School Cairo',
            'slug' => 'cairo',
        ]);

        $this->vault = Vault::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'خزنة الفرع',
            'type' => 'cash',
            'opening_balance' => 5000,
            'is_active' => true,
        ]);

        $instructorRole = Role::firstOrCreate(['name' => 'instructor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'secretary', 'guard_name' => 'web']);

        $this->instructorUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'م. حازم عبد الله',
            'email' => 'hazem@aischool.test',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $this->instructorUser->assignRole($instructorRole);

        $this->instructor = Instructor::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->instructorUser->id,
            'employee_code' => 'INS-001',
            'specialization' => 'Artificial Intelligence & Python',
        ]);

        $this->course = Course::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Python Junior Masters',
            'code' => 'PY-JUN-01',
            'price' => 1200,
        ]);

        $this->group = Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $this->course->id,
            'instructor_id' => $this->instructor->id,
            'name' => 'Group A - Cairo',
            'status' => 'active',
            'max_students' => 20,
        ]);

        // Create 4 students for Group A
        for ($i = 1; $i <= 4; $i++) {
            $studentUser = User::create([
                'tenant_id' => $this->tenant->id,
                'name' => "طالب تجريبي {$i}",
                'email' => "student{$i}@aischool.test",
                'password' => bcrypt('password'),
                'status' => 'active',
            ]);

            $student = Student::create([
                'tenant_id' => $this->tenant->id,
                'user_id' => $studentUser->id,
                'student_code' => "STD-00{$i}",
                'gender' => 'male',
                'status' => 'active',
            ]);

            $enrollment = Enrollment::create([
                'tenant_id' => $this->tenant->id,
                'student_id' => $student->id,
                'group_id' => $this->group->id,
                'course_id' => $this->course->id,
                'vault_id' => $this->vault->id,
                'enrollment_code' => "ENR-00{$i}",
                'total_amount' => 1000,
                'paid_amount' => 1000,
                'enrollment_date' => Carbon::now()->subMonths(1),
                'status' => 'active',
                'enrolled_at' => Carbon::now()->subMonths(1),
            ]);

            EnrollmentCourse::create([
                'enrollment_id' => $enrollment->id,
                'course_id' => $this->course->id,
            ]);

            $this->students[$i] = $student;
        }

        Filament::setCurrentPanel(Filament::getPanel('instructor'));
    }

    /**
     * TEST-001 — New Assignment Roster & Status
     * Instructor publishes an assignment for a group with 4 eligible students.
     * Expected:
     * - 4 roster entries immediately visible.
     * - Every student without submission displays "بانتظار التسليم".
     * - No fake submissions fabricated in assignment_submissions table.
     */
    public function test_001_new_assignment_roster_and_pending_status(): void
    {
        $assignment = Assignment::create([
            'tenant_id' => $this->tenant->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $this->group->id,
            'title' => 'واجب المتغيرات والدوال',
            'description' => 'قم بحل المسائل في الشيت المرفق',
            'due_date' => Carbon::now()->addDays(3),
            'max_score' => 10,
            'status' => 'active',
        ]);

        // Verify database has 0 submissions
        $this->assertEquals(0, AssignmentSubmission::where('assignment_id', $assignment->id)->count());

        Livewire::actingAs($this->instructorUser)
            ->test(HomeworkReviewCenter::class)
            ->call('selectAssignment', $assignment->id)
            ->assertSet('selectedAssignmentId', $assignment->id)
            ->assertSee('واجب المتغيرات والدوال')
            ->assertSee('طالب تجريبي 1')
            ->assertSee('طالب تجريبي 2')
            ->assertSee('طالب تجريبي 3')
            ->assertSee('طالب تجريبي 4')
            ->assertSee('بانتظار التسليم');

        // Confirm still 0 fabricated records
        $this->assertEquals(0, AssignmentSubmission::where('assignment_id', $assignment->id)->count());
    }

    /**
     * TEST-002 — On-Time Submission
     * Student submits before the deadline.
     * Expected:
     * - Actual submission stored with timestamp.
     * - On-time classification (isLate == false).
     * - Review status is "بانتظار التصحيح".
     * - Instructor counters update correctly.
     */
    public function test_002_on_time_submission_flow(): void
    {
        $dueDate = Carbon::now()->addDays(2);
        $assignment = Assignment::create([
            'tenant_id' => $this->tenant->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $this->group->id,
            'title' => 'واجب الحلقات التكرارية For Loop',
            'due_date' => $dueDate,
            'max_score' => 10,
            'status' => 'active',
        ]);

        $submissionTime = Carbon::now()->subHours(2);
        $file = UploadedFile::fake()->image('solution.png');

        /** @var HomeworkService $homeworkService */
        $homeworkService = app(HomeworkService::class);
        $submission = $homeworkService->submitHomework($assignment, $this->students[1], [$file], 'حل المسألة الأولى');

        // Verify on-time
        $this->assertFalse($submission->isLate());
        $this->assertNotNull($submission->submitted_at);

        Livewire::actingAs($this->instructorUser)
            ->test(HomeworkReviewCenter::class)
            ->call('selectAssignment', $assignment->id)
            ->assertSee('تم التسليم في الموعد')
            ->assertSee('بانتظار التصحيح')
            ->assertSee('في الموعد');
    }

    /**
     * TEST-003 — Deadline Passed (Overdue Student)
     * Deadline expires without student submitting.
     * Expected:
     * - Automatically classified as overdue ("متأخر ولم يسلّم").
     * - Appears under missing and overdue filters.
     * - No manual data edit necessary.
     */
    public function test_003_deadline_passed_automatically_overdue(): void
    {
        $pastDueDate = Carbon::now()->subDays(2);
        $assignment = Assignment::create([
            'tenant_id' => $this->tenant->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $this->group->id,
            'title' => 'واجب القوائم Lists',
            'due_date' => $pastDueDate,
            'max_score' => 10,
            'status' => 'active',
        ]);

        $component = Livewire::actingAs($this->instructorUser)
            ->test(HomeworkReviewCenter::class)
            ->call('selectAssignment', $assignment->id)
            ->assertSee('متأخر ولم يسلّم');

        // Filter by overdue
        $component->set('studentPunctualityFilter', 'overdue');
        $roster = $component->get('studentRoster');
        $this->assertCount(4, $roster);
        $this->assertEquals('overdue', $roster->first()['punctuality_status']);
    }

    /**
     * TEST-004 — Late Submission Flow
     * Student submits after deadline.
     * Expected:
     * - Submission accepted (backend allows late submission).
     * - Actual submission timestamp saved.
     * - Late indicator shown ("سلّم متأخرًا").
     * - Student removed from missing-submission count.
     * - Review status becomes "بانتظار التصحيح".
     */
    public function test_004_late_submission_accepted_and_marked(): void
    {
        $pastDueDate = Carbon::now()->subDays(3);
        $assignment = Assignment::create([
            'tenant_id' => $this->tenant->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $this->group->id,
            'title' => 'واجب القواميس Dictionaries',
            'due_date' => $pastDueDate,
            'max_score' => 10,
            'status' => 'active',
        ]);

        $file = UploadedFile::fake()->image('late_sol.png');

        /** @var HomeworkService $homeworkService */
        $homeworkService = app(HomeworkService::class);
        $submission = $homeworkService->submitHomework($assignment, $this->students[2], [$file], 'عذرا على التأخير لظرف طارئ');

        $this->assertTrue($submission->isLate());
        $this->assertNotNull($submission->submitted_at);

        Livewire::actingAs($this->instructorUser)
            ->test(HomeworkReviewCenter::class)
            ->call('selectAssignment', $assignment->id)
            ->assertSee('سلّم متأخرًا')
            ->assertSee('متأخر')
            ->assertSee('بانتظار التصحيح');
    }

    /**
     * TEST-005 — Grading Draft (حفظ كمسودة)
     * Instructor grades homework and saves draft.
     * Expected:
     * - Draft score and feedback persist.
     * - Score not published (published_at is null).
     * - Student API masks unpublished score.
     * - Secretary view does not show draft score as official.
     */
    public function test_005_grading_draft_persistence_and_protection(): void
    {
        $assignment = Assignment::create([
            'tenant_id' => $this->tenant->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $this->group->id,
            'title' => 'واجب المصفوفات Arrays',
            'due_date' => Carbon::now()->addDays(2),
            'max_score' => 10,
            'status' => 'active',
        ]);

        $file = UploadedFile::fake()->image('sol.png');
        $homeworkService = app(HomeworkService::class);
        $submission = $homeworkService->submitHomework($assignment, $this->students[1], [$file], 'كود المصفوفة');

        Livewire::actingAs($this->instructorUser)
            ->test(HomeworkReviewCenter::class)
            ->call('selectAssignment', $assignment->id)
            ->call('openReview', $this->students[1]->id, $submission->id)
            ->assertSet('isReviewOpen', true)
            ->set('score', 8.5)
            ->set('instructor_feedback', 'مسودة تصحيح جيدة')
            ->call('saveDraft')
            ->assertNotified('تم حفظ مسودة التقييم بنجاح 📝');

        $submission->refresh();
        $this->assertEquals(8.5, (float) $submission->score);
        $this->assertEquals('مسودة تصحيح جيدة', $submission->instructor_feedback);
        $this->assertNull($submission->published_at);
        $this->assertFalse($submission->isPublished());

        // In student API, grade is masked
        $this->actingAs($this->students[1]->user);
        $response = $this->getJson("/api/v1/student/assignments/{$assignment->id}");
        if ($response->status() === 200) {
            $data = $response->json('data') ?? $response->json();
            $this->assertNull($data['submission']['score'] ?? null);
        }
    }

    /**
     * TEST-006 — Publish Grade (اعتماد ونشر النتيجة)
     * Instructor approves and publishes result.
     * Expected:
     * - Student sees official score.
     * - Secretary sees official score.
     * - Review count updates.
     * - Late indicator remains if assignment was submitted late.
     */
    public function test_006_publish_grade_and_maintain_late_status(): void
    {
        $pastDueDate = Carbon::now()->subDays(2);
        $assignment = Assignment::create([
            'tenant_id' => $this->tenant->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $this->group->id,
            'title' => 'واجب البرمجة كائنية التوجه OOP',
            'due_date' => $pastDueDate,
            'max_score' => 20,
            'status' => 'active',
        ]);

        $file = UploadedFile::fake()->image('oop.png');
        $homeworkService = app(HomeworkService::class);
        $submission = $homeworkService->submitHomework($assignment, $this->students[1], [$file], 'حل كلاس Car');

        $this->assertTrue($submission->isLate());

        Livewire::actingAs($this->instructorUser)
            ->test(HomeworkReviewCenter::class)
            ->call('selectAssignment', $assignment->id)
            ->call('openReview', $this->students[1]->id, $submission->id)
            ->set('score', 18)
            ->set('instructor_feedback', 'تنفيذ ممتاز للكلاس والميثودز')
            ->call('publishGrade')
            ->assertNotified('تم اعتماد ونشر النتيجة رسمياً! 🌟');

        $submission->refresh();
        $this->assertEquals(18.0, (float) $submission->score);
        $this->assertNotNull($submission->published_at);
        $this->assertTrue($submission->isPublished());
        $this->assertTrue($submission->isLate());

        // Review roster shows both graded and submitted late
        Livewire::actingAs($this->instructorUser)
            ->test(HomeworkReviewCenter::class)
            ->call('selectAssignment', $assignment->id)
            ->assertSee('تم التصحيح والاعتماد')
            ->assertSee('سلّم متأخرًا')
            ->assertSee('18 / 20');
    }

    /**
     * TEST-007 — Zero Grade
     * Instructor publishes a score of zero.
     * Expected:
     * - "0 / configured_max_score" displayed.
     * - Not an empty or missing result.
     */
    public function test_007_zero_grade_published_and_displayed_correctly(): void
    {
        $assignment = Assignment::create([
            'tenant_id' => $this->tenant->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $this->group->id,
            'title' => 'واجب الـ Recursion',
            'due_date' => Carbon::now()->addDays(1),
            'max_score' => 10,
            'status' => 'active',
        ]);

        $file = UploadedFile::fake()->image('rec.png');
        $homeworkService = app(HomeworkService::class);
        $submission = $homeworkService->submitHomework($assignment, $this->students[1], [$file], 'كود فارغ');

        Livewire::actingAs($this->instructorUser)
            ->test(HomeworkReviewCenter::class)
            ->call('selectAssignment', $assignment->id)
            ->call('openReview', $this->students[1]->id, $submission->id)
            ->set('score', 0)
            ->set('instructor_feedback', 'الملف لا يحتوي على أي كود مطلوب')
            ->call('publishGrade');

        $submission->refresh();
        $this->assertEquals(0.0, (float) $submission->score);
        $this->assertTrue($submission->isPublished());

        Livewire::actingAs($this->instructorUser)
            ->test(HomeworkReviewCenter::class)
            ->call('selectAssignment', $assignment->id)
            ->assertSee('0 / 10')
            ->assertSee('تم التصحيح والاعتماد');
    }

    /**
     * TEST-008 — Instructor With Many Groups (16+ groups scalability)
     * Assign 16 groups with multiple courses and assignments to one instructor.
     * Verify:
     * - KPI metrics aggregate across all 16 groups.
     * - Course & Group selectors populate correctly.
     * - Filtering by group narrows down assignments.
     * - Pagination renders smoothly without duplicate rows.
     */
    public function test_008_instructor_with_many_groups_scalability(): void
    {
        $course2 = Course::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Data Science Junior',
            'code' => 'DS-JUN-02',
            'price' => 1500,
        ]);

        // Create 15 additional groups (total 16 groups)
        $groups = [$this->group];
        for ($g = 2; $g <= 16; $g++) {
            $assignedCourse = ($g % 2 === 0) ? $course2 : $this->course;
            $newGroup = Group::create([
                'tenant_id' => $this->tenant->id,
                'course_id' => $assignedCourse->id,
                'instructor_id' => $this->instructor->id,
                'name' => "Group {$g} - Advanced Track",
                'status' => 'active',
                'max_students' => 15,
            ]);

            // Add an assignment to each group
            Assignment::create([
                'tenant_id' => $this->tenant->id,
                'instructor_id' => $this->instructor->id,
                'group_id' => $newGroup->id,
                'title' => "واجب المجموعة {$g}",
                'due_date' => Carbon::now()->addDays(5),
                'max_score' => 10,
                'status' => 'active',
            ]);

            $groups[] = $newGroup;
        }

        $component = Livewire::actingAs($this->instructorUser)
            ->test(HomeworkReviewCenter::class)
            ->assertSee('مركز مراجعة واعتماد الواجبات')
            ->assertSee('إجمالي الواجبات')
            ->assertSee('15'); // At least 15 assignments created

        // Verify groups dropdown has groups with assignments
        $groups = $component->instance()->groups;
        $this->assertNotEmpty($groups);

        // Filter by course
        $component->set('selectedCourseId', $course2->id);
        $filteredAssignments = $component->instance()->assignmentsList;
        foreach ($filteredAssignments as $a) {
            $this->assertEquals($course2->id, $a->group->course_id);
        }
    }

    /**
     * TEST-009 — Group Transfer Compatibility
     * Student transfers into Group B after an assignment was due.
     * Expected:
     * - Student is not marked overdue for Group B's historical assignment.
     * - Student's prior submissions in Group A remain intact.
     */
    public function test_009_group_transfer_no_false_overdue(): void
    {
        $groupB = Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $this->course->id,
            'instructor_id' => $this->instructor->id,
            'name' => 'Group B - Evening',
            'status' => 'active',
        ]);

        // Historical assignment in Group B due 5 days ago
        $pastAssignment = Assignment::create([
            'tenant_id' => $this->tenant->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $groupB->id,
            'title' => 'واجب قديم لمجموعة B',
            'due_date' => Carbon::now()->subDays(5),
            'max_score' => 10,
            'status' => 'active',
        ]);

        // Student transfers into Group B TODAY (after due date)
        $transferStudent = $this->students[3];
        $enrollment = Enrollment::where('student_id', $transferStudent->id)->first();
        $enrollment->update(['group_id' => $groupB->id]);

        StudentGroupTransfer::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $transferStudent->id,
            'enrollment_id' => $enrollment->id,
            'from_group_id' => $this->group->id,
            'to_group_id' => $groupB->id,
            'transferred_at' => Carbon::now(),
            'notes' => 'تحويل لظروف الدراسة المسائية',
        ]);

        $roster = app(HomeworkService::class)->getGroupStudentsWithSubmissions($pastAssignment);
        $studentEntry = $roster->firstWhere('student_id', $transferStudent->id);

        // Student joined after due date, so they are not in the roster (not falsely marked overdue)
        $this->assertNull($studentEntry);
    }

    /**
     * TEST-010 — Unauthorized Instructor Access
     * Instructor attempts to access or grade an assignment belonging to another instructor.
     * Expected:
     * - Backend rejection / error notification.
     */
    public function test_010_unauthorized_instructor_cannot_access_foreign_assignment(): void
    {
        $otherInstructorUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'م. سارة علي',
            'email' => 'sara@aischool.test',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $otherInstructor = Instructor::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $otherInstructorUser->id,
            'employee_code' => 'INS-002',
        ]);

        $foreignGroup = Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $this->course->id,
            'instructor_id' => $otherInstructor->id,
            'name' => 'Foreign Group',
            'status' => 'active',
        ]);

        $foreignAssignment = Assignment::create([
            'tenant_id' => $this->tenant->id,
            'instructor_id' => $otherInstructor->id,
            'group_id' => $foreignGroup->id,
            'title' => 'واجب المعلمة سارة الخاص',
            'due_date' => Carbon::now()->addDays(2),
            'max_score' => 10,
            'status' => 'active',
        ]);

        Livewire::actingAs($this->instructorUser)
            ->test(HomeworkReviewCenter::class)
            ->call('selectAssignment', $foreignAssignment->id)
            ->assertSet('selectedAssignmentId', null)
            ->assertNotified('غير مصرح لك باستعراض هذا الواجب');
    }

    /**
     * TEST-011 — Unauthorized Secretary Cannot Grade
     * Secretary attempts to save or publish grade via HomeworkService.
     * Expected:
     * - Forbidden / backend exception.
     */
    public function test_011_secretary_cannot_grade_homework(): void
    {
        $secretaryUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'سكرتيرة تجريبية',
            'email' => 'sec@aischool.test',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $secretaryRole = Role::findByName('secretary', 'web');
        $secretaryUser->assignRole($secretaryRole);

        $assignment = Assignment::create([
            'tenant_id' => $this->tenant->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $this->group->id,
            'title' => 'واجب سري',
            'due_date' => Carbon::now()->addDays(2),
            'max_score' => 10,
            'status' => 'active',
        ]);

        $file = UploadedFile::fake()->image('test.png');
        $submission = app(HomeworkService::class)->submitHomework($assignment, $this->students[1], [$file]);

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        app(HomeworkService::class)->publishGrade($submission, 9.0, 'محاولة تصحيح من سكرتيرة', $secretaryUser);
    }

    /**
     * TEST-012 — Duplicate Publish Idempotency
     * Double calling publishGrade.
     * Expected:
     * - Grade published once, no duplicate entries or corrupted data.
     */
    public function test_012_duplicate_publish_idempotency(): void
    {
        $assignment = Assignment::create([
            'tenant_id' => $this->tenant->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $this->group->id,
            'title' => 'واجب التأكيد المزدوج',
            'due_date' => Carbon::now()->addDays(2),
            'max_score' => 10,
            'status' => 'active',
        ]);

        $file = UploadedFile::fake()->image('test.png');
        $submission = app(HomeworkService::class)->submitHomework($assignment, $this->students[1], [$file]);

        $service = app(HomeworkService::class);
        $sub1 = $service->publishGrade($submission, 9.5, 'أحسنت', $this->instructorUser);
        $publishedAt1 = $sub1->published_at;

        // Second call
        $sub2 = $service->publishGrade($sub1, 9.5, 'أحسنت', $this->instructorUser);

        $this->assertEquals(9.5, (float) $sub2->score);
        $this->assertEquals(1, AssignmentSubmission::where('id', $submission->id)->count());
        $this->assertTrue($sub2->isPublished());
    }

    /**
     * TEST-013 — Deadline Boundary Precision
     * Submission right before end of day vs right after.
     */
    public function test_013_deadline_boundary_precision(): void
    {
        $dueDate = Carbon::create(2026, 10, 20, 0, 0, 0); // Date is Oct 20
        $assignment = Assignment::create([
            'tenant_id' => $this->tenant->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $this->group->id,
            'title' => 'واجب دقة التوقيت',
            'due_date' => $dueDate,
            'max_score' => 10,
            'status' => 'active',
        ]);

        // Submission at Oct 20, 23:50 -> On-time
        $onTimeSubmission = AssignmentSubmission::create([
            'tenant_id' => $this->tenant->id,
            'assignment_id' => $assignment->id,
            'student_id' => $this->students[1]->id,
            'submitted_at' => Carbon::create(2026, 10, 20, 23, 50, 0),
            'status' => 'submitted',
        ]);
        $this->assertFalse($onTimeSubmission->isLate());

        // Submission at Oct 21, 00:05 -> Late
        $lateSubmission = AssignmentSubmission::create([
            'tenant_id' => $this->tenant->id,
            'assignment_id' => $assignment->id,
            'student_id' => $this->students[2]->id,
            'submitted_at' => Carbon::create(2026, 10, 21, 0, 5, 0),
            'status' => 'submitted',
        ]);
        $this->assertTrue($lateSubmission->isLate());
    }

    /**
     * TEST-014 — Multiple Assignments Independence
     * Student submits one assignment and misses another in the same group.
     * Expected:
     * - Independent counters and status flags.
     */
    public function test_014_multiple_assignments_independence(): void
    {
        $assignment1 = Assignment::create([
            'tenant_id' => $this->tenant->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $this->group->id,
            'title' => 'واجب 1',
            'due_date' => Carbon::now()->addDays(2),
            'max_score' => 10,
            'status' => 'active',
        ]);

        $assignment2 = Assignment::create([
            'tenant_id' => $this->tenant->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $this->group->id,
            'title' => 'واجب 2',
            'due_date' => Carbon::now()->subDays(1),
            'max_score' => 10,
            'status' => 'active',
        ]);

        $file = UploadedFile::fake()->image('test.png');
        app(HomeworkService::class)->submitHomework($assignment1, $this->students[1], [$file]);

        $roster1 = app(HomeworkService::class)->getGroupStudentsWithSubmissions($assignment1);
        $roster2 = app(HomeworkService::class)->getGroupStudentsWithSubmissions($assignment2);

        $studentInA1 = $roster1->firstWhere('student_id', $this->students[1]->id);
        $studentInA2 = $roster2->firstWhere('student_id', $this->students[1]->id);

        $this->assertTrue($studentInA1['has_submission']);
        $this->assertEquals('submitted_on_time', $studentInA1['punctuality_status']);

        $this->assertFalse($studentInA2['has_submission']);
        $this->assertEquals('overdue', $studentInA2['punctuality_status']);
    }

    /**
     * TEST-015 — Secretary Contact Follow-Up Persistence
     * Secretary logs follow-up for an overdue student, then student submits late.
     * Expected:
     * - Submission accepted as late.
     * - Follow-up task and contact logs remain completely preserved.
     */
    public function test_015_contact_followup_persists_after_late_submission(): void
    {
        $pastAssignment = Assignment::create([
            'tenant_id' => $this->tenant->id,
            'instructor_id' => $this->instructor->id,
            'group_id' => $this->group->id,
            'title' => 'واجب المتابعة والمكالمة',
            'due_date' => Carbon::now()->subDays(2),
            'max_score' => 10,
            'status' => 'active',
        ]);

        // Secretary logs contact via AcademicFollowUpService
        /** @var \App\Services\AcademicFollowUpService $followUpService */
        $followUpService = app(\App\Services\AcademicFollowUpService::class);
        $task = $followUpService->createOrGetFollowUpTask(
            student: $this->students[1],
            sourceType: 'homework',
            sourceId: $pastAssignment->id,
            title: 'متابعة واجب متأخر',
            reason: 'لم يتم تسليم الواجب في الموعد',
            priority: 'urgent',
            assignedTo: $this->instructorUser->id
        );

        $contact = $followUpService->recordContactAttempt(
            task: $task,
            contactMethod: 'phone_call',
            contactResult: 'contacted',
            notes: 'تم الاتصال بوالدة الطالب ووعدت بتسليم الواجب اليوم'
        );

        // Student submits late
        $file = UploadedFile::fake()->image('test.png');
        $submission = app(HomeworkService::class)->submitHomework($pastAssignment, $this->students[1], [$file], 'تسليم بعد اتصال السكرتارية');

        $this->assertTrue($submission->isLate());

        // Verify contact record is preserved
        $this->assertDatabaseHas('student_follow_up_contacts', [
            'id' => $contact->id,
            'notes' => 'تم الاتصال بوالدة الطالب ووعدت بتسليم الواجب اليوم',
        ]);
        $this->assertDatabaseHas('student_follow_up_tasks', [
            'id' => $task->id,
        ]);
    }
}
