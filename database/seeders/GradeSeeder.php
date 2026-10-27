<?php

namespace Database\Seeders;

use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\Group;
use App\Models\Student;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $exams = Exam::withoutGlobalScopes()->where('status', 'completed')->get();

        if ($exams->isEmpty()) {
            $this->command->warn('No completed exams found. Run ExamSeeder first or change exam status to completed.');
            return;
        }

        // Load groups without global scopes
        $groupIds = $exams->pluck('group_id')->unique();
        $groups = Group::withoutGlobalScopes()->whereIn('id', $groupIds)->get()->keyBy('id');

        $gradesCreated = 0;

        foreach ($exams as $exam) {
            $group = $groups->get($exam->group_id);

            if (!$group) {
                continue;
            }

            // الحصول على الطلاب المسجلين في المجموعة
            $enrollments = Enrollment::withoutGlobalScopes()->where('group_id', $exam->group_id)
                ->where('status', 'active')
                ->get();

            // Manually load students
            $studentIds = $enrollments->pluck('student_id')->unique();
            $students = Student::withoutGlobalScopes()->whereIn('id', $studentIds)->get()->keyBy('id');

            if ($enrollments->isEmpty()) {
                $this->command->warn("No enrollments found for group: {$group->name}");
                continue;
            }

            foreach ($enrollments as $enrollment) {
                // التحقق من عدم وجود درجة مسبقة
                $existingGrade = Grade::withoutGlobalScopes()->where('exam_id', $exam->id)
                    ->where('student_id', $enrollment->student_id)
                    ->first();

                if ($existingGrade) {
                    continue;
                }

                // توزيع واقعي للدرجات
                $distribution = rand(1, 100);
                
                if ($distribution <= 20) {
                    // 20% ممتاز (A+, A)
                    $marksObtained = rand(
                        round($exam->total_marks * 0.90),
                        $exam->total_marks
                    );
                } elseif ($distribution <= 50) {
                    // 30% جيد جداً (B+, B)
                    $marksObtained = rand(
                        round($exam->total_marks * 0.75),
                        round($exam->total_marks * 0.89)
                    );
                } elseif ($distribution <= 75) {
                    // 25% جيد (C+, C)
                    $marksObtained = rand(
                        round($exam->total_marks * 0.60),
                        round($exam->total_marks * 0.74)
                    );
                } elseif ($distribution <= 90) {
                    // 15% مقبول (D)
                    $marksObtained = rand(
                        $exam->passing_marks,
                        round($exam->total_marks * 0.59)
                    );
                } else {
                    // 10% راسب (F)
                    $marksObtained = rand(
                        max(0, $exam->passing_marks - 10),
                        max(0, $exam->passing_marks - 1)
                    );
                }

                Grade::create([
                    'tenant_id' => $exam->tenant_id,
                    'exam_id' => $exam->id,
                    'student_id' => $enrollment->student_id,
                    'enrollment_id' => $enrollment->id,
                    'marks_obtained' => $marksObtained,
                    'graded_by' => $exam->instructor->user_id ?? 1,
                    'graded_at' => $exam->exam_date->addDays(rand(1, 3)),
                ]);

                $gradesCreated++;
            }
        }

        $this->command->info('Grades seeded successfully!');
        $this->command->info("Total grades created: {$gradesCreated}");

        // إحصائيات التوزيع
        $totalGrades = Grade::withoutGlobalScopes()->count();
        $gradeDistribution = Grade::withoutGlobalScopes()
            ->selectRaw('grade, COUNT(*) as count')
            ->whereNotNull('grade')
            ->groupBy('grade')
            ->orderByRaw("FIELD(grade, 'A+', 'A', 'B+', 'B', 'C+', 'C', 'D', 'F')")
            ->get();
            
        $this->command->info("\nGrade Distribution:");
        foreach ($gradeDistribution as $dist) {
            $percentage = round(($dist->count / $totalGrades) * 100, 1);
            $this->command->info("  {$dist->grade}: {$dist->count} ({$percentage}%)");
        }
    }
}