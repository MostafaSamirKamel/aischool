<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\Supervisor;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeTopNavbarAndMegaDropdownTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $adminUser;
    protected User $instructorUser;
    protected User $supervisorUser;
    protected User $studentUser;
    protected User $parentUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'فرع القاهرة التجريبي',
            'domain' => 'cairo.aischool.test',
        ]);

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'instructor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);

        $this->adminUser = User::create([
            'name' => 'المدير العام التجريبي',
            'email' => 'admin.test@aischool.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => true,
            'status' => 'active',
        ]);
        $this->adminUser->assignRole('super_admin');

        $this->instructorUser = User::create([
            'name' => 'المعلم التجريبي',
            'email' => 'instructor.test@aischool.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
        ]);
        $this->instructorUser->assignRole('instructor');
        Instructor::create([
            'user_id' => $this->instructorUser->id,
            'tenant_id' => $this->tenant->id,
            'employee_code' => 'INS-TEST-01',
            'name' => 'المعلم التجريبي',
        ]);

        $this->supervisorUser = User::create([
            'name' => 'المشرف التجريبي',
            'email' => 'supervisor.test@aischool.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
        ]);
        $this->supervisorUser->assignRole('supervisor');
        Supervisor::create([
            'user_id' => $this->supervisorUser->id,
            'tenant_id' => $this->tenant->id,
            'employee_number' => 'SUP-TEST-01',
            'national_id' => '1234567890',
            'date_of_birth' => '1985-05-15',
            'gender' => 'male',
            'phone' => '0501234567',
            'hire_date' => '2023-01-01',
            'status' => 'active',
        ]);

        $this->studentUser = User::create([
            'name' => 'طالب تجريبي',
            'email' => 'student.test@aischool.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
        ]);
        $this->studentUser->assignRole('student');
        $student = Student::create([
            'user_id' => $this->studentUser->id,
            'tenant_id' => $this->tenant->id,
            'student_code' => 'STU-TEST-01',
            'first_name' => 'طالب',
            'last_name' => 'تجريبي',
            'gender' => 'male',
            'status' => 'active',
            'grade_level' => 'الصف الأول الثانوي',
        ]);

        $this->parentUser = User::create([
            'name' => 'ولي أمر تجريبي',
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
    public function test_admin_panel_renders_top_navbar_with_mega_dropdowns(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $response = $this->actingAs($this->adminUser, 'web')->get('/admin');
        $response->assertStatus(200);
        $content = $response->getContent();

        // 1. Verify Top Navbar presence & class
        $this->assertStringContainsString('ai-employee-top-navbar', $content, 'Top navbar must be rendered in Admin Panel');
        $this->assertStringContainsString('ai-mega-dropdown', $content, 'Mega dropdown panels must be present');
        $this->assertStringContainsString('employee-navbar.css', $content, 'Employee navbar CSS must be linked');

        // 2. Verify Full Content Width (has-top-navigation class on body)
        $this->assertStringContainsString('fi-body-has-top-navigation', $content, 'Body must have top-navigation flag to maximize width');

        // 3. Verify Categories in Admin Navbar
        $this->assertStringContainsString('الطلاب والتسجيل', $content);
        $this->assertStringContainsString('الشؤون الأكاديمية', $content);
        $this->assertStringContainsString('الإدارة المالية', $content);
        $this->assertStringContainsString('التقارير والإحصائيات', $content);
        $this->assertStringContainsString('إدارة النظام', $content);

        // 4. Verify Theme Toggle and User Menu
        $this->assertStringContainsString('ai-theme-toggle-btn', $content);
        $this->assertStringContainsString('ai-user-profile-trigger', $content);
    }

    /** @test */
    public function test_instructor_panel_renders_top_navbar_with_mega_dropdowns(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('instructor'));

        $response = $this->actingAs($this->instructorUser, 'web')->get('/instructor');
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('ai-employee-top-navbar', $content, 'Top navbar must be rendered in Instructor Panel');
        $this->assertStringContainsString('fi-body-has-top-navigation', $content, 'Instructor panel must have top-navigation class');
        $this->assertStringContainsString('employee-navbar.css', $content);

        // Verify Instructor Categories
        $this->assertStringContainsString('التدريس والدرجات', $content);
        $this->assertStringContainsString('المجموعات والطلاب', $content);
        $this->assertStringContainsString('المتابعة والتقارير', $content);

        // Verify key destinations exist in links
        $this->assertStringContainsString('homework-review-center', $content);
        $this->assertStringContainsString('my-groups', $content);
    }

    /** @test */
    public function test_supervisor_panel_renders_top_navbar_with_mega_dropdowns(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('supervisor'));

        $response = $this->actingAs($this->supervisorUser, 'web')->get('/supervisor');
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('ai-employee-top-navbar', $content, 'Top navbar must be rendered in Supervisor Panel');
        $this->assertStringContainsString('fi-body-has-top-navigation', $content);
        $this->assertStringContainsString('employee-navbar.css', $content);

        // Verify Supervisor Categories
        $this->assertStringContainsString('الإشراف والمجموعات', $content);
        $this->assertStringContainsString('الجودة التعليمية والتوجيه', $content);
    }

    /** @test */
    public function test_student_panel_is_strictly_unchanged_and_does_not_contain_employee_navbar(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('student'));

        $response = $this->actingAs($this->studentUser, 'web')->get('/student/dashboard');
        $response->assertStatus(200);
        $content = $response->getContent();

        // Must NOT contain employee top navbar
        $this->assertStringNotContainsString('ai-employee-top-navbar', $content, 'Student panel must NEVER render employee navbar');
        $this->assertStringNotContainsString('employee-navbar.css', $content, 'Student panel must NEVER link employee-navbar.css');

        // Must PRESERVE Student Mobile App Navigation
        $this->assertStringContainsString('ai-student-bottom-bar', $content, 'Student panel must retain bottom navigation bar');
        $this->assertStringContainsString('ai-student-drawer-trigger', $content, 'Student panel must retain mobile drawer trigger');
    }

    /** @test */
    public function test_parent_panel_is_strictly_unchanged_and_does_not_contain_employee_navbar(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('parent'));

        $response = $this->actingAs($this->parentUser, 'web')->get('/parent');
        $response->assertStatus(200);
        $content = $response->getContent();

        // Must NOT contain employee top navbar
        $this->assertStringNotContainsString('ai-employee-top-navbar', $content, 'Parent panel must NEVER render employee navbar');
        $this->assertStringNotContainsString('employee-navbar.css', $content, 'Parent panel must NEVER link employee-navbar.css');

        // Must PRESERVE Parent Mobile App Navigation
        $this->assertStringContainsString('ai-parent-bottom-bar', $content, 'Parent panel must retain bottom navigation bar');
        $this->assertStringContainsString('ai-parent-drawer-trigger', $content, 'Parent panel must retain parent mobile drawer trigger');
    }
}
