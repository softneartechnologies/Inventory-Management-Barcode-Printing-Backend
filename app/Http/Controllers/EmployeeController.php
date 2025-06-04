<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Workstation;
use App\Models\Department;
use App\Models\Role;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
       
class EmployeeController extends Controller
{

    public function index()
    {
        // $employees = Employee::all();
        $employees = Employee::where('status', 'active')->get();

        return response()->json($employees, 200);
    }

    public function inactiveEmployee()
    {
        // $employees = Employee::all();
        $employees = Employee::where('status', 'inactive')->get();

        return response()->json($employees, 200);
    }
    // ✅ Create a New Employee
    public function store(Request $request)
    {
    
        if($request->access_for_login =="true"){
            
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|string|max:255',
                'employee_name' => 'required|string|max:255',
                'department' => 'required|string|max:255',
                'work_station' => 'required|string|max:255',
                'status' => 'required|in:active,inactive',
                'access_for_login' =>'required|in:true,false',
                'role_id'=>'required',
                'email' => 'required|email:dns|unique:users,email',
                'password' => 'required|min:6',

            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }
        }else {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|string|max:255',
                'employee_name' => 'required|string|max:255',
                'department' => 'required|string|max:255',
                'work_station' => 'required|string|max:255',
                'status' => 'required|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }
        }

        if($request->access_for_login==="true"){
            
            $employee = Employee::create([
                'employee_id' => $request->employee_id,
                'employee_name' => $request->employee_name,
                'department' => $request->department,
                'work_station' => $request->work_station,
                'status' => $request->status,
                'access_for_login' => $request->access_for_login,
            ]);
        
            // Ensure Employee was created successfully
            if ($employee) {
                // Create User
                $user = User::create([
                    'employee_id' => $employee->id, // Use object property instead of array notation
                    'role_id' => $request->role_id,
                    'name' => $request->employee_name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);

            // Generate a JWT token for the user
            $token = JWTAuth::fromUser($user);
            }

            return response()->json([
                'message' => 'Employee and user created successfully',
                'employee' => $employee,
                'user' => $user,
                'token' => $token,
            ], 200);

        }else{
            $employee = Employee::create($request->all());
            return response()->json(['message' => 'Employee created successfully', 'employee' => $employee], 200);
        }

    }

   
    public function show($id)
{
    // Get the user with employee relation
    // $employee = User::with('employee:id,employee_name,department,work_station,access_for_login,status')
    //     ->where('employee:id', $id)
    //     ->first();
    $employee = User::with('employee:id,employee_name,employee_id,department,work_station,access_for_login,status')
    ->whereHas('employee', function ($query) use ($id) {
        $query->where('id', $id);
    })
    ->first();
    

    if (!$employee || !$employee->employee) {
        
        $employee = Employee::where('id',$id)->first();
          if (!empty($employee)) {
        $employeeDetails = [
        'employee_id'       => $employee->id,
        'company_employee_id' =>$employee->employee_id,
        'employee_name'     => $employee->employee_name,
        'department'        => $employee->department,
        'work_station'      => $employee->work_station,
        'access_for_login'  => $employee->access_for_login,
        'status'            => $employee->status,
    ];
          }else{
        return response()->json(['message' => 'Employee not found'], 404);
              
          }
        
    }else{
        
    

    $employeeDetails = [
        'employee_id'       => $employee->employee->id,
        'company_employee_id' => $employee->employee->employee_id,
        'employee_name'     => $employee->employee->employee_name,
        'department'        => $employee->employee->department,
        'work_station'      => $employee->employee->work_station,
        'access_for_login'  => $employee->employee->access_for_login,
        'email'             => $employee->email,
        'role_id'           => $employee->role_id,
        'status'            => $employee->employee->status,
    ];
    }

    return response()->json([
        'employee_details' => $employeeDetails
    ], 200);
}


    // ✅ Update Employee
    public function update(Request $request, $id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        if($request->access_for_login =="true"){
            
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|string|max:255',
                'employee_name' => 'required|string|max:255',
                'department' => 'required|string|max:255',
                'work_station' => 'required|string|max:255',
                'status' => 'required|in:active,inactive',
                'access_for_login' =>'required|in:true,false',
                'role_id'=>'required',
                'email' => 'required',
                'password' => 'required|min:6',

            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }
        }else {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|string|max:255',
                'employee_name' => 'required|string|max:255',
                'department' => 'required|string|max:255',
                'work_station' => 'required|string|max:255',
                'status' => 'required|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }
        }

