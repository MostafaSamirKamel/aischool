<?php

/**
 * Script to verify the Role and Permission System
 * 
 * Run: php check_roles_system.php
 */

require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

// Initialize the app
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

echo "\n=== Role and Permission System Verification ===\n\n";

// 1. Check Roles
echo "1. Checking Roles:\n";
echo "─────────────────\n";

$roles = Role::all();
echo "Total Roles: " . $roles->count() . "\n";

foreach ($roles as $role) {
    $permissionsCount = $role->permissions()->count();
    echo "  • {$role->name}: {$permissionsCount} permissions\n";
}

echo "\n";

// 2. Check Permissions
echo "2. Checking Permissions:\n";
echo "─────────────────────────\n";

$permissions = Permission::all();
echo "Total Permissions: " . $permissions->count() . "\n";

$resourcePermissions = $permissions->groupBy(function ($item) {
    return explode(':', $item->name)[1] ?? 'Unknown';
});

foreach ($resourcePermissions as $resource => $perms) {
    echo "  • {$resource}: " . $perms->count() . " permissions\n";
}

echo "\n";

// 3. Check Role-Permission Mapping
echo "3. Role-Permission Mapping:\n";
echo "──────────────────────────\n";

foreach ($roles as $role) {
    echo "  {$role->name}:\n";
    $permissions = $role->permissions()->pluck('name')->toArray();
    
    if (empty($permissions)) {
        echo "    - No permissions\n";
    } else {
        foreach ($permissions as $permission) {
            echo "    ✓ {$permission}\n";
        }
    }
    echo "\n";
}

// 4. Check Users with Roles
echo "4. Users with Roles:\n";
echo "────────────────────\n";

$users = User::with('roles')->limit(5)->get();
echo "Sample Users (max 5):\n";

foreach ($users as $user) {
    $userRoles = $user->roles->pluck('name')->toArray();
    $rolesList = !empty($userRoles) ? implode(', ', $userRoles) : 'No roles assigned';
    echo "  • {$user->name} ({$user->email}): {$rolesList}\n";
}

echo "\n=== Verification Complete ===\n\n";
