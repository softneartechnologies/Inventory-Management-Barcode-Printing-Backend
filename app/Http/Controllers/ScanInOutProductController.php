<?php

namespace App\Http\Controllers;

use App\Models\ScanInOutProduct;
use App\Models\Product;
use App\Models\Employee;
use App\Models\Stock;
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
    //  public function storeIn(Request $request)
    // {
    //     $product = Product::find($request->product_id);

    //     if($request->purpose =="Return"){
            
    //         $validated = $request->validate([
    //             'product_id' => 'required|exists:products,id',
    //             'issue_from_user_id' => 'required',
    //             'employee_id' => 'required|exists:employees,id',
    //             'location_id' => 'required',
    //             'in_out_date_time' => 'required|date',
    //             'type' => 'required|in:in',
    //             'purpose' => 'required',
    //             // 'department_id' => 'required',
    //             // 'work_station_id' => 'required',
    //             // 'machine_id' => 'required',
    //             'comments' => 'required',
    //             'in_quantity' => 'required|integer|min:1',
    //             'previous_stock' => 'required',
    //             'total_current_stock' => 'required',
    //             'threshold' => 'required',
    //         ]);

            
    //     }else {
    //         $validated = $request->validate([
    //             'product_id' => 'required|exists:products,id',
    //             'issue_from_user_id' => 'required',
    //             'employee_id' => 'required|exists:employees,id',
    //             'location_id' => 'required',
    //             'in_out_date_time' => 'required|date',
    //             'type' => 'required|in:in',
    //             'purpose' => 'required',
    //             'comments' => 'required',
    //             'in_quantity' => 'required|integer|min:1',
    //             'previous_stock' => 'required',
    //             'total_current_stock' => 'required',
    //             'threshold' => 'required',
    //         ]);

           
    //     }

    //     $validated['vendor_id'] = $product->vendor_id?? null;
    //     $validated['category_id'] = $product->category_id?? null;
    //     $scanRecord = ScanInOutProduct::create($validated);

    //     // $quantity = $request->in_quantity;
    //     // $productOpeningStock = $product->opening_stock + $quantity;

    //     // $product->update(['opening_stock' => $productOpeningStock]);

    //     $quantity = $request->in_quantity;
    //     $productOpeningStock = $product->opening_stock + $quantity;
    //     $locationIds = json_decode($product->location_id); 
    //     $quantities = json_decode($product->quantity); 
    //     $pdate = array_combine($locationIds, $quantities);
    //     $rlocationId = $request->location_id;
    //     $pdate[$rlocationId] = $pdate[$rlocationId] + $quantity;
    //     $updatedQuantities = [];
    //     foreach ($locationIds as $lid) {
    //         $updatedQuantities[] = $pdate[$lid];
    //     }

    //     $totalQuantity = array_sum($updatedQuantities);
    //     // Step 6: Update the product
    //     $product->update([
    //         'opening_stock' => $productOpeningStock,
    //         'quantity' => json_encode($updatedQuantities)
    //     ]);

    //        $product_location = Stock::where('product_id', $request->product_id)
    //             ->where('location_id', $request->location_id)
    //             ->first();

           
    //             $currentStock = $product_location->current_stock;
               
    //             $newStock = $currentStock + $quantity;
                   
    //             $stockData = [
    //                 'current_stock' => $newStock,
    //                 'new_stock' => $newStock,
    //             ];

    //             $product_location->update($stockData);
                

    //     return response()->json($scanRecord, 200);
    // }

    public function storeIn(Request $request)
{
    $product = Product::find($request->product_id);

    if ($request->purpose == "Return") {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'issue_from_user_id' => 'required',
            'employee_id' => 'required|exists:employees,id',
            'location_id' => 'required',
            // 'in_out_date_time' => 'required|date',
            'type' => 'required|in:in',
            'purpose' => 'required',
            'comments' => 'required',
            'in_quantity' => 'required|integer|min:1',
            'previous_stock' => 'required',
            'total_current_stock' => 'required',
            'threshold' => 'required',
        ]);
    } else {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'issue_from_user_id' => 'required',
            'employee_id' => 'required|exists:employees,id',
            'location_id' => 'required',
            // 'in_out_date_time' => 'required|date',
            'type' => 'required|in:in',
            'purpose' => 'required',
            'comments' => 'required',
            'in_quantity' => 'required|integer|min:1',
            'previous_stock' => 'required',
            'total_current_stock' => 'required',
            'threshold' => 'required',
        ]);
    }

    $validated['vendor_id']   = $product->vendor_id ?? null;
    $validated['category_id'] = $product->category_id ?? null;

    // Save scan record
    $scanRecord = ScanInOutProduct::create($validated);

    // ========== STOCK CALCULATION ==========
    $quantity            = $request->in_quantity;
    $productOpeningStock = $product->opening_stock + $quantity;

    // Decode location & quantities safely
    $locationIds = json_decode($product->location_id, true) ?: [];
    $quantities  = json_decode($product->quantity, true) ?: [];
    $per_unit_cost  = json_decode($product->per_unit_cost, true) ?: [];
    // $total_cost  = json_decode($product->total_cost, true) ?: [];

    if (is_array($per_unit_cost) && isset($per_unit_cost[0]) && is_string($per_unit_cost[0])) {
    $per_unit_cost = explode(',', $per_unit_cost[0]);
}

