<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // public function index()
    // {
    //     $orders = Order::with('product:id,product_name','category:id,name','location:id,name','user:id,name')->where('deleted', '0')->orderBy('id', 'desc')->get();
    
    //     // Transform data to move product_name to the root level
    //     $orders = $orders->map(function ($order) {
    //         return [
    //             'id' => $order->id,
    //             'deleted' => $order->deleted,
    //             'current_date' => $order->current_date,
    //             'product_id' => $order->product_id,
    //             'sku' => $order->sku,
    //             'current_stock' => $order->current_stock,
    //             'threshold_count' => $order->threshold_count,
    //             'location_name' => optional($order->location)->name,
    //             'quantity'=>$order->quantity,
    //             'category_name'=>optional($order->category)->name,
    //             'total_current_stock'=>$order->total_current_stock,
    //             'order_by'=>optional($order->user)->name,
    //             'status'=>$order->status,
    //             'created_at' => $order->created_at,
    //             'updated_at' => $order->updated_at,
    //             'product_name' => $order->product->product_name ?? null, // Move product_name outside
    //         ];
    //     });
    
    //     return response()->json($orders, 200);
    // }
    
    public function index(Request $request)
{
    // Defaults
    $sortBy = $request->get('sort_by', 'id');       // default sort column
    $sortOrder = $request->get('sort_order', 'desc'); // default sort order
    $limit = $request->get('per_page', null);          // default = all records
    $search = $request->get('search', null);

    $query = Order::with([
        'product:id,product_name',
        'category:id,name',
        'location:id,name',
        'user:id,name'
    ])->where('deleted', '0');

    // 🔎 Searching
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('sku', 'like', "%{$search}%")
              ->orWhere('status', 'like', "%{$search}%")
              ->orWhere('quantity', 'like', "%{$search}%")
              ->orWhereHas('product', function ($q2) use ($search) {
                  $q2->where('product_name', 'like', "%{$search}%");
              })
              ->orWhereHas('category', function ($q2) use ($search) {
                  $q2->where('name', 'like', "%{$search}%");
              })
              ->orWhereHas('location', function ($q2) use ($search) {
                  $q2->where('name', 'like', "%{$search}%");
              })
              ->orWhereHas('user', function ($q2) use ($search) {
                  $q2->where('name', 'like', "%{$search}%");
              });
        });
    }

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
    // ✅ Sorting (safe handling for related columns)
    $allowedSorts = ['id', 'sku', 'quantity', 'status', 'current_stock', 'threshold_count', 'total_current_stock', 'created_at'];

    if (in_array($sortBy, $allowedSorts)) {
        $query->orderBy($sortBy, $sortOrder);
    } elseif ($sortBy === 'product_name') {
        $query->orderBy(
            \App\Models\Product::select('product_name')
                ->whereColumn('products.id', 'orders.product_id'),
            $sortOrder
        );
    } elseif ($sortBy === 'category_name') {
        $query->orderBy(
            \App\Models\Category::select('name')
                ->whereColumn('categories.id', 'orders.category_id'),
            $sortOrder
        );
    } elseif ($sortBy === 'location_name') {
        $query->orderBy(
            \App\Models\Location::select('name')
                ->whereColumn('locations.id', 'orders.location_id'),
            $sortOrder
        );
    } elseif ($sortBy === 'order_by') {
        $query->orderBy(
            \App\Models\User::select('name')
                ->whereColumn('users.id', 'orders.user_id'),
            $sortOrder
        );
    } else {
        $query->orderBy('id', 'desc'); // fallback
    }

    // 📑 Pagination or All
    if (!empty($limit) && is_numeric($limit)) {
        $orders = $query->paginate($limit);
    } else {
        $orders = $query->get();
    }

    // Transform output
    $orders->transform(function ($order) {
        return [
            'id' => $order->id,
            'deleted' => $order->deleted,
            'current_date' => $order->current_date,
            'product_id' => $order->product_id,
            'sku' => $order->sku,
            'current_stock' => $order->current_stock,
            'threshold_count' => $order->threshold_count,
            'location_name' => optional($order->location)->name,
            'quantity' => $order->quantity,
            'category_name' => optional($order->category)->name,
            'total_current_stock' => $order->total_current_stock,
            'order_by' => optional($order->user)->name,
            'status' => $order->status,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
            'product_name' => $order->product->product_name ?? null,
        ];
    });

    return response()->json($orders, 200);
}


    // Store a newly created product in storage.
    public function store(Request $request)
    {
        // print_r($request->all());die;
        $validated = $request->validate([
            'product_id' => 'required',
            'sku' => 'required',
            'current_stock' => 'required|integer',
            'threshold_count' => 'required|integer',
            'location_id' => 'required|integer',
            'quantity' => 'required|integer',
            'current_date' => 'required',
            'category_id' => 'required',
            'total_current_stock' => 'required',
            'order_by' => 'required',
            'status' => 'required',
        ]);

        $Order = Order::create($validated);

        return response()->json($Order, 200);
    }

    // Display the specified product.
    public function show(Order $Order)
    {
        return $Order;
    }

    // Update the specified product in storage.
    public function update(Request $request, Order $Order)
    {
        $validated = $request->validate([
            'product_id' => 'required',
            'sku' => 'required',
            'current_stock' => 'sometimes|required|integer',
            'threshold_count' => 'sometimes|required|integer',
            'location_id' => 'sometimes|required|string',
            'category_id' => 'required',
            'total_current_stock' => 'required',
            'order_by' => 'required',
        ]);

        $Order->update($validated);

        return response()->json($product, 200);
    }

    // Remove the specified product from storage.
    public function destroy(Order $Order)
    {
        $Order->delete();

        return response()->json(null, 204);
    }
    
     public function removeOrder($id)
    {

        $order = Order::find($id);
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $validatedData['deleted'] = '1';
            

        $order->update($validatedData);
        
        return response()->json(['message' => 'Order Removed successfully'], 200);
    }
    
}
