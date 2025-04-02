<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
     // Get all Roles
     public function getRoles()
     {
         // $roles = Role::with('permissions')->get();
         $roles = Role::all();
         return response()->json(['roles' => $roles]);
     }

    public function createRole(Request $request)
{
    $request->validate([
        'name' => 'required|string|unique:roles,name',
        'permissions' => 'required|array', // Ensure permissions are provided
        // 'permissions.*' => 'string|exists:permissions,name',
    ]);

    // Create Role
    $role = Role::create([
        'name' => $request->name,
        'guard_name' => 'api'
    ]);

    // Assign Permissions to Role
    $permissions = Permission::whereIn('name', $request->permissions)
        ->where('guard_name', 'api')
        ->get();

    $role->syncPermissions($permissions);

    return response()->json([
        'message' => 'Role created successfully with permissions',
        'role' => $role,
        'permissions' => $permissions
    ], 201);
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


public function updateRole(Request $request, $roleId)
{
    $request->validate([
        'name' => 'required|string|unique:roles,name,' . $roleId,
        'permissions' => 'required|array',
        'permissions.*' => 'exists:permissions,name'
    ]);

    // Fetch the role
    $role = Role::findOrFail($roleId);

    // Update role name
    $role->update(['name' => $request->name]);

    // Sync new permissions
    $role->syncPermissions($request->permissions);

    // Fetch updated permissions
    $updatedPermissions = $role->permissions()->get(['name', 'module']);

    return response()->json([
        'message' => 'Role updated successfully',
        'role' => [
            'id' => $role->id,
            'name' => $role->name
        ],
        'permissions' => $updatedPermissions
    ], 200);
}


public function deleteRole($roleId)
{
    // Fetch the role
    $role = Role::findOrFail($roleId);

    // Remove all permissions
    $role->syncPermissions([]);

    // Delete the role
    $role->delete();

    return response()->json([
        'message' => 'Role and associated permissions deleted successfully'
    ], 200);
}



}


