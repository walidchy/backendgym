<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use App\Models\Membership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MembershipController extends Controller
{
    public function index()
    {
        $plans = MembershipPlan::all();
        return response()->json($plans);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'features' => 'required|array',
            'is_active' => 'sometimes|boolean',
        ]);
        
        $plan = MembershipPlan::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'duration_days' => $request->duration_days,
            'features' => $request->features,
            'is_active' => $request->is_active ?? true,
        ]);
        
        return response()->json($plan, 201);
    }
    
    public function show($id)
    {
        $plan = MembershipPlan::findOrFail($id);
        return response()->json($plan);
    }
    
    public function update(Request $request, $id)
    {
        $plan = MembershipPlan::findOrFail($id);
        
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'duration_days' => 'sometimes|integer|min:1',
            'features' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
        ]);
        
        $plan->update($request->only([
            'name', 'description', 'price', 'duration_days', 'features', 'is_active'
        ]));
        
        return response()->json($plan);
    }
    
    public function destroy($id)
    {
        $plan = MembershipPlan::findOrFail($id);
        
        // Check if any users have this membership
        if ($plan->Memberships()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete plan with active memberships',
            ], 400);
        }
        
        $plan->delete();
        
        return response()->json(null, 204);
    }
    
    public function subscribe(Request $request)
    {
        $request->validate([
            'membership_plan_id' => 'required|exists:membership_plans,id',
            'payment_method' => 'required|string',
        ]);
        
        $user = Auth::user();
        $plan = MembershipPlan::findOrFail($request->membership_plan_id);
        
        // Deactivate any existing active membership
        Membership::where('user_id', $user->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);
        
        // Create new membership
        $startDate = now();
        $endDate = now()->addDays($plan->duration_days);
        
        $membership = Membership::create([
            'user_id' => $user->id,
            'membership_plan_id' => $plan->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => true,
        ]);
        
        // Record payment
        $payment = $user->payments()->create([
            'membership_plan_id' => $plan->id,
            'amount' => $plan->price,
            'payment_method' => $request->payment_method,
            'status' => 'completed',
            'payment_date' => now(),
        ]);
        
        return response()->json([
            'membership' => $membership,
            'payment' => $payment,
            'message' => 'Successfully subscribed to ' . $plan->name,
        ]);
    }
    
    public function getMembership()
    {
        $user = Auth::user();
        $activeMembership = $user->memberships()
            ->with('membershipPlan')
            ->where('is_active', true)
            ->where('end_date', '>=', now())
            ->latest()
            ->first();
        
        if (!$activeMembership) {
            return response()->json([
                'message' => 'No active membership',
                'active' => false,
            ]);
        }
        
        return response()->json([
            'membership' => $activeMembership,
            'active' => true,
            'expires_in_days' => now()->diffInDays($activeMembership->end_date),
        ]);
    }
}
