<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subcategory;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;

class SubcategoryController extends Controller
{
    // ✅ Get All Subcategories
    public function index()
    {
        $subcategories = Subcategory::with('category')->get();
        return response()->json($subcategories, 200);
    }

    // ✅ Create a New Subcategory
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $subcategory = Subcategory::create($request->all());

        return response()->json(['message' => 'Subcategory created successfully', 'subcategory' => $subcategory], 201);
    }

    // ✅ Get Single Subcategory
    public function show($id)
    {
        $subcategory = Subcategory::with('category')->find($id);
        if (!$subcategory) {
            return response()->json(['error' => 'Subcategory not found'], 404);
        }

        return response()->json($subcategory, 200);
    }

    // ✅ Update Subcategory
    public function update(Request $request, $id)
    {
        $subcategory = Subcategory::find($id);
        if (!$subcategory) {
            return response()->json(['error' => 'Subcategory not found'], 404);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $subcategory->update($request->all());

        return response()->json(['message' => 'Subcategory updated successfully', 'subcategory' => $subcategory], 200);
    }

    // ✅ Delete Subcategory
    public function destroy($id)
    {
        $subcategory = Subcategory::find($id);
        if (!$subcategory) {
            return response()->json(['error' => 'Subcategory not found'], 404);
        }

        $subcategory->delete();

        return response()->json(['message' => 'Subcategory deleted successfully'], 200);
    }
}
