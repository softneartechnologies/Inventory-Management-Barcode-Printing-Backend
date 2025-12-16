<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Location;
use App\Models\Stock;
use App\Models\ScanInOutProduct;
use App\Models\Category;
use App\Models\CurrencySetting;
// use App\Models\WorkStation;
use App\Models\Workstation;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
//         if ($request->filled('start_date') || $request->filled('end_date')) {
    
//             $start_date  = $request->start_date;
//             $end_date    = $request->end_date;

//             $query->where(function ($q) use ($start_date, $end_date) {
        
      

//         // ✅ Date range filter
//         if (!empty($start_date) && !empty($end_date)) {
//             $q->whereBetween('created_at', [$start_date, $end_date]);
//         } elseif (!empty($start_date)) {
//             $q->whereDate('created_at', '>=', $start_date);
//         } elseif (!empty($end_date)) {
//             $q->whereDate('created_at', '<=', $end_date);
//         }
//     });
// }


//         $productCount = Product::count();
//         $employeeUsingProduct = ScanInOutProduct::count();
       

//         $inventory_alert = Product::select('id','product_name','sku','opening_stock','location_id','inventory_alert_threshold',DB::raw("'Warning' as status"))->where('opening_stock', '<', DB::raw('inventory_alert_threshold'))->get();
//         $low_stock_alert = count($inventory_alert);




// ===============================
// ✅ Base Product Query
$productQuery = Product::query();

// ✅ Date filter
if ($request->filled('start_date') || $request->filled('end_date')) {
    $start_date  = $request->start_date;
    $end_date    = $request->end_date;

    $productQuery->where(function ($q) use ($start_date, $end_date) {
        if (!empty($start_date) && !empty($end_date)) {
            $q->whereBetween('created_at', [$start_date, $end_date]);
        } elseif (!empty($start_date)) {
            $q->whereDate('created_at', '>=', $start_date);
        } elseif (!empty($end_date)) {
            $q->whereDate('created_at', '<=', $end_date);
        }
    });
}

// 👉 Final product count with filters
$totalproductCount = $productQuery->count();

// return $productCount;
// ===============================
// ✅ Employee ScanInOutProduct Query
$employeeQuery = ScanInOutProduct::query();

// Apply date filter
if ($request->filled('start_date') || $request->filled('end_date')) {
    $employeeQuery->where(function ($q) use ($start_date, $end_date) {
        if (!empty($start_date) && !empty($end_date)) {
            $q->whereBetween('created_at', [$start_date, $end_date]);
        } elseif (!empty($start_date)) {
            $q->whereDate('created_at', '>=', $start_date);
        } elseif (!empty($end_date)) {
            $q->whereDate('created_at', '<=', $end_date);
        }
    });
}


if ($request->filled('category') || $request->filled('status')) {
    $employeeQuery->whereHas('product', function ($q) use ($request) {
        if ($request->filled('category')) {
            $q->where('category_id', $request->category);
        }
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }
    });
}

// 👉 Final employee count
// $employeeUsingProduct = $employeeQuery->count();
$employeeUsingProduct = Employee::count();


// ===============================
// ✅ Inventory alert with same filters
$inventory_alert = $productQuery->clone() // same filters reuse
    ->select(
        'id',
        'product_name',
        'sku',
        'opening_stock',
        'location_id',
        'inventory_alert_threshold',
        DB::raw("'Warning' as status")
    )
    ->where('opening_stock', '<', DB::raw('inventory_alert_threshold'))
    ->get();

$low_stock_alert = $inventory_alert->count();


//  total count filter date


        // $startDate = Carbon::now()->subMonths(5)->startOfMonth();
        // $endDate = Carbon::now()->endOfMonth();
        
        // // Fetch raw data
        // $monthlyCountsRaw = ScanInOutProduct::select(
        //         DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
        //         'type',
        //         DB::raw("COUNT(*) as total")
        //     )
        //     ->whereIn('type', ['in', 'out'])
        //     ->whereBetween('created_at', [$startDate, $endDate])
        //     ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"), 'type')
        //     ->orderBy('month')
        //     ->get();
        
        // // Build base structure
        // $allMonths = collect();
        // $current = $startDate->copy();
        // while ($current <= $endDate) {
        //     $allMonths->put($current->format('Y-m'), ['in' => 0, 'out' => 0]);
        //     $current->addMonth();
        // }
        
        // foreach ($monthlyCountsRaw as $row) {
        //     $month = $row->month;
        //     $type = $row->type;
        //     $total = $row->total;
        
        //     $data = $allMonths->get($month);
        //     $data[$type] = $total;
        //     $allMonths->put($month, $data);
        // }


        
    // $startDate = $request->filled('start_date') 
    //     ? Carbon::parse($request->start_date)->startOfMonth()
    //     : Carbon::now()->subMonths(5)->startOfMonth();

    // $endDate = $request->filled('end_date') 
    //     ? Carbon::parse($request->end_date)->endOfMonth()
    //     : Carbon::now()->endOfMonth();

    // // Fetch raw data
    // $monthlyCountsRaw = ScanInOutProduct::select(
    //         DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
    //         'type',
    //         DB::raw("COUNT(*) as total")
    //     )
    //     ->whereIn('type', ['in', 'out'])
    //     ->whereBetween('created_at', [$startDate, $endDate])
    //     ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"), 'type')
    //     ->orderBy('month')
    //     ->get();

    // // Build base structure (fill missing months with 0)
    // $allMonths = collect();
    // $current = $startDate->copy();
    // while ($current <= $endDate) {
    //     $allMonths->put($current->format('Y-m'), ['in' => 0, 'out' => 0]);
    //     $current->addMonth();
    // }

    // foreach ($monthlyCountsRaw as $row) {
    //     $month = $row->month;
    //     $type  = $row->type;
    //     $total = $row->total;

    //     $data = $allMonths->get($month);
    //     $data[$type] = $total;
    //     $allMonths->put($month, $data);

       
    // }

  





    $startDate = $request->filled('start_date') 
    ? Carbon::parse($request->start_date)->startOfMonth()
    : Carbon::now()->subMonths(5)->startOfMonth();

$endDate = $request->filled('end_date') 
    ? Carbon::parse($request->end_date)->endOfMonth()
    : Carbon::now()->endOfMonth();

/*
|--------------------------------------------------------------------------
| Fetch raw data (month, type, product)
|--------------------------------------------------------------------------
*/
// $monthlyRaw = ScanInOutProduct::with('product')->select(
//         DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
//         'type',
//         'product_id'
//     )
//     ->whereIn('type', ['in', 'out'])
//     ->whereBetween('created_at', [$startDate, $endDate])
//     ->orderBy('month')
//     ->get();

