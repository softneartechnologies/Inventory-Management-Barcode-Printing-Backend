<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    // ✅ Get All Categories
    // public function index()
    // {
    //     $categories = Category::orderBy('id', 'desc')->get();
    //     return response()->json($categories, 200);
    // }

    // public function index(Request $request)
    // {
    //     // Default values
    //     $total_count = Category::count();
    //     $sortBy = $request->get('sort_by', 'id'); // default column
    //     $sortOrder = $request->get('sort_order', 'desc'); // default order
    //     $limit = $request->get('per_page', null); // default null = all records
    //     $search = $request->get('search', null);

    //     $query = Category::query();

    //     // Searching
    //     if (!empty($search)) {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('name', 'like', "%{$search}%")
    //             ->orWhere('description', 'like', "%{$search}%");
    //         });
    //     }

    //     // Sorting
    //     $query->orderBy($sortBy, $sortOrder);

    //     // If limit is given, apply pagination
    //     if (!empty($limit) && is_numeric($limit)) {
    //         if(!empty($search)){
    //             print_r($search);die;
    //             $categories = $query->paginate($limit);
    //         return response()->json(['total' =>$query->count(), 'categories'=>$categories], 200);
            
    //         }else{
    //         $categories = $query->paginate($limit);
    //         return response()->json(['total' =>$total_count, 'categories'=>$categories], 200);
            
    //         }
            
    //     } else {
    //         // Default get all data
    //         $categories = $query->get();
    //         return response()->json($categories, 200);
    //     }

    //     // return response()->json($categories, 200);
    // }

    public function index(Request $request)
    {
        // Default values
        $total_count = Category::count();
        $sortBy = $request->get('sort_by', 'id'); // default column
        $sortOrder = $request->get('sort_order', 'desc'); // default order
        $limit = $request->get('per_page', null); // default null = all records
        $search = $request->get('search', null);

        $query = Category::query();

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
                $total_count = Category::count();
            $categories = $query->paginate($limit);
            return response()->json(['total' =>$total_count, 'categories'=>$categories], 200);
            
            }
            
        } else {
            // Default get all data
            $categories = $query->get();
            return response()->json($categories, 200);
        }

        // return response()->json($categories, 200);
    }

    // ✅ Create a New Category
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $exists = Category::where('name', $request->name)->exists();
        if ($exists) {
            return response()->json(['error' => 'Category with this name already exists.'], 409);
        }else{

    
        $category = Category::create($request->all());

        return response()->json(['message' => 'Category created successfully', 'category' => $category], 201);
        }
    }

    // ✅ Get Single Category
    public function show($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        return response()->json($category, 200);
    }

    // ✅ Update Category
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $exists = Category::where('name', $request->name)->exists();
        if ($exists) {
            return response()->json(['error' => 'Category with this name already exists.'], 409);
        }

        $category->update($request->all());

        return response()->json(['message' => 'Category updated successfully', 'category' => $category], 200);
    }

    // ✅ Delete Category
    // public function destroy($id)
    // {
    //     $category = Category::find($id);
    //     if (!$category) {
    //         return response()->json(['error' => 'Category not found'], 404);
    //     }


    //     $category->delete();

    //     $subcategory = Subcategory::where('category_id ',$id);
    //     if (!$subcategory) {
    //         return response()->json(['error' => 'Subcategory not found'], 404);
    //     }

    //     $subcategory->delete();


    //     return response()->json(['message' => 'Category deleted successfully'], 200);
    // }
    public function destroy($id)
{
    $category = Category::find($id);

    if (!$category) {
        return response()->json(['error' => 'Category not found'], 404);
    }

    // Delete all related subcategories first
    $deletedSubcategories = Subcategory::where('category_id', $id)->delete();

    // Then delete the category
    $category->delete();

    return response()->json([
        'message' => 'Category and related subcategories deleted successfully',
        'deleted_subcategories' => $deletedSubcategories
    ], 200);
}

}
