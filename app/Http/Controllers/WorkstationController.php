<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Workstation;
use Illuminate\Http\Request;

class WorkstationController extends Controller
{
    //
    public function index()
    {
        return response()->json(Workstation::all());
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

