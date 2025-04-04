<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Location;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
// use Milon\Barcode\Facades\DNS1DFacade as DNS1D;
// use Milon\Barcode\Facades\DNS2DFacade as DNS2D;
use PDF;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use DB;
// use Milon\Barcode\DNS1D;
// use Milon\Barcode\DNS2D;
use Milon\Barcode\Facades\DNS1D;
use Milon\Barcode\Facades\DNS2D;




class ProductController extends Controller
{
    public function index()
    {
        return response()->json(Product::all(), 200);
    }

    // public function store(Request $request)
    // {
    //     $validatedData = $request->validate([
    //         'product_name' => 'required|string|max:255',
    //         'sku' => 'required|string|max:255|unique:products',
    //         'units' => 'required|string',
    //         'category' => 'required|string',
    //         'sub_category' => 'nullable|string',
    //         'manufacturer' => 'nullable|string',
    //         'vendor' => 'nullable|string',
    //         'model' => 'nullable|string',
    //         'weight' => 'nullable|numeric',
    //         'weight_unit' => 'nullable|string',
    //         'storage_location' => 'nullable|string',
    //         'thumbnail' => 'nullable|string',
    //         'description' => 'nullable|string',
    //         'returnable' => 'boolean',
    //         'track_inventory' => 'boolean',
    //         'opening_stock' => 'integer|min:0',
    //         'selling_cost' => 'nullable|numeric',
    //         'cost_price' => 'nullable|numeric',
    //         'commit_stock_check' => 'boolean',
    //         'project_name' => 'nullable|string',
    //         'length' => 'nullable|numeric',
    //         'width' => 'nullable|numeric',
    //         'depth' => 'nullable|numeric',
    //         'measurement_unit' => 'nullable|string',
    //         'inventory_alert' => 'integer|min:0',
    //         'status' => ['required', Rule::in(['active', 'inactive'])],
    //     ]);

    //     // barcode generate
    //     $barcode = $this->generateBarcode($validated['sku']);
    //     $validated['barcode_number'] = $barcode['number'];
    //     $validated['barcode'] = $barcode['image'];

    //     // QR Code generate
    //     $productDetails = [
    //         'barcode_number' => $product->barcode_number,
    //         'name' => $product->name,
    //         'sku' => $product->sku,
    //         'description' => $product->description,
    //         'price' => number_format($product->price, 2),
    //         'stock' => $product->stock
    //     ];

    //     // Update the product with QR code
    //     $product->update([
    //         'qr_code' => DNS2D::getBarcodePNG(json_encode($productDetails), 'QRCODE')
    //     ]);


    //     $product = Product::create($validatedData);
    //     return response()->json(['message' => 'Product created successfully', 'product' => $product], 201);
    // }