        if($request->access_for_login==="true"){
            
            // $employee = Employee::create([
            //     'employee_name' => $request->employee_name,
            //     'department' => $request->department,
            //     'work_station' => $request->work_station,
            //     'status' => $request->status,
            //     'access_for_login' => $request->access_for_login,
            // ]);

            $employee->update([
                'employee_id' => $request->employee_id,
                'employee_name' => $request->employee_name,
                'department' => $request->department,
                'work_station' => $request->work_station,
                'status' => $request->status,
                'access_for_login' => $request->access_for_login,
            ]);
        
            // Ensure Employee was created successfully
            if ($employee) {
                // Create User
                $usersdata = User::where('employee_id',$id)->first();

                if(!empty($usersdata)){
                    $emailscheck = User::where('email',$request->email)->first();
                    if(!empty($emailscheck)){
                         $usersdata->update([
                        'employee_id' => $employee->id, // Use object property instead of array notation
                        'role_id' => $request->role_id,
                        'name' => $request->employee_name,
                        // 'email' => $request->email,
                        'password' => Hash::make($request->password),
                    ]);
                    }else{
                        
                   
                    $usersdata->update([
                        'employee_id' => $employee->id, // Use object property instead of array notation
                        'role_id' => $request->role_id,
                        'name' => $request->employee_name,
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                    ]);
                    }
                }else{
                    $usersdata = User::create([
                        'employee_id' => $employee->id, // Use object property instead of array notation
                        'role_id' => $request->role_id,
                        'name' => $request->employee_name,
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                    ]);
    
                }
                
            // Generate a JWT token for the user
            $token = JWTAuth::fromUser($usersdata);
            }

            return response()->json([
                'message' => 'Employee and user created successfully',
                'employee' => $employee,
                'user' => $usersdata,
                'token' => $token,
            ], 200);

        }else{
            $employee->update($request->all());

            return response()->json(['message' => 'Employee updated successfully', 'employee' => $employee], 200);
        }
        

        // Validate request
        // $validator = Validator::make($request->all(), [
        //     'employee_name' => 'required|string|max:255',
        //     'department' => 'required|string|max:255',
        //     'work_station' => 'required|string|max:255',
        //     'status' => 'required|in:active,inactive'
        // ]);

        // if ($validator->fails()) {
        //     return response()->json(['error' => $validator->errors()], 400);
        // }

        // $employee->update($request->all());

