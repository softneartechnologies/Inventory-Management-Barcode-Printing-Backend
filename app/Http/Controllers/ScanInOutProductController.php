<?php

namespace App\Http\Controllers;

use App\Models\ScanInOutProduct;
use App\Models\Product;
use App\Models\Employee;
use Illuminate\Http\Request;

class ScanInOutProductController extends Controller
{
    // List all scan records
    // public function index()
    // {
    //     $scanRecords = ScanInOutProduct::with(['product:id,product_name,opening_stock', 'employee:id,employee_name', 'user:id,name'])->get();

    //     $scanRecords = $scanRecords->map(function ($scanRecords) {
    //         return [
    //             'id' => $scanRecords->id,
    //             'product_id' => $scanRecords->product_id,
    //             'issue_from_user_id' => $scanRecords->issue_from_user_id,
    //             'employee_id' => $scanRecords->employee_id,
    //             'in_out_date_time' => $scanRecords->in_out_date_time,
    //             'in_quantity' => $scanRecords->in_quantity,
    //             'out_quantity' => $scanRecords->out_quantity,
    //             'type' => $scanRecords->type,
    //             'product_name' => $scanRecords->product->product_name ?? null, // Move product_name outside
    //             'quantity' => $scanRecords->product->opening_stock ?? null, // Move product_name outside
    //             'issue_from_name' => $scanRecords->user->name,
    //             'employee_name' => $scanRecords->employee->employee_name ?? null,
    //             'created_at' => $scanRecords->created_at,
    //             'updated_at' => $scanRecords->updated_at,
    //         ];
    //     });

    //     return response()->json($scanRecords, 200);
    // }
    public function index()
    {
        $scanRecords = ScanInOutProduct::with([
            'product:id,product_name,sku,opening_stock',
            'employee:id,employee_name',
            'user:id,name','machine:id,name','workStation:id,name','department:id,name','location:id,name'
        ])->get();

        $scanRecords = $scanRecords->map(function ($scanRecords) {
            return [
                'id' => $scanRecords->id,
                'product_id' => $scanRecords->product_id,
                'in_out_date_time' => $scanRecords->in_out_date_time,
                'machine_name' => optional($scanRecords->machine)->name,
                'workStation_name' => optional($scanRecords->workStation)->name,
                'department_name' => optional($scanRecords->department)->name,
                'issue_from_name' => $scanRecords->user->name ?? null, 
                'employee_name' => $scanRecords->employee->employee_name ?? null,
                'location_name' => optional($scanRecords->location)->name,
                'issue_from_user_id' => $scanRecords->issue_from_user_id,
                'employee_id' => $scanRecords->employee_id,
                'in_quantity' => $scanRecords->in_quantity,
                'out_quantity' => $scanRecords->out_quantity,
                'type' => $scanRecords->type,
                'purpose' => $scanRecords->purpose,
                'product_name' => $scanRecords->product->product_name ?? null,
                'sku' => $scanRecords->product->sku ?? null,
                'quantity' => $scanRecords->product->opening_stock ?? null,
                'previous_stock' => $scanRecords->previous_stock,
                'total_current_stock' => $scanRecords->total_current_stock,
                'threshold' => $scanRecords->threshold,
                'comments' => $scanRecords->comments,
                'created_at' => $scanRecords->created_at,
                'updated_at' => $scanRecords->updated_at,
            ];
        });

        return response()->json($scanRecords, 200);
    }


