<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
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

    // ✅ Get Single Employee
    public function show($id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }
        $usersdata = User::where('employee_id',$id)->first();
        if(!empty($usersdata)){
            return response()->json(['employee'=>$employee, 'user'=>$usersdata], 200);
        }else{
            return response()->json(['employee'=>$employee], 200);
        }
        
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
                    $usersdata->update([
                        'employee_id' => $employee->id, // Use object property instead of array notation
                        'role_id' => $request->role_id,
                        'name' => $request->employee_name,
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                    ]);
                }else{
                    $user = User::create([
                        'employee_id' => $employee->id, // Use object property instead of array notation
                        'role_id' => $request->role_id,
                        'name' => $request->employee_name,
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                    ]);
    
                }
                
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
}