        // return response()->json(['message' => 'Employee updated successfully', 'employee' => $employee], 200);
    }

    // ✅ Delete Employee
    public function destroy($id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        $employee->delete();
        $usersdata = User::where('employee_id',$id)->first();
        if(!empty($usersdata)){

            $usersdata->delete();
        }

    

        return response()->json(['message' => 'Employee deleted successfully'], 200);
    }
    //   public function uploadEmployeeCSV(Request $request)
    //     {
    //         $request->validate([
    //             'file' => 'required|mimes:csv,txt|max:2048'
    //         ]);

    //         $file = $request->file('file');
    //         $handle = fopen($file->getPathname(), "r");

    //         $header = fgetcsv($handle);
    //         $expectedHeaders = array_map('trim', [
    //             "employee_name", "department", "work_station", "access_for_login",
    //             "role_id", "email", "password"
    //         ]);

    //         $header = array_map('trim', $header);
    //         if ($header !== $expectedHeaders) {
    //             return response()->json(['error' => 'Invalid CSV format. Please use the correct template.'], 400);
    //         }

    //         $invalidRows = [];
    //         $rowNumber = 2;

    //         while ($row = fgetcsv($handle)) {
    //             $row = array_map('trim', $row);

    //             if (count($row) !== count($expectedHeaders) || empty($row[0]) || empty($row[1])) {
    //                 $invalidRows[] = $rowNumber++;
    //                 continue;
    //             }

    //             // Check for existing user by email (index 5)
    //             if (User::where('email', $row[5])->exists()) {
    //                 $rowNumber++;
    //                 continue;
    //             }

    //             // Get or create Department and Workstation
                
    //             // $workstation = Workstation::where('name', $row[2])->first();
    //             $department = Department::firstOrCreate(['name' => $row[1]], ['description' => 'HR Department']);
    //             $workstation = Workstation::firstOrCreate(['name' => $row[2]], ['department_id '=>$department->id]);
    //             // Get role
    //         //    $role = Role::firstOrCreate(['name' => $row[4]], ['guard_name' => 'api']);
    //             $role = Role::where('name', $row[4])->first();

    //             if (!$role) {
    //                 $invalidRows[] = $rowNumber++;
    //                 continue;
    //             }

    //             // Create Employee
    //             $employee = Employee::create([
    //                 'employee_name' => $row[0],
    //                 'department' => $department->name,
    //                 'work_station' => $workstation->name,
    //                 'access_for_login' => $row[3] === "1" ? true : false,
    //             ]);

    //             // If access_for_login is "true", create User
    //             if (strtolower($row[3]) === "1" && $employee) {
    //                 $user = User::create([
    //                     'employee_id' => $employee->id,
    //                     'role_id' => $role->id,
    //                     'name' => $row[0],
    //                     'email' => $row[5],
    //                     'password' => Hash::make($row[6]),
    //                 ]);

    //                 // Optionally generate JWT token
    //                 $token = JWTAuth::fromUser($user);
    //             }

    //             $rowNumber++;
    //         }

    //         fclose($handle);

    //         return response()->json([
    //             'message' => 'CSV uploaded successfully.',
    //             'invalid_rows' => $invalidRows
    //         ], 200);
    //     }
    // public function uploadEmployeeCSV(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required'
    //     ]);

    //     $file = $request->file('file');
    //     $handle = fopen($file->getPathname(), "r");

    //     $header = fgetcsv($handle);
    //     $expectedHeaders = array_map('trim', [
    //         "employee_id","employee_name", "department", "work_station", "access_for_login", "role_id", "email", "password","status"
    //     ]);

    //     $header = array_map('trim', $header);
    //     if ($header !== $expectedHeaders) {
    //         return response()->json(['error' => 'Invalid CSV format. Please use the correct template.'], 400);
    //     }

    //     $invalidRows = [];
    //     $rowNumber = 1;

    //     while ($row = fgetcsv($handle)) {
    //         $row = array_map('trim', $row);

    //         if (count($row) !== count($expectedHeaders) || empty($row[0]) || empty($row[1])) {
    //             $invalidRows[] = $rowNumber++;
    //             continue;
    //         }

    //         // Skip if user already exists
    //         // if (User::where('email', $row[5])->exists()) {
    //         //     $rowNumber++;
    //         //     continue;
    //         // }

    //         // Get or create department
    //         $department = Department::firstOrCreate(
    //             ['name' => $row[2]],
    //             ['description' => 'HR Department']
    //         );

    //         // Get or create workstation with department_id
    //         $workstation = Workstation::firstOrCreate(
    //             ['name' => $row[3], 'department_id' => $department->id],
    //             ['name' => $row[3], 'department_id' => $department->id]
    //         );


    //         //  $role = Role::firstOrCreate(
    //         //     ['name' => $row[5], 'guard_name' => 'api'],
    //         //     ['name' => $row[5], 'guard_name' =>'api']
    //         // );
            
    //         // Create employee
    //         if ($row[4] == "1") {

    //         // $employee = Employee::create([
    //         //     'employee_id'    => $row[0],
    //         //     'employee_name'    => $row[1],
    //         //     'department'       => $row[2],
    //         //     'work_station'     => $row[3],
    //         //     'access_for_login' => "true",
    //         // ]);
    //         $employee = Employee::firstOrNew(['employee_id' => $row[0]]);

    //     if ($employee->exists) {
    //         // Only update specific fields if record exists
    //         $employee->department       = $row[2];
    //         $employee->work_station     = $row[3];
    //         $employee->access_for_login = "true";
    //         $employee->status = $row[8];
    //     } else {
    //         // Create new record with all fields
            
    //         $employee->employee_id    = $row[0];
    //         $employee->employee_name    = $row[1];
    //         $employee->department       = $row[2];
    //         $employee->work_station     = $row[3];
    //         $employee->access_for_login = "true";
    //         $employee->status = $row[8];
    //     }

    //     $employee->save();


    //             $role = Role::where('name', $row[5])->first();

    //             if (empty($role)) {
    //                 $role = Role::create([
    //                     'name' => $row[5],
    //                     'guard_name' => 'api',
    //                     'created_at' => now(),
    //                     'updated_at' => now()
    //                 ]);
    //             }

                
    //         $existingUser = User::where('employee_id', $employee->id)->first();

    //         if ($existingUser) {
    //             // Check if email already taken by another user
    //             $emailExists = User::where('email', $row[6])
    //                             ->where('id', '!=', $existingUser->id)
    //                             ->exists();

    //             if (!$emailExists) {
    //                $user =  $existingUser->update([
    //                     'role_id'  => $role->id,
    //                     'name'     => $row[1],
    //                     'email'    => $row[6], // Safe to update
    //                     'password' => Hash::make($row[7]),
    //                 ]);
    //                 $token = JWTAuth::fromUser($user);
    //             } 
    //             // else {
    //             //     // Handle duplicate email case
    //             //     // Example: Skip update or log error
    //             //     // $token = JWTAuth::fromUser($user);
    //             // }
    //         } else {
    //             // New user creation
    //             $user = User::create([
    //                 'employee_id' => $employee->id,
    //                 'role_id'     => $role->id,
    //                 'name'        => $row[1],
    //                 'email'       => $row[6],
    //                 'password'    => Hash::make($row[7]),
    //             ]);
    //             $token = JWTAuth::fromUser($user);
    //         }


    //             // Generate JWT token (optional)
                
    //         }else{
    //         //     $employee = Employee::create([
    //         //     'employee_id'    => $row[0],
    //         //     'employee_name'    => $row[1],
    //         //     'department'       => $department->name,
    //         //     'work_station'     => $workstation->name,
    //         //     'access_for_login' => "false",
    //         // ]);

    //         $employee = Employee::firstOrNew(['employee_id' => $row[0]]);

    //     if ($employee->exists) {
    //         // Only update specific fields if record exists
    //         $employee->department       = $row[2];
    //         $employee->work_station     = $row[3];
    //         $employee->access_for_login = "false";
    //         $employee->status = $row[8];
    //     } else {
    //         // Create new record with all fields
            
    //         $employee->employee_id    = $row[0];
    //         $employee->employee_name    = $row[1];
    //         $employee->department       = $department->name;
    //         $employee->work_station     = $workstation->name;
    //         $employee->access_for_login = "false";
    //         $employee->status = $row[8];
    //     }

    //     $employee->save();

    //         }

    //         $rowNumber++;
    //     }

    //     fclose($handle);

    //     return response()->json([
    //         'message'       => 'CSV uploaded successfully.',
    //         'invalid_rows'  => $invalidRows
    //     ], 200);
    // }

    public function uploadEmployeeCSV(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:csv,txt'
    ]);

    $file = $request->file('file');
    $handle = fopen($file->getPathname(), "r");

    $header = fgetcsv($handle);
    $expectedHeaders = array_map('trim', [
        "employee_id", "employee_name", "department", "work_station",
        "access_for_login", "role_id", "email", "password", "status"
    ]);

    $header = array_map('trim', $header);
    if ($header !== $expectedHeaders) {
        return response()->json(['error' => 'Invalid CSV format. Please use the correct template.'], 400);
    }

    $invalidRows = [];
    $rowNumber = 2; // CSV starts from line 2 after header

    while (($row = fgetcsv($handle)) !== false) {
        $row = array_map('trim', $row);

        if (count($row) !== count($expectedHeaders) || empty($row[0]) || empty($row[1])) {
            $invalidRows[] = $rowNumber++;
            continue;
        }

        // Department
        $department = Department::firstOrCreate(
            ['name' => $row[2]],
            ['description' => 'HR Department']
        );

        // Workstation
        $workstation = Workstation::firstOrCreate(
            ['name' => $row[3], 'department_id' => $department->id],
            ['name' => $row[3], 'department_id' => $department->id]
        );

        // Role
        

        // Employee
        $employee = Employee::firstOrNew(['employee_id' => $row[0]])->exists();
        if (!empty($employee)) {
            // Employee exists – DO NOT update name or ID
            $employee->department       = $department->id;
            $employee->work_station     = $workstation->id;
            $employee->access_for_login = $row[4] == "1" ? "true" : "false";
            $employee->status           = $row[8];
        } else {
            // New employee
            $employee->employee_id      = $row[0];
            $employee->employee_name    = $row[1];
            $employee->department       = $department->id;
            $employee->work_station     = $workstation->id;
            $employee->access_for_login = $row[4] == "1" ? "true" : "false";
            $employee->status           = $row[8];
        }
        $employee->save();

        // If login access allowed
        if ($row[4] == "1") {
       
          

            $existingUser = User::where('employee_id', $employee->id)->first();

            if ($existingUser) {
                // Check for email conflict with other users
                $emailConflict = User::where('email', $row[6])
                    ->where('id', '!=', $existingUser->id)
                    ->exists();

                if ($emailConflict) {
                    $invalidRows[] = $rowNumber++;
                    continue;
                }

                 $role = Role::firstOrCreate(
                        ['name' => trim($row[5]), 'guard_name' => 'api']
                    );

                    $roleId = $role->id;

                $existingUser->update([
                    'role_id'  => $role->id,
                    'name'     => $row[1],
                    'email'    => $row[6],
                    'password' => Hash::make($row[7]),
                ]);

            } else {
                // Email already exists? skip to avoid crash
                if (User::where('email', $row[6])->exists()) {
                    $invalidRows[] = $rowNumber++;
                    continue;
                }
                $role = Role::firstOrCreate(
                    ['name' => trim($row[5]), 'guard_name' => 'api']
                );

                $roleId = $role->id;
                try {
                    User::create([
                        'employee_id' => $employee->id,
                        'role_id'     => $role->id,
                        'name'        => $row[1],
                        'email'       => $row[6],
                        'password'    => Hash::make($row[7]),
                    ]);
                } catch (\Exception $e) {
                    $invalidRows[] = $rowNumber++;
                    continue;
                }
            }
        }

        $rowNumber++;
    }

    fclose($handle);

    return response()->json([
        'message' => 'CSV uploaded successfully.',
        'invalid_rows' => $invalidRows
    ], 200);
}


    // public function updateStatus(Request $request, $id)
    // {
    //     $employee = Employee::find($id);

    //     if (!$employee) {
    //         return response()->json([
    //             'message' => 'Employee not found',
    //             'status' => 404
    //         ], 404);
    //     }

    //     // Optional: validate status from request
    //     $request->validate([
    //         'status' => 'required|in:active,inactive'
    //     ]);

    //     $employee->status = $request->status;
    //     $employee->save();

    //     return response()->json([
    //         'message' => 'Employee status updated successfully',
    //         'data' => $employee,
    //         'status' => 200
    //     ], 200);
    // }

