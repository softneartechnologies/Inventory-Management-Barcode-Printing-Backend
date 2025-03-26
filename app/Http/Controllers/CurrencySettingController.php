<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CurrencySetting;
use Illuminate\Support\Facades\Validator;

class CurrencySettingController extends Controller
{
    // ✅ Get All Currencies
    public function index()
    {
        $currencies = CurrencySetting::all();
        return response()->json($currencies, 200);
    }

    // ✅ Create a New Currency
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'currency_name' => 'required|string|max:255',
            'currency_code' => 'required|string|max:10|unique:currency_settings,currency_code',
            'symbol' => 'required|string|max:5',
            'default_status' => 'required|in:yes,no'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // If "default_status" is set to "yes", reset all other currencies to "no"
        if ($request->default_status === 'yes') {
            CurrencySetting::where('default_status', 'yes')->update(['default_status' => 'no']);
        }

        $currency = CurrencySetting::create($request->all());

        return response()->json(['message' => 'Currency created successfully', 'currency' => $currency], 201);
    }

    // ✅ Get Single Currency
    public function show($id)
    {
        $currency = CurrencySetting::find($id);
        if (!$currency) {
            return response()->json(['error' => 'Currency not found'], 404);
        }

        return response()->json($currency, 200);
    }

    // ✅ Update Currency
    public function update(Request $request, $id)
    {
        $currency = CurrencySetting::find($id);
        if (!$currency) {
            return response()->json(['error' => 'Currency not found'], 404);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'currency_name' => 'required|string|max:255',
            'currency_code' => 'required|string|max:10|unique:currency_settings,currency_code,' . $id,
            'symbol' => 'required|string|max:5',
            'default_status' => 'required|in:yes,no'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // If "default_status" is set to "yes", reset all other currencies to "no"
        if ($request->default_status === 'yes') {
            CurrencySetting::where('default_status', 'yes')->update(['default_status' => 'no']);
        }

        $currency->update($request->all());

        return response()->json(['message' => 'Currency updated successfully', 'currency' => $currency], 200);
    }

    // ✅ Delete Currency
    public function destroy($id)
    {
        $currency = CurrencySetting::find($id);
        if (!$currency) {
            return response()->json(['error' => 'Currency not found'], 404);
        }

        $currency->delete();

        return response()->json(['message' => 'Currency deleted successfully'], 200);
    }
}
