<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WeightUnit;
use Illuminate\Support\Facades\Validator;

class WeightUnitController extends Controller
{
    // ✅ Get All Weight Units
    public function index()
    {
        $weightUnits = WeightUnit::all();
        return response()->json($weightUnits, 200);
    }

    // ✅ Create a New Weight Unit
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

        $weightUnit = WeightUnit::create($request->all());

        return response()->json(['message' => 'Weight Unit created successfully', 'weightUnit' => $weightUnit], 201);
    }

    // ✅ Get Single Weight Unit
    public function show($id)
    {
        $weightUnit = WeightUnit::find($id);
        if (!$weightUnit) {
            return response()->json(['error' => 'Weight Unit not found'], 404);
        }

        return response()->json($weightUnit, 200);
    }

    // ✅ Update Weight Unit
    public function update(Request $request, $id)
    {
        $weightUnit = WeightUnit::find($id);
        if (!$weightUnit) {
            return response()->json(['error' => 'Weight Unit not found'], 404);
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

        $weightUnit->update($request->all());

        return response()->json(['message' => 'Weight Unit updated successfully', 'weightUnit' => $weightUnit], 200);
    }

    // ✅ Delete Weight Unit
    public function destroy($id)
    {
        $weightUnit = WeightUnit::find($id);
        if (!$weightUnit) {
            return response()->json(['error' => 'Weight Unit not found'], 404);
        }

        $weightUnit->delete();

        return response()->json(['message' => 'Weight Unit deleted successfully'], 200);
    }
}