//     public function uploadEmployeeCSV(Request $request)
// {
//     $request->validate([
//         'file' => 'required|file|mimes:csv,txt'
//     ]);

//     $file = $request->file('file');
//     $handle = fopen($file->getPathname(), "r");

//     $header = fgetcsv($handle);
//     $expectedHeaders = [
//         "employee_id", "employee_name", "department", "work_station",
//         "access_for_login", "role_id", "email", "password", "status"
//     ];

//     // Trim and compare headers
//     $header = array_map('trim', $header);
//     $expectedHeaders = array_map('trim', $expectedHeaders);

//     if ($header !== $expectedHeaders) {
//         return response()->json(['error' => 'Invalid CSV format. Please use the correct template.'], 400);
//     }

//     $invalidRows = [];
//     $rowNumber = 2; // start from 2 because 1 is header

//     while ($row = fgetcsv($handle)) {
//         $row = array_map('trim', $row);

//         // Basic validation: count and essential fields
//         if (count($row) !== count($expectedHeaders) || empty($row[0]) || empty($row[1])) {
//             $invalidRows[] = $rowNumber++;
//             continue;
//         }

//         // Create or fetch department
//         $department = Department::firstOrCreate(
//             ['name' => $row[2]],
//             ['description' => 'HR Department']
//         );

