<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Workstation;
use Illuminate\Http\Request;

class WorkstationController extends Controller
{
   

    //  public function index(Request $request)
    // {
    //     // Default values
        
    //     $sortBy = $request->get('sort_by', 'id'); // default column
    //     $sortOrder = $request->get('sort_order', 'desc'); // default order
    //     $limit = $request->get('per_page', null); // default null = all records
    //     $search = $request->get('search', null);

    //         $totalcount = Workstation::count();
    //     $query = Workstation::with('department');
        
    //     // Searching
    //     if (!empty($search)) {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('name', 'like', "%{$search}%")
    //             ->orWhere('description', 'like', "%{$search}%");
    //         });
    //     }


    //              // ✅ Filter
    //             if ($request->filled('department')) {
                    
    //                 $department  = $request->department;
                

    //                 $query->where(function ($q) use ($department) {
                    

    //                     // ✅ Department filter
    //                     if (!empty($department)) {
    //                         $q->where('department', 'like', "%{$department}%");
    //                     }

    //                         });
    //             }

    //     // Sorting
    //     $query->orderBy($sortBy, $sortOrder);
    //     $query->department_mame = $query->department['name'];

    //     // If limit is given, apply pagination
    //     if (!empty($limit) && is_numeric($limit)) {
    //         $workstation = $query->paginate($limit);
    //         return response()->json(['total' =>$totalcount, 'workstation'=>$workstation], 200);
            
    //     } else {
    //         // Default get all data
    //         $workstation = $query->orderBy('id','desc')->get();
    //         return response()->json($workstation, 200);
    //     }

        
    // }

    public function index(Request $request)
{
    // Default values
    $sortBy    = $request->get('sort_by', 'id'); // default column
    $sortOrder = $request->get('sort_order', 'desc'); // default order
    $limit     = $request->get('per_page', null); // default null = all records
    $search    = $request->get('search', null);

   
    $query = Workstation::with('department');

    // 🔍 Searching
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // ✅ Filter by department (relation)
    if ($request->filled('department')) {
        $department = $request->department;

        $query->whereHas('department', function ($q) use ($department) {
            $q->where('name', 'like', "%{$department}%");
        });
    }

    // ✅ Sorting
    $query->orderBy($sortBy, $sortOrder);

    // ✅ Apply pagination or get all
    if (!empty($limit) && is_numeric($limit)) {
        $workstations = $query->paginate($limit);
    } else {
        $workstations = $query->orderBy('id', 'desc')->get();
    }

    // ✅ Map department_name as extra field
    $workstations->map(function ($ws) {
        $ws->department_name = $ws->department?->name; 
        return $ws;
    });
     if(!empty($search)){
                $totalcount = $query->count();
            }else{
                 $totalcount = Workstation::count();
            }

    if (!empty($limit) && is_numeric($limit)) {
        return response()->json([
            'total'       => $totalcount, 
            'workstation' => $workstations
        ], 200);
    }

    return response()->json($workstations, 200);
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

