<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\Vault;
use Illuminate\Database\Eloquent\Factories\Factory;

class VaultFactory extends Factory
{
    protected $model = Vault::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => $this->faker->words(2, true) . ' Vault',
            'type' => 'cash',
            'opening_balance' => 10000.00,
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
