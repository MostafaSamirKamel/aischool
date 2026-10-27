<?php

use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentCourse;
use App\Models\Group;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('attendance session creation increments enrollment course session counter', function () {
    // Create necessary models
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'tenant_id' => $tenant->id,
    ]);
    $group = Group::factory()->create(['tenant_id' => $tenant->id]);
    $course = Course::factory()->create(['tenant_id' => $tenant->id]);
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'group_id' => $group->id,
        'tenant_id' => $tenant->id,
    ]);

    // Create enrollment course (student enrolled in course with 10 sessions)
    $enrollmentCourse = EnrollmentCourse::create([
        'enrollment_id' => $enrollment->id,
        'course_id' => $course->id,
        'group_id' => $group->id,
        'status' => 'active',
        'current_session' => 0,
        'total_sessions' => 10,
        'course_price' => 1000,
    ]);

    expect($enrollmentCourse->current_session)->toBe(0);
    expect($enrollmentCourse->status)->toBe('active');

    // Create attendance session
    AttendanceSession::create([
        'tenant_id' => $tenant->id,
        'group_id' => $group->id,
        'course_id' => $course->id,
        'attendance_date' => now(),
        'recorded_by' => $user->id,
    ]);

    // Refresh the enrollment course
    $enrollmentCourse->refresh();

    // Assert session counter incremented
    expect($enrollmentCourse->current_session)->toBe(1);
    expect($enrollmentCourse->status)->toBe('active');
});

test('enrollment course completes automatically when reaching total sessions', function () {
    // Create necessary models
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'tenant_id' => $tenant->id,
    ]);
    $group = Group::factory()->create(['tenant_id' => $tenant->id]);
    $course = Course::factory()->create(['tenant_id' => $tenant->id]);
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'group_id' => $group->id,
        'tenant_id' => $tenant->id,
    ]);

    // Create enrollment course with 9 sessions already completed
    $enrollmentCourse = EnrollmentCourse::create([
        'enrollment_id' => $enrollment->id,
        'course_id' => $course->id,
        'group_id' => $group->id,
        'status' => 'active',
        'current_session' => 9,
        'total_sessions' => 10,
        'course_price' => 1000,
    ]);

    expect($enrollmentCourse->status)->toBe('active');

    // Create the 10th attendance session
    AttendanceSession::create([
        'tenant_id' => $tenant->id,
        'group_id' => $group->id,
        'course_id' => $course->id,
        'attendance_date' => now(),
        'recorded_by' => $user->id,
    ]);

    // Refresh the enrollment course
    $enrollmentCourse->refresh();

    // Assert course is completed
    expect($enrollmentCourse->current_session)->toBe(10);
    expect($enrollmentCourse->status)->toBe('completed');
});

test('deleting attendance session decrements enrollment course session counter', function () {
    // Create necessary models
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'tenant_id' => $tenant->id,
    ]);
    $group = Group::factory()->create(['tenant_id' => $tenant->id]);
    $course = Course::factory()->create(['tenant_id' => $tenant->id]);
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'group_id' => $group->id,
        'tenant_id' => $tenant->id,
    ]);

    // Create enrollment course with 5 sessions completed
    $enrollmentCourse = EnrollmentCourse::create([
        'enrollment_id' => $enrollment->id,
        'course_id' => $course->id,
        'group_id' => $group->id,
        'status' => 'active',
        'current_session' => 5,
        'total_sessions' => 10,
        'course_price' => 1000,
    ]);

    // Create and then delete attendance session
    $session = AttendanceSession::create([
        'tenant_id' => $tenant->id,
        'group_id' => $group->id,
        'course_id' => $course->id,
        'attendance_date' => now(),
        'recorded_by' => $user->id,
    ]);

    $enrollmentCourse->refresh();
    expect($enrollmentCourse->current_session)->toBe(6);

    // Delete the session
    $session->delete();

    // Refresh and assert counter decremented
    $enrollmentCourse->refresh();
    expect($enrollmentCourse->current_session)->toBe(5);
    expect($enrollmentCourse->status)->toBe('active');
});

test('deleting session reverts completed course back to active', function () {
    // Create necessary models
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'tenant_id' => $tenant->id,
    ]);
    $group = Group::factory()->create(['tenant_id' => $tenant->id]);
    $course = Course::factory()->create(['tenant_id' => $tenant->id]);
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'group_id' => $group->id,
        'tenant_id' => $tenant->id,
    ]);

    // Create enrollment course completed
    $enrollmentCourse = EnrollmentCourse::create([
        'enrollment_id' => $enrollment->id,
        'course_id' => $course->id,
        'group_id' => $group->id,
        'status' => 'completed',
        'current_session' => 10,
        'total_sessions' => 10,
        'course_price' => 1000,
    ]);

    expect($enrollmentCourse->status)->toBe('completed');

    // Create and delete a session
    $session = AttendanceSession::create([
        'tenant_id' => $tenant->id,
        'group_id' => $group->id,
        'course_id' => $course->id,
        'attendance_date' => now(),
        'recorded_by' => $user->id,
    ]);

    $session->delete();

    // Refresh and assert status reverted to active
    $enrollmentCourse->refresh();
    expect($enrollmentCourse->current_session)->toBe(9);
    expect($enrollmentCourse->status)->toBe('active');
});
