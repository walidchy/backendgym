<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ActivitySchedule;
use App\Models\Activity;

class ActivityScheduleFactory extends Factory
{
    protected $model = ActivitySchedule::class;

    public function definition()
    {
        return [
            'activity_id' => Activity::factory(),
            'day_of_week' => $this->faker->randomElement(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']),
            'start_time' => $this->faker->time(),
            'end_time' => $this->faker->time(),
            'is_recurring' => $this->faker->boolean(),
            'specific_date' => $this->faker->date(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

