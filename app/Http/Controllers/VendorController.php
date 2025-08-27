<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class VendorController extends Controller
{
    // Display a listing of vendors
    // public function index()
    // {
    //     return Vendor::orderBy('id', 'desc')->get();
    // }

    // public function index(Request $request)
    // {
    //     // Default values
        
    //     $sortBy = $request->get('sort_by', 'id'); // default column
    //     $sortOrder = $request->get('sort_order', 'desc'); // default order
    //     $limit = $request->get('per_page', null); // default null = all records
    //     $search = $request->get('search', null);

           
    //     $query = Vendor::query();

    //     // Searching
    //     if (!empty($search)) {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('vendor_name', 'like', "%{$search}%")
    //             ->orWhere('company_name', 'like', "%{$search}%");
    //         });
    //     }

    //     if(!empty($search)){
    //             $total_count = $query->count();
                
    //         }else{
    //             $total_count = Vendor::count();
           
    //         }
    //     // Sorting
    //     if($sortOrder){

    //     }
    //     $query->orderBy($sortBy, $sortOrder);

    //     // If limit is given, apply pagination
    //     // if (!empty($limit) && is_numeric($limit)) {
    //     //     $vendor = $query->paginate($limit);
    //     //     return response()->json(['total' =>$totalcount, 'vendor'=>$vendor], 200);
            
    //     // }

         

    //     if (!empty($limit) && is_numeric($limit)) {
            
               
    //         $vendor = $query->paginate($limit);
    //         return response()->json(['total' =>$total_count, 'vendor'=>$vendor], 200);
        
            
    //     }

    //      else {
    //         // Default get all data
    //         $vendor = $query->orderBy('id','desc')->get();
    //         return response()->json($vendor, 200);
    //     }

        
    // }

public function index(Request $request)
{
    // Default values
    $sortBy    = $request->get('sort_by', 'id');       // default column
    $sortOrder = $request->get('sort_order', 'desc');  // default order
    $limit     = $request->get('per_page', null);      // per page limit
    $search    = $request->get('search', null);

    $query = Vendor::query();

    // Searching
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('vendor_name', 'like', "%{$search}%")
              ->orWhere('company_name', 'like', "%{$search}%");
        });
    }

    // Total count (with search filter if applied)
    $total_count = $query->count();

    // Sorting
    $query->orderBy($sortBy, $sortOrder);

    // Pagination or Get All
    if (!empty($limit) && is_numeric($limit)) {
        $vendor = $query->paginate($limit);

        // 🔥 Fix: If requested page is greater than last page → return last page data
        if ($vendor->currentPage() > $vendor->lastPage() && $vendor->lastPage() > 0) {
            $vendor = $query->paginate($limit, ['*'], 'page', $vendor->lastPage());
        }

    } else {
        $vendor = $query->get();
    }

    return response()->json([
        'total'  => $total_count,
        'vendor' => $vendor
    ], 200);
}


    // Store a newly created vendor
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'vendor_name' => 'required|string|max:255',
            // 'company_name' => 'required|string|max:255',
            // 'phone_number' => 'required|numeric',
            // 'email' => 'required|string|email|max:255|unique:vendors',
            // 'billing_address' => 'required|string',
            // 'shipping_address' => 'required|string',
        ]);


        $vendor = Vendor::create($request->all());

        return response()->json($vendor, 200);
    }

    // Display the specified vendor
    public function show($id)
    {
        $vendor = Vendor::find($id);
        if (!$vendor) {
            return response()->json(['error' => 'vendor not found'], 404);
        }

        return response()->json($vendor, 200);
    }

    // Update the specified vendor
    public function update(Request $request, $id)
    {
        $vendor = Vendor::find($id);
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        $request->validate([
            'vendor_name' => 'required|string|max:255',
            // 'company_name' => 'required|string|max:255',
            // 'phone_number' => 'required|string|max:20',
            // 'email' => 'required|email|unique:vendors,email,' . $id,
            // 'billing_address' => 'required|string',
            // 'shipping_address' => 'required|string',
        ]);
        //  $validatedData = Validator::make($request->all(), [
        //         'vendor_name' => 'required|string|max:255',
        //     'company_name' => 'required|string|max:255',
        //     'phone_number' => 'required|numeric',
        //     'email' => 'required|email|unique:vendors,email,' . $id,
        //     'billing_address' => 'required|string',
        //     'shipping_address' => 'required|string',
        //     ]);

        //     if ($validatedData->fails()) {
        //         return response()->json(['error' => $validatedData->errors()], 400);
        //     }



        $vendor->update($request->all());
        return response()->json($vendor, 200);
    }
    // Remove the specified vendor
    public function destroy($id)
    {
        $vendor = Vendor::find($id);
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        $vendor->delete();
        return response()->json(['message' => 'Vendor deleted successfully'], 200);
    }
}