$monthlyRaw = ScanInOutProduct::join('products', 'products.id', '=', 'scan_in_out_products.product_id')
    ->select(
        DB::raw("DATE_FORMAT(scan_in_out_products.created_at, '%Y-%m') as month"),
        'scan_in_out_products.type',
        'scan_in_out_products.product_id',
        'products.*'
    )
    ->whereIn('scan_in_out_products.type', ['in', 'out'])
    ->whereBetween('scan_in_out_products.created_at', [$startDate, $endDate])
    ->orderBy('month')
    ->get();

/*
|--------------------------------------------------------------------------
| Build base structure (same as your code)
|--------------------------------------------------------------------------
*/
$allMonths = collect();
$current = $startDate->copy();

while ($current <= $endDate) {
    $allMonths->put($current->format('Y-m'), [
        'in' => 0,
        'out' => 0,
        'in_product_list' => [],
        'out_product_list' => []
    ]);
    $current->addMonth();
}

/*
|--------------------------------------------------------------------------
| Fill month-wise data
|--------------------------------------------------------------------------
*/
$grouped = $monthlyRaw->groupBy(['month', 'type', 'product_id']);

foreach ($grouped as $month => $types) {

    if (!$allMonths->has($month)) {
        continue;
    }

    $monthData = $allMonths->get($month);

    foreach ($types as $type => $products) {

        foreach ($products as $productId => $rows) {

            $qty = $rows->count();

            if ($type === 'in') {
                $monthData['in'] += $qty;
                $monthData['in_product_list'] = $rows
                ;
            }

            if ($type === 'out') {
                $monthData['out'] += $qty;
                $monthData['out_product_list']= $rows
                ;
            }
        }
    }

    $allMonths->put($month, $monthData);
}


    // return response()->json([
    //     'start_date' => $startDate->toDateString(),
    //     'end_date'   => $endDate->toDateString(),
    //     'data'       => $allMonths
    // ]);

        
// stock filter date

            //     $total = Product::count();
            //     $returnable = Product::where('returnable', 1)->count();
            //     $nonReturnable = $total - $returnable;
            //     $returnablePercent = $total > 0 ? round(($returnable / $total) * 100, 2) : 0;
            //     $nonReturnablePercent = $total > 0 ? round(($nonReturnable / $total) * 100, 2) : 0;
            //     $returnableNonReturnableItems = [
            //         'total_products' => $total,
            //         'returnable' => [
            //             'count' => $returnable,
            //             'percent' => $returnablePercent
            //         ],
            //         'non_returnable' => [
            //             'count' => $nonReturnable,
            //             'percent' => $nonReturnablePercent
            //         ]
            //     ];

            //    $categories_list = Category::orderBy('id', 'desc')->get();

            // $products = Product::with('category:id,name', 'vendor:id,vendor_name', 'sub_category:id,name')
            //     ->orderBy('id', 'desc')
            //     ->get();

            // $totalProducts = $products->count();

            // $categoryStats = $totalProducts > 0
            //     ? $products->groupBy('category.id')->map(function ($items, $categoryId) use ($totalProducts) {
            //         $count = $items->count();
            //         $percentage = round(($count / $totalProducts) * 100, 2);

            //         return [
            //             'category_id' => $categoryId,
            //             'category_name' => optional($items->first()->category)->name,
            //             'product_count' => $count,
            //             'percentage' => $percentage,
            //         ];
            //     })->values()
            //     : collect();

            // $uniqueCategoryCount = $categoryStats->toArray();
            // $productCount = $totalProducts;

            // $employeeUsingProduct = 0; // Define as needed
            // $returnableNonReturnableItems = []; // Define as needed

            // $scanRecords = ScanInOutProduct::with([
            //         'product:id,product_name,sku,opening_stock',
            //         'employee:id,employee_name',
            //         'user:id,name'
            //     ])->get();

            // $scanRecords = $scanRecords->map(function ($scanRecords) {
            //     return [
            //         'id' => $scanRecords->id,
            //         'product_id' => $scanRecords->product_id,
            //         'issue_from_user_id' => $scanRecords->issue_from_user_id,
            //         'employee_id' => $scanRecords->employee_id,
            //         'in_out_date_time' => $scanRecords->in_out_date_time,
            //         'in_quantity' => $scanRecords->in_quantity,
            //         'out_quantity' => $scanRecords->out_quantity,
            //         'type' => $scanRecords->type,
            //         'purpose' => $scanRecords->purpose,
            //         'product_name' => $scanRecords->product->product_name ?? null,
            //         'sku' => $scanRecords->product->sku ?? null,
            //         'quantity' => $scanRecords->product->opening_stock ?? null,
            //         'issue_from_name' => $scanRecords->user->name ?? null, 
            //         'employee_name' => $scanRecords->employee->employee_name ?? null,
            //         'created_at' => $scanRecords->created_at,
            //         'updated_at' => $scanRecords->updated_at,
            //     ];
            // });


            // 📅 Start & End Date (अगर नहीं है तो पूरे data पर काम करेगा)
    $start_date = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
    $end_date   = $request->filled('end_date')   ? Carbon::parse($request->end_date)->endOfDay()   : null;

    // ============================
    // 📦 Product Query with filter
    $productQuery = Product::query();

    if ($start_date && $end_date) {
        $productQuery->whereBetween('created_at', [$start_date, $end_date]);
    } elseif ($start_date) {
        $productQuery->whereDate('created_at', '>=', $start_date);
    } elseif ($end_date) {
        $productQuery->whereDate('created_at', '<=', $end_date);
    }

    $total = $productQuery->count();
    $returnable = (clone $productQuery)->where('returnable', 1)->count();
    $nonReturnable = $total - $returnable;

    $returnablePercent = $total > 0 ? round(($returnable / $total) * 100, 2) : 0;
    $nonReturnablePercent = $total > 0 ? round(($nonReturnable / $total) * 100, 2) : 0;

    $returnableNonReturnableItems = [
        'total_products' => $total,
        'returnable' => [
            'count' => $returnable,
            'percent' => $returnablePercent
        ],
        'non_returnable' => [
            'count' => $nonReturnable,
            'percent' => $nonReturnablePercent
        ]
    ];

    // ============================
    // 📂 Categories
    $categories_list = Category::orderBy('id', 'desc')->get();

    $products = $productQuery->with(
            'category:id,name',
            'vendor:id,vendor_name',
            'sub_category:id,name'
        )
        ->orderBy('id', 'desc')
        ->get();

    $totalProducts = $products->count();

    $categoryStats = $totalProducts > 0
        ? $products->groupBy('category.id')->map(function ($items, $categoryId) use ($totalProducts) {
            $count = $items->count();
            $percentage = round(($count / $totalProducts) * 100, 2);

            return [
                'category_id' => $categoryId,
                'category_name' => optional($items->first()->category)->name,
                'product_count' => $count,
                'percentage' => $percentage,
            ];
        })->values()
        : collect();

    $uniqueCategoryCount = $categoryStats->toArray();
    $productCount = $totalProducts;


    // ============================
    // 👨‍💼 ScanInOutProduct with date filter
    $scanQuery = ScanInOutProduct::with([
        'product:id,product_name,sku,opening_stock',
        'employee:id,employee_name',
        'user:id,name'
    ]);

    if ($start_date && $end_date) {
        $scanQuery->whereBetween('created_at', [$start_date, $end_date]);
    } elseif ($start_date) {
        $scanQuery->whereDate('created_at', '>=', $start_date);
    } elseif ($end_date) {
        $scanQuery->whereDate('created_at', '<=', $end_date);
    }

    $scanRecords = $scanQuery->get();

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
            'product_name' => $scanRecords->product->product_name ?? null,
            'sku' => $scanRecords->product->sku ?? null,
            'quantity' => $scanRecords->product->opening_stock ?? null,
            'issue_from_name' => $scanRecords->user->name ?? null,
            'employee_name' => $scanRecords->employee->employee_name ?? null,
            'created_at' => $scanRecords->created_at,
            'updated_at' => $scanRecords->updated_at,
        ];
    });

