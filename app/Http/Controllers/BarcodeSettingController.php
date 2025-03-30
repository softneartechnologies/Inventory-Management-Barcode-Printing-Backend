<?php

namespace App\Http\Controllers;

use App\Models\BarcodeSetting;
use Illuminate\Http\Request;

class BarcodeSettingController extends Controller
{
    // Get all barcode settings
    public function index()
    {
        return response()->json(BarcodeSetting::all(), 200);
    }


    // Get a single barcode setting
    public function show()
    {
        $barcodeSetting = BarcodeSetting::first();

        if (!$barcodeSetting) {
            return response()->json(['message' => 'Barcode setting not found'], 404);
        }

        return response()->json(['barcode_setting'=>$barcodeSetting], 200);
    }

    // Update barcode setting
    public function update(Request $request)
    {
        $barcodeSetting = BarcodeSetting::first();

        if (!$barcodeSetting) {
            return response()->json(['message' => 'Barcode setting not found'], 404);
        }

        // $request->validate([
        //     'sku' => 'string|unique:barcode_settings,sku,' . $id,
        //     'product_name' => 'string',
        //     'description' => 'nullable|string',
        //     'units' => 'string',
        //     'category' => 'string',
        //     'sub_category' => 'nullable|string',
        //     'manufacturer' => 'nullable|string',
        //     'vendor' => 'nullable|string',
        //     'model' => 'nullable|string',
        //     'returnable' => 'boolean',
        //     'cost_price' => 'numeric|min:0',
        //     'selling_cost' => 'numeric|min:0',
        //     'weight' => 'nullable|numeric|min:0',
        //     'weight_unit' => 'nullable|string',
        //     'length' => 'nullable|numeric|min:0',
        //     'width' => 'nullable|numeric|min:0',
        //     'depth' => 'nullable|numeric|min:0',
        //     'measurement_unit' => 'nullable|string',
        // ]);

        $barcodeSetting->update($request->all());

        return response()->json(['message' => 'Barcode setting updated successfully', 'data' => $barcodeSetting], 200);
    }


   
}