    public function store(Request $request)
{
    $validatedData = $request->validate([
        'product_name' => 'required|string|max:255',
        'sku' => 'required|string|max:255|unique:products',
        'units' => 'required|string',
        'category' => 'required|string',
        'sub_category' => 'nullable|string',
        'manufacturer' => 'nullable|string',
        'vendor' => 'nullable|string',
        'model' => 'nullable|string',
        'weight' => 'nullable|numeric',
        'weight_unit' => 'nullable|string',
        'storage_location' => 'array',
        'thumbnail' => 'nullable|string',
        'description' => 'nullable|string',
        'returnable' => 'boolean',
        'track_inventory' => 'boolean',
        'opening_stock' => 'integer|min:0',
        'selling_cost' => 'nullable|numeric',
        'cost_price' => 'nullable|numeric',
        'commit_stock_check' => 'boolean',
        'project_name' => 'nullable|string',
        'length' => 'nullable|numeric',
        'width' => 'nullable|numeric',
        'depth' => 'nullable|numeric',
        'measurement_unit' => 'nullable|string',
        'inventory_alert_threshold' => 'integer|min:0',
        'status' => ['required', Rule::in(['active', 'inactive'])],
    ]);

    // ✅ Generate Barcode
    // $barcodeImage = DNS1D::getBarcodePNG($barcodeNumber, 'C39'); // Generate barcode image
    
    
    // if ($barcodeImage) {
        //     $path = $barcodeImage->store('public/barcodes');
        
        // }
        
    $barcodeNumber = $request->sku; // Unique barcode
    if ($barcodeNumber) {
        // ✅ Generate Barcode as Base64
        // $barcodeImage = \Milon\Barcode\DNS1D::getBarcodePNG($barcodeNumber, 'C39');
        $barcodeImage = DNS1D::getBarcodePNG($barcodeNumber, 'C39');
    
        // $barcodeImage = 'jjkkjjhjkkjsakjasasajajasjjsajassaejejea';
        // ✅ Convert Base64 to an Image File
        $imagePath = 'public/barcodes/' . $barcodeNumber . '.png'; 
        Storage::put($imagePath, base64_decode($barcodeImage));
    
        // ✅ Store the public path for access
        $savedBarcodePath = str_replace('public/', 'storage/', $imagePath);
    }

    // $barcodes = storage_path('app/public/barcodes');
    // $qrcode = storage_path('app/public/qrcode');
    // $images = storage_path('app/public/images');

    // Add barcode data
    $validatedData['barcode_number'] = $barcodeNumber;
    $validatedData['generated_barcode'] = $barcodeImage;

    // ✅ Create Product
    

    // ✅ Generate QR Code after product is created
    $productDetails = [
        'barcode_number' => $barcodeNumber,
        'name' => $request->product_name,
        'sku' => $request->sku,
        'description' => $request->description,
        'price' => number_format($request->selling_cost, 2),
        'stock' => $request->opening_stock
    ];

    // $validatedData['generated_qrcode'] = DNS2D::getBarcodePNG(json_encode($productDetails), 'QRCODE');

    // if ($validatedData['generated_qrcode']) {
    //     $path = $validatedData['generated_qrcode']->store('public/qrcode');
    //     // $validatedData['thumbnail'] = str_replace('public/', 'storage/', $path);
    // }

    if ($productDetails) {
        // ✅ Generate a Unique QR Code Name
        $fileName = 'qrcode_' . time() . '.png';
    
        // ✅ Generate QR Code as Base64

        $qrcodeBase64 = DNS2D::getBarcodePNG(json_encode($productDetails), 'QRCODE');
        // $qrcodeBase64 = json_encode($productDetails).'QRCODE';
    
        // ✅ Convert Base64 to an Image File and Save
        $imagePath = 'public/qrcode/' . $fileName; 
        Storage::put($imagePath, base64_decode($qrcodeBase64));
    
        // ✅ Store the public path for access
        $savedQRCodePath = str_replace('public/', 'storage/', $imagePath);
    
        // ✅ Store QR Code Path in Database
        $validatedData['generated_qrcode'] = $qrcodeBase64;
    }


    if ($request->hasFile('thumbnail')) {
        $path = $request->file('thumbnail')->store('public/thumbnails');
        $validatedData['thumbnail'] = str_replace('public/', 'storage/', $path);
    }

    $validatedData['location_id'] = json_encode($request->storage_location);

    
    $product = Product::create($validatedData);

   $multiLocation = $request->storage_location;

    foreach($multiLocation as $multiData){

    
            Stock::create([
                'product_id'=>$product->id,
                'vendor_id'=>$request->vendor,
                'category_id'=>$request->category,
                'current_stock'=>$request->opening_stock,
                'unit'=>$request->units,
                'location_id'=>$multiData,
                'stock_date'=>now(),
            ]);
        }

    return response()->json([
        'message' => 'Product created successfully',
        'product' => $product
    ], 200);
}

    public function show($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
        return response()->json($product);
    }


