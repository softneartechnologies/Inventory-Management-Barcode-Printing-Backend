<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Manufacturer;
use Illuminate\Support\Facades\Validator;

class ManufacturerController extends Controller
{
    // ✅ Get All Manufacturers
    // public function index()
    // {
    //     $manufacturers = Manufacturer::orderBy('id', 'desc')->get();
    //     return response()->json($manufacturers, 200);
    // }

      public function index(Request $request)
    {
        // Default values
        
        $sortBy = $request->get('sort_by', 'id'); // default column
        $sortOrder = $request->get('sort_order', 'desc'); // default order
        $limit = $request->get('per_page', null); // default null = all records
        $search = $request->get('search', null);

            $totalcount = Manufacturer::count();
        $query = Manufacturer::query();

        // Searching
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $query->orderBy($sortBy, $sortOrder);

        // If limit is given, apply pagination
        if (!empty($limit) && is_numeric($limit)) {
            $manufacturer = $query->paginate($limit);
            return response()->json(['total' =>$totalcount, 'manufacturer'=>$manufacturer], 200);
            
        } else {
            // Default get all data
            $manufacturer = $query->orderBy('id','desc')->get();
            return response()->json($manufacturer, 200);
        }

        
    }
    // ✅ Create a New Manufacturer
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $manufacturer = Manufacturer::create($request->all());

        return response()->json(['message' => 'Manufacturer created successfully', 'manufacturer' => $manufacturer], 201);
    }

    // ✅ Get Single Manufacturer
    public function show($id)
    {
        $manufacturer = Manufacturer::find($id);
        if (!$manufacturer) {
            return response()->json(['error' => 'Manufacturer not found'], 404);
        }

        return response()->json($manufacturer, 200);
    }

    // ✅ Update Manufacturer
    public function update(Request $request, $id)
    {
        $manufacturer = Manufacturer::find($id);
        if (!$manufacturer) {
            return response()->json(['error' => 'Manufacturer not found'], 404);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $manufacturer->update($request->all());

        return response()->json(['message' => 'Manufacturer updated successfully', 'manufacturer' => $manufacturer], 200);
    }

    // ✅ Delete Manufacturer
    public function destroy($id)
    {
        $manufacturer = Manufacturer::find($id);
        if (!$manufacturer) {
            return response()->json(['error' => 'Manufacturer not found'], 404);
        }

        $manufacturer->delete();

        return response()->json(['message' => 'Manufacturer deleted successfully'], 200);
    }
}
