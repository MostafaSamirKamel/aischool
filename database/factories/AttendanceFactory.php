<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\Group;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = $this->faker->dateTimeBetween('-30 days', 'now');
        $status = $this->faker->randomElement([
            'present', 'present', 'present', 'present', // 70% حضور
            'absent', 
            'late',
            'excused'
        ]);

        return [
            'tenant_id' => Tenant::inRandomOrder()->first()?->id ?? Tenant::factory(),
            'student_id' => Student::inRandomOrder()->first()?->id ?? Student::factory(),
            'group_id' => Group::inRandomOrder()->first()?->id ?? Group::factory(),
            'date' => $date,
            'status' => $status,
            'check_in_time' => $status === 'present' || $status === 'late' 
                ? $this->faker->time('H:i:s', '09:00:00')
                : null,
            'check_out_time' => $status === 'present' 
                ? $this->faker->time('H:i:s', '14:00:00') 
                : null,
            'notes' => $status === 'excused' || $status === 'absent'
                ? $this->faker->optional(0.5)->sentence()
                : null,
            'recorded_by' => User::inRandomOrder()->first()?->id,
        ];
    }

    /**
     * Indicate that the student is present.
     */
    public function present(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'present',
            'check_in_time' => $this->faker->time('H:i:s', '08:30:00'),
            'check_out_time' => $this->faker->time('H:i:s', '14:00:00'),
            'notes' => null,
        ]);
    }

    /**
     * Indicate that the student is absent.
     */
    public function absent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'absent',
            'check_in_time' => null,
            'check_out_time' => null,
            'notes' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the student is late.
     */
    public function late(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'late',
            'check_in_time' => $this->faker->time('H:i:s', '09:30:00'),
            'check_out_time' => $this->faker->time('H:i:s', '14:00:00'),
            'notes' => 'تأخر عن الموعد',
        ]);
    }

    /**
     * Indicate that the absence is excused.
     */
    public function excused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'excused',
            'check_in_time' => null,
            'check_out_time' => null,
            'notes' => $this->faker->randomElement([
                'عذر طبي',
                'ظروف عائلية',
                'إجازة رسمية',
                'تصريح من الإدارة'
            ]),
        ]);
    }
}
