<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Milon\Barcode\Facades\DNS1DFacade as DNS1D;
use Milon\Barcode\Facades\DNS2DFacade as DNS2D;
use PDF;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use DB;




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
        'storage_location' => 'nullable|string',
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
        $barcodeImage = DNS1D::getBarcodePNG($barcodeNumber, 'C39');
    
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

    $product = Product::create($validatedData);

    // Stock::create([
    //     'product_id'=>$product->id,
    //     'current_stock'=>$product->id,
    //     'unit'=>$product->id,
    //     'location'=>$product->id,
    // ]);

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
            'storage_location' => 'nullable|string',
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
        $barcodeImage = DNS1D::getBarcodePNG($barcodeNumber, 'C39');
    
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

    public function updateStock(Request $request, $product_id)
    {
        $product = Product::find($product_id);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $validatedData = $request->validate([
           
            // 'product_id' => 'string',
            'current_stock' => 'nullable|string',
            'quantity' => 'nullable|string',
            'unit' => 'nullable|string',
            'reason_for_update' => 'nullable|string',
            'location' => 'nullable|string',
            'stock_date' => 'nullable|string',
            'vendor' => 'nullable|string',
            'adjustment' => 'nullable|string',
           
        ]);

        $validatedData['product_id'] = $product_id;

        if($request->adjustment =='add'){

            $productopening_stock =  $product->opening_stock + $request->quantity;
        }

        if($request->adjustment =='subscription'){

            $productopening_stock = $product->opening_stock - $request->quantity;
        }


    // ✅ Generate QR Code after product is created
    $productDetails = [
        'opening_stock' => $productopening_stock
    ];
        $validatedData['current_stock'] =$product->opening_stock;
        $validatedData['new_stock'] =$productopening_stock;
        
        $stock = Stock::create($validatedData);
        $product->update($productDetails);

        return response()->json(['message' => 'Stock updated successfully', 'product' => $stock], 200);
    }

    public function inventoryAlert()
        {
        $inventory_alert = Product::select('id','product_name','sku','opening_stock','storage_location','inventory_alert_threshold',DB::raw("'Warning' as status"))->where('opening_stock', '<', DB::raw('inventory_alert_threshold'))->get();

        return response()->json(['inventory_alert' => $inventory_alert], 200);
        }


    // public function inventoryAdjustmentsReport(){

    //     // $stock = Stock::all();

    //     $stock = Stock::with('product:id,product_name,sku,category', 'product.category:id,name')->get();

    //     // Transform data to move product_name to the root level
    //     $stock = $stock->map(function ($stock) {

    //         if($stock->adjustment == 'subscription'){
    //             $new_stock =  $stock->current_stock -$stock->quantity;
    //             $adjustment =  ' - '.$stock->quantity;
                

    //         }else if($stock->adjustment == 'add'){
    //             $new_stock =  $stock->current_stock + $stock->quantity;
    //             $adjustment =  ' + '.$stock->quantity;
    //         }
           
    //         return [
    //             'id' => $stock->id,
    //             'product_id' => $stock->product_id,
    //             'product_name' => $stock->product->product_name ?? null, // Move product_name outside
    //             'sku' => $stock->product->sku,
    //             'category_name' => $stock->product->category->name ?? null, // Ensure category exists
    //             'vendor_name' => $stock->product->category->name ?? null, // Ensure category exists
    //             'previous_stock' => $stock->current_stock,
    //             'new_stock' => $stock->new_stock,
    //             'adjustment' => $adjustment,
    //             'reason' => $stock->reason_for_update,
    //             'location' => $stock->location,
    //             'created_at' => $stock->created_at,
    //             'updated_at' => $stock->updated_at,
                
                
    //         ];
    //     });

    //     return response()->json(['inventory_adjustments' => $stock], 200);
       
    // }

//     public function inventoryAdjustmentsReport()
// {
//     // Fetch stock details with related product, vendor, and category
//     $stock = Stock::with([
//         'product:id,product_name,sku,category,vendor',
//         'product.category:id,name',
//         'product.vendor:id,vendor_name'
//     ])->get();

//     // Transform data
//     $stock = $stock->map(function ($stock) {
//         // Calculate adjustment
//         if ($stock->adjustment == 'subscription') {
//             $new_stock = $stock->current_stock - $stock->quantity;
//             $adjustment = ' - ' . $stock->quantity;
//         } elseif ($stock->adjustment == 'add') {
//             $new_stock = $stock->current_stock + $stock->quantity;
//             $adjustment = ' + ' . $stock->quantity;
//         }

//         return [
//             'id' => $stock->id,
//             'product_id' => $stock->product_id,
//             'product_name' => $stock->product->product_name ?? null,
//             'sku' => $stock->product->sku ?? null,
//             'category_name' => $stock->product->category->name ?? null,
//             'vendor_name' => $stock->product->vendor->vendor_name ?? null,
//             'previous_stock' => $stock->current_stock,
//             'new_stock' => $new_stock,
//             'adjustment' => $adjustment,
//             'reason' => $stock->reason_for_update,
//             'location' => $stock->location,
//             'created_at' => $stock->created_at,
//             'updated_at' => $stock->updated_at
//         ];
//     });

//     return response()->json(['inventory_adjustments' => $stock], 200);
// }


public function inventoryAdjustmentsReport()
{
    $stocks = Stock::with([
        'product.category', // Load category via product
        'product.vendor'    // Load vendor via product
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
            'category_name' => $stock->product->category->name ?? 'N/A',  // Ensure category is not null
            'vendor_name' => $stock->product->vendor->vendor_name ?? 'N/A', // Ensure vendor is not null
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
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048' // Ensure it's a CSV file
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), "r");

        // Read and validate CSV header
        $header = fgetcsv($handle);
        $expectedHeaders = [
            "Product Name", "SKU", "Units", "Category", "Sub Category", "Manufacturer",
            "Vendor", "Model", "Storage Location (Rack)", "Description", "Opening Stock",
            "Selling Cost", "Cost Price", "Project Name", "Weight", "Weight Unit",
            "Dim.Length", "Dim.Width", "Dim.Depth", "Dim.Measurement Unit",
            "Inventory Alert (Threhold Count)", "Status", "Returnable"
        ];

        if ($header !== $expectedHeaders) {
            return response()->json(['error' => 'Invalid CSV format'], 400);
        }

        $products = [];

        while ($row = fgetcsv($handle)) {
            $products[] = [
                'name' => $row[0],
                'sku' => $row[1],
                'units' => $row[2],
                'category' => $row[3],
                'sub_category' => $row[4],
                'manufacturer' => $row[5],
                'vendor' => $row[6],
                'model' => $row[7],
                'storage_location' => $row[8],
                'description' => $row[9],
                'opening_stock' => $row[10],
                'selling_cost' => $row[11],
                'cost_price' => $row[12],
                'project_name' => $row[13],
                'weight' => $row[14],
                'weight_unit' => $row[15],
                'dim_length' => $row[16],
                'dim_width' => $row[17],
                'dim_depth' => $row[18],
                'dim_measurement_unit' => $row[19],
                'inventory_alert' => $row[20],
                'status' => $row[21],
                'returnable' => $row[22] === 'Yes' ? 1 : 0,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        fclose($handle);

        Product::insert($products);

        return response()->json([
            'message' => count($products) . ' products uploaded successfully'
        ], 201);
    }
}


