<?php

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\Group;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

function createTenant(string $name): Tenant
{
    return Tenant::create([
        'name' => $name,
        'slug' => Str::slug($name) . '-' . Str::lower(Str::random(6)),
        'is_active' => true,
    ]);
}

function createUserForTenant(Tenant $tenant, bool $isSuperAdmin = false): User
{
    return User::create([
        'name' => 'Test User ' . Str::random(5),
        'email' => Str::lower(Str::random(8)) . '@example.test',
        'password' => Hash::make('password'),
        'tenant_id' => $isSuperAdmin ? null : $tenant->id,
        'is_super_admin' => $isSuperAdmin,
        'status' => 'active',
    ]);
}

function createAcademicContext(Tenant $tenant): array
{
    $track = Track::create([
        'tenant_id' => $tenant->id,
        'name' => 'Track ' . Str::random(4),
        'status' => 'active',
    ]);

    $course = Course::create([
        'tenant_id' => $tenant->id,
        'track_id' => $track->id,
        'name' => 'Course ' . Str::random(4),
        'status' => 'active',
    ]);

    $group = Group::create([
        'tenant_id' => $tenant->id,
        'course_id' => $course->id,
        'name' => 'Group ' . Str::random(4),
        'status' => 'active',
    ]);

    return compact('track', 'course', 'group');
}

function createStudentForTenant(Tenant $tenant): Student
{
    $studentUser = createUserForTenant($tenant);

    return Student::create([
        'tenant_id' => $tenant->id,
        'user_id' => $studentUser->id,
        'student_code' => 'STU-' . now()->format('Y') . '-' . Str::upper(Str::random(4)),
        'gender' => 'male',
        'status' => 'active',
    ]);
}

function grantPermission(User $user, string $permissionName): void
{
    Permission::firstOrCreate([
        'name' => $permissionName,
        'guard_name' => 'web',
    ]);

    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $user->givePermissionTo($permissionName);
}

test('legacy grade endpoint forbids creating grade without permission', function () {
    $tenant = createTenant('Tenant A');
    $user = createUserForTenant($tenant);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/grades', []);

    $response->assertForbidden();
});

test('legacy grade endpoint validates marks and rejects cross tenant student', function () {
    $tenantA = createTenant('Tenant A');
    $tenantB = createTenant('Tenant B');

    $superAdmin = createUserForTenant($tenantA, true);
    grantPermission($superAdmin, 'Create:Grade');

    Sanctum::actingAs($superAdmin);

    $context = createAcademicContext($tenantA);
    $studentFromOtherTenant = createStudentForTenant($tenantB);

    $exam = Exam::create([
        'tenant_id' => $tenantA->id,
        'course_id' => $context['course']->id,
        'group_id' => $context['group']->id,
        'title' => 'Cross Tenant Exam',
        'type' => 'quiz',
        'exam_date' => now()->addDay(),
        'duration' => 60,
        'total_marks' => 100,
        'passing_marks' => 50,
        'status' => 'scheduled',
    ]);

    $response = $this->postJson('/api/grades', [
        'exam_id' => $exam->id,
        'student_id' => $studentFromOtherTenant->id,
        'marks_obtained' => 70,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['student_id']);
});

test('legacy grade endpoint creates grade successfully when data is valid', function () {
    $tenant = createTenant('Tenant A');
    $user = createUserForTenant($tenant);
    grantPermission($user, 'Create:Grade');

    Sanctum::actingAs($user);

    $context = createAcademicContext($tenant);
    $student = createStudentForTenant($tenant);

    $exam = Exam::create([
        'tenant_id' => $tenant->id,
        'course_id' => $context['course']->id,
        'group_id' => $context['group']->id,
        'title' => 'Valid Exam',
        'type' => 'quiz',
        'exam_date' => now()->addDay(),
        'duration' => 45,
        'total_marks' => 100,
        'passing_marks' => 50,
        'status' => 'scheduled',
    ]);

    $response = $this->postJson('/api/grades', [
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'marks_obtained' => 95,
        'remarks' => 'Excellent',
    ]);

    $response->assertCreated()
        ->assertJsonPath('message', 'Grade created successfully');

    expect(Grade::query()->count())->toBe(1);
});

test('legacy attendance endpoint forbids creating attendance without permission', function () {
    $tenant = createTenant('Tenant A');
    $user = createUserForTenant($tenant);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/attendance', []);

    $response->assertForbidden();
});

test('legacy attendance endpoint rejects cross tenant student group pair', function () {
    $tenantA = createTenant('Tenant A');
    $tenantB = createTenant('Tenant B');

    $superAdmin = createUserForTenant($tenantA, true);
    grantPermission($superAdmin, 'Create:Attendance');

    Sanctum::actingAs($superAdmin);

    $contextA = createAcademicContext($tenantA);
    $studentFromB = createStudentForTenant($tenantB);

    $response = $this->postJson('/api/attendance', [
        'student_id' => $studentFromB->id,
        'group_id' => $contextA['group']->id,
        'date' => now()->toDateString(),
        'status' => 'present',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['group_id']);
});

test('legacy attendance endpoint prevents duplicate record per student group date', function () {
    $tenant = createTenant('Tenant A');
    $user = createUserForTenant($tenant);
    grantPermission($user, 'Create:Attendance');

    Sanctum::actingAs($user);

    $context = createAcademicContext($tenant);
    $student = createStudentForTenant($tenant);
    $date = now()->toDateString();

    $first = $this->postJson('/api/attendance', [
        'student_id' => $student->id,
        'group_id' => $context['group']->id,
        'date' => $date,
        'status' => 'present',
    ]);

    $first->assertCreated();

    $second = $this->postJson('/api/attendance', [
        'student_id' => $student->id,
        'group_id' => $context['group']->id,
        'date' => $date,
        'status' => 'present',
    ]);

    $second->assertStatus(422)
        ->assertJsonValidationErrors(['date']);

    expect(Attendance::query()->count())->toBe(1);
});
