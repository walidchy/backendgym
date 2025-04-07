<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\MembershipPlan;

class MembershipPlanFactory extends Factory
{
    protected $model = MembershipPlan::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'duration_days' => $this->faker->randomElement([30, 60, 90, 180, 365]),
            'features' => json_encode([$this->faker->sentence(), $this->faker->sentence()]),
            'is_active' => $this->faker->boolean(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}


