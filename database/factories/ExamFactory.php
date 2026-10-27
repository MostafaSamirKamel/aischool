<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Exam;
use App\Models\Group;
use App\Models\Instructor;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamFactory extends Factory
{
    protected $model = Exam::class;

    public function definition(): array
    {
        $totalMarks = fake()->randomElement([50, 100, 150, 200]);
        $passingMarks = round($totalMarks * 0.5); // 50% passing

        return [
            'tenant_id' => Tenant::inRandomOrder()->first()?->id ?? Tenant::factory(),
            'course_id' => Course::inRandomOrder()->first()?->id ?? Course::factory(),
            'group_id' => Group::inRandomOrder()->first()?->id ?? Group::factory(),
            'instructor_id' => Instructor::inRandomOrder()->first()?->id ?? Instructor::factory(),
            'title' => fake()->randomElement([
                'امتحان الوحدة الأولى',
                'اختبار شهري',
                'امتحان منتصف الفصل',
                'امتحان نهائي',
                'واجب عملي',
                'مشروع نهائي',
                'اختبار قصير',
                'امتحان تجريبي',
            ]) . ' - ' . fake()->word(),
            'description' => fake()->optional(0.7)->sentence(10),
            'type' => fake()->randomElement(['quiz', 'midterm', 'final', 'assignment', 'project']),
            'exam_date' => fake()->dateTimeBetween('-2 months', '+2 months'),
            'duration' => fake()->randomElement([30, 45, 60, 90, 120, 180]),
            'total_marks' => $totalMarks,
            'passing_marks' => $passingMarks,
            'status' => fake()->randomElement(['scheduled', 'completed', 'in_progress', 'cancelled']),
            'instructions' => fake()->optional(0.5)->paragraph(3),
        ];
    }

    public function quiz(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'quiz',
            'duration' => 30,
            'total_marks' => 20,
            'passing_marks' => 10,
        ]);
    }

    public function midterm(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'midterm',
            'duration' => 90,
            'total_marks' => 100,
            'passing_marks' => 50,
        ]);
    }

    public function final(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'final',
            'duration' => 120,
            'total_marks' => 200,
            'passing_marks' => 100,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'exam_date' => fake()->dateTimeBetween('now', '+1 month'),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'exam_date' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }
}