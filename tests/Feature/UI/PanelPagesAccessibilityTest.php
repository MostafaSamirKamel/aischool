<?php

namespace Tests\Feature\UI;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Student;
use App\Models\Instructor;
use App\Models\Supervisor;
use App\Models\StudentParent;
use App\Models\Course;
use App\Models\Group;
use App\Models\Vault;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PanelPagesAccessibilityTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $superAdmin;
    protected User $branchManager;
    protected User $instructorUser;
    protected Instructor $instructor;
    protected User $supervisorUser;
    protected Supervisor $supervisor;
    protected User $studentUser;
    protected Student $student;
    protected User $parentUser;
    protected StudentParent $parent;
    protected Course $course;
    protected Group $group;
    protected Vault $vault;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $bmRole = Role::firstOrCreate(['name' => 'branch_manager', 'guard_name' => 'web']);
        $pCreate = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'Create:Student', 'guard_name' => 'web']);
        $pViewAny = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'ViewAny:Student', 'guard_name' => 'web']);
        $bmRole->givePermissionTo([$pCreate, $pViewAny]);
        Role::firstOrCreate(['name' => 'instructor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);

        $this->tenant = Tenant::create([
            'name' => 'UI Audit Main Branch',
            'domain' => 'audit.aischool.test',
        ]);

        $this->superAdmin = User::factory()->create([
            'name' => 'UI Super Admin',
            'email' => 'superadmin.ui@aischool.test',
            'is_super_admin' => true,
            'status' => 'active',
        ]);
        $this->superAdmin->assignRole('super_admin');

        $this->branchManager = User::factory()->create([
            'name' => 'UI Branch Manager',
            'email' => 'bm.ui@aischool.test',
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => false,
            'status' => 'active',
        ]);
        $this->branchManager->assignRole('branch_manager');

        $this->instructorUser = User::factory()->create([
            'name' => 'UI Instructor',
            'email' => 'instructor.ui@aischool.test',
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
        ]);
        $this->instructorUser->assignRole('instructor');
        $this->instructor = Instructor::create([
            'user_id' => $this->instructorUser->id,
            'tenant_id' => $this->tenant->id,
            'employee_code' => 'INS-UI-001',
            'specialization' => 'Robotics & AI',
            'status' => 'active',
        ]);

        $this->supervisorUser = User::factory()->create([
            'name' => 'UI Supervisor',
            'email' => 'supervisor.ui@aischool.test',
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
        ]);
        $this->supervisorUser->assignRole('supervisor');
        $this->supervisor = Supervisor::create([
            'user_id' => $this->supervisorUser->id,
            'tenant_id' => $this->tenant->id,
            'employee_number' => 'SUP-UI-001',
            'national_id' => '28001010000001',
            'date_of_birth' => '1985-01-01',
            'gender' => 'male',
            'phone' => '01011112222',
            'hire_date' => '2024-01-01',
            'status' => 'active',
        ]);

        $this->studentUser = User::factory()->create([
            'name' => 'UI Student',
            'email' => 'student.ui@aischool.test',
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
        ]);
        $this->studentUser->assignRole('student');
        $this->student = Student::create([
            'user_id' => $this->studentUser->id,
            'tenant_id' => $this->tenant->id,
            'student_code' => 'STU-UI-001',
            'national_id' => '30501010000001',
            'gender' => 'male',
            'phone' => '01033334444',
            'status' => 'active',
        ]);

        $this->parentUser = User::factory()->create([
            'name' => 'UI Parent',
            'email' => 'parent.ui@aischool.test',
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
        ]);
        $this->parentUser->assignRole('parent');
        $this->parent = StudentParent::create([
            'user_id' => $this->parentUser->id,
            'tenant_id' => $this->tenant->id,
            'relation' => 'father',
            'student_id' => $this->student->id,
            'is_primary' => true,
        ]);

        $this->course = Course::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'UI Python Masterclass',
            'code' => 'PY-UI-101',
            'total_sessions' => 12,
            'default_price' => 1200.00,
            'status' => 'active',
        ]);

        $this->group = Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $this->course->id,
            'instructor_id' => $this->instructor->id,
            'supervisor_id' => $this->supervisor->id,
            'name' => 'UI Cohort Alpha',
            'status' => 'active',
        ]);

        $this->vault = Vault::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'UI Main Vault',
            'type' => 'cash',
            'opening_balance' => 15000.00,
            'is_active' => true,
        ]);
    }

    /**
     * Test Admin Panel Key Pages Render HTTP 200
     */
    public function test_admin_panel_pages_render_successfully(): void
    {
        $this->actingAs($this->superAdmin);

        $pages = [
            '/admin',
            '/admin/students',
            '/admin/students/transcript',
            '/admin/students/certificate',
            '/admin/courses',
            '/admin/groups',
            '/admin/enrollments',
            '/admin/payments',
            '/admin/vaults',
            '/admin/vault-transfers',
            '/admin/attendance-sessions',
            '/admin/all-installments-report',
            '/admin/installments-report',
            '/admin/course-financial-report',
            '/admin/vault-report',
        ];

        foreach ($pages as $url) {
            $response = $this->get($url);
            $this->assertTrue($response->isSuccessful(), "Failed accessing {$url}: status {$response->status()}");
        }

        // Branch manager has a tenant and is authorized to create students
        $this->actingAs($this->branchManager);
        $bmResponse = $this->get('/admin/students/create');
        $this->assertTrue($bmResponse->isSuccessful(), "Branch manager failed accessing /admin/students/create: status {$bmResponse->status()}");
    }

    /**
     * Test Instructor Panel Key Pages Render HTTP 200
     */
    public function test_instructor_panel_pages_render_successfully(): void
    {
        $this->actingAs($this->instructorUser);

        $pages = [
            '/instructor',
            '/instructor/my-groups',
            '/instructor/attendance-sessions',
            '/instructor/assignments',
            '/instructor/assignment-submissions',
            '/instructor/learning-resources',
            '/instructor/group-message-resource/group-messages',
            '/instructor/student-messages',
            '/instructor/instructor-reports',
        ];

        foreach ($pages as $url) {
            $response = $this->get($url);
            $this->assertTrue($response->isSuccessful(), "Instructor Failed accessing {$url}: status {$response->status()}");
        }
    }

    /**
     * Test Supervisor Panel Key Pages Render HTTP 200
     */
    public function test_supervisor_panel_pages_render_successfully(): void
    {
        $this->actingAs($this->supervisorUser);

        $pages = [
            '/supervisor',
            '/supervisor/groups',
            '/supervisor/students',
            '/supervisor/attendance-sessions',
        ];

        foreach ($pages as $url) {
            $response = $this->get($url);
            $this->assertTrue($response->isSuccessful(), "Supervisor Failed accessing {$url}: status {$response->status()}");
        }
    }

    /**
     * Test Student Panel Key Pages Render HTTP 200
     */
    public function test_student_panel_pages_render_successfully(): void
    {
        $this->actingAs($this->studentUser);

        // /student redirects to /student/dashboard
        $this->get('/student')->assertRedirect('/student/dashboard');

        $pages = [
            '/student/dashboard',
            '/student/my-courses',
            '/student/my-assignments',
            '/student/my-attendance',
            '/student/my-payments',
            '/student/student-learning-resources',
            '/student/group-messages',
            '/student/student-reports',
        ];

        foreach ($pages as $url) {
            $response = $this->get($url);
            $this->assertTrue($response->isSuccessful(), "Student Failed accessing {$url}: status {$response->status()}");
        }
    }

    /**
     * Test Parent Panel Key Pages Render HTTP 200
     */
    public function test_parent_panel_pages_render_successfully(): void
    {
        $this->actingAs($this->parentUser);

        $pages = [
            '/parent',
            '/parent/my-courses',
            '/parent/my-attendance',
            '/parent/my-payments',
            '/parent/group-messages',
            '/parent/student-reports',
        ];

        foreach ($pages as $url) {
            $response = $this->get($url);
            $this->assertTrue($response->isSuccessful(), "Parent Failed accessing {$url}: status {$response->status()}");
        }
    }
}
