<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TrainerAvailability;
use App\Models\User;

class TrainerAvailabilityFactory extends Factory
{
    protected $model = TrainerAvailability::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'day_of_week' => $this->faker->randomElement(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']),
            'start_time' => $this->faker->time(),
            'end_time' => $this->faker->time(),
            'is_available' => $this->faker->boolean(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

