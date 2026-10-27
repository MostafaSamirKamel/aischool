<?php

namespace Tests\Feature;

use App\Filament\Supervisor\Resources\AttendanceSessions\Pages\ListAttendanceSessions;
use App\Filament\Supervisor\Widgets\RecentAttendanceWidget;
use App\Filament\Supervisor\Widgets\SupervisorCalendarWidget;
use App\Filament\Supervisor\Widgets\SupervisorGroupsWidget;
use App\Filament\Supervisor\Widgets\SupervisorNotificationsWidget;
use App\Filament\Supervisor\Widgets\SupervisorStatsWidget;
use App\Models\AttendanceSession;
use App\Models\Group;
use App\Models\Instructor;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupervisorDashboardAndWidgetsTest extends TestCase
{
    use RefreshDatabase;
    protected Tenant $tenant;
    protected User $supervisorUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['name' => 'Cairo Branch', 'domain' => 'cairo']);
        
        $role = Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'web']);
        $this->supervisorUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
        ]);
        $this->supervisorUser->assignRole($role);

        \App\Models\Supervisor::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->supervisorUser->id,
            'employee_number' => 'SUP-TEST-01',
            'national_id' => '2850101000099',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'phone' => '01000000000',
            'status' => 'active',
            'hire_date' => now(),
        ]);
    }

    public function test_attendance_session_has_instructor_relation_through_group(): void
    {
        $course = \App\Models\Course::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Physics 101',
            'status' => 'active',
        ]);

        $instructorUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Dr. Tareq',
        ]);
        
        $instructor = Instructor::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $instructorUser->id,
            'status' => 'active',
            'employee_code' => 'INS-TEST-99',
        ]);

        $group = Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $course->id,
            'name' => 'Test Physics Group',
            'instructor_id' => $instructor->id,
            'status' => 'active',
        ]);

        $session = AttendanceSession::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $course->id,
            'group_id' => $group->id,
            'attendance_date' => now()->toDateString(),
        ]);

        // Verify relation resolution
        $this->assertNotNull($session->instructor);
        $this->assertEquals($instructor->id, $session->instructor->id);
        $this->assertEquals('Dr. Tareq', $session->instructor->user->name);

        // Verify eager loading
        $loadedSession = AttendanceSession::with('instructor.user')->find($session->id);
        $this->assertTrue($loadedSession->relationLoaded('instructor'));
        $this->assertEquals('Dr. Tareq', $loadedSession->instructor->user->name);
    }

    public function test_supervisor_widgets_mount_and_execute_cleanly(): void
    {
        Livewire::actingAs($this->supervisorUser)
            ->test(SupervisorStatsWidget::class)
            ->assertSuccessful();

        Livewire::actingAs($this->supervisorUser)
            ->test(SupervisorNotificationsWidget::class)
            ->assertSuccessful();

        Livewire::actingAs($this->supervisorUser)
            ->test(SupervisorGroupsWidget::class)
            ->assertSuccessful();

        Livewire::actingAs($this->supervisorUser)
            ->test(SupervisorCalendarWidget::class)
            ->assertSuccessful();

        Livewire::actingAs($this->supervisorUser)
            ->test(RecentAttendanceWidget::class)
            ->assertSuccessful();
    }

    public function test_attendance_sessions_list_page_mounts_cleanly(): void
    {
        Livewire::actingAs($this->supervisorUser)
            ->test(ListAttendanceSessions::class)
            ->assertSuccessful();
    }
}
