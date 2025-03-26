<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Manufacturer;
use Illuminate\Support\Facades\Validator;

class ManufacturerController extends Controller
{
    // ✅ Get All Manufacturers
    public function index()
    {
        $manufacturers = Manufacturer::all();
        return response()->json($manufacturers, 200);
    }

    // ✅ Create a New Manufacturer
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

        $manufacturer = Manufacturer::create($request->all());

        return response()->json(['message' => 'Manufacturer created successfully', 'manufacturer' => $manufacturer], 201);
    }

    // ✅ Get Single Manufacturer
    public function show($id)
    {
        $manufacturer = Manufacturer::find($id);
        if (!$manufacturer) {
            return response()->json(['error' => 'Manufacturer not found'], 404);
        }

        return response()->json($manufacturer, 200);
    }

    // ✅ Update Manufacturer
    public function update(Request $request, $id)
    {
        $manufacturer = Manufacturer::find($id);
        if (!$manufacturer) {
            return response()->json(['error' => 'Manufacturer not found'], 404);
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

        $manufacturer->update($request->all());

        return response()->json(['message' => 'Manufacturer updated successfully', 'manufacturer' => $manufacturer], 200);
    }

    // ✅ Delete Manufacturer
    public function destroy($id)
    {
        $manufacturer = Manufacturer::find($id);
        if (!$manufacturer) {
            return response()->json(['error' => 'Manufacturer not found'], 404);
        }

        $manufacturer->delete();

        return response()->json(['message' => 'Manufacturer deleted successfully'], 200);
    }
}
