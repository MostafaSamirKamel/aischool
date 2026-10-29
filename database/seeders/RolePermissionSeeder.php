<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 10 system roles
        $roleDefinitions = [
            'super_admin' => 'Super Admin',
            'branch_manager' => 'Branch Manager',
            'admin' => 'Admin',
            'supervisor' => 'Supervisor',
            'instructor' => 'Instructor / Teacher',
            'secretary' => 'Secretary',
            'accountant' => 'Accountant',
            'registration' => 'Registration Officer',
            'student' => 'Student',
            'parent' => 'Parent',
        ];

        foreach ($roleDefinitions as $slug => $label) {
            Role::firstOrCreate(
                ['name' => $slug],
                ['guard_name' => 'web']
            );
        }

        $dataFile = __DIR__ . '/roles_permissions_data.json';
        if (!file_exists($dataFile)) {
            $this->command?->error("Permissions data file not found: {$dataFile}");
            return;
        }

        $data = json_decode((string) file_get_contents($dataFile), true);
        if (!is_array($data) || empty($data['permissions'])) {
            $this->command?->error("Invalid permissions data in {$dataFile}");
            return;
        }

        DB::transaction(function () use ($data) {
            $now = now();
            $existingPerms = Permission::pluck('id', 'name')->toArray();
            $toInsert = [];

            foreach ($data['permissions'] as $permName) {
                if (!isset($existingPerms[$permName])) {
                    $toInsert[] = [
                        'name' => $permName,
                        'guard_name' => 'web',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if (!empty($toInsert)) {
                foreach (array_chunk($toInsert, 100) as $chunk) {
                    Permission::insert($chunk);
                }
            }

            $allPerms = Permission::pluck('id', 'name')->toArray();

            foreach ($data['roles'] as $roleName => $perms) {
                $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
                $permIds = [];
                foreach ($perms as $pName) {
                    if (isset($allPerms[$pName])) {
                        $permIds[] = $allPerms[$pName];
                    }
                }
                $role->permissions()->sync($permIds);
            }
        });

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command?->info('✅ Roles and Permissions synchronized successfully!');
        $this->command?->info('📋 Active Roles: ' . count($roleDefinitions));
        $this->command?->info('🔐 Total Permissions: ' . count($data['permissions']));
    }
}