//     return response()->json([
//         'returnableNonReturnableItems' => $returnableNonReturnableItems,
//         'categories_list'              => $categories_list,
//         'categoryStats'                => $categoryStats,
//         'productCount'                 => $productCount,
//         'scanRecords'                  => $scanRecords,
//     ]);
// }

// returnableNonReturnableItems filter date
  
            // $stock_update = Stock::with(['product' => function ($query) {
            //     $query->select('id', 'product_name'); // only fetch id and name from product
            // }])->get();

            // $mapped_stock_data = $stock_update->map(function ($stock) {
            //     return [
            //         'product_id'        => $stock->product_id,
            //         'product_name'      => optional($stock->product)->product_name,
            //         'stock_id'          => $stock->id,
            //         'current_stock'     => $stock->current_stock,
            //         'new_stock'         => $stock->new_stock,
            //         'quantity'          => $stock->quantity,
            //         'unit_of_measure'   => $stock->unit_of_measure,
            //         'per_unit_cost'     => $stock->per_unit_cost,
            //         'total_cost'        => $stock->total_cost,
            //         'reason_for_update' => $stock->reason_for_update,
            //         'stock_date'        => $stock->stock_date,
            //         'adjustment'        => $stock->adjustment,
            //         'comment'           => $stock->comment,
            //         'created_at'        => $stock->created_at,
            //         'updated_at'        => $stock->updated_at,
            //     ];
            // });


             // Get filters from request
    $startDate = $request->input('start_date');
    $endDate   = $request->input('end_date');

    // Build query
    $stock_update = Stock::with(['product' => function ($query) {
            $query->select('id', 'product_name'); // only fetch id and name from product
        }])
        ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
            $query->whereBetween('stock_date', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ]);
        })
        ->get();

    // Map data
    $mapped_stock_data = $stock_update->map(function ($stock) {
        return [
            'product_id'        => $stock->product_id,
            'product_name'      => optional($stock->product)->product_name,
            'stock_id'          => $stock->id,
            'current_stock'     => $stock->current_stock,
            'new_stock'         => $stock->new_stock,
            'quantity'          => $stock->quantity,
            'unit_of_measure'   => $stock->unit_of_measure,
            'per_unit_cost'     => $stock->per_unit_cost,
            'total_cost'        => $stock->total_cost,
            'reason_for_update' => $stock->reason_for_update,
            'stock_date'        => $stock->stock_date,
            'adjustment'        => $stock->adjustment,
            'comment'           => $stock->comment,
            'created_at'        => $stock->created_at,
            'updated_at'        => $stock->updated_at,
        ];
    });


            // mapped stock data filter 

            // $issuedRecordsDepartment = ScanInOutProduct::with('department:id,name')->where('type','out')->get();

            // $totalIssues = $issuedRecordsDepartment->count();

            // $departmentWiseCount = $issuedRecordsDepartment
            //     ->filter(function ($record) {
            //         return $record->department; // filter only those with department
            //     })
            //     ->groupBy(function ($item) {
            //         return $item->department->name;
            //     })
            //     ->map(function ($items, $departmentName) use ($totalIssues) {
            //         $count = $items->count();
            //         $percentage = ($totalIssues > 0) ? round(($count / $totalIssues) * 100, 2) : 0;

            //         return [
            //             'department_name' => $departmentName,
            //             'issue_count' => $count,
            //             'issue_percentage' => $percentage
            //         ];
            //     })->values(); // Optional: Reset keys to 0-based index

            // $issuedRecordsDep = [
            //     'total_issues' => $totalIssues,
            //     'department_stats' => $departmentWiseCount
            // ];

            $startDate = $request->input('start_date');
    $endDate   = $request->input('end_date');

    $issuedRecordsDepartment = ScanInOutProduct::with('department:id,name')
        ->where('type', 'out')
        ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ]);
        })
        ->get();

    $totalIssues = $issuedRecordsDepartment->count();

    $departmentWiseCount = $issuedRecordsDepartment
        ->filter(function ($record) {
            return $record->department; // only include records with department
        })
        ->groupBy(function ($item) {
            return $item->department->name;
        })
        ->map(function ($items, $departmentName) use ($totalIssues) {
            $count = $items->count();
            $percentage = ($totalIssues > 0) ? round(($count / $totalIssues) * 100, 2) : 0;

            return [
                'department_name'   => $departmentName,
                'issue_count'       => $count,
                'issue_percentage'  => $percentage,
            ];
        })->values(); // reset keys to 0-based index

    $issuedRecordsDep = [
        'total_issues'     => $totalIssues,
        'department_stats' => $departmentWiseCount,
    ];


            // issuedRecordsDep filter date 


            // $topscanRecords = ScanInOutProduct::with([
            //     'product:id,product_name,sku,inventory_alert_threshold,commit_stock_check,opening_stock,category_id',
            //     'product.category:id,name',
            //     'product.orders:id,product_id,quantity',
            //     'vendor:id,vendor_name',
            //     'employee:id,employee_name',
            //     'user:id,name',
            //     'location:id,name',
            //     'machine:id,name',
            //     'workStation:id,name',
            //     'department:id,name'
            // ])->get();

            // $totalIssues = $topscanRecords->count();
            // $topTenGrouped = $topscanRecords->groupBy('product_id')->map(function ($items, $productId) use ($totalIssues) {
            // $count = $items->count();
            // $percentage = ($totalIssues > 0) ? round(($count / $totalIssues) * 100, 2) : 0;

            // return [
            //     'product_id' => $productId,
            //     'product_name' => optional($items->first()->product)->product_name ?? null,
            //     'sku' => optional($items->first()->product)->sku ?? null,
            //     'issue_count' => $count,
            //     'issue_percentage' => $percentage
            // ];
            // })
            // ->sortByDesc('issue_count')
            // ->take(10)
            // ->values();

                 $startDate = $request->input('start_date');
    $endDate   = $request->input('end_date');

    $topscanRecords = ScanInOutProduct::with([
            'product:id,product_name,model,sku,inventory_alert_threshold,commit_stock_check,opening_stock,category_id',
            'product.category:id,name',
            'product.orders:id,product_id,quantity',
            'vendor:id,vendor_name',
            'employee:id,employee_name',
            'user:id,name',
            'location:id,name',
            'machine:id,name',
            'workStation:id,name',
            'department:id,name'
        ])
        ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ]);
        })
        ->get();

    $totalIssues = $topscanRecords->count();

    $topTenGrouped = $topscanRecords
        ->groupBy('product_id')
        ->map(function ($items, $productId) use ($totalIssues) {
            $count = $items->count();
            $percentage = ($totalIssues > 0) ? round(($count / $totalIssues) * 100, 2) : 0;

            return [
                'product_id'       => $productId,
                'product_name'     => optional($items->first()->product)->product_name ?? null,
                'product_model'     => optional($items->first()->product)->model ?? null,
                'sku'              => optional($items->first()->product)->sku ?? null,
                'issue_count'      => $count,
                'issue_percentage' => $percentage,
            ];
        })
        ->sortByDesc('issue_count')
        ->take(10)
        ->values();

    // return response()->json([
    //     'total_issues' => $totalIssues,
    //     'top_10'       => $topTenGrouped,
    // ]);
            // topTenGrouped filter date 
        //      // Fetch raw data

    // $startDate = Carbon::now()->subMonths(5)->startOfMonth(); 
    // $endDate   = Carbon::now()->endOfMonth();

    // // Query with product relation and sum of quantities
    // $monthlyCountsRawItemTrend = ScanInOutProduct::with([
    //         'product:id,product_name,sku,inventory_alert_threshold,commit_stock_check,opening_stock'
    //     ])
    //     ->select(
    //         DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
    //         'type',
    //         'product_id',
    //         DB::raw("SUM(out_quantity) as total")
    //     )
    //     ->whereBetween('created_at', [$startDate, $endDate])
    //     ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"), 'type', 'product_id')
    //     ->orderBy('month')
    //     ->get();

    // // Initialize all months with default values
    // $current = $startDate->copy();
    // $allMonthsItemtrend = collect();
    // while ($current <= $endDate) {
    //     $allMonthsItemtrend->put($current->format('Y-M'), [
    //         // 'in' => 0,
    //         // 'out' => 0,
    //         'total_quantity' => 0,
    //         'item_name' => '',
    //         'product_name_quantity' => '',
    //     ]);
    //     $current->addMonth();
    // }

    // // Fill data month by month
    // foreach ($monthlyCountsRawItemTrend as $row) {
    //     $month = Carbon::parse($row->month)->format('Y-M');
    //     $type  = strtolower($row->type);
    //     $total = (int) $row->total;
    //     $productName = $row->product->product_name ?? 'N/A';

    //     $data = $allMonthsItemtrend->get($month, [
    //         // 'in' => 0,
    //         // 'out' => 0,
    //         'total_quantity' => 0,
    //         'item_name' => '',
    //         'product_name_quantity' => '',
           
    //     ]);

    //     // update totals
    //     $data[$type] = ($data[$type] ?? 0) + $total;
    //     $data['item_name'] = $productName;
    //     $data['total_quantity'] = ($data['in'] ?? 0) + ($data['out'] ?? 0);
    //     $data['product_name_quantity'] = $productName . ' + '.($data['in'] ?? 0) + ($data['out'] ?? 0);
        

    //     // push product info
    //     // $data['products'][] = [
    //     //     'name' => $productName,
    //     //     'quantity' => $total,
    //     //     'type' => $type
    //     // ];

    //     $allMonthsItemtrend->put($month, $data);
    // }

    // // sort months properly
    // $allMonthsItemtrend = $allMonthsItemtrend->sortBy(function ($value, $key) {
    //     return Carbon::createFromFormat('Y-M', $key)->timestamp;
    // });


    // ✅ Get filters (default = last 5 months)
    // $startDate = $request->input('start_date') 
    //     ? Carbon::parse($request->input('start_date'))->startOfDay()
    //     : Carbon::now()->subMonths(5)->startOfMonth();

    // $endDate = $request->input('end_date') 
    //     ? Carbon::parse($request->input('end_date'))->endOfDay()
    //     : Carbon::now()->endOfMonth();

    // // 🔎 Query data with filters
    // $monthlyCountsRawItemTrend = ScanInOutProduct::with([
    //         'product:id,product_name,sku,inventory_alert_threshold,commit_stock_check,opening_stock,total_cost'
    //     ])
    //     ->select(
    //         DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
    //         'type',
    //         'product_id',
    //         DB::raw("SUM(out_quantity) as total"),
    //         DB::raw("SUM(total_cost) as total_cost")
            
    //     )
    //     ->whereBetween('created_at', [$startDate, $endDate])
    //     ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"), 'type', 'product_id')
    //     ->orderBy('month')
    //     ->get();

    // // 🔧 Initialize months with default values
    // $current = $startDate->copy();
    // $allMonthsItemtrend = collect();
    // while ($current <= $endDate) {
    //     $allMonthsItemtrend->put($current->format('Y-M'), [
    //         'total_quantity'       => 0,
    //         'total_value'       => 0,
    //         'item_name'            => '',
    //         'product_name_quantity'=> '',
    //     ]);
    //     $current->addMonth();
    // }

    // // 📝 Fill month data
    // foreach ($monthlyCountsRawItemTrend as $row) {
    //     $month       = Carbon::parse($row->month)->format('Y-M');
    //     $type        = strtolower($row->type);
    //     $total       = (int) $row->total;
    //     $productName = $row->product->product_name ?? 'N/A';

    //     $data = $allMonthsItemtrend->get($month, [
    //         'total_quantity'       => 0,
    //         'total_value'       => 0,
    //         'item_name'            => '',
    //         'product_name_quantity'=> '',
    //     ]);

    //     $data[$type] = ($data[$type] ?? 0) + $total;
    //     $data['item_name'] = $productName;
    //     $data['total_quantity'] = ($data['in'] ?? 0) + ($data['out'] ?? 0);
    //     $data['product_name_quantity'] = $productName . ' + ' . $data['total_quantity'];

    //     $allMonthsItemtrend->put($month, $data);
    // }

    // // 📅 Sort months correctly
    // $allMonthsItemtrend = $allMonthsItemtrend->sortBy(function ($value, $key) {
    //     return Carbon::createFromFormat('Y-M', $key)->timestamp;
    // });




     $startDate = $request->input('start_date') 
        ? Carbon::parse($request->input('start_date'))->startOfDay()
        : Carbon::now()->subMonths(5)->startOfMonth();

    $endDate = $request->input('end_date') 
        ? Carbon::parse($request->input('end_date'))->endOfDay()
        : Carbon::now()->endOfMonth();

    /* 🔎 Fetch data (NO total_cost from scan table) */
    $monthlyCountsRawItemTrend = ScanInOutProduct::with([
            'product:id,product_name,total_cost'
        ])
        ->select(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
            'type',
            'product_id',
            DB::raw("SUM(out_quantity) as total_quantity")
        )
        ->whereBetween('created_at', [$startDate, $endDate])
        ->groupBy(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m')"),
            'type',
            'product_id'
        )
        ->orderBy('month', 'asc')
        ->get();

    /* 🔧 Initialize all months */
    $current = $startDate->copy();
    $allMonthsItemtrend = collect();

    while ($current <= $endDate) {
        $allMonthsItemtrend->put($current->format('Y-M'), [
            'in'                     => 0,
            'out'                    => 0,
            'total_quantity'         => 0,
            'total_cost'             => 0,
            'item_name'              => '',
            'product_name_quantity'  => '',
        ]);
        $current->addMonth();
    }

    /* 🧮 Process data */
    foreach ($monthlyCountsRawItemTrend as $row) {

        $month       = Carbon::parse($row->month)->format('Y-M');
        $type        = strtolower($row->type); // in / out
        $quantity    = (int) $row->total_quantity;
        $unitCost    = (float) ($row->product->total_cost ?? 0);
        $productName = $row->product->product_name ?? 'N/A';

        $data = $allMonthsItemtrend->get($month);

        // Quantity
        $data[$type] += $quantity;
        $data['total_quantity'] = $data['in'] + $data['out'];

        // 💰 Cost calculation
        $data['total_cost'] += ($quantity * $unitCost);

        // Product info
        $data['item_name'] = $productName;
        $data['product_name_quantity'] = $productName . ' + ' . $data['total_quantity'];

        $allMonthsItemtrend->put($month, $data);
    }

    /* 📅 Sort months */
    $allMonthsItemtrend = $allMonthsItemtrend->sortBy(function ($value, $key) {
        return Carbon::createFromFormat('Y-M', $key)->timestamp;
    });
    
    // return response()->json([
    //     'start_date' => $startDate->toDateString(),
    //     'end_date'   => $endDate->toDateString(),
    //     'trend'      => $allMonthsItemtrend->values(),
    // ]);


            
            // $stockRecords = Stock::with(
            //     'product:id,category_id,product_name',
            //     'Category:id,name'
            // )->get();
               
            // // Group by category_id
            // $categoryStatsStock = $stockRecords->groupBy(function ($stock) {
            //     return optional($stock->Category)->id;
            // });

            // $totalStockCost = $stockRecords->sum(function ($stock) {
            //     return $stock->total_cost ?? 0;
            // });

            // $categoryStockSummary = $categoryStatsStock->map(function ($stocks, $categoryId) use ($totalStockCost) {
            //     $totalStockValue = $stocks->sum(function ($stock) {
            //         return $stock->total_cost ?? 0;
            //     });

            //     $percentage = $totalStockCost > 0 
            //         ? round(($totalStockValue / $totalStockCost) * 100, 2) 
            //         : 0;
            //     $currencySetting =  CurrencySetting::where('default_status','yes')->first();
            //     return [
            //         'category_id'        => $categoryId,
            //         'category_name'      => optional($stocks->first()->Category)->name,
            //         'total_stock_value'  => $currencySetting->symbol.''. $totalStockValue,
            //         'percentage'         => $percentage,
            //         'product_count'      => $stocks->groupBy('product_id')->count(), // unique products in this category
            //     ];
            // })->values();

            // $stockValueByCategory = $categoryStockSummary->toArray();


            $startDate = $request->input('start_date');
    $endDate   = $request->input('end_date');

    // 🔎 Query with filters
    $stockRecords = Stock::with(
            'product:id,category_id,product_name',
            'Category:id,name'
        )
        ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        })
        ->when($startDate && !$endDate, function ($query) use ($startDate) {
            $query->whereDate('created_at', '>=', Carbon::parse($startDate)->startOfDay());
        })
        ->when(!$startDate && $endDate, function ($query) use ($endDate) {
            $query->whereDate('created_at', '<=', Carbon::parse($endDate)->endOfDay());
        })
        ->get();

    // Group by category_id
    $categoryStatsStock = $stockRecords->groupBy(function ($stock) {
        return optional($stock->Category)->id;
    });

    // Total stock cost (filtered)
    $totalStockCost = $stockRecords->sum(function ($stock) {
        return $stock->total_cost ?? 0;
    });

    // Build category summary
    $categoryStockSummary = $categoryStatsStock->map(function ($stocks, $categoryId) use ($totalStockCost) {
        $totalStockValue = $stocks->sum(function ($stock) {
            return $stock->total_cost ?? 0;
        });

        $percentage = $totalStockCost > 0 
            ? round(($totalStockValue / $totalStockCost) * 100, 2) 
            : 0;

        $currencySetting = CurrencySetting::where('default_status', 'yes')->first();

        return [
            'category_id'        => $categoryId,
            'category_name'      => optional($stocks->first()->Category)->name,
            'total_stock_value'  => ($currencySetting->symbol ?? '') . $totalStockValue,
            'percentage'         => $percentage,
            'product_count'      => $stocks->groupBy('product_id')->count(), // unique products in this category
        ];
    })->values();

    $stockValueByCategory = $categoryStockSummary->toArray();

    // return response()->json([
    //     'start_date' => $startDate,
    //     'end_date'   => $endDate,
    //     'stock_value_by_category' => $categoryStockSummary->toArray(),
    // ]);
                

            // $allWorkstations = WorkStation::select('id', 'name')->get();

            // // Fetch all issued records at once
            // $issuedRecords = ScanInOutProduct::where('type', 'out')->get();

            // $issuedRecordsSum = ScanInOutProduct::select('work_station_id')
            //     ->where('type', 'out')
            //     ->whereNotNull('out_quantity')
            //     ->whereNotNull('work_station_id')
            //     ->groupBy('work_station_id')
            //     ->selectRaw('SUM(out_quantity) as total_out_quantity')
            //     ->get();



            // $totalOutQuantity = $issuedRecordsSum->sum('total_out_quantity');

            // $groupedByWorkstation = $issuedRecords
            //     ->groupBy('work_station_id');

            // $workstationStats = $allWorkstations->map(function ($workstation) use ($groupedByWorkstation, $totalOutQuantity) {
            //     $records = $groupedByWorkstation->get($workstation->id, collect());

            //     $workstationTotalQuantity = $records->sum('out_quantity');

            //     $percentage = ($totalOutQuantity > 0)
            //         ? round(($workstationTotalQuantity / $totalOutQuantity) * 100, 2)
            //         : 0;

            //     return [
            //         'workstation_id'       => $workstation->id,
            //         'workstation_name'     => $workstation->name,
            //         'total_out_quantity'   => $workstationTotalQuantity,
            //         'percentage'            => $percentage,
            //     ];
            // });

            // // Sort by highest total_out_quantity
            // $sortedWorkstationStats = $workstationStats
            //     ->sortByDesc('total_out_quantity')
            //     ->values()
            //     ->toArray();

            // $issuedRecordsWorkstation = [
            //     'total_out_quantity' => $totalOutQuantity,
            //     'workstation_stats'  => $sortedWorkstationStats,
            // ];


    //          $startDate = $request->input('start_date');
    // $endDate   = $request->input('end_date');

    // $allWorkstations = WorkStation::select('id', 'name')->get();

    // // Base query for issued records
    // $issuedQuery = ScanInOutProduct::where('type', 'out');

    // // ✅ Apply date filters
    // $issuedQuery->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
    //     $q->whereBetween('created_at', [
    //         Carbon::parse($startDate)->startOfDay(),
    //         Carbon::parse($endDate)->endOfDay()
    //     ]);
    // })
    // ->when($startDate && !$endDate, function ($q) use ($startDate) {
    //     $q->whereDate('created_at', '>=', Carbon::parse($startDate)->startOfDay());
    // })
    // ->when(!$startDate && $endDate, function ($q) use ($endDate) {
    //     $q->whereDate('created_at', '<=', Carbon::parse($endDate)->endOfDay());
    // });

    // // Fetch issued records
    // $issuedRecords = $issuedQuery->get();

    // // Summed issued records
    // $issuedRecordsSum = (clone $issuedQuery)
    //     ->whereNotNull('out_quantity')
    //     ->whereNotNull('work_station_id')
    //     ->groupBy('work_station_id')
    //     ->selectRaw('work_station_id, SUM(out_quantity) as total_out_quantity')
    //     ->get();

    // $totalOutQuantity = $issuedRecordsSum->sum('total_out_quantity');

    // // Group by workstation
    // $groupedByWorkstation = $issuedRecords->groupBy('work_station_id');

    // $workstationStats = $allWorkstations->map(function ($workstation) use ($groupedByWorkstation, $totalOutQuantity) {
    //     $records = $groupedByWorkstation->get($workstation->id, collect());

    //     $workstationTotalQuantity = $records->sum('out_quantity');
    //     $totalOutCost = $records->sum('out_quantity');

    //     $percentage = ($totalOutQuantity > 0)
    //         ? round(($workstationTotalQuantity / $totalOutQuantity) * 100, 2)
    //         : 0;

    //     return [
    //         'workstation_id'     => $workstation->id,
    //         'workstation_name'   => $workstation->name,
    //         'total_out_quantity' => $workstationTotalQuantity,
    //         'total_out_cost' => $totalOutCost,
    //         'percentage'         => $percentage,
    //     ];
    // });

    // // Sort by highest total_out_quantity
    // $sortedWorkstationStats = $workstationStats
    //     ->sortByDesc('total_out_quantity')
    //     ->values()
    //     ->toArray();

    //      $issuedRecordsWorkstation = [
    //             'start_date'          => $startDate,
    //             'end_date'            => $endDate,
    //             'total_out_quantity' => $totalOutQuantity,
    //             'total_out_cost' => $total_out_cost,
    //             'workstation_stats'  => $sortedWorkstationStats,
    //         ];


    $startDate = $request->input('start_date');
