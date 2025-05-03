<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Machine;
use App\Models\ScanInOutProduct;
class MachineController extends Controller
{
    //
    public function index()
    {
        return response()->json(Machine::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'workstation_id' => 'required|exists:workstations,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $machine = Machine::create($validated);
        return response()->json($machine, 201);
    }

    public function show($id)
    {
        $machine = Machine::find($id);
        return response()->json($machine);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'department_id' => 'sometimes|exists:departments,id',
            'workstation_id' => 'required|exists:workstations,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string'
        ]);

        $machine = Machine::find($id);

        $machine->update($validated);
        return response()->json($machine);
    }

    public function destroy($id)
    {
        $machine = Machine::find($id);
        if (!$machine) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        $machine->delete();

        return response()->json(['message' => 'Deleted Successfully']);
    }

    public function machineHistory($id)
    {
        $machine = Machine::find($id);
        $scanRecords = ScanInOutProduct::with([
            'product:id,product_name,sku,opening_stock',
            'employee:id,employee_name',
            'user:id,name'
        ])->where('machine_id',$id)->get();

        $scanRecords = $scanRecords->map(function ($scanRecords) {
            return [
                'id' => $scanRecords->id,
                'product_id' => $scanRecords->product_id,
                'issue_from_user_id' => $scanRecords->issue_from_user_id,
                'employee_id' => $scanRecords->employee_id,
                'in_out_date_time' => $scanRecords->in_out_date_time,
                'in_quantity' => $scanRecords->in_quantity,
                'out_quantity' => $scanRecords->out_quantity,
                'type' => $scanRecords->type,
                'purpose' => $scanRecords->purpose,
                'comments' => $scanRecords->comments,
                'product_name' => $scanRecords->product->product_name ?? null,
                'sku' => $scanRecords->product->sku ?? null,
                'quantity' => $scanRecords->product->opening_stock ?? null,
                'issue_from_name' => $scanRecords->user->name ?? null, 
                'employee_name' => $scanRecords->employee->employee_name ?? null,
                'created_at' => $scanRecords->created_at,
                'updated_at' => $scanRecords->updated_at,
            ];
        });

        $data = array('machine'=>$machine,
        'history'=>$scanRecords
    );
        return response()->json($data);
    }
}
