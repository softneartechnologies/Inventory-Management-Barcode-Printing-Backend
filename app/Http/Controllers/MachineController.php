<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Machine;
use App\Models\UomUnit;
use App\Models\ScanInOutProduct;
 use Carbon\Carbon;
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

    public function machineHistory($id, Request $request)
    {
       
       

$dateFilter = $request->query('date_filter');

$scanQuery = ScanInOutProduct::with([
    'product:id,product_name,sku,opening_stock,per_unit_cost,unit_of_measure,unit_of_measurement_category,location_id',
    'employee:id,employee_name',
    'user:id,name',
    'category:id,name',
    'location:id,name'
])->where('machine_id', $id);

if (!empty($dateFilter)) {
    try {
        // Handle Year e.g., "2025"
        if (preg_match('/^\d{4}$/', $dateFilter)) {
            $scanQuery->whereYear('in_out_date_time', $dateFilter);
        }

        // Handle Year-Month e.g., "2025-05"
        elseif (preg_match('/^\d{4}-\d{2}$/', $dateFilter)) {
            [$year, $month] = explode('-', $dateFilter);
            $scanQuery->whereYear('in_out_date_time', $year)
                      ->whereMonth('in_out_date_time', $month);
        }

        // Handle ISO Week e.g., "2025-W21"
        elseif (preg_match('/^\d{4}-W\d{2}$/', $dateFilter)) {
            [$year, $week] = explode('-W', $dateFilter);
            $startOfWeek = Carbon::now()->setISODate($year, $week)->startOfWeek();
            $endOfWeek = Carbon::now()->setISODate($year, $week)->endOfWeek();
            $scanQuery->whereBetween('in_out_date_time', [$startOfWeek, $endOfWeek]);
        }

        // Optional: fallback for exact date (e.g. "2025-05-20")
        elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFilter)) {
            $scanQuery->whereDate('in_out_date_time', $dateFilter);
        }
    } catch (\Exception $e) {
        // Optional: log error or ignore invalid filter
    }
}

$scanRecords = $scanQuery->orderBy('id', 'desc')->get();




         
        $scanRecords = $scanRecords->map(function ($scanRecords) {
            $locationIds = json_decode($scanRecords->product->location_id); 
            $per_unit_cost = json_decode($scanRecords->product->per_unit_cost); 
            
            $unit_of_measure = json_decode($scanRecords->product->unit_of_measure); 
            $pdate = array_combine($locationIds, $per_unit_cost);
            $udate = array_combine($locationIds, $unit_of_measure);

            $per_unit_costvalue = isset($pdate[$scanRecords->location_id]) ? $pdate[$scanRecords->location_id] : null;
            $unit_of_measurevalue = isset($udate[$scanRecords->location_id]) ? $udate[$scanRecords->location_id] : null;

            // $measure_of_unit_ratio = UomUnit::where('uom_category_id', $scanRecords->product->unit_of_measurement_category)
            //     ->where('unit_name', $unit_of_measurevalue)
            //     ->first();
            $measure_of_unit_ratio = UomUnit::where('unit_name', $unit_of_measurevalue)
                ->first();

// echo $value;
// print_r($measure_of_unit_ratio->ratio);die;

            return [
                'id' => $scanRecords->id,
                'machine_id' => $scanRecords->machine_id,
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
                'per_unit_cost' => $per_unit_costvalue,
                'ratio'=>$measure_of_unit_ratio->ratio ?? null,
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

     $machine = Machine::with('department:id,name', 'workstation:id,name')->where('id', $id)->first();

if ($machine) {
    $machine->part_utilisation_cost = count($scanRecords) ?? null;

    $machine = [
        'id' => $machine->id,
        'name' => $machine->name,
        'description' => $machine->description,
        'department_name' => $machine->department->name ?? null,
        'workstation_name' => $machine->workstation->name ?? null,
        'part_utilisation_cost' => $machine->part_utilisation_cost,
    ];
}
        
        $data = array('machine'=>$machine,
        'history'=>$scanRecords
    );
        return response()->json($data);
    }
}
