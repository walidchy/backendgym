<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Booking;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'booking_id' => Booking::factory(),
            'check_in_time' => $this->faker->dateTime(),
            'check_out_time' => $this->faker->dateTime(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
