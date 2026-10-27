<?php

use App\Services\Api\AttendanceService;
use App\Services\Api\GradeService;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Group;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

it('requires authentication for legacy grade store endpoint', function () {
    $response = $this->postJson('/api/grades', []);

    $response->assertUnauthorized();
});

it('forbids grade creation without permission', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/grades', [
        'exam_id' => 1,
        'student_id' => 1,
        'marks_obtained' => 50,
    ]);

    $response->assertForbidden();
});

it('validates required fields for legacy grade store endpoint', function () {
    Permission::findOrCreate('Create:Grade');

    $user = User::factory()->create();
    $user->givePermissionTo('Create:Grade');
    Sanctum::actingAs($user);

    $this->mock(GradeService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('create')->never();
    });

    $response = $this->postJson('/api/grades', []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['exam_id', 'student_id', 'marks_obtained']);
});

it('returns 422 for tenant isolation failures in legacy grade store', function () {
    Permission::findOrCreate('Create:Grade');

    $tenant = Tenant::create([
        'name' => 'Tenant A',
        'slug' => 'tenant-a-' . Str::lower(Str::random(4)),
        'is_active' => true,
    ]);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $user->givePermissionTo('Create:Grade');
    Sanctum::actingAs($user);

    $track = Track::create([
        'tenant_id' => $tenant->id,
        'name' => 'Track A',
        'status' => 'active',
    ]);

    $course = Course::create([
        'tenant_id' => $tenant->id,
        'track_id' => $track->id,
        'name' => 'Course A',
        'status' => 'active',
    ]);

    $group = Group::create([
        'tenant_id' => $tenant->id,
        'course_id' => $course->id,
        'name' => 'Group A',
        'status' => 'active',
    ]);

    $studentUser = User::create([
        'name' => 'Student User',
        'email' => 'student-' . Str::lower(Str::random(5)) . '@example.test',
        'password' => Hash::make('password'),
        'tenant_id' => $tenant->id,
        'status' => 'active',
    ]);

    $student = Student::create([
        'tenant_id' => $tenant->id,
        'user_id' => $studentUser->id,
        'student_code' => 'STU-' . now()->format('Y') . '-' . Str::upper(Str::random(4)),
        'gender' => 'male',
        'status' => 'active',
    ]);

    $exam = Exam::create([
        'tenant_id' => $tenant->id,
        'course_id' => $course->id,
        'group_id' => $group->id,
        'title' => 'Exam A',
        'type' => 'quiz',
        'exam_date' => now()->addDay(),
        'duration' => 60,
        'total_marks' => 100,
        'passing_marks' => 50,
        'status' => 'scheduled',
    ]);

    $this->mock(GradeService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('create')
            ->once()
            ->andThrow(ValidationException::withMessages([
                'student_id' => ['Student does not belong to exam tenant.'],
            ]));
    });

    $response = $this->postJson('/api/grades', [
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'marks_obtained' => 40,
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['student_id']);
});

it('validates required fields for legacy attendance store endpoint', function () {
    Permission::findOrCreate('Create:Attendance');

    $user = User::factory()->create();
    $user->givePermissionTo('Create:Attendance');
    Sanctum::actingAs($user);

    $this->mock(AttendanceService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('create')->never();
    });

    $response = $this->postJson('/api/attendance', []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['student_id', 'group_id', 'date', 'status']);
});

it('returns 422 for tenant isolation failures in legacy attendance store', function () {
    Permission::findOrCreate('Create:Attendance');

    $tenant = Tenant::create([
        'name' => 'Tenant A',
        'slug' => 'tenant-a-' . Str::lower(Str::random(4)),
        'is_active' => true,
    ]);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $user->givePermissionTo('Create:Attendance');
    Sanctum::actingAs($user);

    $track = Track::create([
        'tenant_id' => $tenant->id,
        'name' => 'Track B',
        'status' => 'active',
    ]);

    $course = Course::create([
        'tenant_id' => $tenant->id,
        'track_id' => $track->id,
        'name' => 'Course B',
        'status' => 'active',
    ]);

    $group = Group::create([
        'tenant_id' => $tenant->id,
        'course_id' => $course->id,
        'name' => 'Group B',
        'status' => 'active',
    ]);

    $studentUser = User::create([
        'name' => 'Student User 2',
        'email' => 'student-' . Str::lower(Str::random(5)) . '@example.test',
        'password' => Hash::make('password'),
        'tenant_id' => $tenant->id,
        'status' => 'active',
    ]);

    $student = Student::create([
        'tenant_id' => $tenant->id,
        'user_id' => $studentUser->id,
        'student_code' => 'STU-' . now()->format('Y') . '-' . Str::upper(Str::random(4)),
        'gender' => 'male',
        'status' => 'active',
    ]);

    $this->mock(AttendanceService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('create')
            ->once()
            ->andThrow(ValidationException::withMessages([
                'group_id' => ['Student and group must belong to the same tenant.'],
            ]));
    });

    $response = $this->postJson('/api/attendance', [
        'student_id' => $student->id,
        'group_id' => $group->id,
        'date' => now()->toDateString(),
        'status' => 'present',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['group_id']);
});
