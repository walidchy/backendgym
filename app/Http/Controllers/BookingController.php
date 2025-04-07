<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $status = $request->query('status', 'all');
            
            $query = Booking::where('user_id', $user->id);
            
            if ($status !== 'all') {
                $query->where('status', $status);
            }
            
            $bookings = $query->with(['activity', 'schedule'])->get();
            
            return response()->json([
                'data' => $bookings,
                'status' => 'success'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Booking index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve bookings',
                'status' => 'error'
            ], 500);
        }
    }
    
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'activity_id' => 'required|exists:activities,id',
                'date' => 'required|date|after_or_equal:today',
                'activity_schedule_id' => 'nullable|exists:activity_schedules,id',
            ]);
            
            $activity = Activity::findOrFail($validated['activity_id']);
            
            // Check availability
            $bookingsCount = Booking::where('activity_id', $activity->id)
                ->where('date', $validated['date'])
                ->where('status', '!=', 'canceled')
                ->count();
                
            if ($bookingsCount >= $activity->max_participants) {
                throw ValidationException::withMessages([
                    'activity_id' => 'This activity is fully booked for the selected date.'
                ]);
            }
            
            $booking = Booking::create([
                'user_id' => Auth::id(),
                'activity_id' => $validated['activity_id'],
                'activity_schedule_id' => $validated['activity_schedule_id'] ?? null,
                'date' => $validated['date'],
                'status' => 'upcoming'
            ]);
            
            return response()->json([
                'data' => $booking->load(['activity', 'schedule']),
                'message' => 'Booking created successfully',
                'status' => 'success'
            ], 201);
            
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
                'status' => 'error'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Booking store error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create booking',
                'status' => 'error'
            ], 500);
        }
    }
    
    public function show($id)
    {
        try {
            $booking = Booking::with(['activity', 'schedule'])->findOrFail($id);
            $user = Auth::user();
            
            // Authorization check
            if ($booking->user_id !== $user->id && !$user->isAdmin() && 
                !($user->isTrainer() && $booking->activity->trainer_id === $user->id)) {
                return response()->json([
                    'message' => 'Unauthorized to view this booking',
                    'status' => 'error'
                ], 403);
            }
            
            return response()->json([
                'data' => $booking,
                'status' => 'success'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Booking show error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Booking not found',
                'status' => 'error'
            ], 404);
        }
    }
    
    public function cancel(Request $request, $id)
    {
        try {
            $booking = Booking::findOrFail($id);
            
            // Authorization check
            if ($booking->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Unauthorized to cancel this booking',
                    'status' => 'error'
                ], 403);
            }
            
            // Status validation
            if ($booking->status !== 'upcoming') {
                throw ValidationException::withMessages([
                    'status' => 'Only upcoming bookings can be canceled'
                ]);
            }
            
            $booking->update([
                'status' => 'canceled',
                'cancellation_reason' => $request->input('cancellation_reason')
            ]);
            
            return response()->json([
                'data' => $booking,
                'message' => 'Booking canceled successfully',
                'status' => 'success'
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
                'status' => 'error'
            ], 400);
        } catch (\Exception $e) {
            Log::error('Booking cancel error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to cancel booking',
                'status' => 'error'
            ], 500);
        }
    }
    
    public function complete($id)
    {
        try {
            $booking = Booking::with('activity')->findOrFail($id);
            $user = Auth::user();
            
            // Authorization check
            if (!$user->isAdmin() && 
                !($user->isTrainer() && $booking->activity->trainer_id === $user->id)) {
                return response()->json([
                    'message' => 'Unauthorized to complete this booking',
                    'status' => 'error'
                ], 403);
            }
            
            // Status validation
            if ($booking->status !== 'upcoming') {
                throw ValidationException::withMessages([
                    'status' => 'Only upcoming bookings can be marked as completed'
                ]);
            }
            
            $booking->update(['status' => 'completed']);
            
            return response()->json([
                'data' => $booking,
                'message' => 'Booking marked as completed',
                'status' => 'success'
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
                'status' => 'error'
            ], 400);
        } catch (\Exception $e) {
            Log::error('Booking complete error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to complete booking',
                'status' => 'error'
            ], 500);
        }
    }
}