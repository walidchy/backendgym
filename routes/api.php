<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TrainerController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProfileController;

// Public routes
Route::delete('/membership-plans/{plan}', [MembershipController::class, 'destroy']);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/members', [MemberController::class, 'index']);
Route::get('/activities', [ActivityController::class, 'index']);
Route::get('/users', [UserController::class, 'index']);
Route::get('/trainers', [TrainerController::class, 'index']);
Route::get('/equipment', [EquipmentController::class, 'index']);
Route::get('/membership-plans', [MembershipController::class, 'index']);
Route::get('memberships', [MembershipController::class, 'index']);
Route::get('/settings', [SettingController::class, 'index']);
Route::delete('/equipment/{equipment}', [EquipmentController::class, 'destroy']);
Route::delete('/activities/{activity}', [ActivityController::class, 'destroy']);
Route::get('/my-membership', [MembershipController::class, 'getMembership']);



 Route::get('/admin/verifications', [AuthController::class, 'getPendingVerifications']);
    Route::put('/admin/users/{id}/verify', [AuthController::class, 'updateVerificationStatus']);

// Protected routes - requires authentication
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/user', function (Request $request) {
        return response()->json([
            'data' => $request->user()->load(['member', 'trainer']),
            'message' => 'User retrieved successfully'
        ]);
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    // members

    Route::post('/members', [MemberController::class, 'store']);
    Route::get('/members/{member}', [MemberController::class, 'show']);
    Route::put('/members/{member}', [MemberController::class, 'update']);
    Route::delete('/members/{member}', [MemberController::class, 'destroy']);
    // profile

    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    // Trainers Routes
    Route::post('/trainers', [TrainerController::class, 'store']);
    Route::get('/trainers/{trainer}', [TrainerController::class, 'show']);
    Route::put('/trainers/{trainer}', [TrainerController::class, 'update']);
    Route::delete('/trainers/{trainer}', [TrainerController::class, 'destroy']);
    // Activities

    Route::get('/activities/{activity}', [ActivityController::class, 'show']);
    Route::post('/activities/{activity}/schedules', [ActivityController::class, 'addSchedule']);
    Route::put('/activities/{activity}/schedules/{schedule}', [ActivityController::class, 'updateSchedule']);
    Route::delete('/activities/{activity}/schedules/{schedule}', [ActivityController::class, 'removeSchedule']);
    
    // Bookings
    Route::get('/member/bookings ', [BookingController::class, 'index']);

    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
    Route::patch('/bookings/{booking}/complete', [BookingController::class, 'complete']);
    
    // Memberships

    Route::get('/membership-plans/{plan}', [MembershipController::class, 'show']);
    
    // Attendance
    Route::post('/bookings/{booking}/attendance', [AttendanceController::class, 'store']);
    Route::patch('/bookings/{booking}/attendance', [AttendanceController::class, 'update']);
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    
    // Equipment (For all authenticated users to view)
    
    // Profile updates
    Route::put('/profile', [UserController::class, 'updateProfile']);
    
    // Trainer routes
    Route::middleware('role:trainer,admin')->group(function () {
        // Trainer availability
        Route::get('/trainer/availability', [UserController::class, 'getAvailability']);
        Route::post('/trainer/availability', [UserController::class, 'updateAvailability']);
        
        // Manage activities (create, update, delete)
        Route::post('/activities', [ActivityController::class, 'store']);
        Route::put('/activities/{activity}', [ActivityController::class, 'update']);
    });
    
    // Admin routes
    Route::middleware('role:admin')->group(function () {
        // User management
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
        Route::patch('/users/{user}/verify', [AuthController::class, 'verifyUser']);
        
        // Membership plans management
        Route::post('/membership-plans', [MembershipController::class, 'store']);
        Route::put('/membership-plans/{plan}', [MembershipController::class, 'update']);
        
        // Equipment management
        Route::post('/equipment', [EquipmentController::class, 'store']);
        Route::put('/equipment/{equipment}', [EquipmentController::class, 'update']);
        
        // Settings
        Route::put('/settings/{key}', [SettingController::class, 'update']);
        
        // Payment management
        Route::get('/payments', [PaymentController::class, 'index']);
        Route::get('/payments/{payment}', [PaymentController::class, 'show']);
    });
});

// Custom role middleware needs to be registered in app/Http/Kernel.php
// protected $routeMiddleware = [
//     // ... other middleware
//     'role' => \App\Http\Middleware\CheckRole::class,
// ];
