<?php

use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\Group;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('attendance record defines attendanceSession alias relationship for timeline and api queries', function () {
    $tenant = Tenant::factory()->create();
    $course = Course::factory()->create(['tenant_id' => $tenant->id]);
    $group = Group::factory()->create(['tenant_id' => $tenant->id, 'course_id' => $course->id]);
    $studentUser = User::factory()->create(['tenant_id' => $tenant->id]);
    $student = Student::factory()->create(['user_id' => $studentUser->id, 'tenant_id' => $tenant->id]);

    $session = AttendanceSession::create([
        'tenant_id' => $tenant->id,
        'group_id' => $group->id,
        'course_id' => $course->id,
        'session_title' => 'Introduction to PHP',
        'session_number' => 1,
        'attendance_date' => now(),
        'status' => 'completed',
    ]);

    $record = AttendanceRecord::create([
        'attendance_session_id' => $session->id,
        'student_id' => $student->id,
        'status' => 'present',
    ]);

    // Test attendanceSession relation eager loading as used in Student::getAcademicTimeline()
    $reloaded = AttendanceRecord::with(['attendanceSession.course', 'attendanceSession.group'])->find($record->id);

    expect($reloaded->attendanceSession)->not->toBeNull()
        ->and($reloaded->attendanceSession->id)->toBe($session->id)
        ->and($reloaded->attendanceSession->course->id)->toBe($course->id);

    // Call getAcademicTimeline directly
    $timeline = $student->getAcademicTimeline();
    expect($timeline)->toBeArray()
        ->and(count($timeline))->toBeGreaterThanOrEqual(1);
});

test('student model defines attendances relationship for legacy attendance access', function () {
    $tenant = Tenant::factory()->create();
    $group = Group::factory()->create(['tenant_id' => $tenant->id]);
    $studentUser = User::factory()->create(['tenant_id' => $tenant->id]);
    $student = Student::factory()->create(['user_id' => $studentUser->id, 'tenant_id' => $tenant->id]);

    $legacyAttendance = Attendance::create([
        'tenant_id' => $tenant->id,
        'student_id' => $student->id,
        'group_id' => $group->id,
        'date' => now()->toDateString(),
        'status' => 'present',
    ]);

    expect($student->attendances)->toHaveCount(1)
        ->and($student->attendances->first()->id)->toBe($legacyAttendance->id);
});

test('supervisor stats widget calculates attendance without calling non-existent attendances method', function () {
    $tenant = Tenant::factory()->create();
    $supervisorUser = User::factory()->create(['tenant_id' => $tenant->id]);
    $supervisor = \App\Models\Supervisor::create([
        'user_id' => $supervisorUser->id,
        'tenant_id' => $tenant->id,
        'employee_number' => 'SUP-001',
        'national_id' => '29001011234567',
        'date_of_birth' => '1990-01-01',
        'gender' => 'male',
        'phone' => '01012345678',
        'hire_date' => '2025-01-01',
        'status' => 'active',
    ]);

    $course = Course::factory()->create(['tenant_id' => $tenant->id]);
    $group = Group::factory()->create([
        'tenant_id' => $tenant->id,
        'course_id' => $course->id,
        'supervisor_id' => $supervisor->id,
    ]);
    $studentUser = User::factory()->create(['tenant_id' => $tenant->id]);
    $student = Student::factory()->create(['user_id' => $studentUser->id, 'tenant_id' => $tenant->id]);
    \App\Models\Enrollment::factory()->create([
        'tenant_id' => $tenant->id,
        'student_id' => $student->id,
        'group_id' => $group->id,
    ]);

    $session = AttendanceSession::create([
        'tenant_id' => $tenant->id,
        'group_id' => $group->id,
        'course_id' => $course->id,
        'session_title' => 'Test Session',
        'session_number' => 1,
        'attendance_date' => now(),
        'status' => 'completed',
    ]);

    AttendanceRecord::create([
        'attendance_session_id' => $session->id,
        'student_id' => $student->id,
        'status' => 'present',
    ]);

    $this->actingAs($supervisorUser);

    // Instantiate SupervisorStatsWidget and invoke getStats
    $widget = new \App\Filament\Supervisor\Widgets\SupervisorStatsWidget();
    $reflection = new \ReflectionClass($widget);
    $method = $reflection->getMethod('getStats');
    $method->setAccessible(true);

    $stats = $method->invoke($widget);
    expect($stats)->toBeArray()
        ->and(count($stats))->toBeGreaterThanOrEqual(3)
        ->and($stats[0]->getValue())->toBe(1);
});
