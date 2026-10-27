<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get branches
        $mainBranch = Tenant::where('slug', 'main')->first();
        $damanhourBranch = Tenant::where('slug', 'main')->first();

        // Owner / Super Admin
        $owner = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'المدير العام',
                'password' => Hash::make('password'),
                'is_super_admin' => true,
                'phone' => '01000000000',
                'job_title' => 'المدير التنفيذي',
            ]
        );
        $owner->assignRole('super_admin');

        // Branch Manager - main
        $managermain = User::firstOrCreate(
            ['email' => 'manager.main@admin.com'],
            [
                'name' => 'مدير الفرع الرئيسي',
                'password' => Hash::make('password'),
                'tenant_id' => $mainBranch?->id,
                'phone' => '01011111111',
                'job_title' => 'مدير الفرع',
            ]
        );
        $managermain->assignRole('branch_manager');

        // Branch Manager - Damanhour City
        $managerDamanhour = User::firstOrCreate(
            ['email' => 'manager.damanhour@admin.com'],
            [
                'name' => 'مدير فرع دمنهور',
                'password' => Hash::make('password'),
                'tenant_id' => $damanhourBranch?->id,
                'phone' => '01022222222',
                'job_title' => 'مدير الفرع',
            ]
        );
        $managerDamanhour->assignRole('branch_manager');

        // Instructor - main
        $instructormain = User::firstOrCreate(
            ['email' => 'instructor.main@admin.com'],
            [
                'name' => 'أحمد محمد - مدرب',
                'password' => Hash::make('password'),
                'tenant_id' => $mainBranch?->id,
                'phone' => '01033333333',
                'job_title' => 'مدرب برمجة',
            ]
        );
        $instructormain->assignRole('instructor');

        // Supervisor - main
        $supervisormain = User::firstOrCreate(
            ['email' => 'supervisor.main@admin.com'],
            [
                'name' => 'خالد أحمد - مشرف',
                'password' => Hash::make('password'),
                'tenant_id' => $mainBranch?->id,
                'phone' => '01077777777',
                'job_title' => 'مشرف تعليمي',
            ]
        );
        $supervisormain->assignRole('supervisor');

        // Secretary - main
        $secretarymain = User::firstOrCreate(
            ['email' => 'secretary.main@admin.com'],
            [
                'name' => 'فاطمة علي - سكرتارية',
                'password' => Hash::make('password'),
                'tenant_id' => $mainBranch?->id,
                'phone' => '01044444444',
                'job_title' => 'سكرتارية',
            ]
        );
        $secretarymain->assignRole('secretary');

        // Instructor - Damanhour City
        $instructorDamanhour = User::firstOrCreate(
            ['email' => 'instructor.damanhour@admin.com'],
            [
                'name' => 'محمود حسن - مدرب',
                'password' => Hash::make('password'),
                'tenant_id' => $damanhourBranch?->id,
                'phone' => '01055555555',
                'job_title' => 'مدرب برمجة',
            ]
        );
        $instructorDamanhour->assignRole('instructor');

        // Supervisor - Damanhour City
        $supervisorDamanhour = User::firstOrCreate(
            ['email' => 'supervisor.damanhour@admin.com'],
            [
                'name' => 'عمر حسين - مشرف',
                'password' => Hash::make('password'),
                'tenant_id' => $damanhourBranch?->id,
                'phone' => '01088888888',
                'job_title' => 'مشرف تعليمي',
            ]
        );
        $supervisorDamanhour->assignRole('supervisor');

        // Secretary - Damanhour City
        $secretaryDamanhour = User::firstOrCreate(
            ['email' => 'secretary.damanhour@admin.com'],
            [
                'name' => 'مريم أحمد - سكرتارية',
                'password' => Hash::make('password'),
                'tenant_id' => $damanhourBranch?->id,
                'phone' => '01066666666',
                'job_title' => 'سكرتارية',
            ]
        );
        $secretaryDamanhour->assignRole('secretary');
    }
}
