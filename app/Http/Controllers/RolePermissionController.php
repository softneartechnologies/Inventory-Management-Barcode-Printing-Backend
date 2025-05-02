<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Validator;

class RolePermissionController extends Controller
{
     // Get all Roles
     public function getRoles()
     {
         // $roles = Role::with('permissions')->get();
         $roles = Role::all();
         return response()->json(['roles' => $roles]);
     }

//     public function createRole(Request $request)
// {
//     $request->validate([
//         'name' => 'required|string|unique:roles,name',
//         'permissions' => 'required|array', // Ensure permissions are provided
//         // 'permissions.*' => 'string|exists:permissions,name',
//     ]);

//     // Create Role
//     $role = Role::create([
//         'name' => $request->name,
//         'guard_name' => 'api'
//     ]);

//     // Assign Permissions to Role
//     $permissions = Permission::whereIn('name', $request->permissions)
//         ->where('guard_name', 'api')
//         ->get();

//     // $role->syncPermissions($permissions);

//     // $role = Role::findOrFail($request->role_id);
//     // $permission = Permission::findOrFail($request->permission_id);
//     $role->permissions()->attach($permissions);

//     return response()->json([
//         'message' => 'Role created successfully with permissions',
//         'role' => $role,
//         'permissions' => $permissions
//     ], 201);
// }


public function createRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        // Role Create Karein
        $role = Role::create(['name' => $request->name]);

        // Role Ko Permissions Assign Karein
        $permissions = Permission::whereIn('name', $request->permissions)->get();
        $role->permissions()->attach($permissions);

        return response()->json([
            'message' => 'Role created successfully!',
            'role' => $role->load('permissions'),
        ], 200);
    }

    
    public function getRoleDetails($roleId)
{
    // Fetch role
    $role = Role::findOrFail($roleId);

    // Fetch assigned permissions with module names
    $assignedPermissions = $role->permissions()->get(['name', 'module']);

    return response()->json([
        'message' => 'Role details retrieved successfully',
        'role' => [
            'id' => $role->id,
            'name' => $role->name
        ],
        'permissions' => $assignedPermissions
    ], 200);
}


// public function updateRole(Request $request, $roleId)
// {
//     $request->validate([
//         'name' => 'required|string|unique:roles,name,' . $roleId,
//         'permissions' => 'required|array',
//         'permissions.*' => 'exists:permissions,name'
//     ]);

//     // Fetch the role
//     $role = Role::findOrFail($roleId);

//     // Update role name
//     $role->update(['name' => $request->name]);

//     // Sync new permissions
//     $role->syncPermissions($request->permissions);

//     // Fetch updated permissions
//     $updatedPermissions = $role->permissions()->get(['name', 'module']);

//     return response()->json([
//         'message' => 'Role updated successfully',
//         'role' => [
//             'id' => $role->id,
//             'name' => $role->name
//         ],
//         'permissions' => $updatedPermissions
//     ], 200);
// }



public function updateRole(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required',
        'permissions' => 'required|array',
        // 'permissions.*' => 'exists:permissions,name'
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

// dd($request->all());
        
    // $role = Role::findOrFail($id);
    // $role->update(['name' => $request->name]);


    $role = Role::findOrFail($id);

    // Check if the name is already taken by another role
    $existingRole = Role::where('name', $request->name)
        ->where('id', '!=', $id)
        ->first();
    
    if ($existingRole) {
        // Duplicate found, don't update
    }else {
        $role->update(['name' => $request->name]);
    }

    

    $permissions = Permission::whereIn('name', $request->permissions)->get();
    $role->permissions()->sync($permissions);

    return response()->json([
        'message' => 'Role updated successfully',
        'role' => [
            'id' => $role->id,
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('name')
        ]
    ]);
}



public function deleteRole($id)
{
    // Fetch the role
    // $role = Role::findOrFail($roleId);

    // Remove all permissions
    // $role->syncPermissions([]);

    // Delete the role
    // $role->delete();

    $role = Role::findOrFail($id);
        $role->permissions()->detach();
        $role->delete();

    return response()->json([
        'message' => 'Role and associated permissions deleted successfully'
    ], 200);
}



}


