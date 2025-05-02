<?php

namespace App\Http\Controllers;

use App\Models\BarcodeSetting;
use Illuminate\Http\Request;

class BarcodeSettingController extends Controller
{
    // Get all barcode settings
    public function index()
    {
        
        // $barcodeSettings = BarcodeSetting::all();
    
        // foreach ($barcodeSettings as $setting) {
        //     $setting->update($request->all());
        // }
    
        // Fetch updated data again
        $updatedSettings = BarcodeSetting::all();
    
        return response()->json([
            'message' => 'Barcode settings List successfully',
            'data' => $updatedSettings
        ], 200);


        // return response()->json(BarcodeSetting::all(), 200);
    }


    // Get a single barcode setting
    public function show()
    {
        // $barcodeSetting = BarcodeSetting::first();
        $updatedSettings = BarcodeSetting::all();
    
        // return response()->json([
        //     'message' => 'Barcode settings List successfully',
        //     'data' => $updatedSettings
        // ], 200);



        if (!$updatedSettings) {
            return response()->json(['message' => 'Barcode setting not found'], 404);
        }

        return response()->json(['barcode_setting'=>$updatedSettings], 200);
    }

    // Update barcode setting
   public function update(Request $request)
{
    $settings = $request->all();

    foreach ($settings as $item) {
        // Find the setting by name
        $setting = BarcodeSetting::where('key', $item['key'])->first();

        if ($setting) {
            $setting->value = $item['value'];
            $setting->save();
        }
    }

    $updatedSettings = BarcodeSetting::all();

    return response()->json([
        'message' => 'Barcode settings updated successfully',
        'data' => $updatedSettings
    ], 200);
}



   
}
