<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Workstation;
use Illuminate\Http\Request;

class WorkstationController extends Controller
{
    //
    // public function index()
    // {
    //     return response()->json(Workstation::all());
    // }
    //   public function index(Request $request)
    // {
    //     $totalcount = Workstation::count();
    //     $query = Workstation::query();
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
    //     $workstation = $query->paginate($perPage);

    //     return response()->json(['total_count' =>$totalcount,'workstation' => $workstation], 200);
    
    // }

     public function index(Request $request)
    {
        // Default values
        
        $sortBy = $request->get('sort_by', 'id'); // default column
        $sortOrder = $request->get('sort_order', 'desc'); // default order
        $limit = $request->get('per_page', null); // default null = all records
        $search = $request->get('search', null);

            $totalcount = Workstation::count();
        $query = Workstation::query();

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
            $workstation = $query->paginate($limit);
            return response()->json(['total' =>$totalcount, 'workstation'=>$workstation], 200);
            
        } else {
            // Default get all data
            $workstation = $query->orderBy('id','desc')->get();
            return response()->json($workstation, 200);
        }

        
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $workstation = Workstation::create($validated);

        return response()->json($workstation, 200);
    }

   
    public function show($id)
    {
        $department = Workstation::find($id);
        if (!$department) {
            return response()->json(['message' => 'Not Found'], 404);
        }
        return response()->json($department);
    }

    public function update(Request $request, $id)
    {
        $workstation = Workstation::find($id);
        if (!$workstation) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        $validated = $request->validate([
            'department_id' => 'sometimes|exists:departments,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string'
        ]);

        $workstation->update($validated);

        // $department->update($request->all());

        return response()->json($workstation);
    }

    
    public function destroy($id)
    {
        $workstation = Workstation::find($id);
        if (!$workstation) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        $workstation->delete();

        return response()->json(['message' => 'Deleted Successfully']);
    }


   
}

