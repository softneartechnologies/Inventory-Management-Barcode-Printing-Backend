<?php



// app/Http/Controllers/Api/DepartmentController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    // public function index()
    // {
    //     return response()->json(Department::all(), 200);
    // }

    //  public function index(Request $request)
    // {
    //     $totalcount = Department::count();
    //     $query = Department::query();
    //     if ($request->has('search') && !empty($request->search)) {
    //         $search = $request->search;
    //         $query->where(function ($q) use ($search) {
    //             $q->where('name', 'like', "%$search%")
    //             ->orWhere('description', 'like', "%$search%");
    //             // Add more searchable fields if needed
    //         });
    //     }

    //     // ✅ Sorting functionality
    //     $sortBy = $request->get('sort_by', 'id'); // Default to 'id'
    //     $sortOrder = $request->get('sort_order', 'desc'); // Default to 'desc'
    //     $query->orderBy($sortBy, $sortOrder);

    //     // ✅ Pagination
    //     $perPage = $request->get('per_page', 10); // default 10 items per page
    //     $department = $query->paginate($perPage);

    //     return response()->json(['total_count' =>$totalcount,'department' => $department], 200);
    
    // }

        public function index(Request $request)
        {
            // Default values
           
            $sortBy = $request->get('sort_by', 'id'); // default column
            $sortOrder = $request->get('sort_order', 'desc'); // default order
            $limit = $request->get('per_page', null); // default null = all records
            $search = $request->get('search', null);

             $totalcount = Department::count();
            $query = Department::query();

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
                $department = $query->paginate($limit);
                return response()->json(['total' =>$totalcount, 'department'=>$department], 200);
                
            } else {
                // Default get all data
                $department = $query->orderBy('id','desc')->get();
                return response()->json($department, 200);
            }

            
        }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $department = Department::create($request->all());

        return response()->json($department, 200);
    }

    public function show($id)
    {
        $department = Department::find($id);
        if (!$department) {
            return response()->json(['message' => 'Not Found'], 404);
        }
        return response()->json($department);
    }

    public function update(Request $request, $id)
    {
        $department = Department::find($id);
        if (!$department) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $department->update($request->all());

        return response()->json($department);
    }

    public function destroy($id)
    {
        $department = Department::find($id);
        if (!$department) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        $department->delete();

        return response()->json(['message' => 'Deleted Successfully']);
    }
}
