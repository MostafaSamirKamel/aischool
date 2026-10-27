<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use App\Models\Grade;
use App\Models\Group;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StudentExamAndAssignmentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_take_and_submit_exam_via_api(): void
    {
        $tenant = Tenant::create(['name' => 'School A', 'domain' => 'school-a.com']);
        $studentUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $student = Student::create([
            'tenant_id' => $tenant->id,
            'user_id' => $studentUser->id,
            'student_code' => 'STU-100',
            'gender' => 'male',
        ]);

        $course = Course::create([
            'tenant_id' => $tenant->id,
            'name' => 'Laravel Mastery',
            'price' => 500,
        ]);

        $group = Group::factory()->create(['tenant_id' => $tenant->id]);

        $exam = Exam::create([
            'tenant_id' => $tenant->id,
            'course_id' => $course->id,
            'group_id' => $group->id,
            'title' => 'Midterm Exam',
            'exam_date' => now()->toDateString(),
            'duration' => 30,
            'total_marks' => 20,
            'passing_marks' => 10,
        ]);

        $question1 = ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_text' => 'What is Laravel?',
            'points' => 10,
            'order' => 1,
        ]);

        $opt1_1 = ExamQuestionOption::create([
            'exam_question_id' => $question1->id,
            'option_text' => 'PHP Framework',
            'is_correct' => true,
            'order' => 1,
        ]);

        $opt1_2 = ExamQuestionOption::create([
            'exam_question_id' => $question1->id,
            'option_text' => 'JavaScript Library',
            'is_correct' => false,
            'order' => 2,
        ]);

        $question2 = ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_text' => 'What is Composer?',
            'points' => 10,
            'order' => 2,
        ]);

        $opt2_1 = ExamQuestionOption::create([
            'exam_question_id' => $question2->id,
            'option_text' => 'Dependency Manager',
            'is_correct' => true,
            'order' => 1,
        ]);

        Sanctum::actingAs($studentUser);

        // 1. Get Exam Details
        $getRes = $this->getJson("/api/student/exams/{$exam->id}");
        $getRes->assertStatus(200)
            ->assertJsonPath('title', 'Midterm Exam')
            ->assertJsonPath('total_marks', 20)
            ->assertJsonCount(2, 'questions');

        // Check that is_correct is NOT leaked to the client!
        $this->assertArrayNotHasKey('is_correct', $getRes->json('questions.0.options.0'));

        // 2. Start Exam
        $startRes = $this->postJson("/api/student/exams/{$exam->id}/start");
        $startRes->assertStatus(200)
            ->assertJsonStructure(['attempt_id', 'started_at', 'duration_minutes']);

        // 3. Submit Exam (Question 1 correct, Question 2 correct -> 20/20)
        $submitRes = $this->postJson("/api/student/exams/{$exam->id}/submit", [
            'answers' => [
                $question1->id => $opt1_1->id,
                $question2->id => $opt2_1->id,
            ],
        ]);

        $submitRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('score', 20)
            ->assertJsonPath('percentage', 100)
            ->assertJsonPath('passed', true);

        // Verify Grade was created in database
        $grade = Grade::where('student_id', $student->id)->where('exam_id', $exam->id)->first();
        $this->assertNotNull($grade);
        $this->assertEquals(20, $grade->marks_obtained);
        $this->assertTrue($grade->isPass());

        // 4. Duplicate submission attempt should be rejected
        $dupRes = $this->postJson("/api/student/exams/{$exam->id}/submit", [
            'answers' => [$question1->id => $opt1_1->id],
        ]);
        $dupRes->assertStatus(422);
    }

    public function test_student_can_submit_assignment_and_review(): void
    {
        $tenant = Tenant::create(['name' => 'School B', 'domain' => 'school-b.com']);
        $studentUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $student = Student::create([
            'tenant_id' => $tenant->id,
            'user_id' => $studentUser->id,
            'student_code' => 'STU-200',
            'gender' => 'male',
        ]);

        $instructorUser = User::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Teacher John']);
        $instructor = Instructor::create([
            'tenant_id' => $tenant->id,
            'user_id' => $instructorUser->id,
            'employee_code' => 'INS-200',
        ]);

        $group = Group::factory()->create(['tenant_id' => $tenant->id]);

        $assignment = Assignment::create([
            'tenant_id' => $tenant->id,
            'group_id' => $group->id,
            'instructor_id' => $instructor->id,
            'title' => 'Build a portfolio page',
            'description' => 'Create a single page portfolio',
            'due_date' => now()->addDays(7),
        ]);

        Sanctum::actingAs($studentUser);

        // Submit assignment with text notes
        $submitAsn = $this->postJson("/api/student/assignments/{$assignment->id}/submit", [
            'notes' => 'Here is my assignment submission.',
        ]);

        $submitAsn->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('submission.status', 'submitted');

        // Submit instructor review
        $reviewRes = $this->postJson('/api/student/reviews', [
            'instructor_id' => $instructor->id,
            'rating' => 5,
            'comment' => 'Great instructor!',
        ]);

        $reviewRes->assertStatus(200)
            ->assertJsonPath('success', true);

        // Submit student project
        $projectRes = $this->postJson('/api/student/projects', [
            'title' => 'My Personal Blog',
            'description' => 'Built with Laravel and SQLite',
            'github_url' => 'https://github.com/student/blog',
        ]);

        $projectRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('project.title', 'My Personal Blog');

        // Check timeline endpoint
        $timelineRes = $this->getJson('/api/student/timeline');
        $timelineRes->assertStatus(200)
            ->assertJsonStructure(['timeline', 'follow_up_status']);
    }
}
