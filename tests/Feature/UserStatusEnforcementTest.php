<?php

use App\Http\Middleware\CheckUserStatus;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

test('active user passes CheckUserStatus middleware', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'status' => 'active',
    ]);

    $this->actingAs($user);

    $request = Request::create('/admin', 'GET');
    $middleware = new CheckUserStatus();

    $response = $middleware->handle($request, function ($req) {
        return new Response('OK', 200);
    });

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getContent())->toBe('OK');
});

test('inactive user is logged out and redirected by CheckUserStatus middleware', function () {
    $tenant = Tenant::factory()->create();
    $inactiveUser = User::factory()->create([
        'tenant_id' => $tenant->id,
        'status' => 'inactive',
    ]);

    $this->actingAs($inactiveUser);
    expect(auth()->check())->toBeTrue();

    $request = Request::create('/admin', 'GET');
    $middleware = new CheckUserStatus();

    $response = $middleware->handle($request, function ($req) {
        return new Response('OK', 200);
    });

    // Should redirect (302) and logout
    expect($response->getStatusCode())->toBe(302)
        ->and(auth()->check())->toBeFalse();
});

test('suspended or blocked user is logged out and redirected for non-admin panels', function () {
    $tenant = Tenant::factory()->create();

    $panels = [
        'instructor' => '/instructor',
        'supervisor' => '/supervisor',
        'student' => '/student',
        'parent' => '/parent',
    ];

    foreach ($panels as $panel => $url) {
        $inactiveUser = User::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => 'inactive',
        ]);

        $this->actingAs($inactiveUser);
        expect(auth()->check())->toBeTrue();

        $request = Request::create($url, 'GET');
        $middleware = new CheckUserStatus();

        $response = $middleware->handle($request, function ($req) {
            return new Response('OK', 200);
        });

        expect($response->getStatusCode())->toBe(302)
            ->and(auth()->check())->toBeFalse();
    }
});
