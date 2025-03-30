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


    public function inventoryAdjustmentsReport(){

        // $stock = Stock::all();

        $stock = Stock::with('product:id,product_name,sku,category', 'product.category:id,name')->get();

        // Transform data to move product_name to the root level
        $stock = $stock->map(function ($stock) {
            return [
                'id' => $stock->id,
                'product_id' => $stock->product_id,
                'sku' => $stock->product->sku,
                'current_stock' => $stock->current_stock,
                'location' => $stock->location,
                'created_at' => $stock->created_at,
                'updated_at' => $stock->updated_at,
                'product_name' => $stock->product->product_name ?? null, // Move product_name outside
                'category' => $stock->product->category->name ?? null, // Ensure category exists
            ];
        });

        return response()->json(['inventory_adjustments' => $stock], 200);
       
    }
}