    public function view($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
        return response()->json($product);
    }


    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $validatedData = $request->validate([
            'product_name' => 'string|max:255',
            'sku' => 'string|max:255|unique:products,sku,' . $id,
            'units' => 'string',
            'category' => 'string',
            'sub_category' => 'nullable|string',
            'manufacturer' => 'nullable|string',
            'vendor' => 'nullable|string',
            'model' => 'nullable|string',
            'weight' => 'nullable|numeric',
            'weight_unit' => 'nullable|string',
            'location_id' => 'nullable|string',
            'thumbnail' => 'nullable|string',
            'description' => 'nullable|string',
            'returnable' => 'boolean',
            'track_inventory' => 'boolean',
            'opening_stock' => 'integer|min:0',
            'selling_cost' => 'nullable|numeric',
            'cost_price' => 'nullable|numeric',
            'commit_stock_check' => 'boolean',
            'project_name' => 'nullable|string',
            'length' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'depth' => 'nullable|numeric',
            'measurement_unit' => 'nullable|string',
            'inventory_alert_threshold' => 'integer|min:0',
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);


        $barcodeNumber = $request->sku; // Unique barcode
    if ($barcodeNumber) {
        // ✅ Generate Barcode as Base64
        // $barcodeImage = DNS1D::getBarcodePNG($barcodeNumber, 'C39');
        $barcodeImage = $barcodeNumber. 'C39';
    
        // ✅ Convert Base64 to an Image File
        $imagePath = 'public/barcodes/' . $barcodeNumber . '.png'; 
        Storage::put($imagePath, base64_decode($barcodeImage));
    
        // ✅ Store the public path for access
        $savedBarcodePath = str_replace('public/', 'storage/', $imagePath);
    }

    // $barcodes = storage_path('app/public/barcodes');
    // $qrcode = storage_path('app/public/qrcode');
    // $images = storage_path('app/public/images');

    // Add barcode data
    $validatedData['barcode_number'] = $barcodeNumber;
    $validatedData['generated_barcode'] = $barcodeImage;

    // ✅ Create Product
    

    // ✅ Generate QR Code after product is created
    $productDetails = [
        'barcode_number' => $barcodeNumber,
        'name' => $request->product_name,
        'sku' => $request->sku,
        'description' => $request->description,
        'price' => number_format($request->selling_cost, 2),
        'stock' => $request->opening_stock
    ];

    // $validatedData['generated_qrcode'] = DNS2D::getBarcodePNG(json_encode($productDetails), 'QRCODE');

    // if ($validatedData['generated_qrcode']) {
    //     $path = $validatedData['generated_qrcode']->store('public/qrcode');
    //     // $validatedData['thumbnail'] = str_replace('public/', 'storage/', $path);
    // }

    if ($productDetails) {
        // ✅ Generate a Unique QR Code Name
        $fileName = 'qrcode_' . time() . '.png';
    
        // ✅ Generate QR Code as Base64
        // $qrcodeBase64 = DNS2D::getBarcodePNG(json_encode($productDetails), 'QRCODE');

        $qrcodeBase64 = json_encode($productDetails).'QRCODE';
    
        // ✅ Convert Base64 to an Image File and Save
        $imagePath = 'public/qrcode/' . $fileName; 
        Storage::put($imagePath, base64_decode($qrcodeBase64));
    
        // ✅ Store the public path for access
        $savedQRCodePath = str_replace('public/', 'storage/', $imagePath);
    
        // ✅ Store QR Code Path in Database
        $validatedData['generated_qrcode'] = $qrcodeBase64;
    }


    if ($request->hasFile('thumbnail')) {
        $path = $request->file('thumbnail')->store('public/thumbnails');
        $validatedData['thumbnail'] = str_replace('public/', 'storage/', $path);
    }


        $product->update($validatedData);
        return response()->json(['message' => 'Product updated successfully', 'product' => $product], 200);
    }

    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $product->delete();
        return response()->json(['message' => 'Product deleted successfully'], 200);
    }

    // public function updateStock(Request $request, $product_id)
    // {
    //     // print_r($request->all());die;
    //     $product = Product::find($product_id);
    //     if (!$product) {
    //         return response()->json(['error' => 'Product not found'], 404);
    //     }

    //     $validatedData = $request->validate([
           
    //         // 'product_id' => 'string',
    //         'current_stock' => 'nullable|string',
    //         'quantity' => 'nullable|string',
    //         'unit' => 'nullable|string',
    //         'reason_for_update' => 'nullable|string',
    //         'location' => 'array',
    //         'stock_date' => 'nullable|string',
    //         'vendor_id' => 'nullable|string',
    //         'adjustment' => 'nullable|string',
           
    //     ]);

    //     // foreach(){

    //     $multiLocation = $request->storage_location;

        
    //     foreach($multiLocation as $key=>$multiData){
            
    //         $product_location = Stock::where('product_id',$product_id)->where('location',$multiData['location'])->first();

    //         // print_r($product_location->location);die;
    //         // print_r($multiData['adjustment']);die;
    //         $validatedData['product_id'] = $product_id;
    //         $validatedData['category_id'] = $product->category;

    //         if($multiData['adjustment'] =='add'){

    //             $productopening_stock =  $product->opening_stock + $multiData['quantity'];
    //         }

    //         if($multiData['adjustment'] =='subscription'){

    //             $productopening_stock = $product->opening_stock - $multiData['quantity'];
    //         }


    //         // ✅ Generate QR Code after product is created
    //         $productDetails = [
    //             'opening_stock' => $productopening_stock
    //         ];
    //             $validatedData['current_stock'] =$product->opening_stock;
    //             $validatedData['new_stock'] =$productopening_stock;

    //             // print_r($product_location->location);die;

    //             if($product_location->location === $multiData['location']){

    //                 $product_location->update($validatedData);
    //             }else{

    //                 $newStock =  $multiData;
    //                 $newStock['stock_date'] = $rewuest->stock_date;
    //                 $newStock['vendor_id'] = $rewuest->vendor_id;
    //                 $newStock['reason_for_update'] = $rewuest->reason_for_update;
    //                 print_r($newStock);die;
    //                 $stock = Stock::create($validatedData);
    //             }

    //     }
    //         $product->update($productDetails);

    //      return response()->json(['message' => 'Stock updated successfully', 'product' => $stock], 200);
    // }

    // public function updateStock(Request $request, $product_id)
    // {
    //     $product = Product::find($product_id);
    //     if (!$product) {
    //         return response()->json(['error' => 'Product not found'], 404);
    //     }

    //     $validatedData = $request->validate([
    //         'stock_date' => 'nullable|string',
    //         'vendor_id' => 'nullable|string',
    //         'reason_for_update' => 'nullable|string',
    //         'storage_location' => 'required|array',
    //         'storage_location.*.current_stock' => 'nullable|string',
    //         'storage_location.*.quantity' => 'required|numeric',
    //         'storage_location.*.unit' => 'nullable|string',
    //         'storage_location.*.location' => 'required|string',
    //         'storage_location.*.adjustment' => 'required|string|in:add,subscription',
    //     ]);

    //     $multiLocation = $request->storage_location;

    //     foreach ($multiLocation as $multiData) {
    //         $product_location = Stock::where('product_id', $product_id)
    //             ->where('location', $multiData['location'])
    //             ->first();
                
    //             $validatedData = [
    //                 'product_id' => $product_id,
    //                 'category_id' => $product->category,
    //                 'current_stock' =>$multiData['current_stock'],
    //                 'unit' => $multiData['unit'] ?? null,
    //                 'location' => $multiData['location'],
    //                 'quantity' => $multiData['quantity'],
    //                 'adjustment' => $multiData['adjustment'],
    //                 'stock_date' => $request->stock_date,
    //                 'vendor_id' => $request->vendor_id,
    //                 'reason_for_update' => $request->reason_for_update,
    //             ];

    //         // Adjust stock
    //         if ($multiData['adjustment'] === 'add') {
    //             $productopening_stock = $product->opening_stock + $multiData['quantity'];
    //             $new_stock = $product_location->current_stock + $multiData['quantity'];

    //             if(!empty($product_location->current_stock)){
    //                 $new_stock = $product_location->current_stock + $multiData['quantity'];
    //                 $validatedData['current_stock'] = $product_location->current_stock;
    //             }else{

    //                 $new_stock = $multiData['current_stock'] + $multiData['quantity'];
    //                 $validatedData['current_stock'] = $multiData['current_stock'];
    //             }

    //         } else if($multiData['adjustment'] ==='subscription'){
    //             $productopening_stock = $product->opening_stock - $multiData['quantity'];
    //             if(!empty($product_location->current_stock)){
    //                 $new_stock = $product_location->current_stock - $multiData['quantity'];

    //                 $validatedData['current_stock'] = $product_location->current_stock;
    //             }else{

    //                 $new_stock = $multiData['current_stock'] - $multiData['quantity'];

    //                 $validatedData['current_stock'] = $multiData['current_stock'];
    //             }
    //         }

    //         $validatedData['new_stock'] = $new_stock;
            
            

    //         if ($product_location) {
    //             $product_location->update($validatedData);
    //         } else {
    //             Stock::create($validatedData);
    //         }

    //         // Update product's opening stock once per loop
    //         $product->update(['opening_stock' => $productopening_stock]);
    //     }

    //     return response()->json(['message' => 'Stock updated successfully'], 200);
    // }

    public function updateStock(Request $request, $product_id)
    {
        $product = Product::find($product_id);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $validatedRequest = $request->validate([
            'stock_date' => 'nullable|string',
            'vendor_id' => 'nullable|string',
            'reason_for_update' => 'nullable|string',
            'storage_location' => 'required|array',
            'storage_location.*.current_stock' => 'nullable|numeric',
            'storage_location.*.quantity' => 'required|numeric',
            'storage_location.*.unit' => 'nullable|string',
            'storage_location.*.location' => 'required|string',
            'storage_location.*.adjustment' => 'required|string|in:add,subscription',
        ]);

        $multiLocation = $validatedRequest['storage_location'];

        foreach ($multiLocation as $multiData) {
            $product_location = Stock::where('product_id', $product_id)
                ->where('location_id', $multiData['location'])
                ->first();

            $quantity = $multiData['quantity'];
            $adjustment = $multiData['adjustment'];

            if ($product_location) {
                // Update existing stock record
                // $currentStock = $product_location->current_stock;
                $currentStock = $multiData['current_stock'];

                if ($adjustment === 'add') {
                    $newStock = $currentStock + $quantity;
                    $productOpeningStock = $product->opening_stock + $quantity;
                } else {
                    $newStock = $currentStock - $quantity;
                    $productOpeningStock = $product->opening_stock - $quantity;
                }

                $stockData = [
                    'current_stock' => $currentStock,
                    'new_stock' => $newStock,
                    'unit' => $multiData['unit'] ?? $product_location->unit,
                    'quantity' => $quantity,
                    'adjustment' => $adjustment,
                    'stock_date' => $validatedRequest['stock_date'] ?? null,
                    'vendor_id' => $validatedRequest['vendor_id'] ?? null,
                    'reason_for_update' => $validatedRequest['reason_for_update'] ?? null,
                ];

                $product_location->update($stockData);
            } else {
                // Create new stock record
                $currentStock = $multiData['current_stock'] ?? 0;

                if ($adjustment === 'add') {
                    $newStock = $currentStock + $quantity;
                    $productOpeningStock = $product->opening_stock + $quantity;
                } else {
                    $newStock = $currentStock - $quantity;
                    $productOpeningStock = $product->opening_stock - $quantity;
                }

                $stockData = [
                    'product_id' => $product_id,
                    'category_id' => $product->category,
                    'current_stock' => $currentStock,
                    'new_stock' => $newStock,
                    'unit' => $multiData['unit'] ?? null,
                    'location_id' => $multiData['location'],
                    'quantity' => $quantity,
                    'adjustment' => $adjustment,
                    'stock_date' => $validatedRequest['stock_date'] ?? null,
                    'vendor_id' => $validatedRequest['vendor_id'] ?? null,
                    'reason_for_update' => $validatedRequest['reason_for_update'] ?? null,
                ];

                Stock::create($stockData);
            }

            // Update the product's opening stock
            $product->update(['opening_stock' => $productOpeningStock]);
        }

        return response()->json(['message' => 'Stock updated successfully'], 200);
    }
    // public function editStock($product_id)
    // {
    //     // Fetch all stock entries with relations
    //     $stocks = Stock::with([
    //         'product:id,product_name,opening_stock',
    //         'category:id,name',
    //         'vendor:id,vendor_name','location:id,name'
    //     ])->where('product_id', $product_id)->get();

    //     // Check if stock records exist
    //     if ($stocks->isEmpty()) {
    //         return response()->json(['error' => 'Stock not found for this product'], 404);
    //     }

    //     // Get product info from the first stock record
    //     $product = $stocks->first()->product;

    //     // Map stock details
    //     $stockDetails = $stocks->map(function ($stock) {
    //         return [
    //             'stock_id' => $stock->id,
    //             'location' => $stock->location->name, // Assuming 'location' is a string field in Stock table
    //             'current_stock' => $stock->current_stock,
    //             'new_stock' => $stock->new_stock,
    //             'unit' => $stock->unit,
    //             'quantity' => $stock->quantity,
    //             'adjustment' => $stock->adjustment,
    //             'stock_date' => $stock->stock_date,
    //             'vendor_id' => $stock->vendor_id,
    //             'vendor_name' => optional($stock->vendor)->vendor_name,
    //             'category' => $stock->category->name,
    //             'reason_for_update' => $stock->reason_for_update,
    //         ];
    //     });

    //     return response()->json([
    //         'product_id' => $product->id,
    //         'product_name' => $product->product_name,
    //         'opening_stock' => $product->opening_stock,
    //         'stock_details' => $stockDetails
    //     ], 200);
    // }

    
    public function editStock($product_id)
{
    // Fetch all stock entries with necessary relations
    $stocks = Stock::with([
        'product:id,product_name,opening_stock',
        'category:id,name',
        'vendor:id,vendor_name',
        'location:id,name'
    ])->where('product_id', $product_id)->get();

    // Check if stock records exist
    if ($stocks->isEmpty()) {
        return response()->json(['error' => 'Stock not found for this product'], 404);
    }

    // Get product info from the first stock record
    $product = $stocks->first()->product;

    // Map each stock record
    $stockDetails = $stocks->map(function ($stock) {
        return [
            'stock_id' => $stock->id,
            'location' => optional($stock->location)->name, // Safely get location name
            'current_stock' => $stock->current_stock,
            'new_stock' => $stock->new_stock,
            'unit' => $stock->unit,
            'quantity' => $stock->quantity,
            'adjustment' => $stock->adjustment,
            'stock_date' => $stock->stock_date,
            'vendor_id' => $stock->vendor_id,
            'vendor_name' => optional($stock->vendor)->vendor_name,
            'category' => optional($stock->category)->name,
            'reason_for_update' => $stock->reason_for_update,
        ];
    });

    return response()->json([
        'product_id' => $product->id,
        'product_name' => $product->product_name,
        'opening_stock' => $product->opening_stock,
        'stock_details' => $stockDetails
    ], 200);
}

    

    

    public function inventoryAlert()
        {
        $inventory_alert = Product::select('id','product_name','sku','opening_stock','location_id','inventory_alert_threshold',DB::raw("'Warning' as status"))->where('opening_stock', '<', DB::raw('inventory_alert_threshold'))->get();

        return response()->json(['inventory_alert' => $inventory_alert], 200);
        }


    // public function inventoryAdjustmentsReport(){

    //     // $stock = Stock::all();

    //     $stock = Stock::with('product:id,product_name,sku,category', 'product.category:id,name')->get();

    //     // Transform data to move product_name to the root level
    //     $stock = $stock->map(function ($stock) {
    //         return [
    //             'id' => $stock->id,
    //             'product_id' => $stock->product_id,
    //             'sku' => $stock->product->sku,
    //             'current_stock' => $stock->current_stock,
    //             'location' => $stock->location,
    //             'created_at' => $stock->created_at,
    //             'updated_at' => $stock->updated_at,
    //             'product_name' => $stock->product->product_name ?? null, // Move product_name outside
    //             'category' => $stock->product->category->name ?? null, // Ensure category exists
    //         ];
    //     });

    //     return response()->json(['inventory_adjustments' => $stock], 200);
       
    // }

    public function inventoryAdjustmentsReport()
{
    $stocks = Stock::with([
        'product.category', // Load category via product
        'category:id,name','vendor:id,vendor_name'    // Load vendor via product
    ])->get();


    $adjustments = $stocks->map(function ($stock) {
        $adjustmentSymbol = $stock->adjustment == 'subscription' ? '-' : '+';
        $newStock = $stock->adjustment == 'subscription'
            ? $stock->current_stock - $stock->quantity
            : $stock->current_stock + $stock->quantity;

            // print_r($stock->product->category);die;
        return [
            'id' => $stock->id,
            'product_id' => $stock->product_id,
            'product_name' => $stock->product->product_name ?? 'N/A',
            'sku' => $stock->product->sku ?? 'N/A',
            'category_name' => $stock->category->name ?? 'N/A',  // Ensure category is not null
            'vendor_name' => $stock->vendor->vendor_name ?? 'N/A', // Ensure vendor is not null
            'previous_stock' => $stock->current_stock,
            'new_stock' => $newStock,
            'adjustment' => "{$adjustmentSymbol} {$stock->quantity}",
            'reason' => $stock->reason_for_update ?? 'N/A',
            'location' => $stock->location ?? 'N/A',
            'created_at' => $stock->created_at,
            'updated_at' => $stock->updated_at,
        ];
    });

    return response()->json(['inventory_adjustments' => $adjustments], 200);
}

