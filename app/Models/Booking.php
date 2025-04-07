<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity_id',
        'activity_schedule_id',
        'date',
        'status',
        'cancellation_reason',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function schedule()
    {
        return $this->belongsTo(ActivitySchedule::class, 'activity_schedule_id');
    }

    public function attendance()
    {
        return $this->hasOne(Attendance::class);
    }
}