$endDate   = $request->input('end_date');

/*
|--------------------------------------------------------------------------
| All Workstations
|--------------------------------------------------------------------------
*/
$allWorkstations = WorkStation::select('id', 'name')->get();

/*
|--------------------------------------------------------------------------
| Base Query (Issued / OUT records)
|--------------------------------------------------------------------------
*/
// $issuedQuery = ScanInOutProduct::with('product.total_cost')->where('type', 'out');
$issuedQuery = ScanInOutProduct::with('product:id,product_name,total_cost')
    ->where('type', 'out');
/*
|--------------------------------------------------------------------------
| Apply Date Filters
|--------------------------------------------------------------------------
*/
$issuedQuery
    ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
        $q->whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ]);
    })
    ->when($startDate && !$endDate, function ($q) use ($startDate) {
        $q->whereDate('created_at', '>=', Carbon::parse($startDate)->startOfDay());
    })
    ->when(!$startDate && $endDate, function ($q) use ($endDate) {
        $q->whereDate('created_at', '<=', Carbon::parse($endDate)->endOfDay());
    });

/*
|--------------------------------------------------------------------------
| Fetch Issued Records
|--------------------------------------------------------------------------
*/
$issuedRecords = $issuedQuery->get();

/*
|--------------------------------------------------------------------------
| Summed Issued Records (Workstation wise)
|--------------------------------------------------------------------------
*/
$issuedRecordsSum = (clone $issuedQuery)
    ->whereNotNull('out_quantity')
    ->whereNotNull('work_station_id')
    ->groupBy('work_station_id')
    ->selectRaw('work_station_id, SUM(out_quantity) as total_out_quantity')
    ->get();

