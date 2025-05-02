<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Machine;
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

}
