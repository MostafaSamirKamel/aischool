<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Services\HomeworkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomeworkSubmissionAndGradingTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $instructorUser;
    protected Instructor $instructor;
    protected User $studentUser;
    protected Student $student;
    protected Course $course;
    protected Group $group;
    protected Assignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');

        // Create Tenant
        $this->tenant = Tenant::create([
            'name' => 'AI School Cairo',
            'domain' => 'cairo.aischool.com',
        ]);

        // Create Course
        $this->course = Course::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Python for AI',
            'code' => 'PY-AI-101',
            'price' => 1000,
        ]);

        // Create Instructor User & Instructor Model
        $this->instructorUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Eng. Ahmed Khaled',
            'email' => 'ahmed.khaled@aischool.test',
        ]);

        $this->instructor = Instructor::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->instructorUser->id,
            'employee_code' => 'INS-001',
            'specialization' => 'AI & Data Science',
        ]);

        // Create Group
        $this->group = Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $this->course->id,
            'instructor_id' => $this->instructor->id,
            'name' => 'Group Python Saturday',
            'status' => 'active',
        ]);

        // Create Student User & Student Model
        $this->studentUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Omar Hany',
            'email' => 'omar.hany@student.test',
        ]);

        $this->student = Student::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->studentUser->id,
            'student_code' => 'STU-PY-001',
            'gender' => 'male',
        ]);

        $vault = \App\Models\Vault::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'خزنة الفرع',
            'type' => 'cash',
            'opening_balance' => 5000,
            'is_active' => true,
        ]);

        // Enroll Student in Group
        Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $this->student->id,
            'course_id' => $this->course->id,
            'group_id' => $this->group->id,
            'vault_id' => $vault->id,
            'enrollment_code' => 'ENR-001',
            'status' => 'active',
            'total_amount' => 1000,
            'paid_amount' => 1000,
            'enrollment_date' => now(),
        ]);

        // Create Homework Assignment
        $this->assignment = Assignment::create([
            'tenant_id' => $this->tenant->id,
            'group_id' => $this->group->id,
            'course_id' => $this->course->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'Python Conditions Homework',
            'description' => 'حل التمارين من 1 إلى 5 بخط اليد وصور الحل.',
            'due_date' => now()->addDays(5),
            'max_score' => 10,
            'status' => 'active',
        ]);
    }

    /**
     * Complete Workflow Test:
     * Student uploads 3 images -> Instructor reviews -> Saves draft -> Student cannot see draft
     * -> Instructor publishes 8.5/10 with feedback -> Student sees published grade.
     */
    public function test_complete_student_upload_and_instructor_grading_workflow(): void
    {
        $homeworkService = app(HomeworkService::class);

        // 1. Student uploads 3 homework images
        $image1 = UploadedFile::fake()->image('solution_page1.jpg', 1200, 1600)->size(1500);
        $image2 = UploadedFile::fake()->image('solution_page2.png', 1200, 1600)->size(2000);
        $image3 = UploadedFile::fake()->image('solution_page3.webp', 1200, 1600)->size(1200);

        $submission = $homeworkService->submitHomework(
            $this->assignment,
            $this->student,
            [$image1, $image2, $image3],
            'مرفق صور حل واجب الشروط والتفرع البرمجي'
        );

        // 2. Verify files and database record
        $this->assertNotNull($submission);
        $this->assertEquals($this->assignment->id, $submission->assignment_id);
        $this->assertEquals($this->student->id, $submission->student_id);
        $this->assertEquals('submitted', $submission->status);
        $this->assertEquals(3, $submission->image_count);
        $this->assertCount(3, $submission->images_list);
        $this->assertNull($submission->published_at);
        $this->assertFalse($submission->isPublished());
        $this->assertEquals('تم التسليم', $submission->student_status_label);

        // 3. Verify files exist on private storage
        foreach ($submission->images_list as $path) {
            Storage::disk('local')->assertExists($path);
        }

        // 4. Secure Media Controller Access:
        // Student owner can access image 0
        $this->actingAs($this->studentUser);
        $response = $this->get(route('assignments.submissions.image', ['submission' => $submission->id, 'index' => 0]));
        $response->assertStatus(200);

        // Instructor can access image 1
        $this->actingAs($this->instructorUser);
        $response = $this->get(route('assignments.submissions.image', ['submission' => $submission->id, 'index' => 1]));
        $response->assertStatus(200);

        // 5. Instructor saves Draft Grade (8.5/10)
        $feedback = 'الحل كويس جدًا، بس محتاج تراجع استخدام الشرط الثاني.';
        $submission = $homeworkService->saveDraftGrade($submission, 8.5, $feedback, $this->instructorUser);

        $this->assertEquals(8.5, (float) $submission->score);
        $this->assertEquals($feedback, $submission->instructor_feedback);
        $this->assertNull($submission->published_at);
        $this->assertTrue($submission->isDraft());
        $this->assertFalse($submission->isPublished());

        // 6. Verify student CANNOT see the draft grade
        $this->actingAs($this->studentUser);
        $this->assertEquals('قيد المراجعة', $submission->student_status_label);

        // 7. Instructor Publishes Final Grade
        $submission = $homeworkService->publishGrade($submission, 8.5, $feedback, $this->instructorUser);

        $this->assertEquals('graded', $submission->status);
        $this->assertNotNull($submission->published_at);
        $this->assertTrue($submission->isPublished());
        $this->assertFalse($submission->isDraft());
        $this->assertEquals('تم التقييم', $submission->student_status_label);

        // 8. Verify student sees the published grade and feedback
        $this->assertEquals(8.5, (float) $submission->score);
        $this->assertEquals($feedback, $submission->instructor_feedback);
        $this->assertEquals(3, $submission->image_count);
    }

    /**
     * Test Grade Boundaries: Only 0-10 allowed.
     */
    public function test_grade_boundaries_and_zero_grade(): void
    {
        $homeworkService = app(HomeworkService::class);
        $image = UploadedFile::fake()->image('answer.jpg');
        $submission = $homeworkService->submitHomework($this->assignment, $this->student, [$image]);

        // Test valid zero grade (0/10)
        $submissionZero = $homeworkService->publishGrade($submission, 0, 'لم يتم حل أي تمرين', $this->instructorUser);
        $this->assertEquals(0.0, (float) $submissionZero->score);
        $this->assertTrue($submissionZero->isPublished());
        $this->assertEquals('تم التقييم', $submissionZero->student_status_label);

        // Test valid maximum grade (10/10)
        $submissionTen = $homeworkService->publishGrade($submission, 10, 'ممتاز كامل', $this->instructorUser);
        $this->assertEquals(10.0, (float) $submissionTen->score);

        // Test Grade > 10 throws validation exception
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $homeworkService->publishGrade($submission, 11, 'Invalid', $this->instructorUser);
    }

    /**
     * Test Negative Grade is rejected.
     */
    public function test_negative_grade_is_rejected(): void
    {
        $homeworkService = app(HomeworkService::class);
        $image = UploadedFile::fake()->image('answer.jpg');
        $submission = $homeworkService->submitHomework($this->assignment, $this->student, [$image]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $homeworkService->publishGrade($submission, -1, 'Invalid', $this->instructorUser);
    }

    /**
     * Test Image Limits: Maximum 5 images allowed.
     */
    public function test_maximum_five_images_allowed(): void
    {
        $homeworkService = app(HomeworkService::class);

        // Create 6 images (exceeding limit of 5)
        $images = [];
        for ($i = 0; $i < 6; $i++) {
            $images[] = UploadedFile::fake()->image("page_{$i}.jpg");
        }

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $homeworkService->submitHomework($this->assignment, $this->student, $images);
    }

    /**
     * Test Non-Image File is rejected.
     */
    public function test_non_image_file_is_rejected(): void
    {
        $homeworkService = app(HomeworkService::class);
        $pdfFile = UploadedFile::fake()->create('answers.pdf', 1000, 'application/pdf');

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $homeworkService->submitHomework($this->assignment, $this->student, [$pdfFile]);
    }

    /**
     * Test File Size Limit: Image exceeding 5MB is rejected.
     */
    public function test_image_exceeding_5mb_is_rejected(): void
    {
        $homeworkService = app(HomeworkService::class);
        $hugeImage = UploadedFile::fake()->image('huge.jpg')->size(5200); // 5.2MB > 5MB

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $homeworkService->submitHomework($this->assignment, $this->student, [$hugeImage]);
    }

    /**
     * Test Unauthorized Student cannot access other student's submission images.
     */
    public function test_unauthorized_student_cannot_access_submission_images(): void
    {
        $homeworkService = app(HomeworkService::class);
        $image = UploadedFile::fake()->image('page1.jpg');
        $submission = $homeworkService->submitHomework($this->assignment, $this->student, [$image]);

        // Create another student
        $otherStudentUser = User::factory()->create(['tenant_id' => $this->tenant->id]);
        Student::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $otherStudentUser->id,
            'student_code' => 'STU-OTHER',
            'gender' => 'female',
        ]);

        $this->actingAs($otherStudentUser);
        $response = $this->get(route('assignments.submissions.image', ['submission' => $submission->id, 'index' => 0]));
        $response->assertStatus(403);
    }

    /**
     * Test Cross-Tenant Access Prevention.
     */
    public function test_cross_tenant_access_is_prevented(): void
    {
        $homeworkService = app(HomeworkService::class);
        $image = UploadedFile::fake()->image('page1.jpg');
        $submission = $homeworkService->submitHomework($this->assignment, $this->student, [$image]);

        // Create different tenant and user
        $otherTenant = Tenant::create(['name' => 'School B', 'domain' => 'school-b.test']);
        $foreignUser = User::factory()->create(['tenant_id' => $otherTenant->id]);

        $this->actingAs($foreignUser);
        $response = $this->get(route('assignments.submissions.image', ['submission' => $submission->id, 'index' => 0]));
        $this->assertTrue(in_array($response->status(), [403, 404]), 'Cross-tenant access must be rejected (403 or 404 via BelongsToBranch scope).');
    }

    /**
     * Test Resubmission resets publication status.
     */
    public function test_resubmission_resets_published_status(): void
    {
        $homeworkService = app(HomeworkService::class);
        $image1 = UploadedFile::fake()->image('attempt1.jpg');
        $submission = $homeworkService->submitHomework($this->assignment, $this->student, [$image1]);

        // Graded and published
        $submission = $homeworkService->publishGrade($submission, 7.0, 'يحتاج إعادة محاولة', $this->instructorUser);
        $this->assertTrue($submission->isPublished());

        // Student resubmits new images
        $image2 = UploadedFile::fake()->image('attempt2.jpg');
        $updatedSubmission = $homeworkService->submitHomework($this->assignment, $this->student, [$image2], 'إعادة تسليم بعد التعديل');

        $this->assertEquals($submission->id, $updatedSubmission->id);
        $this->assertNull($updatedSubmission->published_at);
        $this->assertFalse($updatedSubmission->isPublished());
        $this->assertEquals('submitted', $updatedSubmission->status);
        $this->assertEquals('تم التسليم', $updatedSubmission->student_status_label);
    }

    /**
     * Test Student Portal API properly masks draft grades and reveals published grades.
     */
    public function test_api_masks_draft_grades_and_reveals_published(): void
    {
        $homeworkService = app(HomeworkService::class);
        $image = UploadedFile::fake()->image('homework.jpg');
        $submission = $homeworkService->submitHomework($this->assignment, $this->student, [$image]);

        // Save as draft
        $homeworkService->saveDraftGrade($submission, 9.0, 'مسودة خاصة بالمهندس', $this->instructorUser);

        // Student checks portal API
        $this->actingAs($this->studentUser);
        $response = $this->getJson('/api/v1/student/portal');
        $response->assertStatus(200);

        $assignmentData = collect($response->json('assignments'))->firstWhere('id', (string) $this->assignment->id);
        $this->assertNotNull($assignmentData);
        $this->assertEquals('under_review', $assignmentData['status']);
        $this->assertEquals('قيد المراجعة', $assignmentData['status_label']);
        $this->assertNull($assignmentData['score']);
        $this->assertNull($assignmentData['feedback']);

        // Now instructor publishes
        $homeworkService->publishGrade($submission, 9.0, 'أحسنت حلاً ممتازاً!', $this->instructorUser);

        $responsePublished = $this->getJson('/api/v1/student/portal');
        $responsePublished->assertStatus(200);

        $publishedData = collect($responsePublished->json('assignments'))->firstWhere('id', (string) $this->assignment->id);
        $this->assertEquals('graded', $publishedData['status']);
        $this->assertEquals('تم التقييم', $publishedData['status_label']);
        $this->assertEquals(9.0, (float) $publishedData['score']);
        $this->assertEquals('أحسنت حلاً ممتازاً!', $publishedData['feedback']);
    }

    /**
     * Test ManageAssignmentSubmissions Livewire page actions.
     */
    public function test_manage_assignment_submissions_livewire_page(): void
    {
        $homeworkService = app(HomeworkService::class);
        $image = UploadedFile::fake()->image('solution.png');
        $submission = $homeworkService->submitHomework($this->assignment, $this->student, [$image], 'ملاحظة الطالب');

        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('instructor'));

        \Livewire\Livewire::actingAs($this->instructorUser)
            ->test(\App\Filament\Instructor\Resources\Assignments\Pages\ManageAssignmentSubmissions::class, [
                'record' => $this->assignment->id,
            ])
            ->assertSee($this->student->user->name)
            ->assertSee('تم التسليم')
            ->call('openReview', $this->student->id, $submission->id)
            ->assertSet('isReviewOpen', true)
            ->set('score', 8.5)
            ->set('instructor_feedback', 'شغل رائع يا بطل')
            ->call('saveDraft')
            ->assertNotified('تم حفظ مسودة التقييم بنجاح')
            ->call('publishGrade')
            ->assertNotified('تم اعتماد ونشر التقييم بنجاح');

        $submission->refresh();
        $this->assertEquals(8.5, (float) $submission->score);
        $this->assertEquals('شغل رائع يا بطل', $submission->instructor_feedback);
        $this->assertTrue($submission->isPublished());
    }

    /**
     * Test Student MyAssignments Livewire Page workflow.
     */
    public function test_student_my_assignments_livewire_page(): void
    {
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('student'));

        // 1. Initial view: assignment visible as not submitted
        \Livewire\Livewire::actingAs($this->studentUser)
            ->test(\App\Filament\Student\Pages\MyAssignments::class)
            ->assertSee($this->assignment->title)
            ->assertSee('لم يتم التسليم')
            ->call('openAssignment', $this->assignment->id)
            ->assertSet('activeAssignmentId', $this->assignment->id);

        // 2. Submit homework via service
        $homeworkService = app(HomeworkService::class);
        $image = UploadedFile::fake()->image('homework_page.jpg');
        $submission = $homeworkService->submitHomework($this->assignment, $this->student, [$image], 'حليت المسائل بالكامل');

        // 3. Verify page now shows 'تم التسليم'
        \Livewire\Livewire::actingAs($this->studentUser)
            ->test(\App\Filament\Student\Pages\MyAssignments::class)
            ->assertSee('تم التسليم');

        // 4. Instructor saves draft -> Student sees 'قيد المراجعة', NOT the score
        $homeworkService->saveDraftGrade($submission, 8.5, 'ملاحظات مسودة', $this->instructorUser);

        \Livewire\Livewire::actingAs($this->studentUser)
            ->test(\App\Filament\Student\Pages\MyAssignments::class)
            ->assertSee('قيد المراجعة')
            ->assertDontSee('8.5 / 10')
            ->assertDontSee('ملاحظات مسودة');

        // 5. Instructor publishes grade -> Student sees 'تم التقييم' and '8.5 / 10' and feedback in details modal
        $homeworkService->publishGrade($submission, 8.5, 'أحسنت حلاً رائعاً', $this->instructorUser);

        \Livewire\Livewire::actingAs($this->studentUser)
            ->test(\App\Filament\Student\Pages\MyAssignments::class)
            ->assertSee('تم التقييم')
            ->assertSee('8.5')
            ->call('openAssignment', $this->assignment->id)
            ->assertSee('أحسنت حلاً رائعاً');
    }

    /**
     * Test Student cannot see or submit assignment from another group in same tenant.
     */
    public function test_student_cannot_see_or_open_assignments_from_another_group_in_same_tenant(): void
    {
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('student'));

        // Create Group B in the same tenant and an assignment for Group B
        $groupB = Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $this->course->id,
            'instructor_id' => $this->instructor->id,
            'name' => 'Group Scratch Secret',
            'status' => 'active',
        ]);

        $assignmentB = Assignment::create([
            'tenant_id' => $this->tenant->id,
            'group_id' => $groupB->id,
            'course_id' => $this->course->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'Scratch Secret HW',
            'due_date' => now()->addDays(3),
            'max_score' => 10,
            'status' => 'active',
        ]);

        // Student is ONLY enrolled in Group Python Saturday (NOT Group B)
        \Livewire\Livewire::actingAs($this->studentUser)
            ->test(\App\Filament\Student\Pages\MyAssignments::class)
            ->assertSee($this->assignment->title)
            ->assertDontSee($assignmentB->title)
            ->call('openAssignment', $assignmentB->id)
            ->assertSet('activeAssignmentId', null)
            ->assertNotified('غير مصرح');
    }
}

