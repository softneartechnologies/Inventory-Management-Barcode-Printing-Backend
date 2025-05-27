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
        
        // Safely update counts
        foreach ($monthlyCountsRaw as $row) {
            $month = $row->month;
            $type = $row->type;
            $total = $row->total;
        
            $data = $allMonths->get($month);
            $data[$type] = $total;
            $allMonths->put($month, $data);
        }
        


                $total = Product::count();

                // Returnable products (assuming is_returnable = 1)
                $returnable = Product::where('returnable', 1)->count();

                // Non-returnable products (assuming is_returnable = 0)
                $nonReturnable = $total - $returnable;

                // Avoid divide-by-zero
                $returnablePercent = $total > 0 ? round(($returnable / $total) * 100, 2) : 0;
                $nonReturnablePercent = $total > 0 ? round(($nonReturnable / $total) * 100, 2) : 0;

                // Return as JSON or use in view
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

    // Category-wise product count and percentage
    $categoryStats = $totalProducts > 0
        ? $products->groupBy('category.id')->map(function ($items, $categoryId) use ($totalProducts) {
            $count = $items->count();
            $percentage = round(($count / $totalProducts) * 100, 2);

            return [
                'category_id' => $categoryId,
                'category_name' => optional($items->first()->category)->name,
                'product_count' => $count,
                'percentage' => $percentage, // percentage of total products
            ];
        })->values()
        : collect(); // agar product hi nahi hai to empty collection

    $uniqueCategoryCount = $categoryStats->toArray();

    // Aapko yeh variables khud define karne honge
    $productCount = $totalProducts;
    $employeeUsingProduct = 0; // Define as needed
    // $low_stock_alert = []; // Define as needed
    // $allMonths = []; // Define as needed
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

$topTenGrouped = $topscanRecords
    ->groupBy('product_id')
    ->map(function ($items, $productId) use ($totalIssues) {
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
    ], 200);
}
}
