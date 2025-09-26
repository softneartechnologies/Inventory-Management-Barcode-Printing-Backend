<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // List all roles
    public function index()
    {
        return response()->json(Role::all());
    }

    // Store a new role
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'description' => 'nullable|string',
        ]);

         $exists = Role::where('name', $request->name)->exists();
        if ($exists) {
            return response()->json(['error' => 'Role with this name already exists.'], 409);
        }
        $role = Role::create($request->all());

        return response()->json($role, 200);
    }

    // Show a specific role
    public function show($id)
    {
        $role = Role::find($id);
        return response()->json($role);
    }

    // Update a role
    public function update(Request $request, $id)
    {
        $role = Role::find($id);
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'description' => 'nullable|string',
        ]);
         $exists = Role::where('name', $request->name)->exists();
        if ($exists) {
            return response()->json(['error' => 'Role with this name already exists.'], 409);
        }
        
        $role->update($request->all());

        return response()->json($role);
    }

    // Delete a role
    public function destroy($id)
    {
        $role = Role::find($id);
        $role->delete();

        return response()->json(['message' => 'Role deleted successfully']);
    }
}
