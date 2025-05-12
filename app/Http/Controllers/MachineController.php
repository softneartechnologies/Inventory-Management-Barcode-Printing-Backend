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
        $machine = Machine::with('department:id,name','workstation:id,name')->where('id',$id)->get();
        $scanRecords = ScanInOutProduct::with([
            'product:id,product_name,sku,opening_stock',
            'employee:id,employee_name',
            'user:id,name','category:id,name','location:id,name'
        ])->where('machine_id',$id)->orderBy('id','desc')->get();

        $scanRecords = $scanRecords->map(function ($scanRecords) {
            return [
                'id' => $scanRecords->id,
                'in_out_date_time' => $scanRecords->in_out_date_time,
                'product_id' => $scanRecords->product_id,
                'product_name' => $scanRecords->product->product_name ?? null,
                'sku' => $scanRecords->product->sku ?? null,
                'category' => $scanRecords->category->name ?? null,
                'location' => $scanRecords->location->name ?? null,
                'quantity' => $scanRecords->product->opening_stock ?? null,
                'issue_from_name' => $scanRecords->user->name ?? null, 
                'employee_name' => $scanRecords->employee->employee_name ?? null,
                'issue_from_user_id' => $scanRecords->issue_from_user_id,
                'employee_id' => $scanRecords->employee_id,
                'in_quantity' => $scanRecords->in_quantity,
                'out_quantity' => $scanRecords->out_quantity,
                'previous_stock' => $scanRecords->previous_stock,
                'total_current_stock' => $scanRecords->total_current_stock,
                'threshold' => $scanRecords->threshold,
                'type' => $scanRecords->type,
                'purpose' => $scanRecords->purpose,
                'comments' => $scanRecords->comments,
                'created_at' => $scanRecords->created_at,
                'updated_at' => $scanRecords->updated_at,
            ];
        });


        $machine = $machine->map(function ($machine) {
            return [
                'id' => $machine->id,
                'name' => $machine->name,
                'description' => $machine->description,
                // 'employee_id' => $machine->employee_id,
                // 'in_out_date_time' => $machine->in_out_date_time,
                // 'in_quantity' => $machine->in_quantity,
                // 'out_quantity' => $machine->out_quantity,
                // 'type' => $machine->type,
                // 'purpose' => $machine->purpose,
                // 'comments' => $machine->comments,
                'department_name' => $machine->department->name ?? null,
                'workstation_name' => $machine->workstation->name ?? null,
                'part_utilisation_cost' => 'It will calculate based on date filters ' ?? null,
                
            ];
        });

        $data = array('machine'=>$machine,
        'history'=>$scanRecords
    );
        return response()->json($data);
    }
}
