<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Location;
use App\Models\Stock;
use App\Models\ScanInOutProduct;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index()
    {
        $productCount = Product::count();
        $employeeUsingProduct = ScanInOutProduct::count();
       

        $inventory_alert = Product::select('id','product_name','sku','opening_stock','location_id','inventory_alert_threshold',DB::raw("'Warning' as status"))->where('opening_stock', '<', DB::raw('inventory_alert_threshold'))->get();
        $low_stock_alert = count($inventory_alert);


        $startDate = Carbon::now()->subMonths(5)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        
        // Fetch raw data
        $monthlyCountsRaw = ScanInOutProduct::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                'type',
                DB::raw("COUNT(*) as total")
            )
            ->whereIn('type', ['in', 'out'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"), 'type')
            ->orderBy('month')
            ->get();
        
        // Build base structure
        $allMonths = collect();
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $allMonths->put($current->format('Y-m'), ['in' => 0, 'out' => 0]);
            $current->addMonth();
        }
        
        foreach ($monthlyCountsRaw as $row) {
            $month = $row->month;
            $type = $row->type;
            $total = $row->total;
        
            $data = $allMonths->get($month);
            $data[$type] = $total;
            $allMonths->put($month, $data);
        }
        


                $total = Product::count();
                $returnable = Product::where('returnable', 1)->count();
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

               $categories_list = Category::orderBy('id', 'desc')->get();

            $products = Product::with('category:id,name', 'vendor:id,vendor_name', 'sub_category:id,name')
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
            $employeeUsingProduct = 0; // Define as needed
            $returnableNonReturnableItems = []; // Define as needed

            $scanRecords = ScanInOutProduct::with([
                    'product:id,product_name,sku,opening_stock',
                    'employee:id,employee_name',
                    'user:id,name'
                ])->get();

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

  
            $stock_update = Stock::with(['product' => function ($query) {
                $query->select('id', 'product_name'); // only fetch id and name from product
            }])->get();

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

            $issuedRecordsDepartment = ScanInOutProduct::with('department:id,name')->where('type','out')->get();

            $totalIssues = $issuedRecordsDepartment->count();

            $departmentWiseCount = $issuedRecordsDepartment
                ->filter(function ($record) {
                    return $record->department; // filter only those with department
                })
                ->groupBy(function ($item) {
                    return $item->department->name;
                })
                ->map(function ($items, $departmentName) use ($totalIssues) {
                    $count = $items->count();
                    $percentage = ($totalIssues > 0) ? round(($count / $totalIssues) * 100, 2) : 0;

                    return [
                        'department_name' => $departmentName,
                        'issue_count' => $count,
                        'issue_percentage' => $percentage
                    ];
                })->values(); // Optional: Reset keys to 0-based index

            $issuedRecordsDep = [
                'total_issues' => $totalIssues,
                'department_stats' => $departmentWiseCount
            ];



            $topscanRecords = ScanInOutProduct::with([
                'product:id,product_name,sku,inventory_alert_threshold,commit_stock_check,opening_stock,category_id',
                'product.category:id,name',
                'product.orders:id,product_id,quantity',
                'vendor:id,vendor_name',
                'employee:id,employee_name',
                'user:id,name',
                'location:id,name',
                'machine:id,name',
                'workStation:id,name',
                'department:id,name'
            ])->get();

            $totalIssues = $topscanRecords->count();
            $topTenGrouped = $topscanRecords->groupBy('product_id')->map(function ($items, $productId) use ($totalIssues) {
            $count = $items->count();
            $percentage = ($totalIssues > 0) ? round(($count / $totalIssues) * 100, 2) : 0;

            return [
                'product_id' => $productId,
                'product_name' => optional($items->first()->product)->product_name ?? null,
                'sku' => optional($items->first()->product)->sku ?? null,
                'issue_count' => $count,
                'issue_percentage' => $percentage
            ];
            })
            ->sortByDesc('issue_count')
            ->take(10)
            ->values();


        //      // Fetch raw data
            $monthlyCountsRawItemTrend = ScanInOutProduct::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%M') as month"),
                'type',
                DB::raw("COUNT(*) as total")
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%M')"), 'type')
            ->orderBy('month')
            ->get();
        
        
      
                $startDate = Carbon::now()->subMonths(5)->startOfMonth(); 
                $endDate   = Carbon::now()->endOfMonth();

                $current = $startDate->copy();
                $allMonthsItemtrend = collect();
                while ($current <= $endDate) {
                    $allMonthsItemtrend->put($current->format('Y-M'), [
                        'in' => 0,
                        'out' => 0,
                        'total_quantity' => 0
                    ]);
                    $current->addMonth();
                }

                foreach ($monthlyCountsRawItemTrend as $row) {
                    $month = Carbon::parse($row->month)->format('Y-M');
                    $type  = strtolower($row->type);
                    $total = (int) $row->total;

                    $data = $allMonthsItemtrend->get($month, [
                        'in' => 0,
                        'out' => 0,
                        'total_quantity' => 0
                    ]);

                    $data[$type] = ($data[$type] ?? 0) + $total;
                    $data['total_quantity'] = ($data['in'] ?? 0) + ($data['out'] ?? 0);

                    $allMonthsItemtrend->put($month, $data);
                }

                $allMonthsItemtrend = $allMonthsItemtrend->sortKeys();

                $allMonthsItemtrend = $allMonthsItemtrend->sortBy(function ($value, $key) {
                    return Carbon::createFromFormat('Y-M', $key)->timestamp;
                });


            //         $stock_value_by_category = DB::table('items')
            // ->join('categories', 'items.category_id', '=', 'categories.id')
            // ->select('categories.name as category', DB::raw('SUM(items.stock_quantity * items.unit_price) as total_value'))
            // ->groupBy('categories.name')
            // ->get();

            $stockValueByCategoryproducts = Product::with(
                    'category:id,name',
                    'stocksData:id,product_id,total_cost', // ensure product_id for relation
                    'vendor:id,vendor_name',
                    'sub_category:id,name'
                )
                ->orderBy('id', 'desc')
                ->get();

            $totalProductsStock = $stockValueByCategoryproducts->count();

            $categoryStatsStock = $totalProductsStock > 0
                ? $stockValueByCategoryproducts->groupBy('category.id')->map(function ($items, $categoryId) use ($totalProductsStock) {
                    $count = $items->count();
                    $percentages = round(($count / $totalProductsStock) * 100, 2);

                    // stock total_cost sum by category
                    $totalStockValue = $items->sum(function ($product) {
                        // agar relation one-to-one hai
                        return optional($product->stocksData)->total_cost ?? 0;

                        // agar one-to-many hai to use this instead:
                        // return $product->stocksData->sum('total_cost');
                    });

                    return [
                        'category_id'        => $categoryId,
                        'category_name'      => optional($items->first()->category)->name,
                        'product_count'      => $count,
                        'percentage'         => $percentages,
                        'total_stock_value'  => $totalStockValue,
                    ];
                })->values()
                : collect();

            $stockValueByCategory = $categoryStatsStock->toArray();
                

            $issuedRecordsWorkstation = ScanInOutProduct::with('workStation:id,name')->where('type','out')->get();

            $totalIssues = $issuedRecordsWorkstation->count();

            $workstationWiseCount = $issuedRecordsWorkstation
                ->filter(function ($record) {
                    return $record->workStation; // filter only those with department
                })
                ->groupBy(function ($item) {
                    return $item->workStation->name;
                })
                ->map(function ($items, $workstationName) use ($totalIssues) {
                    $count = $items->count();
                    $percentage = ($totalIssues > 0) ? round(($count / $totalIssues) * 100, 2) : 0;

                    return [
                        'workstation_name' => $workstationName,
                        'issue_count' => $count,
                        'issue_percentage' => $percentage
                    ];
                })->values(); // Optional: Reset keys to 0-based index

            $issuedRecordsWorkstation = [
                'total_issues' => $totalIssues,
                'workstation_stats' => $workstationWiseCount
            ];


                        $topscanRecordsIssuesItemValue = ScanInOutProduct::with([
                    'product:id,product_name,sku,inventory_alert_threshold,commit_stock_check,opening_stock,category_id',
                    'product.category:id,name',
                    'product.orders:id,product_id,quantity',
                    'vendor:id,vendor_name',
                    'employee:id,employee_name',
                    'user:id,name',
                    'location:id,name',
                    'machine:id,name',
                    'workStation:id,name',
                    'department:id,name'
                ])->get();

            $totalIssuesItemValue = $topscanRecordsIssuesItemValue->count();

            $topTenIssuesItemValue = $topscanRecordsIssuesItemValue->groupBy('product_id')->map(function ($items, $productId) use ($totalIssuesItemValue) {
                $counts = $items->count();
                $percentage = ($totalIssuesItemValue > 0) ? round(($counts / $totalIssuesItemValue) * 100, 2) : 0;

                // Issues product value (example: orders quantity × product price)
                $issueValue = $items->sum(function ($scan) {
                    $product = $scan->product;
                    if (!$product) {
                        return 0;
                    }
                   
                    $stock_update = Stock::where('product_id',$product->id)->get();

                    // $total = $stock_update->sum('total_cost');
   $total = $stock_update->sum(function ($item) {
    return (float) $item->total_cost;
});
// print_r($total);die;
                    // agar orders relation hai
                    $totalQty = $product->orders->sum('quantity') ?? 0;

                    // maan lete hain product me price field hai
                    return $totalQty * ( $total?? 0);
                });

                return [
                    'product_id'   => $productId,
                    'product_name' => optional($items->first()->product)->product_name,
                    'category'     => optional(optional($items->first()->product)->category)->name,
                    'issues_count' => $counts,
                    'percentage'   => $percentage,
                    'issue_value'  => $issueValue,
                ];
            })->values();


            return response()->json([
                'total_product' => $productCount,
                'total_employee_tools' => $employeeUsingProduct,
                'low_stock_alert' => $low_stock_alert,
                'stock_movement' => $allMonths,
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
            ], 200);

    }
}
