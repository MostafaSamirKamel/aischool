<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

test('legacy exams index returns validation contract for invalid filters', function () {
    Permission::findOrCreate('ViewAny:Exam');

    $user = User::factory()->create();
    $user->givePermissionTo('ViewAny:Exam');
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/exams?status=bad-status&per_page=0');

    $response->assertUnprocessable();
    $response->assertJsonStructure([
        'message',
        'errors' => ['status', 'per_page'],
    ]);
});

test('legacy exams upcoming returns validation contract for invalid limit', function () {
    Permission::findOrCreate('ViewAny:Exam');

    $user = User::factory()->create();
    $user->givePermissionTo('ViewAny:Exam');
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/exams/upcoming?limit=0');

    $response->assertUnprocessable();
    $response->assertJsonStructure([
        'message',
        'errors' => ['limit'],
    ]);
});

test('legacy students index returns validation contract for invalid per page', function () {
    Permission::findOrCreate('ViewAny:Student');

    $user = User::factory()->create();
    $user->givePermissionTo('ViewAny:Student');
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/students?per_page=0');

    $response->assertUnprocessable();
    $response->assertJsonStructure([
        'message',
        'errors' => ['per_page'],
    ]);
});

test('legacy students endpoints are forbidden without view permission', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/students');

    $response->assertForbidden();
    $response->assertJsonStructure(['message']);
});

test('legacy exams endpoints are forbidden without view permission', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/exams');

    $response->assertForbidden();
    $response->assertJsonStructure(['message']);
});
