<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Equipment;

class EquipmentFactory extends Factory
{
    protected $model = Equipment::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'category' => $this->faker->word(),
            'quantity' => $this->faker->numberBetween(1, 20),
            'purchase_date' => $this->faker->date(),
            'maintenance_date' => $this->faker->date(),
            'status' => $this->faker->randomElement(['available', 'in_use', 'maintenance', 'retired']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

