<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'trainer_id',
        'category',
        'difficulty_level',
        'duration_minutes',
        'max_participants',
        'location',
        'equipment_needed',
    ];

    protected $casts = [
        'equipment_needed' => 'array',
    ];
   
        public function users()
        {
            return $this->belongsToMany(User::class, 'activity_user');
        }
    
    
    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function schedules()
    {
        return $this->hasMany(ActivitySchedule::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
