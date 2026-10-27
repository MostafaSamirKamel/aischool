<?php

namespace Tests\Feature\UI;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\Supervisor;
use App\Models\ParentProfile;
use App\Models\Course;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AiSchoolProjectWideUiUxVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $superAdmin;
    protected User $branchManager;
    protected User $instructorUser;
    protected User $supervisorUser;
    protected User $studentUser;
    protected User $parentUser;
    protected Course $course;
    protected Group $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'فرع أبو المطامير',
            'domain' => 'matamir.aischool.test',
        ]);

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'branch_manager', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'instructor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);

        $this->superAdmin = User::create([
            'name' => 'المدير العام',
            'phone' => '01000000000',
            'email' => 'superadmin@aischool.test',
            'password' => bcrypt('password'),
            'is_super_admin' => true,
            'status' => 'active',
        ]);
        $this->superAdmin->assignRole('super_admin');

        $this->branchManager = User::create([
            'name' => 'مدير فرع أبو المطامير',
            'phone' => '01070801308',
            'email' => 'manager@aischool.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => false,
            'status' => 'active',
        ]);
        $this->branchManager->assignRole('branch_manager');

        $this->instructorUser = User::create([
            'name' => 'أحمد حسن - مدرب أبو المطامير',
            'phone' => '01091812925',
            'email' => 'instructor@aischool.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
        ]);
        $this->instructorUser->assignRole('instructor');
        Instructor::create([
            'user_id' => $this->instructorUser->id,
            'tenant_id' => $this->tenant->id,
            'employee_code' => 'INS-MATAMIR-01',
            'specialization' => 'Python & AI',
            'session_rate' => 150.00,
            'status' => 'active',
        ]);

        $this->supervisorUser = User::create([
            'name' => 'المشرف التربوي',
            'phone' => '01077777777',
            'email' => 'supervisor@aischool.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
        ]);
        $this->supervisorUser->assignRole('supervisor');
        Supervisor::create([
            'user_id' => $this->supervisorUser->id,
            'tenant_id' => $this->tenant->id,
            'employee_number' => 'SUP-MATAMIR-01',
            'national_id' => '28001010000001',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'phone' => '01077777777',
            'hire_date' => '2023-01-01',
            'status' => 'active',
        ]);

        $this->studentUser = User::create([
            'name' => 'زياد طارق - طالب أبو المطامير',
            'phone' => '01007287273',
            'email' => 'student@aischool.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
        ]);
        $this->studentUser->assignRole('student');
        $student = Student::create([
            'user_id' => $this->studentUser->id,
            'tenant_id' => $this->tenant->id,
            'first_name' => 'زياد',
            'last_name' => 'طارق',
            'gender' => 'male',
            'student_code' => 'STU-MATAMIR-01',
            'grade_level' => 'الصف الأول الإعدادي',
            'status' => 'active',
        ]);

        $this->parentUser = User::create([
            'name' => 'طارق كمال - ولي أمر زياد',
            'phone' => '01009988771',
            'email' => 'parent@aischool.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
        ]);
        $this->parentUser->assignRole('parent');
        \App\Models\StudentParent::create([
            'user_id' => $this->parentUser->id,
            'student_id' => $student->id,
            'tenant_id' => $this->tenant->id,
            'relation' => 'father',
        ]);

        $this->course = Course::create([
            'name' => 'Python Junior',
            'tenant_id' => $this->tenant->id,
            'price' => 1500.00,
            'status' => 'active',
        ]);

        $this->group = Group::create([
            'name' => 'Group A — Python Junior',
            'course_id' => $this->course->id,
            'instructor_id' => $this->instructorUser->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
            'max_students' => 15,
            'schedule' => [['day' => 'thursday', 'start_time' => '10:00', 'end_time' => '12:00']],
        ]);
    }

    /**
     * Test Admin Panel Dashboard and Top Navbar for Super Admin.
     */
    public function test_super_admin_panel_dashboard_and_navbar_render_correctly(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('ai-employee-top-navbar-wrapper', false);
        $response->assertSee('custom-theme.css', false);
        $response->assertSee('employee-navbar.css', false);
        $response->assertSee('Tajawal', false);
    }

    /**
     * Test Branch Manager (Abu El Matamir) access to Admin Panel with scoped tenant data.
     */
    public function test_branch_manager_admin_panel_renders_correctly(): void
    {
        $response = $this->actingAs($this->branchManager)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('ai-employee-top-navbar', false);
        $response->assertSee('أبو المطامير', false);
    }

    /**
     * Test Instructor Panel Dashboard and Navigation.
     */
    public function test_instructor_panel_renders_correctly(): void
    {
        $response = $this->actingAs($this->instructorUser)->get('/instructor');
        $response->assertStatus(200);
        $response->assertSee('ai-employee-top-navbar', false);
        $response->assertSee('custom-theme.css', false);
    }

    /**
     * Test Supervisor Panel Dashboard and Navigation.
     */
    public function test_supervisor_panel_renders_correctly(): void
    {
        $response = $this->actingAs($this->supervisorUser)->get('/supervisor');
        $response->assertStatus(200);
        $response->assertSee('ai-employee-top-navbar', false);
        $response->assertSee('custom-theme.css', false);
    }

    /**
     * Test Student Panel Dashboard preserves Mobile App Layout and does NOT contain Employee Navbar.
     */
    public function test_student_panel_preserves_mobile_app_design(): void
    {
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('student'));

        $response = $this->actingAs($this->studentUser, 'web')->get('/student/dashboard');
        $response->assertStatus(200);
        $response->assertDontSee('ai-employee-top-navbar-wrapper', false);
        $response->assertSee('custom-theme.css', false);
    }

    /**
     * Test Parent Panel Dashboard preserves Mobile App Layout and does NOT contain Employee Navbar.
     */
    public function test_parent_panel_preserves_mobile_app_design(): void
    {
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('parent'));

        $response = $this->actingAs($this->parentUser, 'web')->get('/parent');
        $response->assertStatus(200);
        $response->assertDontSee('ai-employee-top-navbar-wrapper', false);
        $response->assertSee('custom-theme.css', false);
    }

    /**
     * Test Essential Admin Resources render HTTP 200 without layout crash.
     */
    public function test_essential_admin_resources_render_successfully(): void
    {
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('admin'));
        $this->actingAs($this->superAdmin, 'web');

        $routes = [
            '/admin/students',
            '/admin/groups',
            '/admin/courses',
            '/admin/classrooms',
            '/admin/enrollments',
            '/admin/payments',
            '/admin/vaults',
            '/admin/exams',
            '/admin/salary-payments',
            '/admin/academic-follow-up-center',
        ];

        foreach ($routes as $route) {
            $resp = $this->get($route);
            $this->assertContains($resp->status(), [200, 302], "Failed route: $route");
        }
    }
}
