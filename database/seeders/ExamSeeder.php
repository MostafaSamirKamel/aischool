<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Exam;
use App\Models\Group;
use App\Models\Instructor;
use Illuminate\Database\Seeder;

class ExamSeeder extends Seeder
{
    public function run(): void
    {
        $groups = Group::withoutGlobalScopes()->get();

        if ($groups->isEmpty()) {
            $this->command->warn('No groups found. Please create groups first.');
            return;
        }

        // Load courses without global scopes
        $courseIds = $groups->pluck('course_id')->unique();
        $courses = Course::withoutGlobalScopes()->whereIn('id', $courseIds)->get()->keyBy('id');

        $examsCreated = 0;

        foreach ($groups as $group) {
            $course = $courses->get($group->course_id);
            
            if (!$course) {
                continue;
            }

            // الحصول على مدرس من نفس الفرع
            $instructor = Instructor::withoutGlobalScopes()->where('tenant_id', $group->tenant_id)
                ->inRandomOrder()
                ->first();

            if (!$instructor) {
                // إذا لم يوجد مدرس في نفس الفرع، جرب أي مدرس
                $instructor = Instructor::withoutGlobalScopes()->inRandomOrder()->first();
            }

            if (!$instructor) {
                continue;
            }

            // إنشاء 2-3 امتحانات لكل مجموعة
            $examsCount = rand(2, 3);

            // اختبار قصير
            if ($examsCount >= 1) {
                Exam::create([
                    'tenant_id' => $group->tenant_id,
                    'course_id' => $course->id,
                    'group_id' => $group->id,
                    'instructor_id' => $instructor->id,
                    'type' => 'quiz',
                    'title' => 'اختبار قصير - ' . $course->name,
                    'description' => 'اختبار قصير لتقييم الفهم الأساسي',
                    'duration' => 30,
                    'total_marks' => 20,
                    'passing_marks' => 10,
                    'status' => 'completed',
                    'exam_date' => now()->subDays(rand(20, 40)),
                ]);
                $examsCreated++;
            }

            // امتحان منتصف الفصل
            if ($examsCount >= 2) {
                Exam::create([
                    'tenant_id' => $group->tenant_id,
                    'course_id' => $course->id,
                    'group_id' => $group->id,
                    'instructor_id' => $instructor->id,
                    'type' => 'midterm',
                    'title' => 'امتحان منتصف الفصل - ' . $course->name,
                    'description' => 'امتحان شامل للجزء الأول من المنهج',
                    'duration' => 90,
                    'total_marks' => 100,
                    'passing_marks' => 50,
                    'status' => 'completed',
                    'exam_date' => now()->subDays(rand(10, 20)),
                ]);
                $examsCreated++;
            }

            // امتحان نهائي
            if ($examsCount >= 3) {
                Exam::create([
                    'tenant_id' => $group->tenant_id,
                    'course_id' => $course->id,
                    'group_id' => $group->id,
                    'instructor_id' => $instructor->id,
                    'type' => 'final',
                    'title' => 'الامتحان النهائي - ' . $course->name,
                    'description' => 'امتحان نهائي شامل',
                    'duration' => 120,
                    'total_marks' => 200,
                    'passing_marks' => 100,
                    'status' => 'scheduled',
                    'exam_date' => now()->addDays(rand(5, 15)),
                ]);
                $examsCreated++;
            }
        }

        $this->command->info('Exams seeded successfully!');
        $this->command->info("Total exams created: {$examsCreated}");
    }
}
