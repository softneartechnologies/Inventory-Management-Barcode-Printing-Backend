<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subcategory;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;

class SubcategoryController extends Controller
{
    // ✅ Get All Subcategories
    // public function index()
    // {
    //     $subcategories = Subcategory::with('category')->orderBy('id', 'desc')->get();
    //     return response()->json($subcategories, 200);
    // }

        public function index(Request $request)
{
    // Default values
    // $total_count = Subcategory::with('category')->count();
    $sortBy = $request->get('sort_by', 'id'); // default column
    $sortOrder = $request->get('sort_order', 'desc'); // default order
    $limit = $request->get('per_page', null); // default null = all records
    $search = $request->get('search', null);

    $query = Subcategory::with('category');


    // Searching
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // Sorting
    $query->orderBy($sortBy, $sortOrder);

    // If limit is given, apply pagination
    if (!empty($limit) && is_numeric($limit)) {
            if(!empty($search)){
                $total_count = $query->count();
                $categories = $query->paginate($limit);
            return response()->json(['total' =>$total_count, 'categories'=>$categories], 200);
            
            }else{
                $total_count = Subcategory::with('category')->count();;
            $categories = $query->paginate($limit);
            return response()->json(['total' =>$total_count, 'categories'=>$categories], 200);
            
            }
            
        } else {
        // Default get all data
        $categories = $query->get();
        return response()->json($categories, 200);
    }

    // return response()->json(['total_count'=> $total_count,'categories'=>$categories], 200);
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
