<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Group;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        if (!$tenant) return;

        // Ensure instructor user has Instructor profile
        $instructorUser = User::where('email', 'instructor.main@admin.com')->first();
        if ($instructorUser) {
            $instructor = Instructor::firstOrCreate(
                ['user_id' => $instructorUser->id],
                [
                    'tenant_id' => $tenant->id,
                    'employee_code' => 'INS-MAIN-001',
                    'specialization' => 'Web Development',
                    'status' => 'active',
                ]
            );

            // Find or assign to an active group
            $group = Group::where('tenant_id', $tenant->id)->first();
            if ($group) {
                $group->update(['instructor_id' => $instructor->id]);

                // Create Homework Assignment
                Assignment::firstOrCreate(
                    [
                        'title' => 'واجب الشروط والمتغيرات البرمجية - Python Conditions',
                        'group_id' => $group->id,
                    ],
                    [
                        'tenant_id' => $tenant->id,
                        'instructor_id' => $instructor->id,
                        'description' => 'حل التمارين من صفحة 15 إلى 18 بخط اليد وصور الحل بكاميرا الهاتف أو ارفع الصور من المعرض (بحد أقصى 5 صور). تأكد من وضوح الخط وكتابة اسمك في أعلى كل صفحة.',
                        'due_date' => now()->addDays(7),
                        'max_score' => 10,
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}
