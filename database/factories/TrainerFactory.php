<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Trainer;
use App\Models\User;

class TrainerFactory extends Factory
{
    protected $model = Trainer::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'specialization' => $this->faker->word(),
            'bio' => $this->faker->paragraph(),
            'experience_years' => $this->faker->numberBetween(1, 20),
            'certifications' => json_encode([$this->faker->word(), $this->faker->word()]),
            'phone' => $this->faker->phoneNumber(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

