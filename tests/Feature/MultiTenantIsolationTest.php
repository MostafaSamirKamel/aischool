<?php

use App\Models\Course;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vault;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('tenant user can only see records belonging to their tenant', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $userA = User::factory()->create(['tenant_id' => $tenantA->id, 'is_super_admin' => false]);
    $userB = User::factory()->create(['tenant_id' => $tenantB->id, 'is_super_admin' => false]);

    // Create courses for each tenant
    $courseA = Course::factory()->create(['tenant_id' => $tenantA->id, 'name' => 'Course Tenant A']);
    $courseB = Course::factory()->create(['tenant_id' => $tenantB->id, 'name' => 'Course Tenant B']);

    // Act as user A
    $this->actingAs($userA);

    $visibleCourses = Course::all();
    expect($visibleCourses->pluck('id')->all())->toContain($courseA->id)
        ->and($visibleCourses->pluck('id')->all())->not->toContain($courseB->id);

    // Act as user B
    $this->actingAs($userB);

    $visibleCoursesB = Course::all();
    expect($visibleCoursesB->pluck('id')->all())->toContain($courseB->id)
        ->and($visibleCoursesB->pluck('id')->all())->not->toContain($courseA->id);
});

test('user without tenant_id and not super_admin sees no tenant records', function () {
    $tenant = Tenant::factory()->create();
    Course::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Secret Course']);

    $orphanUser = User::factory()->create([
        'tenant_id' => null,
        'is_super_admin' => false,
    ]);

    $this->actingAs($orphanUser);

    $visibleCourses = Course::all();
    expect($visibleCourses)->toBeEmpty();
});

test('super admin without tenant_id sees all records across tenants', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $courseA = Course::factory()->create(['tenant_id' => $tenantA->id]);
    $courseB = Course::factory()->create(['tenant_id' => $tenantB->id]);

    $superAdmin = User::factory()->create([
        'tenant_id' => null,
        'is_super_admin' => true,
    ]);

    $this->actingAs($superAdmin);

    $allCourses = Course::all();
    expect($allCourses->pluck('id')->all())->toContain($courseA->id, $courseB->id);
});

test('tenant user cannot spoof tenant_id when creating or updating models', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $userA = User::factory()->create(['tenant_id' => $tenantA->id, 'is_super_admin' => false]);

    $this->actingAs($userA);

    // Try creating a course with tenantB's id
    $course = Course::create([
        'name' => 'Hacked Course',
        'tenant_id' => $tenantB->id, // Attempting IDOR
        'price' => 500,
        'status' => 'active',
    ]);

    // Should be forced to userA's tenant_id
    expect($course->tenant_id)->toBe($tenantA->id);

    // Try updating tenant_id to tenantB
    $course->update(['tenant_id' => $tenantB->id]);
    $course->refresh();

    // Should remain tenantA
    expect($course->tenant_id)->toBe($tenantA->id);
});
