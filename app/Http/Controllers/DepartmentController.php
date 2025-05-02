<?php



// app/Http/Controllers/Api/DepartmentController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        return response()->json(Department::all(), 200);
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
