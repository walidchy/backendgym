<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
  
    
    public function index(Request $request)
    {
        $role = $request->query('role');
        $verified = $request->query('verified');
        
        $query = User::query();
        
        if ($role) {
            $query->where('role', $role);
        }
        
        if ($verified !== null) {
            $query->where('is_verified', $verified === 'true');
        }
        
        $users = $query->get();
        
        return response()->json($users);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:member,trainer,admin',
            'is_verified' => 'boolean',
        ]);
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_verified' => $request->is_verified ?? false,
        ]);
        
        // Create appropriate profile based on role
        if ($user->role === 'member') {
            $user->member()->create();
        } elseif ($user->role === 'trainer') {
            $user->trainer()->create();
        } elseif ($user->role === 'admin') {
            $user->admin()->create();
        }
        
        return response()->json($user, 201);
    }
    
    public function show($id)
    {
        $user = User::findOrFail($id);
        
        // Load appropriate profile based on role
        if ($user->isMember()) {
            $user->load('member');
        } elseif ($user->isTrainer()) {
            $user->load('trainer');
        } elseif ($user->isAdmin()) {
            $user->load('admin');
        }
        
        return response()->json($user);
    }
    
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8',
            'role' => 'sometimes|string|in:member,trainer,admin',
            'is_verified' => 'sometimes|boolean',
        ]);
        
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        
        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }
        
        if ($request->has('role') && $request->role !== $user->role) {
            $user->role = $request->role;
            
            // Handle role change and create appropriate profile
            if ($user->role === 'member' && !$user->member) {
                $user->member()->create();
            } elseif ($user->role === 'trainer' && !$user->trainer) {
                $user->trainer()->create();
            } elseif ($user->role === 'admin' && !$user->admin) {
                $user->admin()->create();
            }
        }
        
        if ($request->has('is_verified')) {
            $user->is_verified = $request->is_verified;
        }
        
        $user->save();
        
        return response()->json($user);
    }
    
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        
        return response()->json(null, 204);
    }
}
