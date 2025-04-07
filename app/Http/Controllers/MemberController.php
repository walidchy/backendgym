<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Member;
use App\Models\Membership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class MemberController extends Controller
{
    // Get all members with filters
    public function index(Request $request)
{
    try {
        $members = User::where('role', 'member')
        ->whereHas('memberships')
            ->with(['memberships' => function($query) {
                $query->latest()->first();
            }])
            ->paginate(10);

        return response()->json([
            'data' => $members,
            'message' => 'Members retrieved successfully'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to load members',
            'error' => $e->getMessage()
        ], 500);
    }
}

    // Create new member
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'birth_date' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
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
                'role' => 'member',
                'is_verified' => true
            ]);

            Member::create([
                'user_id' => $user->id,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'phone' => $request->phone,
                'address' => $request->address
            ]);

            return response()->json([
                'data' => $user->load('member'),
                'message' => 'Member created successfully'
            ], 201);
        });
    }

    // Get single member
    public function show(User $member)
    {
        if ($member->role !== 'member') {
            return response()->json(['message' => 'Not a member'], 404);
        }

        return response()->json([
            'data' => $member->load(['member', 'memberships', 'bookings']),
            'message' => 'Member retrieved successfully'
        ]);
    }

    // Update member
    public function update(Request $request, User $member)
    {
        if ($member->role !== 'member') {
            return response()->json(['message' => 'Not a member'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($member->id)],
            'birth_date' => 'sometimes|date',
            'gender' => 'sometimes|in:male,female,other',
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $member->update($request->only('name', 'email'));
        
        if ($member->member) {
            $member->member->update($request->only('birth_date', 'gender', 'phone', 'address'));
        }

        return response()->json([
            'data' => $member->fresh()->load('member'),
            'message' => 'Member updated successfully'
        ]);
    }

    // Delete member
    public function destroy(User $member)
    {
        if ($member->role !== 'member') {
            return response()->json(['message' => 'Not a member'], 404);
        }

        DB::transaction(function () use ($member) {
            $member->member()->delete();
            $member->delete();
        });

        return response()->json(['message' => 'Member deleted successfully']);
    }
}