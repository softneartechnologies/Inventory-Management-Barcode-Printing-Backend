<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reason;
use Illuminate\Support\Facades\Validator;

class ReasonController extends Controller
{
    public function index()
    {
        $categories = Reason::all();
        return response()->json($categories, 200);
    }

    // ✅ Create a New Category
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $reason = Reason::create($request->all());

        return response()->json(['message' => 'Reason created successfully', 'reason' => $reason], 201);
    }

    // ✅ Get Single Category
    public function show($id)
    {
        $reason = Reason::find($id);
        if (!$reason) {
            return response()->json(['error' => 'Reason not found'], 404);
        }

        return response()->json($reason, 200);
    }

    // ✅ Update Category
    public function update(Request $request, $id)
    {
        $reason = Reason::find($id);
        if (!$reason) {
            return response()->json(['error' => 'Reason not found'], 404);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $reason->update($request->all());

        return response()->json(['message' => 'Reason updated successfully', 'reason' => $reason], 200);
    }

    // ✅ Delete Category
    public function destroy($id)
    {
        $reason = Reason::find($id);
        if (!$reason) {
            return response()->json(['error' => 'Reason not found'], 404);
        }

        $reason->delete();

        return response()->json(['message' => 'Reason deleted successfully'], 200);
    }
}
