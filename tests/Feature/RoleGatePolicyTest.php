<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('super admin bypasses all gates via Gate::before', function () {
    $superAdmin = User::factory()->create([
        'is_super_admin' => true,
        'tenant_id' => null,
    ]);

    $this->actingAs($superAdmin);

    // Any arbitrary permission check should be true for super_admin
    expect(Gate::allows('non_existent_or_unassigned_permission'))->toBeTrue();
});

test('branch manager does not automatically bypass gates and requires assigned permission', function () {
    $tenant = Tenant::factory()->create();

    $role = Role::findOrCreate('branch_manager', 'web');
    $permission = Permission::findOrCreate('view_special_reports', 'web');

    $branchManager = User::factory()->create([
        'is_super_admin' => false,
        'tenant_id' => $tenant->id,
    ]);
    $branchManager->assignRole($role);

    $this->actingAs($branchManager);

    // Branch manager does NOT have view_special_reports yet: must be FALSE
    expect(Gate::allows('view_special_reports'))->toBeFalse();
    expect(Gate::allows('random_unassigned_permission'))->toBeFalse();

    // Now grant the specific permission
    $role->givePermissionTo($permission);
    $branchManager->load('permissions', 'roles');

    // Now should be allowed for granted permission, but still NOT for random unassigned ones
    expect(Gate::allows('view_special_reports'))->toBeTrue();
    expect(Gate::allows('random_unassigned_permission'))->toBeFalse();
});
