<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Member;
use App\Models\Trainer;
use App\Models\Admin;
use App\Models\MembershipPlan;
use App\Models\Membership;
use App\Models\Activity;
use App\Models\ActivitySchedule;
use App\Models\Booking;
use App\Models\Attendance;
use App\Models\Equipment;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\TrainerAvailability;
use App\Models\Setting;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create Users
        User::factory(10)->create()->each(function ($user) {
            if ($user->role == 'member') {
                Member::create([
                    'user_id' => $user->id,
                    'birth_date' => fake()->date(),
                    'gender' => fake()->randomElement(['male', 'female']),
                    'address' => fake()->address(),
                    'phone' => fake()->phoneNumber(),
                    'emergency_contact' => fake()->phoneNumber(),
                    'health_conditions' => fake()->sentence(),
                ]);
            } elseif ($user->role == 'trainer') {
                Trainer::create([
                    'user_id' => $user->id,
                    'specialization' => fake()->jobTitle(),
                    'bio' => fake()->paragraph(),
                    'experience_years' => fake()->numberBetween(1, 20),
                    'certifications' => json_encode([fake()->word(), fake()->word()]),
                    'phone' => fake()->phoneNumber(),
                ]);
            } else {
                Admin::create([
                    'user_id' => $user->id,
                    'position' => fake()->jobTitle(),
                    'department' => fake()->company(),
                    'phone' => fake()->phoneNumber(),
                ]);
            }
        });

        MembershipPlan::factory(5)->create();
        Membership::factory(5)->create();
        Activity::factory(10)->create();
        ActivitySchedule::factory(10)->create();
        Booking::factory(10)->create();
        Attendance::factory(10)->create();
        Equipment::factory(10)->create();
        Notification::factory(10)->create();
        Payment::factory(10)->create();
        TrainerAvailability::factory(5)->create();
        Setting::factory(5)->create();
    }
}