public function uploadCSV(Request $request)
{
    // ✅ Validate CSV file
    $request->validate([
        'file' => 'required|mimes:csv,txt|max:2048' // Max 2MB file
    ]);

    $file = $request->file('file');
    $handle = fopen($file->getPathname(), "r");

    // ✅ Read CSV header
    $header = fgetcsv($handle);
    $expectedHeaders = [
        "product_name", "sku", "units", "category", "sub_category", "manufacturer",
        "vendor", "model", "storage_location", "description","returnable", "track_inventory", "opening_stock",
        "selling_cost", "cost_price", "commit_stock_check","project_name",
        "weight", "weight_unit", "length", "width",
        "depth", "measurement_unit","inventory_alert_threshold","status" 
    ];

    if ($header !== $expectedHeaders) {
        return response()->json(['error' => 'Invalid CSV format. Please use the correct template.'], 400);
    }

    $products = [];
    $invalidRows = [];
    $rowNumber = 2;

    while ($row = fgetcsv($handle)) {
        if (count($row) !== count($expectedHeaders)) {
            $invalidRows[] = $rowNumber;
            continue;
        }

        // ✅ Validate Required Fields
        if (empty($row[0]) || empty($row[1])) {
            $invalidRows[] = $rowNumber;
            continue;
        }

        // ✅ Check Duplicate SKU
        if (Product::where('sku', $row[1])->exists()) {
            continue; // Skip duplicate SKU
        }


        $barcodeNumber = $row[1]; // Unique barcode
        if ($barcodeNumber) {
            // ✅ Generate Barcode as Base64
            // $barcodeImage = \Milon\Barcode\DNS1D::getBarcodePNG($barcodeNumber, 'C39');
            $barcodeImage = DNS1D::getBarcodePNG($barcodeNumber, 'C39');
        
            // $barcodeImage = 'jjkkjjhjkkjsakjasasajajasjjsajassaejejea';
            // ✅ Convert Base64 to an Image File
            $imagePath = 'public/barcodes/' . $barcodeNumber . '.png'; 
            Storage::put($imagePath, base64_decode($barcodeImage));
        
            // ✅ Store the public path for access
            $savedBarcodePath = str_replace('public/', 'storage/', $imagePath);
        }

        // Add barcode data
        $row['barcode_number'] = $barcodeNumber;
        $row['generated_barcode'] = $barcodeImage;
    
        // ✅ Create Product
        
    
        // ✅ Generate QR Code after product is created
        $productDetails = [
            'barcode_number' => $row[1],
            'name' => $row[0],
            'sku' => $row[1],
            'description' => $row[9],
            'price' => number_format($row[13], 2),
            'stock' => $row[12]
        ];
    
        // $validatedData['generated_qrcode'] = DNS2D::getBarcodePNG(json_encode($productDetails), 'QRCODE');
    
        // if ($validatedData['generated_qrcode']) {
        //     $path = $validatedData['generated_qrcode']->store('public/qrcode');
        //     // $validatedData['thumbnail'] = str_replace('public/', 'storage/', $path);
        // }
    
        if ($productDetails) {
            // ✅ Generate a Unique QR Code Name
            $fileName = 'qrcode_' . time() . '.png';
        
            // ✅ Generate QR Code as Base64
    
            $qrcodeBase64 = DNS2D::getBarcodePNG(json_encode($productDetails), 'QRCODE');
            // $qrcodeBase64 = json_encode($productDetails).'QRCODE';
        
            // ✅ Convert Base64 to an Image File and Save
            $imagePath = 'public/qrcode/' . $fileName; 
            Storage::put($imagePath, base64_decode($qrcodeBase64));
        
            // ✅ Store the public path for access
            $savedQRCodePath = str_replace('public/', 'storage/', $imagePath);
        
            // ✅ Store QR Code Path in Database
            $row['generated_qrcode'] = $qrcodeBase64;
        }


        $products[] = [
            'product_name' => $row[0],
            'sku' => $row[1],
            'generated_barcode' => $row['generated_barcode'],
            'generated_qrcode' => $row['generated_qrcode'],
            'units' => $row[2],
            'category' => $row[3],
            'sub_category' => $row[4],
            'manufacturer' => $row[5],
            'vendor' => $row[6],
            'model' => $row[7],
            'location_id' => $row[8],
            'description' => $row[9],
            'returnable' => strtolower($row[10]) === 'yes' ? 1 : 0,
            'track_inventory' => $row[11],
            'opening_stock' => (int)$row[12],
            'selling_cost' => (float)$row[13],
            'cost_price' => (float)$row[14],
            'commit_stock_check' => (float)$row[15],
            'project_name' => $row[16],
            'weight' => (float)$row[17],
            'weight_unit' => $row[18],
            'length' => (float)$row[19],
            'width' => (float)$row[20],
            'depth' => (float)$row[21],
            'measurement_unit' => $row[22],
            'barcode_number' => $row['barcode_number'],
            'inventory_alert_threshold' => (int)$row[23],
            'status' => $row[24],
            'created_at' => now(),
            'updated_at' => now()
        ];
        $rowNumber++;
    }

    fclose($handle);

    if (!empty($products)) {
        Product::insert($products);
    }

    return response()->json([
        'message' => count($products) . ' products uploaded successfully',
        'invalid_rows' => $invalidRows
    ], 200);
}


public function locationList()
{
    $location = Location::all();

    
        return response()->json([
            'message' => 'Location list',
            'location' => $location,
        ], 200);
    
}
public function createLocation(Request $request)
{
    // Validate that location_name is a required string
    $validated = $request->validate([
        'location_name' => 'required|string|max:255',
    ]);

    // Check if location already exists
    $existing = Location::where('name', $validated['location_name'])->first();

    if ($existing) {
        return response()->json([
            'message' => 'Location already exists.',
            'status' => 'duplicate',
            'location' => $existing,
        ], 200);
    }

    // Create new location
    $location = Location::create([
        'name' => $validated['location_name'],
    ]);

    return response()->json([
        'message' => 'Location created successfully.',
        'status' => 'created',
        'location' => $location,
    ], 201);
}

}


