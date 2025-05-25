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
    // ✅ Get All Employees
    public function index()
    {
        $employees = Employee::all();
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
    $employee = User::with('employee:id,employee_name,department,work_station,access_for_login,status')
    ->whereHas('employee', function ($query) use ($id) {
        $query->where('id', $id);
    })
    ->first();
    

    if (!$employee || !$employee->employee) {
        
        $employee = Employee::where('id',$id)->first();
          if (!empty($employee)) {
        $employeeDetails = [
        'employee_id'       => $employee->id,
        'company_employee_id' => $employee->employee_id,
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
        'company_employee_id' => $employee->employee_id,
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
    public function uploadEmployeeCSV(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), "r");

        $header = fgetcsv($handle);
        $expectedHeaders = array_map('trim', [
            "employee_id","employee_name", "department", "work_station", "access_for_login", "role_id", "email", "password"
        ]);

        $header = array_map('trim', $header);
        if ($header !== $expectedHeaders) {
            return response()->json(['error' => 'Invalid CSV format. Please use the correct template.'], 400);
        }

        $invalidRows = [];
        $rowNumber = 2;

        while ($row = fgetcsv($handle)) {
            $row = array_map('trim', $row);

            if (count($row) !== count($expectedHeaders) || empty($row[0]) || empty($row[1])) {
                $invalidRows[] = $rowNumber++;
                continue;
            }

            // Skip if user already exists
            if (User::where('email', $row[5])->exists()) {
                $rowNumber++;
                continue;
            }

            // Get or create department
            $department = Department::firstOrCreate(
                ['name' => $row[2]],
                ['description' => 'HR Department']
            );

            // Get or create workstation with department_id
            $workstation = Workstation::firstOrCreate(
                ['name' => $row[3], 'department_id' => $department->id],
                ['name' => $row[3], 'department_id' => $department->id]
            );

            // Get role
            $role = Role::where('name', $row[5])->first();
            if (!$role) {
                $invalidRows[] = $rowNumber++;
                continue;
            }

            // Create employee
            if ($row[4] == "1") {
            $employee = Employee::create([
                'employee_id'    => $row[0],
                'employee_name'    => $row[1],
                'department'       => $row[2],
                'work_station'     => $row[3],
                'access_for_login' => "true",
            ]);

            // If login access is allowed, create user
            
                $user = User::create([
                    'employee_id' => $employee->id,
                    'role_id'     => $role->id,
                    'name'        => $row[1],
                    'email'       => $row[6],
                    'password'    => Hash::make($row[7]),
                ]);

                // Generate JWT token (optional)
                $token = JWTAuth::fromUser($user);
            }else{
                $employee = Employee::create([
                'employee_id'    => $row[0],
                'employee_name'    => $row[1],
                'department'       => $department->name,
                'work_station'     => $workstation->name,
                'access_for_login' => "false",
            ]);
            }

            $rowNumber++;
        }

        fclose($handle);

        return response()->json([
            'message'       => 'CSV uploaded successfully.',
            'invalid_rows'  => $invalidRows
        ], 200);
    }


    public function employeeTemplateCsvUrl()
{
    $filename = 'csv_tem/employee_template.csv';
   
    $columns = [
        "employee_id","employee_name", "department", "work_station", "access_for_login", "role_id", "email", "password"
    ];
    // Open file for writing in local storage
    $filePath = storage_path("app/public/{$filename}");
    $file = fopen($filePath, 'w');
    fputcsv($file, $columns); // Write headers
    fclose($file);

    // Make sure the file is accessible (ensure 'public' disk is linked)
    $url = asset("storage/{$filename}");

    return response()->json([
        'status' => 'success',
        'url' => $url
    ]);
}

}
