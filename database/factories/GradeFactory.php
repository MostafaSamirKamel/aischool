<?php

namespace Database\Factories;

use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradeFactory extends Factory
{
    protected $model = Grade::class;

    public function definition(): array
    {
        $exam = Exam::inRandomOrder()->first();
        
        if (!$exam) {
            $exam = Exam::factory()->create();
        }

        // توزيع واقعي للدرجات: 75% نجاح، 25% رسوب
        $isPass = fake()->boolean(75);
        
        if ($isPass) {
            // درجات النجاح: من درجة النجاح إلى الدرجة الكاملة
            $marksObtained = fake()->numberBetween($exam->passing_marks, $exam->total_marks);
        } else {
            // درجات الرسوب: أقل من درجة النجاح
            $marksObtained = fake()->numberBetween(0, $exam->passing_marks - 1);
        }

        return [
            'tenant_id' => $exam->tenant_id,
            'exam_id' => $exam->id,
            'student_id' => Student::inRandomOrder()->first()?->id ?? Student::factory(),
            'enrollment_id' => Enrollment::inRandomOrder()->first()?->id,
            'marks_obtained' => $marksObtained,
            // سيتم حساب percentage, grade, grade_points, status تلقائياً في Model
            'remarks' => fake()->optional(0.3)->sentence(),
            'graded_by' => User::inRandomOrder()->first()?->id,
            'graded_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function excellent(): static
    {
        return $this->state(function (array $attributes) {
            $exam = Exam::find($attributes['exam_id']);
            return [
                'marks_obtained' => fake()->numberBetween(
                    round($exam->total_marks * 0.90),
                    $exam->total_marks
                ),
            ];
        });
    }

    public function good(): static
    {
        return $this->state(function (array $attributes) {
            $exam = Exam::find($attributes['exam_id']);
            return [
                'marks_obtained' => fake()->numberBetween(
                    round($exam->total_marks * 0.75),
                    round($exam->total_marks * 0.89)
                ),
            ];
        });
    }

    public function passing(): static
    {
        return $this->state(function (array $attributes) {
            $exam = Exam::find($attributes['exam_id']);
            return [
                'marks_obtained' => fake()->numberBetween(
                    $exam->passing_marks,
                    round($exam->total_marks * 0.74)
                ),
            ];
        });
    }

    public function failing(): static
    {
        return $this->state(function (array $attributes) {
            $exam = Exam::find($attributes['exam_id']);
            return [
                'marks_obtained' => fake()->numberBetween(
                    0,
                    $exam->passing_marks - 1
                ),
            ];
        });
    }
}