/*
|--------------------------------------------------------------------------
| Overall Totals
|--------------------------------------------------------------------------
*/
$totalOutQuantity = $issuedRecordsSum->sum('total_out_quantity');

/* 
| If you don't have a cost column, quantity = cost
| Otherwise replace out_quantity with out_cost
*/
// $total_out_cost = $issuedRecords->sum('out_quantity');
// $total_out_cost = $issuedRecords->product['total_cost'] ?? 0;

$total_out_cost = $issuedRecordsSum->sum(function ($record) {
    return $record->product->total_cost ?? 0;
});

/*
|--------------------------------------------------------------------------
| Group Records by Workstation
|--------------------------------------------------------------------------
*/
$groupedByWorkstation = $issuedRecords->groupBy('work_station_id');

/*
|--------------------------------------------------------------------------
| Workstation Statistics
|--------------------------------------------------------------------------
*/
$workstationStats = $allWorkstations->map(function ($workstation) use (
    $groupedByWorkstation,
    $totalOutQuantity
) {

    $records = $groupedByWorkstation->get($workstation->id, collect());

    $workstationTotalQuantity = $records->sum('out_quantity');

    // Same as quantity (change if you have cost column)
    $totalOutCost = $records->sum('out_quantity');

    $percentage = ($totalOutQuantity > 0)
        ? round(($workstationTotalQuantity / $totalOutQuantity) * 100, 2)
        : 0;

    return [
        'workstation_id'      => $workstation->id,
        'workstation_name'    => $workstation->name,
        'total_out_quantity'  => $workstationTotalQuantity,
        'total_out_cost'      => $totalOutCost,
        'percentage'          => $percentage,
    ];
});

