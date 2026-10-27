<?php

namespace Database\Seeders;

use App\Models\Instructor;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InstructorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء معلمين للأقسام المختلفة
        $tenants = Tenant::all();
        
        if ($tenants->isEmpty()) {
            $this->command->warn('No tenants found. Please create tenants first.');
            return;
        }

        foreach ($tenants as $tenant) {
            // الحصول على أقسام المؤسسة
            $departments = Department::where('tenant_id', $tenant->id)->get();
            
            if ($departments->isEmpty()) {
                $this->command->warn("No departments found for tenant: {$tenant->name}");
                continue;
            }

            // إنشاء 3-5 معلمين لكل قسم
            foreach ($departments as $department) {
                $count = rand(3, 5);
                
                for ($i = 0; $i < $count; $i++) {
                    // إنشاء مستخدم للمعلم
                    $user = User::factory()->create([
                        'tenant_id' => $tenant->id,
                        'name' => fake()->name(),
                        'email' => fake()->unique()->safeEmail(),
                    ]);

                    // إنشاء المعلم
                    Instructor::factory()->create([
                        'user_id' => $user->id,
                        'tenant_id' => $tenant->id,
                        'department_id' => $department->id,
                        'status' => fake()->randomElement(['active', 'active', 'active', 'inactive']), // 75% نشط
                    ]);
                }
            }

            $this->command->info("Created instructors for tenant: {$tenant->name}");
        }

        // إنشاء بعض المعلمين بدون قسم محدد
        foreach ($tenants as $tenant) {
            $count = rand(2, 3);
            
            for ($i = 0; $i < $count; $i++) {
                $user = User::factory()->create([
                    'tenant_id' => $tenant->id,
                    'name' => fake()->name(),
                    'email' => fake()->unique()->safeEmail(),
                ]);

                Instructor::factory()->create([
                    'user_id' => $user->id,
                    'tenant_id' => $tenant->id,
                    'department_id' => null,
                    'status' => 'active',
                ]);
            }
        }

        $this->command->info('Instructor seeding completed successfully!');
    }
}
