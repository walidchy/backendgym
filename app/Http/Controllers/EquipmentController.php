<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the equipment.
     */
    public function index(Request $request)
    {
        $query = Equipment::query();

        // Search filter
        if ($request->has('search')) {
            $query->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('description', 'like', '%'.$request->search.'%');
        }

        // Category filter
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Pagination
        $perPage = $request->per_page ?? 10;
        $equipment = $query->paginate($perPage);

        return response()->json([
            'data' => $equipment->items(),
            'current_page' => $equipment->currentPage(),
            'total' => $equipment->total(),
            'per_page' => $equipment->perPage(),
            'last_page' => $equipment->lastPage(),
        ]);
    }

    /**
     * Store a newly created equipment in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'purchase_date' => 'required|date',
            'maintenance_date' => 'nullable|date',
            'status' => 'required|in:available,in_use,maintenance,retired',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $equipment = Equipment::create($validator->validated());

        return response()->json([
            'status' => 'success',
            'data' => $equipment,
            'message' => 'Equipment created successfully'
        ], 201);
    }

    /**
     * Display the specified equipment.
     */
    public function show(Equipment $equipment)
    {
        return response()->json([
            'status' => 'success',
            'data' => $equipment
        ]);
    }

    /**
     * Update the specified equipment in storage.
     */
    public function update(Request $request, Equipment $equipment)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'category' => 'sometimes|string|max:255',
            'quantity' => 'sometimes|integer|min:1',
            'purchase_date' => 'sometimes|date',
            'maintenance_date' => 'nullable|date',
            'status' => 'sometimes|in:available,in_use,maintenance,retired',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $equipment->update($validator->validated());

        return response()->json([
            'status' => 'success',
            'data' => $equipment,
            'message' => 'Equipment updated successfully'
        ]);
    }

    /**
     * Remove the specified equipment from storage.
     */
    public function destroy(Equipment $equipment)
    {
        $equipment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Equipment deleted successfully'
        ]);
    }

    /**
     * Get equipment statistics
     */
    public function statistics()
    {
        $totalEquipment = Equipment::count();
        $available = Equipment::where('status', 'available')->count();
        $inUse = Equipment::where('status', 'in_use')->count();
        $maintenance = Equipment::where('status', 'maintenance')->count();
        $retired = Equipment::where('status', 'retired')->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_equipment' => $totalEquipment,
                'available' => $available,
                'in_use' => $inUse,
                'maintenance' => $maintenance,
                'retired' => $retired,
            ]
        ]);
    }
}