/*
|--------------------------------------------------------------------------
| Sort by Highest Quantity
|--------------------------------------------------------------------------
*/
$sortedWorkstationStats = $workstationStats
    ->sortByDesc('total_out_quantity')
    ->values()
    ->toArray();

/*
|--------------------------------------------------------------------------
| Final Response
|--------------------------------------------------------------------------
*/
$issuedRecordsWorkstation = [
    'start_date'          => $startDate,
    'end_date'            => $endDate,
    'total_out_quantity'  => $totalOutQuantity,
    'total_out_cost'      => $total_out_cost,
    'workstation_stats'   => $sortedWorkstationStats,
];


    // return response()->json([
    //     'start_date'          => $startDate,
    //     'end_date'            => $endDate,
    //     'total_out_quantity'  => $totalOutQuantity,
    //     'workstation_stats'   => $sortedWorkstationStats,
    // ]);




            //     $topscanRecordsIssuesItemValue = ScanInOutProduct::with([
            //         'product:id,product_name,sku,inventory_alert_threshold,commit_stock_check,opening_stock,category_id',
            //         'product.category:id,name',
            //         'product.orders:id,product_id,quantity',
            //         'vendor:id,vendor_name',
            //         'employee:id,employee_name',
            //         'user:id,name',
            //         'location:id,name',
            //         'machine:id,name',
            //         'workStation:id,name',
            //         'department:id,name'
            //     ])->get();

            // $totalIssuesItemValue = $topscanRecordsIssuesItemValue->count();

            // $topTenIssuesItemValue = $topscanRecordsIssuesItemValue->groupBy('product_id')->map(function ($items, $productId) use ($totalIssuesItemValue) {
            //     $counts = $items->count();
            //     $percentage = ($totalIssuesItemValue > 0) ? round(($counts / $totalIssuesItemValue) * 100, 2) : 0;

            //     // Issues product value (example: orders quantity × product price)
            //     $issueValue = $items->sum(function ($scan) {
            //         $product = $scan->product;
            //         if (!$product) {
            //             return 0;
            //         }
                   
            //         $stock_update = Stock::where('product_id',$product->id)->get();

            //         $total = $stock_update->sum(function ($item) {
            //             return (float) $item->total_cost;
            //         });

            //         $totalQty = $product->orders->sum('quantity') ?? 0;

            //         // maan lete hain product me price field hai
            //         return $totalQty * ( $total?? 0);
            //     });

            //     return [
            //         'product_id'   => $productId,
            //         'product_name' => optional($items->first()->product)->product_name,
            //         'category'     => optional(optional($items->first()->product)->category)->name,
            //         'issues_count' => $counts,
            //         'percentage'   => $percentage,
            //         'issue_value'  => $issueValue,
            //     ];
            // })->values();

    $startDate = $request->input('start_date');
    $endDate   = $request->input('end_date');

    // $topscanRecordsIssuesItemValue = ScanInOutProduct::with([
    //         'product:id,product_name,sku,inventory_alert_threshold,commit_stock_check,opening_stock,category_id',
    //         'product.category:id,name',
    //         'product.orders:id,product_id,quantity',
    //         'vendor:id,vendor_name',
    //         'employee:id,employee_name',
    //         'user:id,name',
    //         'location:id,name',
    //         'machine:id,name',
    //         'workStation:id,name',
    //         'department:id,name'
    //     ])
    //     ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
    //         $query->whereBetween('created_at', [
    //             Carbon::parse($startDate)->startOfDay(),
    //             Carbon::parse($endDate)->endOfDay(),
    //         ]);
    //     })
    //     ->when($startDate && !$endDate, function ($query) use ($startDate) {
    //         $query->whereDate('created_at', '>=', Carbon::parse($startDate)->startOfDay());
    //     })
    //     ->when(!$startDate && $endDate, function ($query) use ($endDate) {
    //         $query->whereDate('created_at', '<=', Carbon::parse($endDate)->endOfDay());
    //     })
    //     ->get();

    // $totalIssuesItemValue = $topscanRecordsIssuesItemValue->count();

    // $topTenIssuesItemValue = $topscanRecordsIssuesItemValue
    //     ->groupBy('product_id')
    //     ->map(function ($items, $productId) use ($totalIssuesItemValue) {
    //         $counts = $items->count();
    //         $percentage = ($totalIssuesItemValue > 0) ? round(($counts / $totalIssuesItemValue) * 100, 2) : 0;
    //         $issueValue = $items->sum(function ($scan) {
    //             $product = $scan->product;
    //             if (!$product) {
    //                 return 0;
    //             }

    //             $stock_update = Stock::where('product_id', $product->id)->get();

    //             $total = $stock_update->sum(function ($item) {
    //                 return (float) $item->total_cost;
    //             });

    //             $totalQty = $product->orders->sum('quantity') ?? 0;

    //             return $totalQty * ($total ?? 0);
    //         });

    //         return [
    //             'product_id'   => $productId,
    //             'product_name' => optional($items->first()->product)->product_name,
    //             'category'     => optional(optional($items->first()->product)->category)->name,
    //             'issues_count' => $counts,
    //             'percentage'   => $percentage,
    //             'issue_value'  => $issueValue,
    //         ];
    //     })->sortByDesc('issue_value')->take(10)->values();



     // Step 1: Get all scan records with product relation

    // $topscanRecordsIssuesItemValue = ScanRecord::with(['product', 'product.category', 'product.orders'])->get();

    // Step 2: Total issues count
    // $totalIssuesItemValue = $topscanRecordsIssuesItemValue->count();

    // Step 3: Group, calculate and transform data

 
    // $startDate = $request->start_date;
    // $endDate   = $request->end_date;

    // Fetch records with relations
