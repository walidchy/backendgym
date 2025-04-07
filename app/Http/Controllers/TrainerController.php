<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Trainer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class TrainerController extends Controller
{
    // Get all trainers
    public function index(Request $request)
    {
        $query = User::where('role', 'trainer')
            ->with(['trainer', 'activities']);
    
        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }
    
        // Paginate trainers
        $trainers = $query->paginate(10);
    
        // Add additional fields to each trainer in the response
        $trainers->getCollection()->transform(function($trainer) {
            // Assuming 'phone' is part of the 'User' model
            $trainer->phone = $trainer->phone;
    
            // Calculate active members (this is just an example, you can customize this logic)
            $trainer->active_members = $trainer->activities->where('status', 'active')->count();
    
            return $trainer;
        });
    
        return response()->json([
            'data' => $trainers,
            'message' => 'Trainers retrieved successfully'
        ]);
    }
    

    // Create new trainer
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'specialization' => 'required|string|max:255',
            'certifications' => 'sometimes|array',
            'experience_years' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        return DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'trainer',
                'is_verified' => true
            ]);

            Trainer::create([
                'user_id' => $user->id,
                'specialization' => $request->specialization,
                'certifications' => $request->certifications,
                'experience_years' => $request->experience_years
            ]);

            return response()->json([
                'data' => $user->load('trainer'),
                'message' => 'Trainer created successfully'
            ], 201);
        });
    }

    // Get single trainer
    public function show(User $trainer)
    {
        if ($trainer->role !== 'trainer') {
            return response()->json(['message' => 'Not a trainer'], 404);
        }

        return response()->json([
            'data' => $trainer->load(['trainer', 'activities']),
            'message' => 'Trainer retrieved successfully'
        ]);
    }

    // Update trainer
    public function update(Request $request, User $trainer)
    {
        if ($trainer->role !== 'trainer') {
            return response()->json(['message' => 'Not a trainer'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($trainer->id)],
            'specialization' => 'sometimes|string|max:255',
            'certifications' => 'sometimes|array',
            'experience_years' => 'sometimes|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $trainer->update($request->only('name', 'email'));
        
        if ($trainer->trainer) {
            $trainer->trainer->update($request->only(
                'specialization', 
                'certifications', 
                'experience_years'
            ));
        }

        return response()->json([
            'data' => $trainer->fresh()->load('trainer'),
            'message' => 'Trainer updated successfully'
        ]);
    }

    // Delete trainer
    public function destroy(User $trainer)
    {
        if ($trainer->role !== 'trainer') {
            return response()->json(['message' => 'Not a trainer'], 404);
        }

        DB::transaction(function () use ($trainer) {
            $trainer->trainer()->delete();
            $trainer->activities()->detach();
            $trainer->delete();
        });

        return response()->json(['message' => 'Trainer deleted successfully']);
    }
}