// अब key => value वाला array बना दो
$per_unit_cost = array_values($per_unit_cost); // reindex just in case

    if (!empty($locationIds) && !empty($quantities) && count($locationIds) === count($quantities)) {
        $pdate = array_combine($locationIds, $quantities);
    } else {
        $pdate = array_fill_keys($locationIds, 0);
    }


//     if (!empty($quantities) && !empty($per_unit_cost) && count($quantities) === count($per_unit_cost)) {
//         $pudate = array_combine($quantities, $per_unit_cost);
//     } else {
//         $pudate = array_fill_keys($quantities, 0);
//     }

//    $result = [];
//     foreach ($pudate as $key => $value) {
//         $result[$key] = $key * $value;
//     }

if (!empty($quantities) && !empty($per_unit_cost) && count($quantities) === count($per_unit_cost)) {
    $pudate = array_combine($quantities, $per_unit_cost);
} else {
    $pudate = array_fill_keys($quantities, 0);
}

$result = [];
foreach ($pudate as $key => $value) {
    // Ensure both are numeric before multiplication
    $qty = floatval($key);
    $cost = floatval($value);

    $result[$key] = $qty * $cost;
}

// ✅ values को string में convert करके फिर JSON encode करें

    $string = json_encode(array_map('strval', array_values($result)));

    $rlocationId = $request->location_id;

    // Safely add stock to the right location
    $pdate[$rlocationId] = ($pdate[$rlocationId] ?? 0) + $quantity;

    // Build updated quantities
    $updatedQuantities = [];
    foreach ($locationIds as $lid) {
        $updatedQuantities[] = $pdate[$lid];
    }
// print_r(json_encode($updatedQuantities));die;

    $totalQuantity = array_sum($updatedQuantities);

    // Update Product table
    $product->update([
        'opening_stock' => $productOpeningStock,
        'quantity'      => json_encode($updatedQuantities),
        'total_cost'    => $string,
    ]);

    // ========== UPDATE STOCKS TABLE ==========
    $product_location = Stock::where('product_id', $request->product_id)
        ->where('location_id', $request->location_id)
        ->first();

    if ($product_location) {
        // Update existing stock
        $newStock = $product_location->current_stock + $quantity;
        $quantitys = $product_location->quantity + $quantity;
        $per_unit_cost = $product_location->per_unit_cost;
        $total_cost = $per_unit_cost * $quantitys;
        
        $product_location->update([
            'current_stock' => $newStock,
            'new_stock'     => $newStock,
            'quantity'     => $quantitys,
            'total_cost'     => $total_cost,
        ]);
    } else {
        // Create new stock record if not exists
        Stock::create([
            'product_id'    => $request->product_id,
            'location_id'   => $request->location_id,
            'current_stock' => $quantity,
            'new_stock'     => $quantity,
        ]);
    }

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

        $validated['vendor_id'] = $product->vendor_id?? null;
        $validated['category_id'] = $product->category_id?? null;
        $scanRecord = ScanInOutProduct::create($validated);

        $quantity = $request->out_quantity;
        $productOpeningStock = $product->opening_stock - $quantity;
        $locationIds = json_decode($product->location_id); 
        $quantities = json_decode($product->quantity); 

         $per_unit_cost  = json_decode($product->per_unit_cost, true) ?: [];
    // $total_cost  = json_decode($product->total_cost, true) ?: [];

    if (is_array($per_unit_cost) && isset($per_unit_cost[0]) && is_string($per_unit_cost[0])) {
    $per_unit_cost = explode(',', $per_unit_cost[0]);
        }
        $per_unit_cost = array_values($per_unit_cost); 

        if (!empty($quantities) && !empty($per_unit_cost) && count($quantities) === count($per_unit_cost)) {
        $pudate = array_combine($quantities, $per_unit_cost);
    } else {
        $pudate = array_fill_keys($quantities, 0);
    }

   $result = [];
    foreach ($pudate as $key => $value) {
        $result[$key] = $key * $value;
    }

