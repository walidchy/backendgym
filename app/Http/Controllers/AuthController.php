<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'sometimes|string|in:member,trainer,admin',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'member',
            'is_verified' =>  false,
        ]);

        // Create appropriate profile based on role
        if ($user->role === 'member') {
            $user->member()->create();
        } elseif ($user->role === 'trainer') {
            $user->trainer()->create();
        } elseif ($user->role === 'admin') {
            $user->admin()->create();
        }

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            
            // Check if user is verified
            if (!$user->is_verified) {
                Auth::logout();
                return response()->json([
                    'message' => 'Your account is pending verification',
                ], 403);
            }
            
            $token = $user->createToken('auth_token')->plainTextToken;
            
            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token,
            ]);
        }
        
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        
        // Load appropriate profile based on role
        if ($user->isMember()) {
            $user->load('member');
        } elseif ($user->isTrainer()) {
            $user->load('traine');
        } elseif ($user->isAdmin()) {
            $user->load('admin');
        }
        
        return response()->json($user);
    }

    public function verifyUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->is_verified = true;
        $user->save();
        
        return response()->json([
            'message' => 'User verified successfully',
            'user' => $user
        ]);
    }
    // In App\Http\Controllers\AdminController.php

public function getPendingVerifications()
{
    // Only admin users should access this
    
    $pendingUsers = User::where('is_verified', false)->get();
    
    return response()->json([
        'status' => 'success',
        'data' => $pendingUsers
    ]);
}

public function updateVerificationStatus(Request $request, $id)
{
    // Only admin users should access this
    // if (!auth()->user() || auth()->user()->role !== 'admin') {
    //     return response()->json(['message' => 'Unauthorized'], 403);
    // }
    
    $request->validate([
        'is_verified' => 'required|boolean'
    ]);
    
    $user = User::findOrFail($id);
    $user->is_verified = $request->is_verified;
    $user->save();
    
    return response()->json([
        'status' => 'success',
        'data' => $user,
        'message' => $request->is_verified ? 'User has been verified' : 'User verification has been rejected'
    ]);
}

}
