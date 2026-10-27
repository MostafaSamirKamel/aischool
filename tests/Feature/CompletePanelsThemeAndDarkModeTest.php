<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Student;
use App\Models\Instructor;
use Filament\Facades\Filament;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompletePanelsThemeAndDarkModeTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $instructorUser;
    protected User $studentUser;
    protected User $parentUser;
    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'AI School Branch',
            'slug' => 'branch',
        ]);

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'instructor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);

        $this->adminUser = User::create([
            'name' => 'Super Admin',
            'email' => 'admin.test@aischool.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => true,
            'status' => 'active',
        ]);
        $this->adminUser->assignRole('super_admin');

        $this->instructorUser = User::create([
            'name' => 'Instructor Test',
            'email' => 'instructor.test@aischool.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
        ]);
        $this->instructorUser->assignRole('instructor');
        Instructor::create([
            'user_id' => $this->instructorUser->id,
            'tenant_id' => $this->tenant->id,
            'employee_code' => 'INS-001',
            'name' => 'Instructor Test',
        ]);

        $this->studentUser = User::create([
            'name' => 'Student Test',
            'email' => 'student.test@aischool.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
        ]);
        $this->studentUser->assignRole('student');
        $student = Student::create([
            'user_id' => $this->studentUser->id,
            'tenant_id' => $this->tenant->id,
            'student_code' => 'STU-001',
            'first_name' => 'طالب',
            'last_name' => 'تجريبي',
            'gender' => 'male',
            'status' => 'active',
            'grade_level' => 'الصف الأول الثانوي',
        ]);

        $this->parentUser = User::create([
            'name' => 'Parent Test',
            'email' => 'parent.test@aischool.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
        ]);
        $this->parentUser->assignRole('parent');

        \App\Models\StudentParent::create([
            'user_id' => $this->parentUser->id,
            'student_id' => $student->id,
            'relation' => 'father',
        ]);
    }

    /** @test */
    public function test_admin_panel_renders_with_color_system_and_theme_toggle(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $response = $this->actingAs($this->adminUser, 'web')->get('/admin');
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('custom-theme.css', $content, 'Admin panel must link custom-theme.css');
        $this->assertStringContainsString('ai-theme-toggle-btn', $content, 'Admin panel must contain accessible theme toggle button');
        $this->assertStringContainsString('localStorage.getItem("theme")', $content, 'Admin panel must contain anti-flash theme script');
    }

    /** @test */
    public function test_instructor_panel_renders_with_color_system_and_theme_toggle(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('instructor'));

        $response = $this->actingAs($this->instructorUser, 'web')->get('/instructor');
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('custom-theme.css', $content, 'Instructor panel must link custom-theme.css');
        $this->assertStringContainsString('ai-theme-toggle-btn', $content, 'Instructor panel must contain accessible theme toggle button');
        $this->assertStringContainsString('localStorage.getItem("theme")', $content, 'Instructor panel must contain anti-flash theme script');
    }

    /** @test */
    public function test_instructor_unified_academic_center_renders_with_theme_and_dark_mode(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('instructor'));

        $response = $this->actingAs($this->instructorUser, 'web')->get('/instructor/homework-review-center');
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('custom-theme.css', $content);
        $this->assertStringContainsString('ai-theme-toggle-btn', $content);
    }

    /** @test */
    public function test_student_panel_renders_with_color_system_and_mobile_navigation(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('student'));

        $response = $this->actingAs($this->studentUser, 'web')->get('/student/dashboard');
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('custom-theme.css', $content, 'Student panel must link custom-theme.css');
        $this->assertStringContainsString('ai-theme-toggle-btn', $content, 'Student panel must contain accessible theme toggle button');
        $this->assertStringContainsString('ai-student-bottom-bar', $content, 'Student panel must contain mobile bottom navigation bar');
    }

    /** @test */
    public function test_student_assignments_and_exams_render_with_theme(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('student'));

        $respAssignments = $this->actingAs($this->studentUser, 'web')->get('/student/my-assignments');
        $respAssignments->assertStatus(200);
        $this->assertStringContainsString('custom-theme.css', $respAssignments->getContent());

        $respExams = $this->actingAs($this->studentUser, 'web')->get('/student/my-exams');
        $respExams->assertStatus(200);
        $this->assertStringContainsString('custom-theme.css', $respExams->getContent());
    }

    /** @test */
    public function test_parent_panel_renders_with_color_system_and_mobile_navigation(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('parent'));

        $response = $this->actingAs($this->parentUser, 'web')->get('/parent');
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('custom-theme.css', $content, 'Parent panel must link custom-theme.css');
        $this->assertStringContainsString('ai-theme-toggle-btn', $content, 'Parent panel must contain accessible theme toggle button');
        $this->assertStringContainsString('ai-parent-bottom-bar', $content, 'Parent panel must contain mobile bottom navigation bar');
    }

    /** @test */
    public function test_parent_reports_render_with_report_theme(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('parent'));

        $response = $this->actingAs($this->parentUser, 'web')->get('/parent/student-reports');
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('report-shell', $content);
        $this->assertStringContainsString('custom-theme.css', $content);
    }
}
