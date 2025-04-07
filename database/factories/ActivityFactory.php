<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Activity;
use App\Models\User;

class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'trainer_id' => User::factory(),
            'category' => $this->faker->word(),
            'difficulty_level' => $this->faker->randomElement(['beginner', 'intermediate', 'advanced']),
            'duration_minutes' => $this->faker->numberBetween(30, 120),
            'max_participants' => $this->faker->numberBetween(5, 30),
            'location' => $this->faker->city(),
            'equipment_needed' => json_encode([$this->faker->word(), $this->faker->word()]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}


