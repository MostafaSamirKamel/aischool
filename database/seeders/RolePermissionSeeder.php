<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
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

        // Create roles
        $roles = [
            'super_admin' => 'Super Admin',
            'branch_manager' => 'Branch Manager',
            'supervisor' => 'Supervisor',
            'instructor' => 'Instructor / Teacher',
            'secretary' => 'Secretary',
            'student' => 'Student',
            'parent' => 'Parent',
        ];

        foreach ($roles as $slug => $name) {
            Role::firstOrCreate(
                ['name' => $slug],
                ['guard_name' => 'web']
            );
        }

        // Define permissions by module
        $permissions = [
            // Tenants/Branches
            'view_tenants',
            'create_tenants',
            'edit_tenants',
            'delete_tenants',
            
            // Users
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            
            // Students (سيتم إضافتها لاحقًا في Sprint 3)
            'view_students',
            'create_students',
            'edit_students',
            'delete_students',
            
            // Courses (سيتم إضافتها لاحقًا في Sprint 2)
            'view_courses',
            'create_courses',
            'edit_courses',
            'delete_courses',
            
            // Attendance (سيتم إضافتها لاحقًا في Sprint 4)
            'view_attendance',
            'create_attendance',
            'edit_attendance',
            'delete_attendance',
            
            // Payments (سيتم إضافتها لاحقًا في Sprint 5)
            'view_payments',
            'create_payments',
            'edit_payments',
            'delete_payments',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }

        // Assign permissions to roles
        $super_admin = Role::findByName('super_admin');
        $super_admin->givePermissionTo(Permission::all());

        $branchManager = Role::findByName('branch_manager');
        $branchManager->givePermissionTo([
            'view_users',
            'create_users',
            'edit_users',
            'view_students',
            'create_students',
            'edit_students',
            'delete_students',
            'view_courses',
            'view_attendance',
            'create_attendance',
            'edit_attendance',
            'view_payments',
            'create_payments',
            'edit_payments',
        ]);

        $instructor = Role::findByName('instructor');
        $instructor->givePermissionTo([
            'view_students',
            'view_courses',
            'view_attendance',
            'create_attendance',
            'edit_attendance',
        ]);

        $secretary = Role::findByName('secretary');
        $secretary->givePermissionTo([
            'view_students',
            'create_students',
            'edit_students',
            'view_payments',
            'create_payments',
            'edit_payments',
        ]);
    }
}
