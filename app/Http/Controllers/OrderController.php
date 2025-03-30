<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('product:id,product_name')->get();
    
        // Transform data to move product_name to the root level
        $orders = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'product_id' => $order->product_id,
                'sku' => $order->sku,
                'current_stock' => $order->current_stock,
                'threshold_count' => $order->threshold_count,
                'location' => $order->location,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
                'product_name' => $order->product->product_name ?? null, // Move product_name outside
            ];
        });
    
        return response()->json($orders, 200);
    }
    


    // Store a newly created product in storage.
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required',
            'sku' => 'required',
            'current_stock' => 'required|integer',
            'threshold_count' => 'required|integer',
            'location' => 'required|string',
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
            'location' => 'sometimes|required|string',
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
    
}
