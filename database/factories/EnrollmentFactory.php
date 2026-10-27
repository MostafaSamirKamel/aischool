<?php

namespace Database\Factories;

use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Student;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnrollmentFactory extends Factory
{
    protected $model = Enrollment::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'student_id' => Student::factory(),
            'group_id' => Group::factory(),
            'enrollment_code' => 'ENR' . fake()->unique()->numerify('######'),
            'enrollment_date' => now(),
            'total_amount' => 1000,
            'paid_amount' => 0,
            'discount' => 0,
            'payment_plan' => 'full',
            'status' => 'active',
        ];
    }
}
