<?php

namespace Database\Factories;

use App\Models\Instructor;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Instructor>
 */
class InstructorFactory extends Factory
{
    protected $model = Instructor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $specializations = [
            'رياضيات', 'فيزياء', 'كيمياء', 'أحياء', 'لغة عربية', 
            'لغة إنجليزية', 'تاريخ', 'جغرافيا', 'علوم الحاسوب', 'تربية إسلامية'
        ];

        $qualifications = [
            [
                'title' => $this->faker->randomElement(['بكالوريوس', 'ماجستير', 'دكتوراه']),
                'institution' => $this->faker->randomElement(['جامعة القاهرة', 'جامعة الأزهر', 'جامعة عين شمس']),
                'year' => $this->faker->numberBetween(2005, 2020),
            ],
        ];

        if ($this->faker->boolean(30)) {
            $qualifications[] = [
                'title' => 'دبلوم تربوي',
                'institution' => $this->faker->randomElement(['كلية التربية', 'معهد الدراسات التربوية']),
                'year' => $this->faker->numberBetween(2010, 2022),
            ];
        }

        return [
            'user_id' => User::factory(),
            'tenant_id' => Tenant::inRandomOrder()->first()?->id ?? Tenant::factory(),
            'department_id' => Department::inRandomOrder()->first()?->id ?? null,
            'employee_code' => 'INS-' . $this->faker->unique()->numberBetween(1000, 9999),
            'specialization' => $this->faker->randomElement($specializations),
            'bio' => $this->faker->paragraph(3),
            'qualifications' => $qualifications,
            'years_of_experience' => $this->faker->numberBetween(0, 30),
            'status' => $this->faker->randomElement(['active', 'inactive', 'on_leave']),
            'hired_date' => $this->faker->dateTimeBetween('-10 years', 'now'),
            'contract_type' => $this->faker->randomElement(['full_time', 'part_time', 'contract']),
            'salary' => $this->faker->randomFloat(2, 3000, 15000),
            'phone' => $this->faker->phoneNumber(),
            'emergency_contact' => $this->faker->name() . ' - ' . $this->faker->phoneNumber(),
        ];
    }

    /**
     * Indicate that the instructor is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the instructor is on leave.
     */
    public function onLeave(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'on_leave',
        ]);
    }

    /**
     * Indicate that the instructor is full-time.
     */
    public function fullTime(): static
    {
        return $this->state(fn (array $attributes) => [
            'contract_type' => 'full_time',
            'salary' => $this->faker->randomFloat(2, 8000, 15000),
        ]);
    }

    /**
     * Indicate that the instructor is part-time.
     */
    public function partTime(): static
    {
        return $this->state(fn (array $attributes) => [
            'contract_type' => 'part_time',
            'salary' => $this->faker->randomFloat(2, 3000, 7000),
        ]);
    }
}