//         // Create or fetch workstation
//         $workstation = Workstation::firstOrCreate(
//             ['name' => $row[3], 'department_id' => $department->id]
//         );

//         // Handle Employee (create or update)
//         $employee = Employee::firstOrNew(['employee_id' => $row[0]]);
//         $employee->employee_name = $row[1];
//         $employee->department = $department->name;
//         $employee->work_station = $workstation->name;
//         $employee->access_for_login = $row[4] == "1" ? "true" : "false";
//         $employee->status = $row[8];
//         $employee->save();

//         // If access_for_login = 1, create/update user account
//         if ($row[4] == "1") {
//             // Handle Role
//             $role = Role::firstOrCreate(
//                 ['name' => $row[5], 'guard_name' => 'api']
//             );

//             // Handle User (create or update)
//             $existingUser = User::where('employee_id', $employee->id)->first();

//             if ($existingUser) {
//                 // Check for duplicate email
//                 $emailTaken = User::where('email', $row[6])
//                     ->where('id', '!=', $existingUser->id)
//                     ->exists();

//                 if (!$emailTaken) {
//                     $existingUser->update([
//                         'role_id'  => $role->id,
//                         'name'     => $row[1],
//                         'email'    => $row[6],
//                         'password' => Hash::make($row[7]),
//                     ]);
//                     // Optional: Generate token
//                     // $token = JWTAuth::fromUser($existingUser);
//                 }
//             } else {
//                 $user = User::create([
//                     'employee_id' => $employee->id,
//                     'role_id'     => $role->id,
//                     'name'        => $row[1],
//                     'email'       => $row[6],
//                     'password'    => Hash::make($row[7]),
//                 ]);
//                 // Optional: Generate token
//                 // $token = JWTAuth::fromUser($user);
//             }
//         }

