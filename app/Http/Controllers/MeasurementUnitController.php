<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MeasurementUnit;
use Illuminate\Support\Facades\Validator;

class MeasurementUnitController extends Controller
{
    // ✅ Get All Measurement Units
    public function index()
    {
        $units = MeasurementUnit::all();
        return response()->json($units, 200);
    }

    // ✅ Create a New Measurement Unit
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:measurement_units',
            // 'description' => 'nullable|string',
            // 'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $exists = MeasurementUnit::where('name', $request->name)->exists();
        if ($exists) {
            return response()->json(['error' => 'MeasurementUnit with this name already exists.'], 409);
        }

        $unit = MeasurementUnit::create($request->all());

        return response()->json(['message' => 'Measurement unit created successfully', 'unit' => $unit], 201);
    }

    // ✅ Get Single Measurement Unit
    public function show($id)
    {
        $unit = MeasurementUnit::find($id);
        if (!$unit) {
            return response()->json(['error' => 'Measurement unit not found'], 404);
        }

        return response()->json($unit, 200);
    }

    // ✅ Update Measurement Unit
    public function update(Request $request, $id)
    {
        $unit = MeasurementUnit::find($id);
        if (!$unit) {
            return response()->json(['error' => 'Measurement unit not found'], 404);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:measurement_units,name,' . $id,
            // 'description' => 'nullable|string',
            // 'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
         $exists = MeasurementUnit::where('name', $request->name)->exists();
        if ($exists) {
            return response()->json(['error' => 'MeasurementUnit with this name already exists.'], 409);
        }
        $unit->update($request->all());

        return response()->json(['message' => 'Measurement unit updated successfully', 'unit' => $unit], 200);
    }

    // ✅ Delete Measurement Unit
    public function destroy($id)
    {
        $unit = MeasurementUnit::find($id);
        if (!$unit) {
            return response()->json(['error' => 'Measurement unit not found'], 404);
        }

        $unit->delete();

        return response()->json(['message' => 'Measurement unit deleted successfully'], 200);
    }
}
