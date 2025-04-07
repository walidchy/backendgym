<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivitySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_recurring',
        'specific_date',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_recurring' => 'boolean',
        'specific_date' => 'date',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
