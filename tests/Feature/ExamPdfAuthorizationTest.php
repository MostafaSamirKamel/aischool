<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentCourse;
use App\Models\Exam;
use App\Models\Group;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ExamPdfAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create storage directory for test pdf
        $dir = storage_path('app/private/exams/pdfs');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
    }

    public function test_unauthenticated_user_cannot_access_exam_pdf(): void
    {
        $response = $this->get('/storage/exams/pdfs/nonexistent.pdf');
        $response->assertStatus(401);
    }

    public function test_unauthorized_user_is_forbidden_from_downloading_exam_pdf(): void
    {
        $tenant = Tenant::create(['name' => 'Tenant 1', 'domain' => 'tenant1.test']);
        $otherTenant = Tenant::create(['name' => 'Tenant 2', 'domain' => 'tenant2.test']);

        $pdfFilename = 'sample_exam.pdf';
        $fullPath = storage_path('app/private/exams/pdfs/' . $pdfFilename);
        File::put($fullPath, '%PDF-1.4 test content');

        $course = Course::create([
            'tenant_id' => $tenant->id,
            'name' => 'Math Course',
            'price' => 200,
        ]);

        $group = Group::factory()->create(['tenant_id' => $tenant->id]);

        $exam = Exam::create([
            'tenant_id' => $tenant->id,
            'course_id' => $course->id,
            'group_id' => $group->id,
            'title' => 'Math Exam',
            'pdf_file' => 'exams/pdfs/' . $pdfFilename,
            'exam_date' => now()->toDateString(),
            'total_marks' => 100,
            'passing_marks' => 50,
            'duration' => 60,
        ]);

        // User from another tenant
        $unauthorizedUser = User::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);

        $response = $this->actingAs($unauthorizedUser)->get('/storage/exams/pdfs/' . $pdfFilename);
        $response->assertStatus(403);

        // Clean up file
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }

    public function test_super_admin_can_download_exam_pdf(): void
    {
        $tenant = Tenant::create(['name' => 'Tenant 1', 'domain' => 'tenant1.test']);

        $pdfFilename = 'sample_exam_admin.pdf';
        $fullPath = storage_path('app/private/exams/pdfs/' . $pdfFilename);
        File::put($fullPath, '%PDF-1.4 test content admin');

        $course = Course::create([
            'tenant_id' => $tenant->id,
            'name' => 'Science Course',
            'price' => 300,
        ]);

        $group = Group::factory()->create(['tenant_id' => $tenant->id]);

        $exam = Exam::create([
            'tenant_id' => $tenant->id,
            'course_id' => $course->id,
            'group_id' => $group->id,
            'title' => 'Science Exam',
            'pdf_file' => 'exams/pdfs/' . $pdfFilename,
            'exam_date' => now()->toDateString(),
            'total_marks' => 100,
            'passing_marks' => 50,
            'duration' => 60,
        ]);

        $superAdmin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'is_super_admin' => true,
        ]);

        $response = $this->actingAs($superAdmin)->get('/storage/exams/pdfs/' . $pdfFilename);
        $response->assertStatus(200);

        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }
}
