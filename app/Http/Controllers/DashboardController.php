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
    $low_stock_alert = []; // Define as needed
    $allMonths = []; // Define as needed
    $returnableNonReturnableItems = []; // Define as needed

    return response()->json([
        'total_product' => $productCount,
        'total_employee_tools' => $employeeUsingProduct,
        'low_stock_alert' => $low_stock_alert,
        'stock_movement' => $allMonths,
        'returnableNonReturnableItems' => $returnableNonReturnableItems,
        'categories_list' => $categories_list,
        'uniqueCategoryCount' => $uniqueCategoryCount,
    ], 200);
}
}
