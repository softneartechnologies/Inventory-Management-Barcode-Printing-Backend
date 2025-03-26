<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Validator;

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
        // Validate request
        $validator = Validator::make($request->all(), [
            'employee_name' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'work_station' => 'required|string|max:255',
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $employee = Employee::create($request->all());

        return response()->json(['message' => 'Employee created successfully', 'employee' => $employee], 201);
    }

    // ✅ Get Single Employee
    public function show($id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        return response()->json($employee, 200);
    }

    // ✅ Update Employee
    public function update(Request $request, $id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'employee_name' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'work_station' => 'required|string|max:255',
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $employee->update($request->all());

        return response()->json(['message' => 'Employee updated successfully', 'employee' => $employee], 200);
    }

    // ✅ Delete Employee
    public function destroy($id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        $employee->delete();

        return response()->json(['message' => 'Employee deleted successfully'], 200);
    }
}
