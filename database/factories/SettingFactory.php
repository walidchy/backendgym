<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Setting;

class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition()
    {
        return [
            'key' => $this->faker->word(),
            'value' => $this->faker->text(),
            'group' => $this->faker->randomElement(['general', 'payment', 'user']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