//         $rowNumber++;
//     }

//     fclose($handle);

//     return response()->json([
//         'message'      => 'CSV uploaded successfully.',
//         'invalid_rows' => $invalidRows
//     ], 200);
// }


    // public function employeeTemplateCsvUrl()
    // {
    //     $filename = 'csv_tem/employee_template.csv';
    
    //     $columns = [
    //         "employee_id","employee_name", "department", "work_station", "access_for_login", "role_id", "email", "password"
    //     ];
    //     // Open file for writing in local storage
    //     $filePath = storage_path("app/public/{$filename}");
    //     $file = fopen($filePath, 'w');
    //     fputcsv($file, $columns); // Write headers
    //     fclose($file);

    //     // Make sure the file is accessible (ensure 'public' disk is linked)
    //     $url = asset("storage/{$filename}");

    //     return response()->json([
    //         'status' => 'success',
    //         'url' => $url
    //     ]);
    // }

    public function employeeTemplateCsvUrl()
    {
        $filename = 'csv_tem/employee_template.csv';
        $employees = collect([
            (object)[
                'employee_id' => 'EMP01',
                'employee_name' => 'Employee',
                'department' => (object)['name' => 'HR'],
                'work_station' => (object)['name' => 'Document management'],
                'access_for_login' => '1',
                'role_id' => (object)['role_name' => 'Employee'],
                'email' => 'demo@gmail.com',
                'password' => 'Demo@123456',
                'status' => 'active',
            
            ]
        ]);

        // Map employee to desired format
        $employees = $employees->map(function ($employee) {
            return [
                'employee_id' => $employee->employee_id,
                'employee_name' => $employee->employee_name,
                'department' => optional($employee->department)->name,
                'work_station' => optional($employee->work_station)->name,
                'access_for_login' => $employee->access_for_login,
                'role_id' => optional($employee->role_id)->role_name,
                'email' => $employee->email,
                'password' => $employee->password,
                'status' => $employee->status,
            ];
        });

        // CSV column headers
        $columns = [
                "employee_id","employee_name", "department", "work_station", "access_for_login", "role_id", "email", "password", "status"
            ];


        // Create CSV file
        $filePath = storage_path("app/public/{$filename}");
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $file = fopen($filePath, 'w');
        fputcsv($file, $columns); // Headers

        foreach ($employees as $employee) {
            fputcsv($file, $employee);
        }

        fclose($file);

        // Return download URL
        $url = asset("storage/{$filename}");

        return response()->json([
            'status' => 'success',
            'url' => $url
        ]);
    }

}
