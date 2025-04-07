<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Booking;
use App\Models\User;
use App\Models\Activity;
use App\Models\ActivitySchedule;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'activity_id' => Activity::factory(),
            'activity_schedule_id' => ActivitySchedule::factory(),
            'date' => $this->faker->date(),
            'status' => $this->faker->randomElement(['upcoming', 'completed', 'canceled']),
            'cancellation_reason' => $this->faker->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