$topscanRecordsIssuesItemValue = ScanInOutProduct::with([
    'product:id,product_name,model,sku,inventory_alert_threshold,commit_stock_check,opening_stock,category_id',
    'product.category:id,name',
    'product.orders:id,product_id,quantity',
    'vendor:id,vendor_name',
    'employee:id,employee_name',
    'user:id,name',
    'location:id,name',
    'machine:id,name',
    'workStation:id,name',
    'department:id,name'
])
->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
    $q->whereBetween('created_at', [
        Carbon::parse($startDate)->startOfDay(),
        Carbon::parse($endDate)->endOfDay(),
    ]);
})
->when($startDate && !$endDate, function ($q) use ($startDate) {
    $q->whereDate('created_at', '>=', Carbon::parse($startDate)->startOfDay());
})
->when(!$startDate && $endDate, function ($q) use ($endDate) {
    $q->whereDate('created_at', '<=', Carbon::parse($endDate)->endOfDay());
})
->get();

// STEP 1: Group & calculate issue value
$itemsCollection = $topscanRecordsIssuesItemValue
    ->groupBy('product_id')
    ->map(function ($items, $productId) {

        $product = $items->first()->product;
        if (!$product) return null;

        // Fetch stock cost
        $totalCost = Stock::where('product_id', $product->id)->sum('total_cost');

        // Total issued quantity
        $totalQty = $product->orders->sum('quantity') ?? 0;

        // Final issued value
        $issueValue = $totalQty * $totalCost;

        return [
            'product_id'   => $productId,
            'product_name' => $product->product_name,
            'product_model' => $product->model,
            'category'     => optional($product->category)->name,
            'issues_count' => $items->count(),
            'issue_value'  => $issueValue,
        ];
    })
    ->filter();