// ✅ values को string में convert करके फिर JSON encode करें

    $string = json_encode(array_map('strval', array_values($result)));



        $pdate = array_combine($locationIds, $quantities);
        $rlocationId = $request->location_id;
        $pdate[$rlocationId] = $pdate[$rlocationId] - $quantity;
        $updatedQuantities = [];
        foreach ($locationIds as $lid) {
            $updatedQuantities[] = $pdate[$lid];
        }

        $totalQuantity = array_sum($updatedQuantities);
        // Step 6: Update the product
        $product->update([
            'opening_stock' => $productOpeningStock,
            'quantity' => json_encode($updatedQuantities),
             'total_cost'    => $string,
        ]);


        $product_location = Stock::where('product_id', $request->product_id)
                ->where('location_id', $request->location_id)
                ->first();

                $currentStock = $product_location->current_stock;
                $newStock = $currentStock - $quantity;
                   $per_unit_cost = $product_location->per_unit_cost;
                    $total_cost = $per_unit_cost * $newStock;

                $stockData = [
                    'current_stock' => $newStock,
                    'new_stock' => $newStock,
                    'quantity'     => $newStock,
                    'total_cost'     => $total_cost,
                ];

                $product_location->update($stockData);

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
            'product:id,product_name,sku,opening_stock,model,manufacturer',
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
                'model' => $scanRecords->product->model?? null,
                'manufacturer' => $scanRecords->product->manufacturer?? null,
                'category' => $scanRecords->category->name ?? null,
                'location' => $scanRecords->location->name ?? null,
                'quantity' => $scanRecords->product->opening_stock ?? null,
                // 'quantity' => $scanRecords->total_current_stock ?? null,
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

    // public function employeeIssuanceHistory(){
        
    //     // $scanRecords = ScanInOutProduct::with(['product:id,product_name', 'employee:id,employee_name','user:id,name'])->orderBy('id','desc')->get();

    //     // $scanRecords = $scanRecords->map(function ($scanRecords) {
    //     //     return [
    //     //         'id' => $scanRecords->id,
    //     //         'in_out_date_time' => $scanRecords->in_out_date_time,
    //     //         'employee_id' => $scanRecords->employee_id,
    //     //         'employee_name' => $scanRecords->employee->employee_name ?? null, // Ensure category exists
    //     //         'issue_from_name' => $scanRecords->user->name ?? null,
    //     //         'product_name' => $scanRecords->product->product_name ?? null, // Move product_name outside
    //     //         'in_quantity' => $scanRecords->in_quantity,
    //     //         'out_quantity' => $scanRecords->out_quantity,
    //     //         'type' => $scanRecords->type,
    //     //         'purpose' => $scanRecords->purpose,
    //     //         'comments' => $scanRecords->comments,
    //     //         'product_id' => $scanRecords->product_id,
    //     //         'created_at' => $scanRecords->created_at,
    //     //         'updated_at' => $scanRecords->updated_at,
    //     //     ];
    //     // });

    //     $scanRecords = ScanInOutProduct::with([
    //         'product:id,product_name,sku,opening_stock',
    //         'employee:id,employee_name',
    //         'user:id,name','category:id,name','location:id,name'
    //     ])->orderBy('id','desc')->get();

    //     $scanRecords = $scanRecords->map(function ($scanRecords) {
    //         return [
    //             'id' => $scanRecords->id,
    //             'in_out_date_time' => $scanRecords->in_out_date_time,
    //             'product_id' => $scanRecords->product_id,
    //             'product_name' => $scanRecords->product->product_name ?? null,
    //             'sku' => $scanRecords->product->sku ?? null,
    //             'category' => $scanRecords->category->name ?? null,
    //             'location' => $scanRecords->location->name ?? null,
    //             'quantity' => $scanRecords->product->opening_stock ?? null,
    //             'issue_from_name' => $scanRecords->user->name ?? null, 
    //             'employee_name' => $scanRecords->employee->employee_name ?? null,
    //             'issue_from_user_id' => $scanRecords->issue_from_user_id,
    //             'employee_id' => $scanRecords->employee_id,
    //             'in_quantity' => $scanRecords->in_quantity,
    //             'out_quantity' => $scanRecords->out_quantity,
    //             'previous_stock' => $scanRecords->previous_stock,
    //             'total_current_stock' => $scanRecords->total_current_stock,
    //             'threshold' => $scanRecords->threshold,
    //             'type' => $scanRecords->type,
    //             'purpose' => $scanRecords->purpose,
    //             'comments' => $scanRecords->comments,
    //             'created_at' => $scanRecords->created_at,
    //             'updated_at' => $scanRecords->updated_at,
    //         ];
    //     });

    //     return response()->json($scanRecords, 200);
    // }

