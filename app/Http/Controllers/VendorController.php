<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class VendorController extends Controller
{
    // Display a listing of vendors
    public function index()
    {
        return Vendor::all();
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


        // $validatedData = Validator::make($request->all(), [
        //     'vendor_name' => 'required|string|max:255',
        //     // 'company_name' => 'required|string|max:255',
        //     // 'phone_number' => 'required|numeric',
        //     // 'email' => 'required|string|email|max:255|unique:vendors',
        //     // 'billing_address' => 'required|string',
        //     // 'shipping_address' => 'required|string',
        //     ]);

        //     if ($validatedData->fails()) {
        //         return response()->json(['error' => $validatedData->errors()], 400);
        //     }

        $vendor = Vendor::create($validatedData);

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

