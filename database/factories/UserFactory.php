<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => $this->faker->randomElement(['member', 'trainer', 'admin']),
            'is_verified' => $this->faker->boolean(),
            'avatar' => $this->faker->imageUrl(200, 200, 'people'),
            'remember_token' => $this->faker->uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function member()
{
    return $this->state(function (array $attributes) {
        return [
            'role' => 'member',
        ];
    });
}
}

