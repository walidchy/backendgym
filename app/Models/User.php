<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_verified',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_verified' => 'boolean',
    ];
    public function member()
    {
        return $this->hasOne(Member::class);
    }
    
    public function trainer()
    {
        return $this->hasOne(Trainer::class);
    }
    public function admin()
    {
        return $this->hasOne(Admin::class);
    }
    public function memberships()
    {
        return $this->hasMany(Membership::class, 'user_id'); // assuming 'user_id' is the foreign key
    }
    
    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'activity_user');
    }
  
    
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
    
    public function trainedActivities()
    {
        return $this->hasMany(Activity::class, 'trainer_id');
    }
    
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    
    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }
    
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
    
    public function availability()
    {
        return $this->hasMany(TrainerAvailability::class);
    }
    
    
    public function activeMembership()
    {
        return $this->memberships()
            ->where('is_active', true)
            ->where('end_date', '>=', now())
            ->latest()
            ->first();
    }
    
    public function isMember()
    {
        return $this->role === 'member';
    }
    
    public function isTrainer()
    {
        return $this->role === 'trainer';
    }
    
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
}