// STEP 2: Total issue VALUE (percentage base)
$totalIssueValue = $itemsCollection->sum('issue_value');

// STEP 3: Add percentage using VALUE logic
$topTenIssuesItemValue = $itemsCollection
    ->map(function ($item) use ($totalIssueValue) {

        $item['percentage'] = $totalIssueValue > 0
            ? round(($item['issue_value'] / $totalIssueValue) * 100, 2)
            : 0;

        return $item;
    })
    ->sortByDesc('issue_value')
    ->take(10)
    ->values();


    // Prepare chart-ready response
    // return response()->json([
    //     'title'  => "Top 10 Issued Items by Value (₹)",
    //     'labels' => $topTen->pluck('product_name'),
    //     'values' => $topTen->pluck('issue_value')->map(function($val){
    //         // Format numbers: 45K, 1.2M etc.
    //         if ($val >= 10000000) return round($val/10000000, 1).'Cr';
    //         if ($val >= 100000) return round($val/100000, 1).'L';
    //         if ($val >= 1000) return round($val/1000, 1).'K';
    //         return $val;
    //     }),
    //     'raw_values' => $topTen->pluck('issue_value'), // actual values for chart scale
    //     'items'  => $topTen
    // ]);



    // Step 4: Format for chart
    // return response()->json([
    //     'labels' => $topTenIssuesItemValue->pluck('product_name'),
    //     'values' => $topTenIssuesItemValue->pluck('issue_value'),
    //     'items'  => $topTenIssuesItemValue, // full data if needed
    // ]);


    // return response()->json([
    //     'total_issues' => $totalIssuesItemValue,
    //     'top_10_issue_items' => $topTenIssuesItemValue,
    // ]);


$startDate = $request->input('start_date');
$endDate   = $request->input('end_date');

// ✅ PPE category find (case-insensitive)
$category = Category::whereRaw('LOWER(name) = ?', ['ppe'])
    ->orderBy('id', 'desc')
    ->select('id', 'name')
    ->first();

$categoryId = $category?->id;

// ✅ Build query
$stock_updateppe = Stock::with(['product' => function ($query) {
        $query->select('id', 'product_name', 'category_id');
    }])
    ->whereHas('product', function ($q) use ($categoryId) {
        $q->where('category_id', $categoryId);
    })
    ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
        $query->whereBetween('stock_date', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ]);
    })
    ->get();

// ✅ Group by product_id and calculate sum
$groupedData = $stock_updateppe->groupBy('product_id');

// ✅ Calculate total quantity (all products)
$totalQuantity = $stock_updateppe->sum('quantity');

$ppe_product_data_category = $groupedData->map(function ($stocks, $productId) use ($totalQuantity) {
    $productName   = optional($stocks->first()->product)->product_name;
    $totalQuantityPerProduct = $stocks->sum('quantity');

    $percentage = ($totalQuantity > 0) 
        ? round(($totalQuantityPerProduct / $totalQuantity) * 100, 2) 
        : 0;

    return [
        'product_id'    => $productId,
        'product_name'  => $productName,
        'total_quantity'=> $totalQuantityPerProduct,
        'percentage'    => $percentage,
    ];
})->values();





// stock movement by values

$startDate = $request->filled('start_date') 
    ? Carbon::parse($request->start_date)->startOfWeek()
    : Carbon::now()->subWeeks(8)->startOfWeek(); // last 8 weeks

$endDate = $request->filled('end_date') 
    ? Carbon::parse($request->end_date)->endOfWeek()
    : Carbon::now()->endOfWeek();

// Fetch weekly raw values
$weeklyRaw = ScanInOutProduct::select(
        DB::raw("YEARWEEK(created_at, 1) as week_number"),
        'type',
        DB::raw("SUM(in_quantity) as total_value"),
        DB::raw("SUM(out_quantity) as total_value"),
        DB::raw("COUNT(*) as total_count")
    )
    ->whereIn('type', ['in', 'out'])
    ->whereBetween('created_at', [$startDate, $endDate])
    ->groupBy(DB::raw("YEARWEEK(created_at, 1)"), 'type')
    ->orderBy('week_number')
    ->get();

// Build base structure (missing weeks = 0)
$allWeeks = collect();
$current = $startDate->copy();

while ($current <= $endDate) {
    $weekKey = $current->format("oW"); // 2024-05 → 202405

    $allWeeks->put($weekKey, [
        'week_label' => $current->startOfWeek()->format('d M') . ' - ' . $current->endOfWeek()->format('d M'),
        'in_quantity'  => 0,
        'out_quantity' => 0,
        'in_count'  => 0,
        'out_count' => 0,
    ]);

    $current->addWeek();
}

// Fill values from raw data
foreach ($weeklyRaw as $row) {
    $weekKey = $row->week_number;

    if ($allWeeks->has($weekKey)) {
        $data = $allWeeks->get($weekKey);

        if ($row->type === 'in') {
            $data['in_quantity']  = $row->total_value;
            $data['in_count']  = $row->total_count;
        }

        if ($row->type === 'out') {
            $data['out_quantity'] = $row->total_value;
            $data['out_count'] = $row->total_count;
        }

        $allWeeks->put($weekKey, $data);
    }
}
$stock_movement_by_value =$allWeeks->values();

// return response()->json([
//     'weekly_stock' => $allWeeks->values()
// ]);





            return response()->json([
                'total_product' => $totalproductCount,
                'total_employee_tools' => $employeeUsingProduct,
                'low_stock_alert' => $low_stock_alert,
                'stock_movement' => $allMonths,
                'stock_movement_by_value' => $stock_movement_by_value,
                'returnableNonReturnableItems' => $returnableNonReturnableItems,
                'categories_list' => $categories_list,
                'uniqueCategoryCount' => $uniqueCategoryCount,
                'issuance_update' => $scanRecords,
                'stock_update' => $mapped_stock_data,
                'part_issued_department'=>$issuedRecordsDep,
                'topTenIssuedProduct'=>$topTenGrouped,
                'topTenIssuedProductItemValue'=>$topTenIssuesItemValue,
                'itemtrend'=>$allMonthsItemtrend,
                'stockValueByCategory'=>$stockValueByCategory,
                'issuedRecordsWorkstation'=>$issuedRecordsWorkstation,
                'ppe_product_data_category'=>$ppe_product_data_category,
            ], 200);

    }
}