    // Store a new scan record
     public function storeIn(Request $request)
    {
        $product = Product::find($request->product_id);

        if($request->purpose =="Return"){
            
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'issue_from_user_id' => 'required',
                'employee_id' => 'required|exists:employees,id',
                'location_id' => 'required',
                'in_out_date_time' => 'required|date',
                'type' => 'required|in:in',
                'purpose' => 'required',
                // 'department_id' => 'required',
                // 'work_station_id' => 'required',
                // 'machine_id' => 'required',
                'comments' => 'required',
                'in_quantity' => 'required|integer|min:1',
                'previous_stock' => 'required',
                'total_current_stock' => 'required',
                'threshold' => 'required',
            ]);

            
        }else {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'issue_from_user_id' => 'required',
                'employee_id' => 'required|exists:employees,id',
                'location_id' => 'required',
                'in_out_date_time' => 'required|date',
                'type' => 'required|in:in',
                'purpose' => 'required',
                'comments' => 'required',
                'in_quantity' => 'required|integer|min:1',
                'previous_stock' => 'required',
                'total_current_stock' => 'required',
                'threshold' => 'required',
            ]);

           
        }

        $validated['vendor_id'] = $product->vendor_id;
        $validated['category_id'] = $product->category_id;
        $scanRecord = ScanInOutProduct::create($validated);

        $quantity = $request->in_quantity;
        $productOpeningStock = $product->opening_stock + $quantity;

        $product->update(['opening_stock' => $productOpeningStock]);

        return response()->json($scanRecord, 200);
    }

    public function storeOut(Request $request)
    {

        $product = Product::find($request->product_id);

        if($request->purpose =="Maintenance Use"){
            
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'issue_from_user_id' => 'required',
                'employee_id' => 'required|exists:employees,id',
                'location_id' => 'required',
                'in_out_date_time' => 'required|date',
                'type' => 'required',
                'purpose' => 'required',
                'department_id' => 'required',
                'work_station_id' => 'required',
                'machine_id' => 'required',
                'comments' => 'required',
                'out_quantity' => 'required|integer|min:1',
                'previous_stock' => 'required',
                'total_current_stock' => 'required',
                'threshold' => 'required',
            ]);

            
        }else {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'issue_from_user_id' => 'required',
                'employee_id' => 'required|exists:employees,id',
                'location_id' => 'required',
                'in_out_date_time' => 'required|date',
                'type' => 'required',
                'purpose' => 'required',
                'comments' => 'required',
                'out_quantity' => 'required|integer|min:1',
                'previous_stock' => 'required',
                'total_current_stock' => 'required',
                'threshold' => 'required',
            ]);

            
        }

        $validated['vendor_id'] = $product->vendor_id;
        $validated['category_id'] = $product->category_id;
        $scanRecord = ScanInOutProduct::create($validated);

        $quantity = $request->out_quantity;
        $productOpeningStock = $product->opening_stock - $quantity;

        $product->update(['opening_stock' => $productOpeningStock]);

        return response()->json($scanRecord, 200);
    }


  

     public function inventorySummaryReport()
    {
        // $deleted ='1';
        // $scanRecords = ScanInOutProduct::with([
        //     'product:id,product_name,sku,inventory_alert_threshold,commit_stock_check,opening_stock,category_id',
        //     'product.category:id,name',
        //     'product.orders:id,product_id,quantity,deleted', // include orders
        //     'vendor:id,vendor_name',
        //     'employee:id,employee_name','user:id,name'
        // ])->get();
        
        //     $scanRecords = $scanRecords->map(function ($scanRecord) {
        //     $orderQuantity = $scanRecord->product->orders->sum('quantity') ?? 0; 
        //     $deletedOrder = $scanRecord->product->deleted;
        //     return [
        //         'id' => $scanRecord->id,
        //         'in_out_date_time' => $scanRecord->in_out_date_time,
        //         'in_quantity' => $scanRecord->in_quantity,
        //         'out_quantity' => $scanRecord->out_quantity,
        //         'type' => $scanRecord->type,
        //         'purpose' => $scanRecord->purpose,
        //         'product_id' => $scanRecord->product_id,
        //         'product_name' => $scanRecord->product->product_name ?? null,
        //         'category_name' => optional($scanRecord->product->category)->name ?? null,
        //         'employee_id' => $scanRecord->employee_id,
        //         'employee_name' => optional($scanRecord->employee)->employee_name ?? null,
        //         'issue_from_name' => $scanRecord->user->name ?? null,
        //         'sku' => $scanRecord->product->sku ?? null,
        //         'inventory_alert_threshold' => $scanRecord->product->inventory_alert_threshold ?? null,
        //         'commit_stock_check' => $scanRecord->product->commit_stock_check ?? null,
        //         'opening_stock' => $scanRecord->product->opening_stock ?? null,
        //         'vendor_name' => optional($scanRecord->vendor)->vendor_name ?? null,
        //         'order_quantity' => $orderQuantity,
        //         'deleted' =>$deletedOrder,
        //         'created_at' => $scanRecord->created_at,
        //         'updated_at' => $scanRecord->updated_at,
        //     ];
        // });

        $scanRecords = ScanInOutProduct::with([
            'product:id,product_name,sku,opening_stock',
            'employee:id,employee_name',
            'user:id,name','category:id,name','location:id,name','product.orders:id,product_id,quantity,deleted',
        ])->orderBy('id','desc')->get();

        $scanRecords = $scanRecords->map(function ($scanRecords) {
            $orderQuantity = $scanRecords->product->orders->sum('quantity') ?? 0; 
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
                'order_quantity' => $orderQuantity,
                'comments' => $scanRecords->comments,
                'created_at' => $scanRecords->created_at,
                'updated_at' => $scanRecords->updated_at,
            ];
        });
    
        return response()->json($scanRecords, 200);
    }
    
    public function employeeHistory($id)
    {
        $scanRecords = ScanInOutProduct::with([
            'product:id,product_name,sku,inventory_alert_threshold,commit_stock_check,opening_stock,category_id',
            'product.category:id,name',
            'product.orders:id,product_id,quantity', // include orders
            'vendor:id,vendor_name',
            'employee:id,employee_name','user:id,name','location:id,name','machine:id,name','workStation:id,name','department:id,name'
        ])->where('employee_id',$id)->orderBy('id','desc')->get();
        
        
    
        $scanRecords = $scanRecords->map(function ($scanRecord) {
            $orderQuantity = $scanRecord->product->orders->sum('quantity') ?? 0;
    
            return [
                
                'id' => $scanRecord->id,
                'in_out_date_time' => $scanRecord->in_out_date_time,
                'product_name' => $scanRecord->product->product_name ?? null,
                'sku' => $scanRecord->product->sku ?? null,
                'category_name' => optional($scanRecord->product->category)->name ?? null,
                'machine_name' => optional($scanRecord->machine)->name,
                'workStation_name' => optional($scanRecord->workStation)->name,
                'department_name' => optional($scanRecord->department)->name,
                'employee_name' => optional($scanRecord->employee)->employee_name ?? null,
                'issue_from_name' => $scanRecord->user->name ?? null,
                'location' => $scanRecord->location->name ?? null,
                'previous_stock' => $scanRecord->previous_stock,
                'in_quantity' => $scanRecord->in_quantity,
                'out_quantity' => $scanRecord->out_quantity,
                'total_current_stock' => $scanRecord->total_current_stock,
                'inventory_alert_threshold' => $scanRecord->threshold ?? null,
                'purpose' => $scanRecord->purpose,
                'comments' => $scanRecord->comments,
                'type' => $scanRecord->type,
                'product_id' => $scanRecord->product_id,
                'employee_id' => $scanRecord->employee_id,
                'commit_stock_check' => $scanRecord->product->commit_stock_check ?? null,
                'opening_stock' => $scanRecord->product->opening_stock ?? null,
                'vendor_name' => optional($scanRecord->vendor)->vendor_name ?? null,
                'order_quantity' => $orderQuantity,
                'created_at' => $scanRecord->created_at,
                'updated_at' => $scanRecord->updated_at,
            ];
        });
    
     $employee = Employee::where('id',$id)->first();
        $employeeDetails = [
        'employee_id'       => $employee->id,
        'employee_name'     => $employee->employee_name,
        'department'        => $employee->department,
        'work_station'      => $employee->work_station,
        'scanRecords' =>$scanRecords,
         ];
         
       
        return response()->json($employeeDetails, 200);
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

    public function employeeIssuanceHistory(){
        
        // $scanRecords = ScanInOutProduct::with(['product:id,product_name', 'employee:id,employee_name','user:id,name'])->orderBy('id','desc')->get();

        // $scanRecords = $scanRecords->map(function ($scanRecords) {
        //     return [
        //         'id' => $scanRecords->id,
        //         'in_out_date_time' => $scanRecords->in_out_date_time,
        //         'employee_id' => $scanRecords->employee_id,
        //         'employee_name' => $scanRecords->employee->employee_name ?? null, // Ensure category exists
        //         'issue_from_name' => $scanRecords->user->name ?? null,
        //         'product_name' => $scanRecords->product->product_name ?? null, // Move product_name outside
        //         'in_quantity' => $scanRecords->in_quantity,
        //         'out_quantity' => $scanRecords->out_quantity,
        //         'type' => $scanRecords->type,
        //         'purpose' => $scanRecords->purpose,
        //         'comments' => $scanRecords->comments,
        //         'product_id' => $scanRecords->product_id,
        //         'created_at' => $scanRecords->created_at,
        //         'updated_at' => $scanRecords->updated_at,
        //     ];
        // });

        $scanRecords = ScanInOutProduct::with([
            'product:id,product_name,sku,opening_stock',
            'employee:id,employee_name',
            'user:id,name','category:id,name','location:id,name'
        ])->orderBy('id','desc')->get();

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

        return response()->json($scanRecords, 200);
    }


    public function productScaned($sku){
        
        $product = Product::where('sku',$sku)->get();

        return response()->json(['product'=>$product], 200);
    }
}
