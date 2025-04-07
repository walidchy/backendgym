<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivitySchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    
    public function index(Request $request)
{
    $query = Activity::with('trainer');

    if ($request->has('category')) {
        $query->where('category', $request->category);
    }

    if ($request->has('difficulty_level')) {
        $query->where('difficulty_level', $request->difficulty_level);
    }

    return response()->json([
        'data' => $query->get(),
        'message' => 'Activities retrieved successfully'
    ]);
}
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'trainer_id' => 'sometimes|nullable|exists:users,id',
            'category' => 'required|string',
            'difficulty_level' => 'required|string|in:beginner,intermediate,advanced',
            'duration_minutes' => 'required|integer|min:5',
            'max_participants' => 'required|integer|min:1',
            'location' => 'required|string',
            'equipment_needed' => 'sometimes|array',
            'schedules' => 'sometimes|array',
            'schedules.*.day_of_week' => 'required_with:schedules|string',
            'schedules.*.start_time' => 'required_with:schedules|date_format:H:i',
            'schedules.*.end_time' => 'required_with:schedules|date_format:H:i|after:schedules.*.start_time',
            'schedules.*.is_recurring' => 'sometimes|boolean',
            'schedules.*.specific_date' => 'required_if:schedules.*.is_recurring,false|nullable|date',
        ]);
        
        $activity = Activity::create([
            'name' => $request->name,
            'description' => $request->description,
            'trainer_id' => $request->trainer_id,
            'category' => $request->category,
            'difficulty_level' => $request->difficulty_level,
            'duration_minutes' => $request->duration_minutes,
            'max_participants' => $request->max_participants,
            'location' => $request->location,
            'equipment_needed' => $request->equipment_needed,
        ]);
        
        // Create schedules if provided
        if ($request->has('schedules')) {
            foreach ($request->schedules as $scheduleData) {
                $activity->schedules()->create([
                    'day_of_week' => $scheduleData['day_of_week'],
                    'start_time' => $scheduleData['start_time'],
                    'end_time' => $scheduleData['end_time'],
                    'is_recurring' => $scheduleData['is_recurring'] ?? true,
                    'specific_date' => $scheduleData['specific_date'] ?? null,
                ]);
            }
        }
        
        return response()->json($activity->load('schedules'), 201);
    }
    
    public function show($id)
    {
        $activity = Activity::with(['schedules', 'trainer'])->findOrFail($id);
        return response()->json($activity);
    }
    
    public function update(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);
        
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'trainer_id' => 'sometimes|nullable|exists:users,id',
            'category' => 'sometimes|string',
            'difficulty_level' => 'sometimes|string|in:beginner,intermediate,advanced',
            'duration_minutes' => 'sometimes|integer|min:5',
            'max_participants' => 'sometimes|integer|min:1',
            'location' => 'sometimes|string',
            'equipment_needed' => 'sometimes|array',
        ]);
        
        $activity->update($request->only([
            'name', 'description', 'trainer_id', 'category',
            'difficulty_level', 'duration_minutes', 'max_participants',
            'location', 'equipment_needed',
        ]));
        
        return response()->json($activity);
    }
    
    public function destroy($id)
    {
        $activity = Activity::findOrFail($id);
        
        // Check if any bookings exist for this activity
        if ($activity->bookings()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete activity with existing bookings',
            ], 400);
        }
        
        $activity->schedules()->delete();
        $activity->delete();
        
        return response()->json(null, 204);
    }
    
    public function addSchedule(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);
        
        $request->validate([
            'day_of_week' => 'required|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_recurring' => 'sometimes|boolean',
            'specific_date' => 'required_if:is_recurring,false|nullable|date',
        ]);
        
        $schedule = $activity->schedules()->create([
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'is_recurring' => $request->is_recurring ?? true,
            'specific_date' => $request->specific_date ?? null,
        ]);
        
        return response()->json($schedule, 201);
    }
    
    public function updateSchedule(Request $request, $id, $scheduleId)
    {
        $activity = Activity::findOrFail($id);
        $schedule = ActivitySchedule::where('activity_id', $id)
            ->where('id', $scheduleId)
            ->firstOrFail();
        
        $request->validate([
            'day_of_week' => 'sometimes|string',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
            'is_recurring' => 'sometimes|boolean',
            'specific_date' => 'required_if:is_recurring,false|nullable|date',
        ]);
        
        $schedule->update($request->only([
            'day_of_week', 'start_time', 'end_time', 'is_recurring', 'specific_date'
        ]));
        
        return response()->json($schedule);
    }
    
    public function removeSchedule($id, $scheduleId)
    {
        $activity = Activity::findOrFail($id);
        $schedule = ActivitySchedule::where('activity_id', $id)
            ->where('id', $scheduleId)
            ->firstOrFail();
        
        // Check if bookings exist for this schedule
        if ($schedule->bookings()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete schedule with existing bookings',
            ], 400);
        }
        
        $schedule->delete();
        
        return response()->json(null, 204);
    }
}
