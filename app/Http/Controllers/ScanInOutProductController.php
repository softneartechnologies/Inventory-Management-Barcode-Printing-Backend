<?php

namespace App\Http\Controllers;

use App\Models\ScanInOutProduct;
use Illuminate\Http\Request;

class ScanInOutProductController extends Controller
{
    // List all scan records
    public function index()
    {
        $scanRecords = ScanInOutProduct::with(['product:id,product_name', 'employee:id,employee_name'])->get();

        $scanRecords = $scanRecords->map(function ($scanRecords) {
            return [
                'id' => $scanRecords->id,
                'product_id' => $scanRecords->product_id,
                'employee_id' => $scanRecords->employee_id,
                'in_out_date_time' => $scanRecords->in_out_date_time,
                'in_quantity' => $scanRecords->in_quantity,
                'out_quantity' => $scanRecords->out_quantity,
                'type' => $scanRecords->type,
                'product_name' => $scanRecords->product->product_name ?? null, // Move product_name outside
                'employee_name' => $scanRecords->product->employee->employee_name ?? null, // Ensure category exists
                'created_at' => $scanRecords->created_at,
                'updated_at' => $scanRecords->updated_at,
            ];
        });

        return response()->json($scanRecords, 200);
    }

    // Store a new scan record
    public function storeIn(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'employee_id' => 'required|exists:employees,id',
            'in_out_date_time' => 'required|date',
            'type' => 'required|in:in',
            'in_quantity' => 'required|integer|min:1'
        ]);

        $scanRecord = ScanInOutProduct::create($validated);

        return response()->json($scanRecord, 200);
    }

    public function storeOut(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'employee_id' => 'required|exists:employees,id',
            'in_out_date_time' => 'required|date',
            'type' => 'required|in:out',
            'out_quantity' => 'required|integer|min:1'
        ]);

        $scanRecord = ScanInOutProduct::create($validated);

        return response()->json($scanRecord, 200);
    }


    public function inventorySummaryReport(){

        $scanRecords = ScanInOutProduct::with(['product:id,product_name,sku,inventory_alert_threshold,commit_stock_check,opening_stock', 'vendor:id,vendor_name'])->get();

        $scanRecords = $scanRecords->map(function ($scanRecords) {
            return [
                'id' => $scanRecords->id,
                'product_id' => $scanRecords->product_id,
                'employee_id' => $scanRecords->employee_id,
                'in_out_date_time' => $scanRecords->in_out_date_time,
                'in_quantity' => $scanRecords->in_quantity,
                'out_quantity' => $scanRecords->out_quantity,
                'type' => $scanRecords->type,
                'product_name' => $scanRecords->product->product_name ?? null, // Move product_name outside
                'sku' => $scanRecords->product->sku ?? null, // Move product_name outside
                'inventory_alert_threshold' => $scanRecords->product->inventory_alert_threshold ?? null, // Move product_name outside
                'commit_stock_check' => $scanRecords->product->commit_stock_check ?? null, // Move product_name outside
                'opening_stock' => $scanRecords->product->opening_stock ?? null, // Move product_name outside
                'vendor_name' => $scanRecords->product->vendor->vendor_name ?? null, // Ensure category exists
                'created_at' => $scanRecords->created_at,
                'updated_at' => $scanRecords->updated_at,
            ];
        });

        return response()->json($scanRecords, 200);
    }

    // Get a single scan record
    public function show(ScanInOutProduct $scanInOutProduct)
    {
        return response()->json($scanInOutProduct, 200);
    }

    // Update a scan record
    public function update(Request $request, ScanInOutProduct $scanInOutProduct)
    {
        $validated = $request->validate([
            'product_id' => 'sometimes|exists:products,id',
            'employee_id' => 'sometimes|exists:employees,id',
            'in_out_date_time' => 'sometimes|date',
            'type' => 'sometimes|in:in,out',
            'quantity' => 'sometimes|integer|min:1'
        ]);

        $scanInOutProduct->update($validated);

        return response()->json($scanInOutProduct, 200);
    }

    // Delete a scan record
    public function destroy(ScanInOutProduct $scanInOutProduct)
    {
        $scanInOutProduct->delete();
        return response()->json(null, 204);
    }
}
