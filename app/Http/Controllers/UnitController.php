<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unit;
use Illuminate\Support\Facades\Validator;

class UnitController extends Controller
{
    // ✅ Get All Units
    public function index()
    {
        $units = Unit::all();
        return response()->json($units, 200);
    }

    // ✅ Create a New Unit
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
         $exists = Unit::where('name', $request->name)->exists();
        if ($exists) {
            return response()->json(['error' => 'Unit with this name already exists.'], 409);
        }

        $unit = Unit::create($request->all());

        return response()->json(['message' => 'Unit created successfully', 'unit' => $unit], 201);
    }

    // ✅ Get Single Unit
    public function show($id)
    {
        $unit = Unit::find($id);
        if (!$unit) {
            return response()->json(['error' => 'Unit not found'], 404);
        }

        return response()->json($unit, 200);
    }

    // ✅ Update Unit
    public function update(Request $request, $id)
    {
        $unit = Unit::find($id);
        if (!$unit) {
            return response()->json(['error' => 'Unit not found'], 404);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
         $exists = Unit::where('name', $request->name)->exists();
        if ($exists) {
            return response()->json(['error' => 'Unit with this name already exists.'], 409);
        }
        $unit->update($request->all());

        return response()->json(['message' => 'Unit updated successfully', 'unit' => $unit], 200);
    }

    // ✅ Delete Unit
    public function destroy($id)
    {
        $unit = Unit::find($id);
        if (!$unit) {
            return response()->json(['error' => 'Unit not found'], 404);
        }

        $unit->delete();

        return response()->json(['message' => 'Unit deleted successfully'], 200);
    }
}
