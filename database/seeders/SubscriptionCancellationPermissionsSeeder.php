<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SubscriptionCancellationPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Cancellation permissions
            'Cancel:Enrollment',
            'cancel_enrollments',
            'cancel_subscription',

            // Settlement permissions
            'Settle:Enrollment',
            'settle_enrollments',
            'settle_subscription',

            // Refund execution permissions
            'Refund:Payment',
            'refund_subscription',
            'execute_refunds',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web']
            );
        }

        // Give permissions to super_admin
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        // Give permissions to branch_manager
        $branchManager = Role::where('name', 'branch_manager')->first();
        if ($branchManager) {
            $branchManager->givePermissionTo($permissions);
        }

        // Give cancel permission to secretary
        $secretary = Role::where('name', 'secretary')->first();
        if ($secretary) {
            $secretary->givePermissionTo([
                'Cancel:Enrollment',
                'cancel_enrollments',
                'cancel_subscription',
            ]);
        }
    }
}
