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
        $barcodeImage = \Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($barcodeNumber, 'C39');
        // $barcodeImage = DNS1D::getBarcodePNG($barcodeNumber, 'C39');
    
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
    // ✅ Validate CSV file
    $request->validate([
        'file' => 'required|mimes:csv,txt|max:2048' // Max 2MB file
    ]);

    $file = $request->file('file');
    $handle = fopen($file->getPathname(), "r");

    // ✅ Read CSV header
    $header = fgetcsv($handle);
    $expectedHeaders = [
        "Product Name", "SKU", "Units", "Category", "Sub Category", "Manufacturer",
        "Vendor", "Model", "Storage Location (Rack)", "Description", "Opening Stock",
        "Selling Cost", "Cost Price", "Project Name", "Weight", "Weight Unit",
        "Dim.Length", "Dim.Width", "Dim.Depth", "Dim.Measurement Unit",
        "Inventory Alert (Threhold Count)", "Status", "Returnable"
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
            'opening_stock' => (int)$row[10],
            'selling_cost' => (float)$row[11],
            'cost_price' => (float)$row[12],
            'project_name' => $row[13],
            'weight' => (float)$row[14],
            'weight_unit' => $row[15],
            'dim_length' => (float)$row[16],
            'dim_width' => (float)$row[17],
            'dim_depth' => (float)$row[18],
            'dim_measurement_unit' => $row[19],
            'inventory_alert' => (int)$row[20],
            'status' => $row[21],
            'returnable' => strtolower($row[22]) === 'yes' ? 1 : 0,
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
    ], 201);
}

    // public function uploadCsv(Request $request) {
        
    //     {
    //         $request->validate([
    //             'file' => 'required|file|mimes:csv,txt'
    //         ]);
    
    //         $file = $request->file('file');
    //         $path = $file->getRealPath();
            
    //         $products = [];
    //         $errors = [];
           
    //         if (($handle = fopen($path, "r")) !== FALSE) {
    //             // Skip header row
    //             $headers = fgetcsv($handle);
                
    //             $row = 2; // Start from row 2 (after headers)
    //             while (($data = fgetcsv($handle)) !== FALSE) {
    //                 $productData = array_combine($headers, $data);
    //                 // print_r($productData);die;
    //                 $generator = DNS1D::getBarcodePNG($barcodeNumber, 'C39');
    
                    
    //                 $validator = Validator::make($productData, [
    //                     'name' => 'required|string|max:255',
    //                     'brand' => 'nullable|string|max:100',
    //                     'vendor' => 'nullable|string|max:100',
    //                     'product_category' => 'nullable|string|max:100',
    //                     'barcode_number' => 'required',
    //                     'avg_cost' => 'nullable|string',
    //                     'last_cost' => 'nullable|numeric|min:0',
    //                     'price' => 'required|numeric|min:0',
    //                     'quantity' => 'nullable|string',
    //                     'low_qty_warning' => 'nullable|integer|min:0',
    //                     'max_quantity' => 'nullable|integer|min:0',
    //                     'min_order_quantity' => 'required|integer|min:0',
    //                 ]);
    
                    
    //                 if ($validator->fails()) {
    //                     $errors[] = "Row {$row}: " . implode(', ', $validator->errors()->all());
    //                 } else {
    //                     try {
                            
    //                         $barcode = DNS1D::getBarcodePNG($productData['barcode_number'], 'C128', 2, 100);
                
    //                         // print_r($productData);die;
    //                         $productData['barcode'] = $barcode;
    
    //                         $product = Product::create([
    //                             'name' => $productData['name'],
    //                             'brand' => $productData['brand'],
    //                             'vendor' => $productData['vendor'],
    //                             'category' => $productData['product_category'],
    //                             'barcode_number'=> $productData['barcode_number'],
    //                             'avg_cost' => $productData['avg_cost'],
    //                             'last_cost' => $productData['last_cost'],
    //                             'price' => $productData['price'],
    //                             'stock' => $productData['quantity'],
    //                             'low_stock_warning' => $productData['low_qty_warning'],
    //                             'max_quantity' => $productData['max_quantity'],
    //                             'min_order_quantity' => $productData['min_order_quantity'],
    //                             'barcode' => $productData['barcode'],
    //                             'sku' => $productData['brand'],
    //                             'description' => 'add',
    //                         ]);
                            
    //                         // $productData['barcode'] = $barcode;
    
    //                         // $product = new Product();
    //                         // $product->fill($productData); // Assigns attributes from $productData
    //                         // $product->save(); // Saves the product to the database
    
    //                         // $product = new Product();
    //                         // $product->name = $productData['name'];
    //                         // $product->brand = $productData['brand'];
    //                         // $product->vendor = $productData['vendor'];
    //                         // $product->category = $productData['product_category'];
    //                         // $product->barcode_number = $productData['barcode_number'];
    //                         // $product->avg_cost = $productData['avg_cost'];
    //                         // $product->last_cost = $productData['last_cost'];
    //                         // $product->price = $productData['price'];
    //                         // $product->stock = $productData['quantity'];
    //                         // $product->low_stock_warning = $productData['low_qty_warning'];
    //                         // $product->max_quantity = $productData['max_quantity'];
    //                         // $product->min_order_quantity = $productData['min_order_quantity'];
    //                         // $product->barcode = $barcode;
    //                         // $product->save();
    //                         // $product = new Product();
    //                         // $product->name = $productData['name'];
    //                         // $product->brand = $productData['brand'];
    //                         // $product->vendor = $productData['vendor'];
    //                         // $product->category = $productData['product_category'];
    //                         // $product->barcode_number = $productData['barcode_number'];
    //                         // $product->avg_cost = $productData['avg_cost'];
    //                         // $product->last_cost = $productData['last_cost'];
    //                         // $product->price = $productData['price'];
    //                         // $product->stock = $productData['quantity'];
    //                         // $product->low_stock_warning = $productData['low_qty_warning'];
    //                         // $product->max_quantity = $productData['max_quantity'];
    //                         // $product->min_order_quantity = $productData['min_order_quantity'];
    //                         // $product->barcode = $productData['barcode'];
    //                         // $product->sku = '1';
    //                         // $product->description = 'add';
    //                         // $product->save();
    
    //                         $products[] = $product;
    //                     } catch (\Exception $e) {
    //                         $errors[] = "Row {$row}: Failed to create product - " . $e->getMessage();
    //                     }
    //                 }
    //                 $row++;
    //             }
    //             fclose($handle);
    //         }
    
    //         $message = count($products) . ' products imported successfully.';
    //         if (!empty($errors)) {
    //             $message .= ' Errors: ' . implode('; ', $errors);
    //             return redirect()->route('products.create')
    //                 ->with('warning', $message);
    //         }
    
    //         return redirect()->route('products.index')
    //             ->with('success', $message);
    //     }
    // }


    // public function uploadCsv(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required|file|mimes:csv,txt'
    //     ]);

    //     $file = $request->file('file');
    //     $path = $file->getRealPath();

    //     $products = [];
    //     $errors = [];

    //     if (($handle = fopen($path, "r")) !== FALSE) {
    //         $headers = fgetcsv($handle);
            
    //         if (!$headers) {
    //             return response()->json(['error' => 'Invalid CSV file format. No headers found.'], 400);
    //         }

    //         $requiredColumns = ['name', 'brand', 'vendor', 'product_category', 'barcode_number', 'avg_cost', 'last_cost', 'price', 'quantity', 'low_qty_warning', 'max_quantity', 'min_order_quantity'];

    //         // Check if all required columns are present
    //         if (count(array_intersect($requiredColumns, $headers)) !== count($requiredColumns)) {
    //             return response()->json(['error' => 'CSV file is missing required columns.'], 400);
    //         }

    //         $row = 2;
    //         while (($data = fgetcsv($handle)) !== FALSE) {
    //             $productData = array_combine($headers, $data);

    //             $validator = Validator::make($productData, [
    //                 'name' => 'required|string|max:255',
    //                 'brand' => 'nullable|string|max:100',
    //                 'vendor' => 'nullable|string|max:100',
    //                 'product_category' => 'nullable|string|max:100',
    //                 'barcode_number' => 'required|string',
    //                 'avg_cost' => 'nullable|string',
    //                 'last_cost' => 'nullable|numeric|min:0',
    //                 'price' => 'required|numeric|min:0',
    //                 'quantity' => 'nullable|integer|min:0',
    //                 'low_qty_warning' => 'nullable|integer|min:0',
    //                 'max_quantity' => 'nullable|integer|min:0',
    //                 'min_order_quantity' => 'required|integer|min:0',
    //             ]);

    //             if ($validator->fails()) {
    //                 $errors[] = "Row {$row}: " . implode(', ', $validator->errors()->all());
    //             } else {
    //                 try {
    //                     DB::beginTransaction();

    //                     $barcode = DNS1D::getBarcodePNG($productData['barcode_number'], 'C128', 2, 100);

    //                     $product = Product::create([
    //                         'name' => $productData['name'],
    //                         'brand' => $productData['brand'],
    //                         'vendor' => $productData['vendor'],
    //                         'category' => $productData['product_category'],
    //                         'barcode_number' => $productData['barcode_number'],
    //                         'avg_cost' => $productData['avg_cost'],
    //                         'last_cost' => $productData['last_cost'],
    //                         'price' => $productData['price'],
    //                         'stock' => $productData['quantity'],
    //                         'low_stock_warning' => $productData['low_qty_warning'],
    //                         'max_quantity' => $productData['max_quantity'],
    //                         'min_order_quantity' => $productData['min_order_quantity'],
    //                         'barcode' => $barcode,
    //                         'sku' => strtoupper(substr($productData['name'], 0, 3)) . rand(1000, 9999), 
    //                         'description' => 'Auto-imported from CSV',
    //                     ]);

    //                     DB::commit();

    //                     $products[] = $product;
    //                 } catch (\Exception $e) {
    //                     DB::rollBack();
    //                     $errors[] = "Row {$row}: Failed to create product - " . $e->getMessage();
    //                 }
    //             }
    //             $row++;
    //         }
    //         fclose($handle);
    //     }

    //     return response()->json([
    //         'success' => count($products) . ' products imported successfully.',
    //         'errors' => $errors
    //     ]);
    // }
}


