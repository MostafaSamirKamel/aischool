<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('authenticated student can retrieve portal data with courses and registrations', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Tariq Student',
        'email' => 'tariq@student.test',
    ]);

    $student = Student::factory()->create([
        'user_id' => $user->id,
        'tenant_id' => $tenant->id,
        'student_code' => 'STD-2026-99',
    ]);

    $course = Course::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'AI and Python 101',
        'price' => 1200.00,
    ]);

    $group = Group::factory()->create([
        'tenant_id' => $tenant->id,
        'course_id' => $course->id,
        'name' => 'Group A1',
    ]);

    $vault = \App\Models\Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Main Vault',
        'type' => 'cash',
        'status' => 'active',
        'opening_balance' => 0,
    ]);

    $enrollment = Enrollment::factory()->create([
        'tenant_id' => $tenant->id,
        'student_id' => $student->id,
        'vault_id' => $vault->id,
        'total_amount' => 1200.00,
        'paid_amount' => 400.00,
        'discount' => 100.00,
    ]);

    $enrollment->courses()->attach($course->id, [
        'course_price' => 1200.00,
        'status' => 'active',
        'current_session' => 3,
        'total_sessions' => 12,
        'group_id' => $group->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/student/portal');

    $response->assertOk()
        ->assertJsonStructure([
            'student' => ['id', 'displayName', 'studentCode', 'email'],
            'courses',
            'registrations',
            'resources',
            'exams',
            'assignments',
            'payments',
            'installments',
            'attendance',
        ]);

    $data = $response->json();
    expect($data['student']['displayName'])->toBe('Tariq Student')
        ->and($data['student']['studentCode'])->toBe('STD-2026-99')
        ->and(count($data['courses']))->toBe(1)
        ->and($data['courses'][0]['title'])->toBe('AI and Python 101')
        ->and($data['courses'][0]['totalSessions'])->toBe(12)
        ->and($data['courses'][0]['completedSessions'])->toBe(3);
});

test('unauthenticated request to student portal is rejected', function () {
    $response = $this->getJson('/api/student/portal');
    $response->assertUnauthorized();
});