//     public function employeeIssuanceHistory(Request $request)
// {
//     // Default values
//     $sortBy = $request->get('sort_by', 'id');       // Default sort column
//     $sortOrder = $request->get('sort_order', 'desc'); // Default sort order
//     $limit = $request->get('limit', null);          // Default = all records
//     $search = $request->get('search', null);

//     $query = ScanInOutProduct::with([
//         'product:id,product_name,sku,opening_stock',
//         'employee:id,employee_name',
//         'user:id,name',
//         'category:id,name',
//         'location:id,name'
//     ]);

//     // Searching
//     if (!empty($search)) {
//         $query->where(function ($q) use ($search) {
//             $q->whereHas('product', function ($q2) use ($search) {
//                 $q2->where('product_name', 'like', "%{$search}%")
//                    ->orWhere('sku', 'like', "%{$search}%");
//             })
//             ->orWhereHas('employee', function ($q2) use ($search) {
//                 $q2->where('employee_name', 'like', "%{$search}%");
//             })
//             ->orWhereHas('user', function ($q2) use ($search) {
//                 $q2->where('name', 'like', "%{$search}%");
//             })
//             ->orWhereHas('category', function ($q2) use ($search) {
//                 $q2->where('name', 'like', "%{$search}%");
//             })
//             ->orWhereHas('location', function ($q2) use ($search) {
//                 $q2->where('name', 'like', "%{$search}%");
//             })
//             ->orWhere('purpose', 'like', "%{$search}%")
//             ->orWhere('comments', 'like', "%{$search}%");
//         });
//     }

//     // Sorting
//     $query->orderBy($sortBy, $sortOrder);

//     // Pagination / All
//     if (!empty($limit) && is_numeric($limit)) {
//         $scanRecords = $query->paginate($limit);
//     } else {
//         $scanRecords = $query->get();
//     }

//     // Map the data
//     $scanRecords->transform(function ($scanRecords) {
//         return [
//             'id' => $scanRecords->id,
//             'in_out_date_time' => $scanRecords->in_out_date_time,
//             'product_id' => $scanRecords->product_id,
//             'product_name' => $scanRecords->product->product_name ?? null,
//             'sku' => $scanRecords->product->sku ?? null,
//             'category' => $scanRecords->category->name ?? null,
//             'location' => $scanRecords->location->name ?? null,
//             'quantity' => $scanRecords->product->opening_stock ?? null,
//             'issue_from_name' => $scanRecords->user->name ?? null,
//             'employee_name' => $scanRecords->employee->employee_name ?? null,
//             'issue_from_user_id' => $scanRecords->issue_from_user_id,
//             'employee_id' => $scanRecords->employee_id,
//             'in_quantity' => $scanRecords->in_quantity,
//             'out_quantity' => $scanRecords->out_quantity,
//             'previous_stock' => $scanRecords->previous_stock,
//             'total_current_stock' => $scanRecords->total_current_stock,
//             'threshold' => $scanRecords->threshold,
//             'type' => $scanRecords->type,
//             'purpose' => $scanRecords->purpose,
//             'comments' => $scanRecords->comments,
//             'created_at' => $scanRecords->created_at,
//             'updated_at' => $scanRecords->updated_at,
//         ];
//     });

