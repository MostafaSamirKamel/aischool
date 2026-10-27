<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class PanelRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء أدوار خاصة بكل panel
        $roles = [
            [
                'name' => 'instructor_manager',
                'panel_type' => 'instructor',
                'guard_name' => 'web',
                'description' => 'مدير لوحة المدرسين - صلاحيات كاملة على Instructor Panel فقط'
            ],
            [
                'name' => 'student_manager',
                'panel_type' => 'student',
                'guard_name' => 'web',
                'description' => 'مدير لوحة الطلاب - صلاحيات كاملة على Student Panel فقط'
            ],
            [
                'name' => 'parent_manager',
                'panel_type' => 'parent',
                'guard_name' => 'web',
                'description' => 'مدير لوحة أولياء الأمور - صلاحيات كاملة على Parent Panel فقط'
            ],
            [
                'name' => 'supervisor_manager',
                'panel_type' => 'supervisor',
                'guard_name' => 'web',
                'description' => 'مدير لوحة المشرفين - صلاحيات كاملة على Supervisor Panel فقط'
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name'], 'guard_name' => $roleData['guard_name']],
                ['panel_type' => $roleData['panel_type']]
            );
            
            $this->command->info("تم إنشاء الدور: {$roleData['name']} ({$roleData['description']})");
        }

        $this->command->info('✅ تم إنشاء جميع أدوار الـ Panels بنجاح!');
        $this->command->info('📝 يمكنك الآن تعديل صلاحيات كل دور من Admin Panel -> الأدوار والصلاحيات');
    }
}
