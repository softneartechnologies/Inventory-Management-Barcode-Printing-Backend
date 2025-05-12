<?php

namespace App\Http\Controllers;

use App\Models\UomCategory;
use App\Models\UomUnit;
use Illuminate\Http\Request;

class UomCategoryController extends Controller
{
    public function index()
    {
       $categories = UomCategory::with('units')->get();

        return response()->json([
            'unite_of_measure_category' => $categories
        ], 200);

    }

public function categoryDetails($id)
{
    $category = UomCategory::with('units')->find($id);
// print_r($category);die;
    if (!$category) {
        return response()->json(['message' => 'Category not found'], 404);
    }

    return response()->json([
        'id' => $category->id,
        'name' => $category->name,
        'units' => $category->units->map(function ($unit) {
            return [
                'id' => $unit->id,
                'unit_name' => $unit->unit_name,
                'abbreviation' => $unit->abbreviation,
                'reference' => $unit->reference,
                'ratio' => $unit->ratio,
                'rounding' => $unit->rounding,
                'active' => $unit->active,
            ];
        })
    ], 200);
}


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:uom_categories,name',
        ]);

        $category = UomCategory::create($validated);
        return response()->json(['category'=>$category], 200);
    }



    public function show($id)
    {
        $uomCategory =UomCategory::find($id);
        return response()->json(['uomCategory'=>$uomCategory], 200);
    }

    public function update(Request $request, $id)
    {
         $uomCategory = UomCategory::find($id);
        $validated = $request->validate([
            'name' => 'required|string|unique:uom_categories,name,' . $uomCategory->id,
        ]);
       
        $uomCategory->update($validated);
        return response()->json($uomCategory);
    }

    public function destroy($id)
    {
        $uomCategory = UomCategory::find($id);
        $uomCategory->delete();
        return response()->json(['message'=>'Successfully'], 200);
    }



    public function storeUnits(Request $request)
    {
        $validated = $request->validate([
            'uom_category_id' => 'required',
            'unit_name' => 'required|string|unique:uom_categories,name',
            'reference' => 'required',
            'ratio' => 'required',
            'rounding' => 'required',
            'active' => 'required',
        ]);
        $validated['abbreviation'] =$request->reference;
        $category = UomUnit::create($validated);
        return response()->json($category, 200);
    }


    public function showUnits($id)
    {
        $uomCategory =UomUnit::find($id);
        return response()->json(['uomCategory'=>$uomCategory], 200);
    }

    public function updateUnits(Request $request, $id)
    {
         $uomCategory = UomUnit::find($id);
        $validated = $request->validate([
            'name' => 'required|string|unique:uom_categories,name,' . $uomCategory->id,
        ]);
       
        $uomCategory->update($validated);
        return response()->json($uomCategory);
    }

    public function destroyUnits($id)
    {
        $uomCategory = UomUnit::find($id);
        $uomCategory->delete();
        return response()->json(['message'=>'Successfully'], 200);
    }
}



