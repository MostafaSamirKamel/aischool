<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\Classrooms\Pages\ClassroomAvailability;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentCourse;
use App\Models\Group;
use App\Models\Instructor;
use App\Models\SessionSchedule;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ClassroomAvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClassroomAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $adminUser;
    protected ClassroomAvailabilityService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Group::unsetEventDispatcher();

        $this->service = app(ClassroomAvailabilityService::class);
        $this->tenant = Tenant::factory()->create(['name' => 'Main Branch']);
        $this->adminUser = User::factory()->create([
            'is_super_admin' => true,
            'tenant_id' => $this->tenant->id,
        ]);
    }

    protected function getOrCreateCourse(): Course
    {
        return Course::first() ?? Course::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    protected function getOrCreateInstructor(): Instructor
    {
        return Instructor::first() ?? Instructor::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
            'user_id' => User::factory()->create(['tenant_id' => $this->tenant->id]),
        ]);
    }

    /**
     * CASE 1 — Empty Classroom: Show verified available periods within operating hours.
     */
    public function test_case_1_empty_classroom_shows_full_operating_availability(): void
    {
        $classroom = Classroom::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Empty Test Hall',
            'capacity' => 30,
            'status' => 'active',
        ]);

        $result = $this->service->calculateClassroomDayAvailability(
            $classroom,
            Carbon::parse('2026-11-01'), // Sunday
            '2026-11-01',
            'sunday',
            'الأحد'
        );

        $this->assertTrue($result['is_active']);
        $this->assertEmpty($result['occupied_slots']);
        $this->assertCount(1, $result['available_slots']);
        $this->assertEquals('09:00', $result['available_slots'][0]['start_time']);
        $this->assertEquals('21:00', $result['available_slots'][0]['end_time']);
        $this->assertEquals(12.0, $result['stats']['available_hours']);
        $this->assertEquals(0, $result['stats']['occupied_hours']);
    }

    /**
     * CASE 2 — One Occupied Period: 4:00 to 5:00 PM (16:00 - 17:00).
     */
    public function test_case_2_one_occupied_period_and_adjacent_available_gaps(): void
    {
        $classroom = Classroom::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Hall Beta',
            'capacity' => 20,
            'status' => 'active',
        ]);

        $course = Course::first() ?? Course::factory()->create(['tenant_id' => $this->tenant->id]);
        $instructor = Instructor::first() ?? Instructor::factory()->create(['tenant_id' => $this->tenant->id]);

        $group = Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'instructor_id' => $instructor->id,
            'name' => 'Python Group B',
            'capacity' => 15,
            'max_students' => 20,
            'start_date' => '2026-10-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
            'schedule' => [
                ['day' => 'sunday', 'start_time' => '16:00', 'end_time' => '17:00'],
            ],
        ]);

        $result = $this->service->calculateClassroomDayAvailability(
            $classroom,
            Carbon::parse('2026-11-01'),
            '2026-11-01',
            'sunday',
            'الأحد'
        );

        $this->assertCount(1, $result['occupied_slots']);
        $this->assertEquals('16:00', $result['occupied_slots'][0]['start_time']);
        $this->assertEquals('17:00', $result['occupied_slots'][0]['end_time']);

        // Adjacent gaps: 09:00-16:00 (7h) and 17:00-21:00 (4h)
        $this->assertCount(2, $result['available_slots']);
        $this->assertEquals('09:00', $result['available_slots'][0]['start_time']);
        $this->assertEquals('16:00', $result['available_slots'][0]['end_time']);
        $this->assertEquals('17:00', $result['available_slots'][1]['start_time']);
        $this->assertEquals('21:00', $result['available_slots'][1]['end_time']);

        $this->assertEquals(1.0, $result['stats']['occupied_hours']);
        $this->assertEquals(11.0, $result['stats']['available_hours']);
    }

    /**
     * CASE 3 — Multiple Bookings on Same Day in Chronological Order.
     */
    public function test_case_3_multiple_bookings_chronological_order(): void
    {
        $classroom = Classroom::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Hall Gamma',
            'capacity' => 25,
            'status' => 'active',
        ]);

        $course = $this->getOrCreateCourse();
        $instructor = $this->getOrCreateInstructor();

        // Group 1: 10:00 - 12:00
        Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'instructor_id' => $instructor->id,
            'name' => 'Morning Group',
            'capacity' => 10,
            'max_students' => 20,
            'start_date' => '2026-10-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
            'schedule' => [
                ['day' => 'sunday', 'start_time' => '10:00', 'end_time' => '12:00'],
            ],
        ]);

        // Group 2: 15:00 - 17:00
        Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'instructor_id' => $instructor->id,
            'name' => 'Afternoon Group',
            'capacity' => 12,
            'max_students' => 20,
            'start_date' => '2026-10-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
            'schedule' => [
                ['day' => 'sunday', 'start_time' => '15:00', 'end_time' => '17:00'],
            ],
        ]);

        $result = $this->service->calculateClassroomDayAvailability(
            $classroom,
            Carbon::parse('2026-11-01'),
            '2026-11-01',
            'sunday',
            'الأحد'
        );

        $this->assertCount(2, $result['occupied_slots']);
        $this->assertEquals('10:00', $result['occupied_slots'][0]['start_time']);
        $this->assertEquals('15:00', $result['occupied_slots'][1]['start_time']);

        // Gaps: 09:00-10:00, 12:00-15:00, 17:00-21:00
        $this->assertCount(3, $result['available_slots']);
        $this->assertEquals('09:00', $result['available_slots'][0]['start_time']);
        $this->assertEquals('10:00', $result['available_slots'][0]['end_time']);
        $this->assertEquals('12:00', $result['available_slots'][1]['start_time']);
        $this->assertEquals('15:00', $result['available_slots'][1]['end_time']);
        $this->assertEquals('17:00', $result['available_slots'][2]['start_time']);
        $this->assertEquals('21:00', $result['available_slots'][2]['end_time']);
    }

    /**
     * CASE 4 — Different Session Durations (1 hour, 90 minutes, 2 hours).
     */
    public function test_case_4_different_session_durations(): void
    {
        $classroom = Classroom::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Flexible Durations Room',
            'capacity' => 20,
            'status' => 'active',
        ]);

        $course = $this->getOrCreateCourse();
        $instructor = $this->getOrCreateInstructor();

        // 90 minute session: 13:30 - 15:00
        Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'instructor_id' => $instructor->id,
            'name' => 'Ninety Mins Group',
            'capacity' => 10,
            'max_students' => 20,
            'start_date' => '2026-10-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
            'schedule' => [
                ['day' => 'monday', 'start_time' => '13:30', 'end_time' => '15:00'],
            ],
        ]);

        $result = $this->service->calculateClassroomDayAvailability(
            $classroom,
            Carbon::parse('2026-11-02'), // Monday
            '2026-11-02',
            'monday',
            'الاثنين'
        );

        $this->assertCount(1, $result['occupied_slots']);
        $this->assertEquals(90, $result['occupied_slots'][0]['duration_minutes']);
        $this->assertEquals(1.5, $result['stats']['occupied_hours']);
        $this->assertEquals(10.5, $result['stats']['available_hours']);
    }

    /**
     * CASE 5 — Group Uses Two Weekly Days (Sunday & Tuesday).
     */
    public function test_case_5_group_uses_two_weekly_days(): void
    {
        $classroom = Classroom::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bi-Weekly Room',
            'capacity' => 20,
            'status' => 'active',
        ]);

        $course = $this->getOrCreateCourse();
        $instructor = $this->getOrCreateInstructor();

        Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'instructor_id' => $instructor->id,
            'name' => 'Bi-weekly Group',
            'capacity' => 10,
            'max_students' => 20,
            'start_date' => '2026-10-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
            'schedule' => [
                ['day' => 'sunday', 'start_time' => '16:00', 'end_time' => '18:00'],
                ['day' => 'tuesday', 'start_time' => '16:00', 'end_time' => '18:00'],
            ],
        ]);

        // Sunday Check
        $sundayResult = $this->service->calculateClassroomDayAvailability(
            $classroom,
            Carbon::parse('2026-11-01'), // Sunday
            '2026-11-01',
            'sunday',
            'الأحد'
        );
        $this->assertCount(1, $sundayResult['occupied_slots']);
        $this->assertEquals('16:00', $sundayResult['occupied_slots'][0]['start_time']);

        // Tuesday Check
        $tuesdayResult = $this->service->calculateClassroomDayAvailability(
            $classroom,
            Carbon::parse('2026-11-03'), // Tuesday
            '2026-11-03',
            'tuesday',
            'الثلاثاء'
        );
        $this->assertCount(1, $tuesdayResult['occupied_slots']);
        $this->assertEquals('16:00', $tuesdayResult['occupied_slots'][0]['start_time']);

        // Wednesday Check (Empty)
        $wedResult = $this->service->calculateClassroomDayAvailability(
            $classroom,
            Carbon::parse('2026-11-04'), // Wednesday
            '2026-11-04',
            'wednesday',
            'الأربعاء'
        );
        $this->assertEmpty($wedResult['occupied_slots']);
    }

    /**
     * CASE 6 — Classroom Reassignment Reflected Dynamically.
     */
    public function test_case_6_classroom_reassignment_reflected_dynamically(): void
    {
        $roomA = Classroom::create(['tenant_id' => $this->tenant->id, 'name' => 'Room A', 'capacity' => 20, 'status' => 'active']);
        $roomB = Classroom::create(['tenant_id' => $this->tenant->id, 'name' => 'Room B', 'capacity' => 20, 'status' => 'active']);

        $course = $this->getOrCreateCourse();
        $instructor = $this->getOrCreateInstructor();

        $group = Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $course->id,
            'classroom_id' => $roomA->id,
            'instructor_id' => $instructor->id,
            'name' => 'Movable Group',
            'capacity' => 10,
            'max_students' => 20,
            'start_date' => '2026-10-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
            'schedule' => [
                ['day' => 'sunday', 'start_time' => '10:00', 'end_time' => '12:00'],
            ],
        ]);

        // Room A initially has the booking
        $initialA = $this->service->calculateClassroomDayAvailability($roomA, Carbon::parse('2026-11-01'), '2026-11-01', 'sunday', 'الأحد');
        $this->assertCount(1, $initialA['occupied_slots']);

        // Reassign group to Room B
        $group->update(['classroom_id' => $roomB->id]);

        $afterA = $this->service->calculateClassroomDayAvailability($roomA, Carbon::parse('2026-11-01'), '2026-11-01', 'sunday', 'الأحد');
        $afterB = $this->service->calculateClassroomDayAvailability($roomB, Carbon::parse('2026-11-01'), '2026-11-01', 'sunday', 'الأحد');

        // Room A is now free; Room B is now occupied
        $this->assertEmpty($afterA['occupied_slots']);
        $this->assertCount(1, $afterB['occupied_slots']);
        $this->assertEquals('Movable Group', $afterB['occupied_slots'][0]['group_name']);
    }

    /**
     * CASE 7 — Capacity and Active Enrolled Students Display.
     */
    public function test_case_7_capacity_and_enrolled_students_count(): void
    {
        $classroom = Classroom::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Capacity Hall',
            'capacity' => 25,
            'status' => 'active',
        ]);

        $course = $this->getOrCreateCourse();
        $instructor = $this->getOrCreateInstructor();

        $group = Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'instructor_id' => $instructor->id,
            'name' => 'Enrolled Group',
            'capacity' => 2,
            'max_students' => 25,
            'start_date' => '2026-10-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
            'schedule' => [
                ['day' => 'sunday', 'start_time' => '10:00', 'end_time' => '12:00'],
            ],
        ]);

        $result = $this->service->calculateClassroomDayAvailability(
            $classroom,
            Carbon::parse('2026-11-01'),
            '2026-11-01',
            'sunday',
            'الأحد'
        );

        $this->assertEquals(25, $result['capacity']);
        $this->assertEquals(25, $result['occupied_slots'][0]['capacity']);
        $this->assertEquals(0, $result['occupied_slots'][0]['students_count']);
        $this->assertEquals(25, $result['occupied_slots'][0]['available_seats']);
    }

    /**
     * CASE 8 — Inactive / Maintenance Classroom Marked Unavailable.
     */
    public function test_case_8_inactive_classroom_marked_unavailable(): void
    {
        $classroom = Classroom::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Maintenance Hall',
            'capacity' => 15,
            'status' => 'maintenance',
        ]);

        $result = $this->service->calculateClassroomDayAvailability(
            $classroom,
            Carbon::parse('2026-11-01'),
            '2026-11-01',
            'sunday',
            'الأحد'
        );

        $this->assertFalse($result['is_active']);
        $this->assertEquals('maintenance', $result['status']);
        $this->assertEmpty($result['available_slots']);
        $this->assertEquals('unavailable', $result['timeline'][0]['status']);
    }

    /**
     * CASE 9 — Group After Course End is Not Displayed.
     */
    public function test_case_9_group_after_course_end_is_not_displayed(): void
    {
        $classroom = Classroom::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Expiring Group Room',
            'capacity' => 20,
            'status' => 'active',
        ]);

        $course = $this->getOrCreateCourse();
        $instructor = $this->getOrCreateInstructor();

        // Group ends on 2026-10-31
        Group::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'instructor_id' => $instructor->id,
            'name' => 'October Only Group',
            'capacity' => 10,
            'max_students' => 20,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
            'status' => 'active',
            'schedule' => [
                ['day' => 'sunday', 'start_time' => '10:00', 'end_time' => '12:00'],
            ],
        ]);

        // Query date is in November (past end_date)
        $result = $this->service->calculateClassroomDayAvailability(
            $classroom,
            Carbon::parse('2026-11-01'),
            '2026-11-01',
            'sunday',
            'الأحد'
        );

        $this->assertEmpty($result['occupied_slots']);
        $this->assertCount(1, $result['available_slots']);
    }

    /**
     * CASE 10 — Livewire ClassroomAvailability Page Mounts and Interacts.
     */
    public function test_case_10_livewire_page_rendering_and_interaction(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(ClassroomAvailability::class)
            ->assertSuccessful()
            ->set('selectedDate', '2026-11-01')
            ->call('setViewMode', 'weekly')
            ->assertSuccessful()
            ->call('setViewMode', 'daily')
            ->assertSuccessful();
    }

    /**
     * CASE 11 — Tenant Isolation Enforced.
     */
    public function test_case_11_tenant_isolation_enforced(): void
    {
        $otherTenant = Tenant::factory()->create(['name' => 'Other Branch', 'is_active' => true]);
        $otherRoom = Classroom::create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Other Branch Room',
            'capacity' => 10,
            'status' => 'active',
        ]);

        // Branch manager of first tenant
        $branchManager = User::factory()->create([
            'is_super_admin' => false,
            'tenant_id' => $this->tenant->id,
        ]);

        $results = $this->service->getAvailabilityForDate('2026-11-01', null, $this->tenant->id);

        $roomIds = array_column($results, 'classroom_id');
        $this->assertNotContains($otherRoom->id, $roomIds);
    }

    /**
     * CASE 12 — No Backend Regression: Availability calculation is strictly read-only and mutates zero records.
     */
    public function test_case_12_availability_calculation_is_strictly_read_only(): void
    {
        $classroom = Classroom::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Read Only Verification Hall',
            'capacity' => 20,
            'status' => 'active',
        ]);

        $initialClassroomCount = Classroom::count();
        $initialGroupCount = Group::count();
        $initialSessionCount = SessionSchedule::count();

        // Calculate availability for day and week
        $this->service->calculateClassroomDayAvailability(
            $classroom,
            Carbon::parse('2026-11-01'),
            '2026-11-01',
            'sunday',
            'الأحد'
        );

        $this->service->getWeeklyAvailability('2026-11-01', $classroom->id);

        $this->assertEquals($initialClassroomCount, Classroom::count(), 'Classroom count must remain untouched');
        $this->assertEquals($initialGroupCount, Group::count(), 'Group count must remain untouched');
        $this->assertEquals($initialSessionCount, SessionSchedule::count(), 'SessionSchedule count must remain untouched');
    }
}

