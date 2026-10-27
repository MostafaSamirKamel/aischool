<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\Course;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory
{
    protected $model = Group::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'course_id' => Course::factory(),
            'name' => 'Group ' . fake()->unique()->word(),
            'capacity' => 20,
            'max_students' => 25,
            'status' => 'active',
            'total_sessions' => 10,
            'completed_sessions' => 0,
            'start_date' => now(),
        ];
    }
}