//     return response()->json($scanRecords, 200);
// }

public function employeeIssuanceHistory(Request $request)
{
    // Default values
    $sortBy = $request->get('sort_by', 'id');      
    $sortOrder = $request->get('sort_order', 'desc'); 
    $limit = $request->get('per_page', null);          
    $search = $request->get('search', null);

    $query = ScanInOutProduct::with([
        'product:id,product_name,sku,opening_stock',
        'employee:id,employee_name',
        'user:id,name',
        'category:id,name',
        'location:id,name'
    ]);

    // Searching
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->whereHas('product', function ($q2) use ($search) {
                $q2->where('product_name', 'like', "%{$search}%")
                   ->orWhere('sku', 'like', "%{$search}%");
            })
            ->orWhereHas('employee', function ($q2) use ($search) {
                $q2->where('employee_name', 'like', "%{$search}%");
            })
            ->orWhereHas('user', function ($q2) use ($search) {
                $q2->where('name', 'like', "%{$search}%");
            })
            ->orWhereHas('category', function ($q2) use ($search) {
                $q2->where('name', 'like', "%{$search}%");
            })
            ->orWhereHas('location', function ($q2) use ($search) {
                $q2->where('name', 'like', "%{$search}%");
            })
            ->orWhere('purpose', 'like', "%{$search}%")
            ->orWhere('comments', 'like', "%{$search}%");
        });
    }

      // ✅ Filter
    // ✅ Filter
if ($request->filled('start_date') || $request->filled('end_date')) {
    $start_date  = $request->start_date;
    $end_date    = $request->end_date;

    $query->where(function ($q) use ($start_date, $end_date) {

        // ✅ Date range filter
        if (!empty($start_date) && !empty($end_date)) {
            $q->whereBetween('created_at', [$start_date, $end_date]);
        } elseif (!empty($start_date)) {
            $q->whereDate('created_at', '>=', $start_date);
        } elseif (!empty($end_date)) {
            $q->whereDate('created_at', '<=', $end_date);
        }
    });
}

    // ✅ Handle Sorting (only allow safe columns)
    $allowedSorts = ['id', 'in_out_date_time', 'product_id', 'employee_id', 'issue_from_user_id', 'created_at'];

    if (in_array($sortBy, $allowedSorts)) {
        $query->orderBy($sortBy, $sortOrder);
    } elseif ($sortBy === 'product_name') {
        $query->orderBy(
            \App\Models\Product::select('product_name')
                ->whereColumn('products.id', 'scan_in_out_products.product_id'),
            $sortOrder
        );
    } elseif ($sortBy === 'employee_name') {
        $query->orderBy(
            \App\Models\Employee::select('employee_name')
                ->whereColumn('employees.id', 'scan_in_out_products.employee_id'),
            $sortOrder
        );
    } else {
        // Default fallback
        $query->orderBy('id', 'desc');
    }

    // Pagination / All
    if (!empty($limit) && is_numeric($limit)) {
        $scanRecords = $query->paginate($limit);
    } else {
        $scanRecords = $query->get();
    }

    // Map the data
    $scanRecords->transform(function ($scanRecords) {
        return [
            'id' => $scanRecords->id,
            'in_out_date_time' => $scanRecords->in_out_date_time,
            'product_id' => $scanRecords->product_id,
            'product_name' => $scanRecords->product->product_name ?? null,
            'sku' => $scanRecords->product->sku ?? null,
            'category' => $scanRecords->category->name ?? null,
            'location' => $scanRecords->location->name ?? null,
            // 'quantity' => $scanRecords->product->opening_stock ?? null,
            'quantity' => $scanRecords->total_current_stock ?? null,
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
