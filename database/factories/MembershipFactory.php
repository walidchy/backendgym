<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Membership;
use App\Models\User;
use App\Models\MembershipPlan;

class MembershipFactory extends Factory
{
    protected $model = Membership::class;

    public function definition()
    {
        return [
            'user_id' => User::factory()->member(), // Only 'member' role users
            'membership_plan_id' => MembershipPlan::factory(),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'is_active' => $this->faker->boolean(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    
}


