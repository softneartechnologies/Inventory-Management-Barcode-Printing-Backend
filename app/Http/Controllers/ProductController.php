<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Location;
use App\Models\Stock;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Vendor;
use App\Models\ScanInOutProduct;
use App\Models\Unit;
use App\Models\InventoryAdjustmentReports;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
// use Milon\Barcode\Facades\DNS1DFacade as DNS1D;
// use Milon\Barcode\Facades\DNS2DFacade as DNS2D;

// use Milon\Barcode\Facades\DNS1DFacade;
// use Milon\Barcode\Facades\DNS2DFacade;

// use Milon\Barcode\Facades\DNS1DFacade as DNS1D;
// use Milon\Barcode\Facades\DNS2DFacade as DNS2D;
// use Milon\Barcode\Facades\DNS1DFacade as DNS1D;

use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
// use Milon\Barcode\DNS1D;
// use Milon\Barcode\DNS2D;
// use Milon\Barcode\DNS1D;
// use Milon\Barcode\DNS2D;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use App\Models\BarcodeSetting;
use Illuminate\Support\Facades\Log;






class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category:id,name','vendor:id,vendor_name',
        'sub_category:id,name')->orderBy('id', 'desc')->get();
    
        $products = $products->map(function ($product) {
            // Get all product attributes + add category name
            $data = $product->toArray();
            $data['category_name'] = optional($product->category)->name;
            $data['subcategory_name'] = optional($product->subcategory)->name;
            $data['vendor_name'] = optional($product->vendor)->vendor_name;
            
            if(!empty($product->thumbnail)){
            $data['product_images'] = url($product->thumbnail); 
            }else{
                 $data['product_images'] = url('/storage/default_image/default_imagess.jpg'); 
            }
    
            return $data;
        });
    
        return response()->json(['products' => $products], 200);
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

//     public function store(Request $request)
// {
//     $validatedData = $request->validate([
//         'product_name' => 'required|string|max:255',
//         'sku' => 'required|string|max:255|unique:products',
//         'units' => 'required|string',
//         'category_id' => 'required|string',
//         'sub_category' => 'nullable|string',
//         'manufacturer' => 'nullable|string',
//         'vendor_id' => 'nullable|string',
//         'model' => 'nullable|string',
//         'weight' => 'nullable|numeric',
//         'weight_unit' => 'nullable|string',
//         'storage_location' => 'array',
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
//         'inventory_alert_threshold' => 'integer|min:0',
//         'status' => ['required', Rule::in(['active', 'inactive'])],
//     ]);

 
//     $barcodeNumber = $request->sku; // Unique barcode
//     if ($barcodeNumber) {
       
//         $barcodeImage = (new DNS1D)->getBarcodePNG($barcodeNumber, 'C39');

//         // $barcodeImage = DNS1D::getBarcodePNG($barcodeNumber, 'C39');
    
//         // $barcodeBase64 = base64_encode((new DNS1D)->getBarcodePNG($barcodeNumber, 'C39'));

//         $imagePath = 'public/barcodes/' . $barcodeNumber . '.png'; 
//         Storage::put($imagePath, $barcodeImage);
    
//         // ✅ Store the public path for access
//         $savedBarcodePath = str_replace('public/', 'storage/', $imagePath);
//     }

//     // $barcodes = storage_path('app/public/barcodes');
//     // $qrcode = storage_path('app/public/qrcode');
//     // $images = storage_path('app/public/images');

//     // Add barcode data
//     $validatedData['barcode_number'] = $barcodeNumber;
//     $validatedData['generated_barcode'] = $barcodeImage;

//     // ✅ Create Product
    

//     // ✅ Generate QR Code after product is created
//     $productDetails = [
//         'barcode_number' => $barcodeNumber,
//         'name' => $request->product_name,
//         'sku' => $request->sku,
//         'description' => $request->description,
//         'price' => number_format($request->selling_cost, 2),
//         'stock' => $request->opening_stock
//     ];
    
//     if ($productDetails) {
//         // Convert to string (JSON format)
//         $productString = json_encode($productDetails, JSON_UNESCAPED_UNICODE);
    
//         // Generate QR code
//         $qrCodeImage = (new DNS2D)->getBarcodePNG($productString, 'QRCODE');
    
//         // Encode to base64
//         $qrcodeBase64 = base64_encode($qrCodeImage);
    
//         // Save to storage
//         $fileName = 'qrcode_' . time() . '.png';
//         $imagePath = 'public/qrcode/' . $fileName;
//         Storage::put($imagePath, $qrCodeImage); // Save actual binary, not base64
    
//         // Path to use for displaying
//         $savedQRCodePath = str_replace('public/', 'storage/', $imagePath);
    
//         // Optional: store path or base64 in DB
//         $validatedData['generated_qrcode'] = $savedQRCodePath; // OR use $qrcodeBase64
//     }



//     if ($request->hasFile('thumbnail')) {
//         $path = $request->file('thumbnail')->store('public/thumbnails');
//         $validatedData['thumbnail'] = str_replace('public/', 'storage/', $path);
//     }

//         $validatedData['location_id'] = json_encode(
//             collect($request->storage_location)->pluck('location')->all()
//         );
//         // $validatedData['location_id'] = json_encode($request->storage_location->location);

//         $product = Product::create($validatedData);

//         foreach ($request->storage_location as $multiData) {
//             Stock::create([
//                 'product_id'    => $product->id,
//                 'vendor_id'     => $request->vendor,
//                 'category_id'   => $request->category,
//                 'current_stock' => $multiData['quantity'],
//                 'unit'          => $multiData['unit'],
//                 'location_id'   => $multiData['location'],
//                 // 'adjustment' => $multiData['adjustment'],
//                 'stock_date'    => now(),
//             ]);
//         }

//     return response()->json([
//         'message' => 'Product created successfully',
//         'product' => $product
//     ], 200);
// }


public function store(Request $request)
{
    $validatedData = $request->validate([
        'product_name' => 'required|string|max:255',
        'sku' => 'required|string|max:255|unique:products',
        'units' => 'required|string',
        // 'unit_cost' => 'required|string',
        // 'total_cost' => 'required|string',
        'category_id' => 'required|string',
        'sub_category_id' => 'required|string',
        'manufacturer' => 'nullable|string',
        'vendor_id' => 'required|string',
        'model' => 'nullable|string',
        'weight' => 'nullable|numeric',
        'weight_unit' => 'nullable|string',
        'storage_location' => 'array',
        'thumbnail' => 'required',
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

 
    $barcodeNumber = $request->sku; // Unique barcode
    if ($barcodeNumber) {
        
        //  $barcodeImage = Milon\Barcode\Facades\DNS1D::getBarcodePNG($barcodeNumber, 'C128');
                    
       
        $barcodeImage = (new DNS1D)->getBarcodePNG($barcodeNumber, 'C128');

        // $barcodeImage = DNS1D::getBarcodePNG($barcodeNumber, 'C39');
    
        // $barcodeBase64 = base64_encode((new DNS1D)->getBarcodePNG($barcodeNumber, 'C39'));

        $imagePath = 'public/barcodes/' . $barcodeNumber . '.png'; 
        Storage::put($imagePath, $barcodeImage);
    
        $savedBarcodePath = str_replace('public/', 'storage/', $imagePath);
    }

    // Add barcode data
    $validatedData['barcode_number'] = $barcodeNumber;
    $validatedData['generated_barcode'] = $barcodeImage;

       $productDetails = [
        'barcode_number' => $barcodeNumber,
        'name' => $request->product_name,
        'sku' => $request->sku,
        'description' => $request->description,
        'price' => number_format($request->selling_cost, 2),
        'stock' => $request->opening_stock
    ];
    
    
    if ($productDetails) {
        // Convert to string (JSON format)
        $productString = json_encode($productDetails, JSON_UNESCAPED_UNICODE);
    
        // Generate QR code
        // $qrCodeImage = Milon\Barcode\Facades\DNS2D::getBarcodePNG($barcodeNumber, 'QRCODE');
        
        $qrCodeImage = (new DNS2D)->getBarcodePNG($barcodeNumber, 'QRCODE');
    
        // Encode to base64
        $qrcodeBase64 = base64_encode($qrCodeImage);
    
        // Save to storage
        $fileName = 'qrcode_' . time() . '.png';
        $imagePath = 'public/qrcode/' . $fileName;
        Storage::put($imagePath, $qrCodeImage); // Save actual binary, not base64
    
        // Path to use for displaying
        $savedQRCodePath = str_replace('public/', 'storage/', $imagePath);
    
        // Optional: store path or base64 in DB
        $validatedData['generated_qrcode'] = $qrCodeImage; // OR use $qrcodeBase64
    }


// print_r($request->all());die;
    // if ($request->hasFile('thumbnail')) {
    //     $path = $request->file('thumbnail')->store('public/thumbnails');
    //     $validatedData['thumbnail'] = str_replace('public/', 'storage/', $path);
    // }
    
    
    if ($request->hasFile('thumbnail')) {
        $image = $request->file('thumbnail');
        $filename = time() . '_' . $image->getClientOriginalName();
        $image->move(public_path('product/thumbnails'), $filename);
        $validatedData['thumbnail']  = 'product/thumbnails/' . $filename;
    }
    

        $validatedData['location_id'] = json_encode(
            collect($request->storage_location)->pluck('location')->all()
        );
        // $validatedData['location_id'] = json_encode($request->storage_location->location);

        $product = Product::create($validatedData);

        foreach ($request->storage_location as $multiData) {
            Stock::create([
                'product_id'    => $product->id,
                'vendor_id'     => $request->vendor,
                'category_id'   => $request->category,
                'current_stock' => $multiData['quantity'],
                'unit'          => $multiData['unit'],
                'unit_cost'          => $multiData['unit_cost'],
                'total_cost'          => $multiData['total_cost'],
                'location_id'   => $multiData['location'],
                // 'adjustment' => $multiData['adjustment'],
                'stock_date'    => now(),
            ]);
        }

    return response()->json([
        'message' => 'Product created successfully',
        'product' => $product
    ], 200);
}


    public function show($product_id)
    {
        $product_detail = Product::with('category:id,name','sub_category:id,name','vendor:id,vendor_name')->find($product_id);
        if (!$product_detail) {
            return response()->json(['error' => 'Product not found'], 404);
        }
      
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
                'location_d' => $stock->location_id,
                'location' => optional($stock->location)->name, // Safely get location name
                'current_stock' => $stock->current_stock,
                'new_stock' => $stock->new_stock,
                'unit' => $stock->unit,
                'unit_cost'=> $stock->unit_cost,
                'total_cost'=> $stock->total_cost,
                'quantity' => $stock->quantity,
                'adjustment' => $stock->adjustment,
                'stock_date' => $stock->stock_date,
                'vendor_id' => $stock->vendor_id,
                'vendor_name' => optional($stock->vendor)->vendor_name,
                'category' => optional($stock->category)->name,
                // 'reason_for_update' => $stock->reason_for_update,
            ];
        });
    
        $productsss= array(['id' => $product_detail->id,
        'product_name' => $product_detail->product_name,
        'sku' => $product_detail->sku,
        'generated_barcode'=>$product_detail->generated_barcode,
        'generated_qrcode'=>$product_detail->generated_qrcode,
        'units'=>$product_detail->units,
        'category_id'=>$product_detail->category_id,
        'category_name' => $product_detail->category->name ?? null,
        'sub_category_id'=>$product_detail->sub_category_id,
        'subcategory_name' => $product_detail->sub_category->name ?? null,
        'manufacturer'=>$product_detail->manufacturer,
        'vendor_name'=>$product_detail->vendor->vendor_name,
        'vendor_id'=>$product_detail->vendor_id,
        'model'=>$product_detail->model,
        'weight'=>$product_detail->weight,
        'weight_unit'=>$product_detail->weight_unit,
        'location_id'=>$product_detail->location_id,
        'thumbnail'=>$product_detail->thumbnail,
        'description'=>$product_detail->description,
        'returnable'=>$product_detail->returnable,
        'track_inventory'=>$product_detail->track_inventory,
        'opening_stock' => $product_detail->opening_stock,
        'selling_cost' => $product_detail->selling_cost,
        'cost_price' => $product_detail->cost_price,
        'commit_stock_check' => $product_detail->commit_stock_check,
        'project_name' => $product_detail->project_name,
        'length' => $product_detail->length,
        'width' => $product_detail->width,
        'depth' => $product_detail->depth,
        'measurement_unit' => $product_detail->measurement_unit,
        'barcode_number' => $product_detail->barcode_number,
        'inventory_alert_threshold' => $product_detail->inventory_alert_threshold,
        'status' => $product_detail->status,
        'created_at' => $product_detail->created_at,
        'updated_at' => $product_detail->updated_at,
    ]);
        return response()->json([
            // 'product_data'=>$product_detail,
            // 'product_id' => $product->id,
            // 'product_name' => $product->product_name,
            // 'opening_stock' => $product->opening_stock,

                'product_data'=>$productsss,

            'stock_details' => $stockDetails
        ], 200);
    }


    public function view($product_id)
    {
        $product_detail = Product::with('category:id,name','sub_category:id,name','vendor:id,vendor_name')->find($product_id);
        if (!$product_detail) {
            return response()->json(['error' => 'Product not found'], 404);
        }
      
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
                'location_d' => $stock->location_id,
                'location' => optional($stock->location)->name, // Safely get location name
                'current_stock' => $stock->current_stock,
                'new_stock' => $stock->new_stock,
                'unit' => $stock->unit,
                'unit_cost'=> $stock->unit_cost,
                'total_cost'=> $stock->total_cost,
                'quantity' => $stock->quantity,
                'adjustment' => $stock->adjustment,
                'stock_date' => $stock->stock_date,
                'vendor_id' => $stock->vendor_id,
                'vendor_name' => optional($stock->vendor)->vendor_name,
                'category' => optional($stock->category)->name,
                // 'reason_for_update' => $stock->reason_for_update,
            ];
        });
        
      
                
    
        $productsss= array(['id' => $product_detail->id,
        'product_name' => $product_detail->product_name,
        'sku' => $product_detail->sku,
        'generated_barcode'=>$product_detail->generated_barcode,
        'generated_qrcode'=>$product_detail->generated_qrcode,
        'units'=>$product_detail->units,
        'category_id'=>$product_detail->category_id,
        'category_name' => $product_detail->category->name ?? null,
        'sub_category_id'=>$product_detail->sub_category_id,
        'subcategory_name' => $product_detail->sub_category->name ?? null,
        'manufacturer'=>$product_detail->manufacturer,
        'vendor_name'=>$product_detail->vendor->vendor_name,
        'vendor_id'=>$product_detail->vendor_id,
        'model'=>$product_detail->model,
        'weight'=>$product_detail->weight,
        'weight_unit'=>$product_detail->weight_unit,
        'location_id'=>$product_detail->location_id,
        'thumbnail'=>$product_detail->thumbnail,
        'description'=>$product_detail->description,
        'returnable'=>$product_detail->returnable,
        'track_inventory'=>$product_detail->track_inventory,
        'opening_stock' => $product_detail->opening_stock,
        'selling_cost' => $product_detail->selling_cost,
        'cost_price' => $product_detail->cost_price,
        'commit_stock_check' => $product_detail->commit_stock_check,
        'project_name' => $product_detail->project_name,
        'length' => $product_detail->length,
        'width' => $product_detail->width,
        'depth' => $product_detail->depth,
        'measurement_unit' => $product_detail->measurement_unit,
        'barcode_number' => $product_detail->barcode_number,
        'inventory_alert_threshold' => $product_detail->inventory_alert_threshold,
        'status' => $product_detail->status,
        'created_at' => $product_detail->created_at,
        'updated_at' => $product_detail->updated_at,
    ]);
        return response()->json([
            // 'product_data'=>$product_detail,
            // 'product_id' => $product->id,
            // 'product_name' => $product->product_name,
            // 'opening_stock' => $product->opening_stock,

                'product_data'=>$productsss,

            'stock_details' => $stockDetails
        ], 200);
    }


    // public function update(Request $request, $id)
    // {
    //     $product = Product::with(['stocks:*'])->find($id);
    
    //     if (!$product) {
    //         return response()->json(['error' => 'Product not found'], 404);
    //     }

    //     $validatedData = $request->validate([
    //         'product_name' => 'string|max:255',
    //         'sku' => 'string|max:255|unique:products,sku,' . $id,
    //         'units' => 'string',
    //         'category_id' => 'required|string',
    //         'sub_category_id' => 'required|string',
    //         'manufacturer' => 'nullable|string',
    //         'vendor_id' => 'required|string',
    //         'model' => 'nullable|string',
    //         'weight' => 'nullable|numeric',
    //         'weight_unit' => 'nullable|string',
    //         // 'location_id' => 'nullable|string',
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
    //         'inventory_alert_threshold' => 'integer|min:0',
    //         'status' => ['nullable', Rule::in(['active', 'inactive'])],
    //     ]);


    //     $barcodeNumber = $request->sku; // Unique barcode
    // if ($barcodeNumber) {
    //     // ✅ Generate Barcode as Base64
    //     // $barcodeImage = DNS1D::getBarcodePNG($barcodeNumber, 'C39');
    //     $barcodeImage = $barcodeNumber. 'C39';
    
    //     // ✅ Convert Base64 to an Image File
    //     $imagePath = 'public/barcodes/' . $barcodeNumber . '.png'; 
    //     Storage::put($imagePath, base64_decode($barcodeImage));
    
    //     // ✅ Store the public path for access
    //     $savedBarcodePath = str_replace('public/', 'storage/', $imagePath);
    // }

    // // $barcodes = storage_path('app/public/barcodes');
    // // $qrcode = storage_path('app/public/qrcode');
    // // $images = storage_path('app/public/images');

    // // Add barcode data
    // $validatedData['barcode_number'] = $barcodeNumber;
    // $validatedData['generated_barcode'] = $barcodeImage;

    // // ✅ Create Product
    

    // // ✅ Generate QR Code after product is created
    // $productDetails = [
    //     'barcode_number' => $barcodeNumber,
    //     'name' => $request->product_name,
    //     'sku' => $request->sku,
    //     'description' => $request->description,
    //     'price' => number_format($request->selling_cost, 2),
    //     'stock' => $request->opening_stock
    // ];

    // // $validatedData['generated_qrcode'] = DNS2D::getBarcodePNG(json_encode($productDetails), 'QRCODE');

    // // if ($validatedData['generated_qrcode']) {
    // //     $path = $validatedData['generated_qrcode']->store('public/qrcode');
    // //     // $validatedData['thumbnail'] = str_replace('public/', 'storage/', $path);
    // // }

    // if ($productDetails) {
    //     // ✅ Generate a Unique QR Code Name
    //     $fileName = 'qrcode_' . time() . '.png';
    
    //     // ✅ Generate QR Code as Base64
    //     // $qrcodeBase64 = DNS2D::getBarcodePNG(json_encode($productDetails), 'QRCODE');

    //     $qrcodeBase64 = json_encode($productDetails).'QRCODE';
    
    //     // ✅ Convert Base64 to an Image File and Save
    //     $imagePath = 'public/qrcode/' . $fileName; 
    //     Storage::put($imagePath, base64_decode($qrcodeBase64));
    
    //     // ✅ Store the public path for access
    //     $savedQRCodePath = str_replace('public/', 'storage/', $imagePath);
    
    //     $validatedData['generated_qrcode'] = $qrcodeBase64;
    // }


    // // if ($request->hasFile('thumbnail')) {
    // //     $path = $request->file('thumbnail')->store('public/thumbnails');
    // //     $validatedData['thumbnail'] = str_replace('public/', 'storage/', $path);
    // // }
    
    //  if ($request->hasFile('thumbnail')) {
    //     $image = $request->file('thumbnail');
    //     $filename = time() . '_' . $image->getClientOriginalName();
    //     $image->move(public_path('product/thumbnails'), $filename);
    //     $validatedData['thumbnail']  = 'product/thumbnails/' . $filename;
    // }

    //     $validatedData['location_id'] = json_encode(
    //         collect($request->storage_location)->pluck('location')->all()
    //     );

    //     $product->update($validatedData);

    //     $multiLocation = $request->storage_location;
    //     $product_id=$id;
    //     foreach ($multiLocation as $multiData) {
    //         $product_location = Stock::where('product_id', $product_id)
    //             ->where('location_id', $multiData['location'])
    //             ->first();

    //         $quantity = $multiData['quantity'];
    //         $adjustment = $multiData['adjustment'];

    //         if ($product_location) {
    //             // Update existing stock record
    //             // $currentStock = $product_location->current_stock;
    //             $currentStock = $multiData['quantity'];

    //             if ($adjustment === 'add') {
    //                 $newStock = $currentStock + $quantity;
    //                 $productOpeningStock = $product->opening_stock + $quantity;
    //             } else {
    //                 $newStock = $currentStock - $quantity;
    //                 $productOpeningStock = $product->opening_stock - $quantity;
    //             }

    //             $stockData = [
    //                 'current_stock' => $currentStock,
    //                 'new_stock' => $newStock,
    //                 'unit' => $multiData['unit'] ?? $product_location->unit,
    //                 'quantity' => $quantity,
    //                 'adjustment' => $adjustment,
    //                 'stock_date' => $validatedRequest['stock_date'] ?? null,
    //                 'vendor_id'     => $request->vendor,
    //                 'category_id'   => $request->category,
    //                 'reason_for_update' => $validatedRequest['reason_for_update'] ?? null,
    //             ];

    //             $product_location->update($stockData);
    //         } else {
    //             // Create new stock record
    //             $currentStock = $multiData['quantity'] ?? 0;

    //             if ($adjustment === 'add') {
    //                 $newStock = $currentStock + $quantity;
    //                 $productOpeningStock = $product->opening_stock + $quantity;
    //             } else {
    //                 $newStock = $currentStock - $quantity;
    //                 $productOpeningStock = $product->opening_stock - $quantity;
    //             }

    //             $stockData = [
    //                 'product_id' => $product_id,
    //                 'category_id' => $product->category,
    //                 'current_stock' => $currentStock,
    //                 'new_stock' => $newStock,
    //                 'unit' => $multiData['unit'] ?? null,
    //                 'location_id' => $multiData['location'],
    //                 'quantity' => $quantity,
    //                 'adjustment' => $adjustment,
    //                 'stock_date' => $validatedRequest['stock_date'] ?? null,
    //                 'vendor_id'     => $request->vendor,
    //                 'category_id'   => $request->category,
    //             ];

    //             Stock::create($stockData);
    //         }

    //         // Update the product's opening stock
    //         // $product->update(['opening_stock' => $productOpeningStock]);
    //     }

    // return response()->json(['message' => 'Product updated successfully', 'product' => $product], 200);


    // }
        
    public function movement($id)
    {
        // Get product with related category, sub-category, and vendor
        $product_detail = Product::with('category:id,name', 'sub_category:id,name', 'vendor:id,vendor_name')->find($id);

        if (!$product_detail) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // Fetch scan in/out records with relations
        $scanRecords = ScanInOutProduct::with([
            'product:id,product_name,opening_stock',
            'employee:id,employee_name',
            'user:id,name','machine:id,name','department:id,name','workStation:id,name'
        ])->where('product_id', $id)->get();

        // Map and transform scan records
        $scanRecords = $scanRecords->map(function ($record) {
            return [
                'id' => $record->id,
                'in_out_date_time' => $record->in_out_date_time,
                'machine_name' => $record->machine->name ?? null,
                'departmente_name' => $record->department->name ?? null,
                'workStation_name' => $record->workStation->name ?? null,
                'issue_from_name' => $record->user->name ?? null, 
                'employee_name' => $record->employee->employee_name ?? null,
                'product_name' => $record->product->product_name ?? null,
                'in_quantity' => $record->in_quantity,
                'out_quantity' => $record->out_quantity,
                'type' => $record->type,
                'purpose' => $record->purpose,
                'comments' => $record->comments,
                'quantity' => $record->product->opening_stock ?? null,
                'product_id' => $record->product_id,
                'issue_from_user_id' => $record->issue_from_user_id,
                'employee_id' => $record->employee_id,
                'created_at' => $record->created_at,
                'updated_at' => $record->updated_at,
            ];
        });

        // Prepare product data response
        $productData = [[
            'id' => $product_detail->id,
            'product_name' => $product_detail->product_name,
            'sku' => $product_detail->sku,
            'category_name' => $product_detail->category->name ?? null,
            'opening_stock' => $product_detail->opening_stock,
            'selling_cost' => $product_detail->selling_cost,
            'cost_price' => $product_detail->cost_price,
            'status' => $product_detail->status,
        ]];

        // Return as JSON
        return response()->json([
            'product_data' => $productData,
            'stock_details' => $scanRecords
        ], 200);
    }


        public function update(Request $request, $id)
    {
        $product = Product::with(['stocks:*'])->find($id);
    
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $validatedData = $request->validate([
            'product_name' => 'string|max:255',
            'sku' => 'string|max:255|unique:products,sku,' . $id,
            'units' => 'string',
            'category_id' => 'string',
            'sub_category_id' => 'nullable|string',
            'manufacturer' => 'nullable|string',
            'vendor_id' => 'nullable|string',
            'model' => 'nullable|string',
            'weight' => 'nullable|numeric',
            'weight_unit' => 'nullable|string',
            // 'location_id' => 'nullable|string',
            'thumbnail' => 'nullable',
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
        $barcodeImage = (new DNS1D)->getBarcodePNG($barcodeNumber, 'C39');

        // $barcodeImage = DNS1D::getBarcodePNG($barcodeNumber, 'C39');
        // $barcodeImage = $barcodeNumber. 'C39';
    
        $imagePath = 'public/barcodes/' . $barcodeNumber . '.png'; 
        Storage::put($imagePath, base64_decode($barcodeImage));
    
        $savedBarcodePath = str_replace('public/', 'storage/', $imagePath);
    }

    // Add barcode data
    $validatedData['barcode_number'] = $barcodeNumber;
    $validatedData['generated_barcode'] = $barcodeImage;

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
        
        $fileName = 'qrcode_' . time() . '.png';

        $productString = json_encode($productDetails, JSON_UNESCAPED_UNICODE);
    
        // Generate QR code
        $qrcodeBase64 = (new DNS2D)->getBarcodePNG($productString, 'QRCODE');
    
    
        // $qrcodeBase64 = DNS2D::getBarcodePNG(json_encode($productDetails), 'QRCODE');
        // $qrcodeBase64 = (new DNS2D)->getBarcodePNG($productDetails, 'QRCODE');
    
    

        // $qrcodeBase64 = json_encode($productDetails).'QRCODE';
    
        $imagePath = 'public/qrcode/' . $fileName; 
        Storage::put($imagePath, base64_decode($qrcodeBase64));
    
        $savedQRCodePath = str_replace('public/', 'storage/', $imagePath);
    
        $validatedData['generated_qrcode'] = $qrcodeBase64;
    }


    if ($request->hasFile('thumbnail')) {
        $image = $request->file('thumbnail');
        $filename = time() . '_' . $image->getClientOriginalName();
        $image->move(public_path('product/thumbnails'), $filename);
        $validatedData['thumbnail']  = 'product/thumbnails/' . $filename;
    }

        $validatedData['location_id'] = json_encode(
            collect($request->storage_location)->pluck('location')->all()
        );

        $product->update($validatedData);

        $multiLocation = $request->storage_location;
        $product_id=$id;
        foreach ($multiLocation as $multiData) {
            // print_r($multiData['unit_cost']);die;
            $product_location = Stock::where('product_id', $product_id)
                ->where('location_id', $multiData['location'])
                ->first();

            $quantity = $multiData['quantity'];
            // $adjustment = $multiData['adjustment'];

            if ($product_location) {
                // Update existing stock record
                // $currentStock = $product_location->current_stock;
                $currentStock = $multiData['quantity'];

                // print_r($multiData['unit_cost']);die;
                $stockData = [
                    'current_stock' => $currentStock,
                    'unit' => $multiData['unit'] ?? $product_location->unit,
                    'unit_cost'=> $multiData['unit_cost'],
                    'total_cost'=> $multiData['total_cost'],
                    'quantity' => $quantity,
                    'stock_date' => $validatedRequest['stock_date'] ?? null,
                    'vendor_id'     => $request->vendor,
                    'category_id'   => $request->category,
                    'reason_for_update' => $validatedRequest['reason_for_update'] ?? null,
                ];

                $product_location->update($stockData);
            } else {
                // Create new stock record
                $currentStock = $multiData['quantity'] ?? 0;


                $stockData = [
                    'product_id' => $product_id,
                    'category_id' => $product->category,
                    'current_stock' => $currentStock,
                    'unit' => $multiData['unit'] ?? null,
                    'unit_cost' => $multiData['unit_cost'],
                    'total_cost' => $multiData['total_cost'],
                    'location_id' => $multiData['location'],
                    'quantity' => $quantity,
                    'stock_date' => $validatedRequest['stock_date'] ?? null,
                    'vendor_id'     => $request->vendor,
                    'category_id'   => $request->category,
                ];

                Stock::create($stockData);
            }

            // Update the product's opening stock
            // $product->update(['opening_stock' => $productOpeningStock]);
        }

    return response()->json(['message' => 'Product updated successfully', 'product' => $product], 200);

    }

    public function destroy($id)
    {
        // $product = Product::find($id);
        $product = Product::with(['stocks:*'])->find($id);
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

        $validatedRequest = $request->validate([
            'stock_date' => 'nullable|string',
            'vendor_id' => 'nullable|string',
            'reason_for_update' => 'nullable|string',
            'storage_location' => 'required|array',
            'storage_location.*.current_stock' => 'nullable|numeric',
            'storage_location.*.quantity' => 'required|numeric',
            'storage_location.*.unit' => 'nullable|string',
            'storage_location.*.unit_cost' => 'nullable|string',
            'storage_location.*.total_cost' => 'nullable|string',
            'storage_location.*.location' => 'required|string',
            'storage_location.*.adjustment' => 'required|string|in:add,Subtract,Select',
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
                    'current_stock' => $newStock,
                    'new_stock' => $newStock,
                    'unit' => $multiData['unit'] ?? $product_location->unit,
                    'unit_cost'          => $multiData['unit_cost'],
                    'total_cost'          => $multiData['total_cost'],
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
                    'current_stock' => $newStock,
                    'new_stock' => $newStock,
                    'unit' => $multiData['unit'] ?? null,
                    'unit_cost'          => $multiData['unit_cost'],
                    'total_cost'          => $multiData['total_cost'],
                    'location_id' => $multiData['location'],
                    'quantity' => $quantity,
                    'adjustment' => $adjustment,
                    'stock_date' => $validatedRequest['stock_date'] ?? null,
                    'vendor_id' => $validatedRequest['vendor_id'] ?? null,
                    'reason_for_update' => $validatedRequest['reason_for_update'] ?? null,
                ];

                Stock::create($stockData);
            }


            $product->update(['opening_stock' => $productOpeningStock]);
        }

            foreach ($multiLocation as $multiData) {
                // $product_location = InventoryAdjustmentReports::where('product_id', $product_id)
                //     ->where('location_id', $multiData['location'])
                //     ->first();
    
                $quantity = $multiData['quantity'];
                $adjustment = $multiData['adjustment'];
    
                // if ($product_location) {
                    // Update existing stock record
                    // $currentStock = $product_location->current_stock;
                    // $currentStock = $multiData['current_stock'];
    
                    // if ($adjustment === 'add') {
                    //     $newStock = $currentStock + $quantity;
                    //     $productOpeningStock = $product->opening_stock + $quantity;
                    // } else {
                    //     $newStock = $currentStock - $quantity;
                    //     $productOpeningStock = $product->opening_stock - $quantity;
                    // }
    
                    // $stockData = [
                    //     'current_stock' => $currentStock,
                    //     'new_stock' => $newStock,
                    //     'unit' => $multiData['unit'] ?? $product_location->unit,
                    //     'quantity' => $quantity,
                    //     'adjustment' => $adjustment,
                    //     'stock_date' => $validatedRequest['stock_date'] ?? null,
                    //     'vendor_id' => $validatedRequest['vendor_id'] ?? null,
                    //     'reason_for_update' => $validatedRequest['reason_for_update'] ?? null,
                    // ];
    
                    // $product_location->update($stockData);


                // } else {
                    // Create new stock record
                    if($adjustment!='Select'){
                        
                    
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
    
                    InventoryAdjustmentReports::create($stockData);
                    
                    }
                // }

            // Update the product's opening stock
            
        }

        return response()->json(['message' => 'Stock updated successfully'], 200);
    }
 
    
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
            'location_d' => $stock->location_id,
            'location' => optional($stock->location)->name, // Safely get location name
            'current_stock' => $stock->current_stock,
            'new_stock' => $stock->new_stock,
            'unit' => $stock->unit,
            'unit_cost'=> $stock->unit_cost,
            'total_cost'=> $stock->total_cost,
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
        $products = Product::select('id', 'product_name', 'sku', 'opening_stock', 'location_id', 'inventory_alert_threshold', DB::raw("'Warning' as status"))
            ->where('opening_stock', '<', DB::raw('inventory_alert_threshold'))
            ->get();
    
        $inventory_alert = $products->map(function ($product) {
            // Decode location IDs (JSON string to array)
            $locationIds = json_decode($product->location_id, true);
    
            // Get location names from DB
            $locationNames = \App\Models\Location::whereIn('id', $locationIds)->pluck('name')->toArray();
    
            return [
                'id' => $product->id,
                'product_id'=>$product->id,
                'product_name' => $product->product_name,
                'sku' => $product->sku,
                'opening_stock' => $product->opening_stock,
                'inventory_alert_threshold' => $product->inventory_alert_threshold,
                'location_id' => $locationIds, // optional: keep raw IDs
                'location_name' => $locationNames, // array of location names
                'status' => 'Warning',
            ];
        });
    
        return response()->json(['inventory_alert' => $inventory_alert], 200);
    }
  

    public function inventoryAdjustmentsReport()
    {
        
        
        // $stocks = Stock::with([
        //     'product.category', // Load category via product
        //     'category:id,name','vendor:id,vendor_name','location:id,name'
        // ])->where('new_stock', '>', 0)->get();


        // $adjustments = $stocks->map(function ($stock) {
        //     $adjustmentSymbol = $stock->adjustment == 'subscription' ? '-' : '+';
        //     $newStock = $stock->adjustment == 'subscription'
        //         ? $stock->current_stock - $stock->quantity
        //         : $stock->current_stock + $stock->quantity;

        //         // print_r($stock->product->category);die;
        //     return [
        //         'id' => $stock->id,
        //         'product_id' => $stock->product_id,
        //         'product_name' => $stock->product->product_name ?? 'N/A',
        //         'sku' => $stock->product->sku ?? 'N/A',
        //         'category_name' => $stock->category->name ?? 'N/A',  // Ensure category is not null
        //         'vendor_name' => $stock->vendor->vendor_name ?? 'N/A', // Ensure vendor is not null
        //         'previous_stock' => $stock->current_stock,
        //         'new_stock' => $newStock,
        //         'adjustment' => "{$adjustmentSymbol} {$stock->quantity}",
        //         'reason' => $stock->reason_for_update ?? 'N/A',
        //         'location' => optional($stock->location)->name, 
        //         'stock_date' => $stock->stock_date,
        //         'created_at' => $stock->created_at,
        //         'updated_at' => $stock->updated_at,
        //     ];
        // });


        $stocks = InventoryAdjustmentReports::with([
            'product.category', // Load category via product
            'category:id,name','vendor:id,vendor_name','location:id,name'
        ])->where('new_stock', '>', 0)->orderBy('id', 'desc')->get();


        $adjustments = $stocks->map(function ($stock) {
            $adjustmentSymbol = $stock->adjustment == 'Subtract' ? '-' : '+';
            $newStock = $stock->adjustment == 'Subtract'
                ? $stock->current_stock - $stock->quantity
                : $stock->current_stock + $stock->quantity;

                // print_r($stock->product->category);die;
            return [
                'id' => $stock->id,
                'product_id' => $stock->product_id,
                'product_name' => $stock->product->product_name ?? 'N/A',
                'sku' => $stock->product->sku ?? 'N/A',
                'category_name' => $stock->product->category->name ?? 'N/A',  // Ensure category is not null
                'vendor_name' => $stock->vendor->vendor_name ?? 'N/A', // Ensure vendor is not null
                'previous_stock' => $stock->current_stock,
                'new_stock' => $newStock,
                'adjustment' => "{$adjustmentSymbol} {$stock->quantity}",
                'reason' => $stock->reason_for_update ?? 'N/A',
                'location' => optional($stock->location)->name, 
                'stock_date' => $stock->stock_date,
                'created_at' => $stock->created_at,
                'updated_at' => $stock->updated_at,
            ];
        });

        // print_r($adjustments);die;
        return response()->json(['inventory_adjustments' => $adjustments], 200);
    }

        // public function uploadCSV(Request $request)
        // {
        //     $request->validate([
        //         'file' => 'required|mimes:csv,txt|max:2048'
        //     ]);
        
        //     $file = $request->file('file');
            
        //     $handle = fopen($file->getPathname(), "r");
        
        //     $header = fgetcsv($handle);
        //     $expectedHeaders = [
        //         "product_name", "sku", "category_id", "sub_category_id", "manufacturer",
        //         "vendor_id", "model", "description", "location_id", "current_stock", "units","opening_stock_total_stock", "inventory_alert_threshold",
        //         "selling_cost", "cost_price", "commit_stock_check", "project_name",
        //         "weight", "weight_unit", "length", "width",
        //         "depth", "measurement_unit", "returnable", "status"
        //     ];
        
        //     if ($header !== $expectedHeaders) {
        //         return response()->json(['error' => 'Invalid CSV format. Please use the correct template.'], 400);
        //     }
        
        //     $products = [];
        //     $invalidRows = [];
        //     $rowNumber = 2;
        
        //     while ($row = fgetcsv($handle)) {
        //         if (count($row) !== count($expectedHeaders)) {
        //             // print_r($rowNumber);die;
        //             $invalidRows[] = $rowNumber;
                    
                      
        //             continue;
        //         }
        
        //         if (empty($row[0]) || empty($row[1])) {
        //             $invalidRows[] = $rowNumber;
        //             continue;
        //         }
        
        //         if (Product::where('sku', $row[1])->exists()) {
        //             continue;
        //         }
        
        
        //         $barcodeNumber = $row[1];
        //         $barcodeImage = (new DNS1D)->getBarcodePNG($barcodeNumber, 'C39');
        //         $barcodePath = 'public/barcodes/' . $barcodeNumber . '.png';
        //         Storage::put($barcodePath, $barcodeImage);
        //         $savedBarcodePath = str_replace('public/', 'storage/', $barcodePath);
        
        //         $productDetails = ['sku' => $row[1]];
        //         $qrCodeImage = (new DNS2D)->getBarcodePNG(json_encode($productDetails, JSON_UNESCAPED_UNICODE), 'QRCODE');
        //         $qrCodeFile = 'qrcode_' . time() . '_' . uniqid() . '.png';
        //         $qrCodePath = 'public/qrcode/' . $qrCodeFile;
        //         Storage::put($qrCodePath, $qrCodeImage);
        //         $savedQRCodePath = str_replace('public/', 'storage/', $qrCodePath);
        
        //         $unit = Unit::firstOrCreate(['name' => $row[10]], ['name' => $row[10]]);
        //         $category = Category::firstOrCreate(['name' => $row[2]], ['description' => $row[2]]);
        //         $subcategory = Subcategory::firstOrCreate([
        //             'name' => $row[3],
        //             'category_id' => $category->id
        //         ], [
        //             'name' => $row[3],
        //             'category_id' => $category->id,
        //             'description' => $row['3'],
        //         ]);
        //         $vendor = Vendor::firstOrCreate(['vendor_name' =>$row[5]], ['vendor_name' => $row[5]]);
        
                
        //         $locationNames = json_decode($row[8], true); // decode JSON string to array
        //         $locationIds = [];
                
                
        //         if (is_array($locationNames)) {
        //             foreach ($locationNames as $name) {
        //                 $name = trim($name);
                
        //                 $location = \App\Models\Location::firstOrCreate(
        //                     ['name' => $name],
        //                     ['name' => $name]
        //                 );
                
        //                 $locationIds[] = $location->id;
        //             }
        //         } else {
        //             // Handle invalid JSON
        //             Log::error('Invalid JSON in location column:', ['value' => $row[8]]);
        //         }
        
                
               
        
        
        //         $product = Product::create([
        //             'product_name' => $row[0],
        //             'sku' => $row[1],
        //             'generated_barcode' => $barcodeImage,
        //             'generated_qrcode' => $qrCodeImage,
        //             'units' => $unit->id,
        //             'category_id' => $category->id,
        //             'sub_category_id' => $subcategory->id,
        //             'manufacturer' => $row[4],
        //             'vendor_id' => $vendor->id,
        //             'model' => $row[6],
        //             'description' => $row[7],
        //             'returnable' => strtolower($row[22]) === 'yes' ? 1 : 0,
        //             'opening_stock' => (int) $row[11],
        //             'selling_cost' => (float) $row[13],
        //             'cost_price' => (float) $row[14],
        //             'commit_stock_check' => (float) $row[15],
        //             'project_name' => $row[16],
        //             'location_id' => json_encode($locationIds),
        //             'weight' => (float) $row[17],
        //             'weight_unit' => $row[18],
        //             'length' => (float) $row[19],
        //             'width' => (float) $row[20],
        //             'depth' => (float) $row[21],
        //             'measurement_unit' => $row[22],
        //             'barcode_number' => $row[1],
        //             'inventory_alert_threshold' => (int) $row[12],
        //             'status' => $row[23],
        //             'created_at' => now(),
        //             'updated_at' => now(),
        //         ]);
        
      
        //         $totalStock = 0;
        //         foreach ($locationIds as $locationId) {
                    
        //             $currentStock = (int)$row[9];
        //             $totalStock += $currentStock;
            
        //             Stock::create([
        //                 'product_id'    => $product->id,
        //                 'vendor_id'     => $vendor->id,
        //                 'category_id'   => $category->id,
        //                 'current_stock' => $currentStock,
        //                 'unit'          => $unit->name,
        //                 'location_id'   => $locationId,
        //                 'stock_date'    => now(),
        //             ]);
        //         }
        //         // $product->opening_stock = $totalStock;
        //         // $product->opening_stock = $totalStock;
        //         // $product->save();
        
        //         $rowNumber++;
        //     }
        
        //     fclose($handle);
        
        //     return response()->json([
        //         'message' => 'CSV uploaded successfully.',
        //         'invalid_rows' => $invalidRows
        //     ], 200);
        // }

public function uploadCSV(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:csv,txt|max:2048'
    ]);

    $file = $request->file('file');
    // print_r($file);die;
    $handle = fopen($file->getPathname(), "r");

    $header = fgetcsv($handle);
    $expectedHeaders = [
        "product_name", "sku", "units", "category_id", "sub_category_id", "manufacturer",
        "vendor_id", "model", "location_id", "description", "returnable", "track_inventory", "opening_stock",
        "selling_cost", "cost_price", "commit_stock_check", "project_name",
        "weight", "weight_unit", "length", "width",
        "depth", "measurement_unit", "inventory_alert_threshold", "status"
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

        if (empty($row[0]) || empty($row[1])) {
            $invalidRows[] = $rowNumber;
            continue;
        }

        if (Product::where('sku', $row[1])->exists()) {
            continue;
        }

        $barcodeNumber = $row[1];
        $barcodeImage = (new DNS1D)->getBarcodePNG($barcodeNumber, 'C39');
        $barcodePath = 'public/barcodes/' . $barcodeNumber . '.png';
        Storage::put($barcodePath, $barcodeImage);
        $savedBarcodePath = str_replace('public/', 'storage/', $barcodePath);

        $productDetails = ['sku' => $row[1]];
        $qrCodeImage = (new DNS2D)->getBarcodePNG(json_encode($productDetails, JSON_UNESCAPED_UNICODE), 'QRCODE');
        $qrCodeFile = 'qrcode_' . time() . '_' . uniqid() . '.png';
        $qrCodePath = 'public/qrcode/' . $qrCodeFile;
        Storage::put($qrCodePath, $qrCodeImage);
        $savedQRCodePath = str_replace('public/', 'storage/', $qrCodePath);

        $unit = Unit::firstOrCreate(['name' => $row[2]], ['name' => $row[2]]);
        $category = Category::firstOrCreate(['name' => $row[3]], ['name' => $row[3]]);
        $subcategory = Subcategory::firstOrCreate([
            'name' => $row[4],
            'category_id' => $category->id
        ], [
            'name' => $row[4],
            'category_id' => $category->id
        ]);
        $vendor = Vendor::firstOrCreate(['vendor_name' =>$row[6]], ['vendor_name' => $row[6]]);

        
        $locationNames = json_decode($row[8], true); // decode JSON string to array
        $locationIds = [];
        
        if (is_array($locationNames)) {
            foreach ($locationNames as $name) {
                $name = trim($name);
        
                $location = \App\Models\Location::firstOrCreate(
                    ['name' => $name],
                    ['name' => $name]
                );
        
                $locationIds[] = $location->id;
            }
        } else {
            // Handle invalid JSON
            Log::error('Invalid JSON in location column:', ['value' => $row[8]]);
        }

        
        $product = Product::create([
            'product_name' => $row[0],
            'sku' => $row[1],
            'generated_barcode' => $barcodeImage,
            'generated_qrcode' => $qrCodeImage,
            'units' => $unit->id,
            'category_id' => $category->id,
            'sub_category_id' => $subcategory->id,
            'manufacturer' => $row[5],
            'vendor_id' => $vendor->id,
            'model' => $row[7],
            'description' => $row[9],
            'returnable' => strtolower($row[10]) === 'yes' ? 1 : 0,
            'track_inventory' => $row[11],
            'opening_stock' => (int) $row[12],
            'selling_cost' => (float) $row[13],
            'cost_price' => (float) $row[14],
            'commit_stock_check' => (float) $row[15],
            'project_name' => $row[16],
            'location_id' => json_encode($locationIds),
            'weight' => (float) $row[17],
            'weight_unit' => $row[18],
            'length' => (float) $row[19],
            'width' => (float) $row[20],
            'depth' => (float) $row[21],
            'measurement_unit' => $row[22],
            'barcode_number' => $row[1],
            'inventory_alert_threshold' => (int) $row[23],
            'status' => $row[24],
            // 'thumbnail'=>$thumbnail,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $totalStock = 0;
        foreach ($locationIds as $locationId) {
            
            $currentStock = (int)$row[12];
            $totalStock += $currentStock;
    
            Stock::create([
                'product_id'    => $product->id,
                'vendor_id'     => $vendor->id,
                'category_id'   => $category->id,
                'current_stock' => $currentStock,
                'unit'          => $unit->name,
                'location_id'   => $locationId,
                'stock_date'    => now(),
            ]);
        }
        $product->opening_stock = $totalStock;
        $product->opening_stock = $totalStock;
        $product->save();

        $rowNumber++;
    }

    fclose($handle);

    return response()->json([
        'message' => 'CSV uploaded successfully.',
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

public function exportProductsToCSV()
{
    $fileName = 'products.csv';
    $products = Product::all();

    $headers = [
        "Content-type"        => "text/csv",
        "Content-Disposition" => "attachment; filename=$fileName",
        "Pragma"              => "no-cache",
        "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        "Expires"             => "0"
    ];

    $columns = ['id','product_name','sku','units','category_id','sub_category_id','manufacturer','vendor_id','model','location_id','description','returnable','track_inventory','opening_stock','selling_cost','cost_price','commit_stock_check','project_name','weight','weight_unit','length','width','depth','measurement_unit','inventory_alert_threshold','status'
];

    $callback = function () use ($products, $columns) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $columns); // heading

        foreach ($products as $product) {
            fputcsv($file, [
                $product,
                // $product->id,
                // $product->product_name,
                // $product->sku,
                // $product->opening_stock,
                // $product->inventory_alert_threshold,
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}


public function printBarcode(Request $request)
{
    $request->validate([
        'type' => 'required|in:barcode,qrcode',
        'count' => 'required|integer|min:1',
        'format' => 'required|in:only_barcode,with_details',
        'orientation' => 'required|in:vertical,horizontal',
        'size' => 'required|in:small,medium,large',
        'data' => 'required|string', // SKU or product code
    ]);

    $barcodes = [];
    for ($i = 0; $i < $request->count; $i++) {
        if ($request->type == 'barcode') {
            
            $barcodes[] = base64_encode((new DNS1D)->getBarcodePNG($request->data, 'C128', 2, 60));

        } else {

        $barcodes[] = (new DNS2D)->getBarcodePNG($request->data, 'QRCODE');
    
        }
    }

// Retrieve the setting where key is 'sku' and value is 1
$settings = BarcodeSetting::where('value', 1)->pluck('key')->toArray();
$productDetail = [];
if (!empty($settings)) {
    // Dynamically select only the keys that are enabled in settings
    $product = Product::select($settings,'product_name')
        ->where('sku', $request->data)
        ->first();

    
        // Return only selected values
       $productDetail = $product->toArray();
   
}


    $pdf = PDF::loadView('pdf.barcodes', [
        'barcodes' => $barcodes,
        'orientation' => $request->orientation,
        'format' => $request->format,
        'size' => $request->size,
        'sku' => $request->data,
        'data' => json_encode($product),
        'type' => $request->type,
        'count' => $request->count,
        
    ]);

    return $pdf->download('barcodes.pdf');
}


    
    // public function downloadCsv()
    // {
    //     $fileName = 'products.csv';
    //     $products = Product::all();
    
    //     $headers = [
    //         "Content-type" => "text/csv",
    //         "Content-Disposition" => "attachment; filename=$fileName",
    //         "Pragma" => "no-cache",
    //         "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
    //         "Expires" => "0"
    //     ];
    
    //     $columns = ['id','product_name','sku','units','category_id','sub_category_id','manufacturer','vendor_id','model','location_id','description','returnable','track_inventory','opening_stock','selling_cost','cost_price','commit_stock_check','project_name','weight','weight_unit','length','width','depth','measurement_unit','inventory_alert_threshold','status'
    // ];
    
    //     $callback = function() use ($products, $columns) {
    //         $file = fopen('php://output', 'w');
    //         fputcsv($file, $columns);
    
    //         foreach ($products as $product) {
    //             fputcsv($file, [
    //                 $product->id,
    //                 $product->product_name,
    //                 $product->sku,
    //                 $product->units,
    //                 $product->category_id,
    //                 $product->sub_category_id,
    //                 $product->manufacturer,
    //                 $product->vendor_id,
    //                 $product->model,
    //                 $product->location_id,
    //                 $product->description,
    //                 $product->returnable,
    //                 $product->track_inventory,
    //                 $product->opening_stock,
    //                 $product->selling_cost,
    //                 $product->cost_price,
    //                 $product->commit_stock_check,
    //                 $product->project_name,
    //                 $product->weight,
    //                 $product->weight_unit,
    //                 $product->length,
    //                 $product->width,
    //                 $product->depth,
    //                 $product->measurement_unit,
    //                 $product->inventory_alert_threshold,
    //                 $product->status,
    //             ]);
    //         }
    
    //         fclose($file);
    //     };
    
    //     return response()->stream($callback, 200, $headers);
    // }
    
    public function downloadCsv()
{
    $fileName = 'products.csv';

    $products = Product::with([
        'category:id,name',
        'vendor:id,vendor_name',
        'sub_category:id,name'
    ])->orderBy('id', 'desc')->get();

    $products = $products->map(function ($product) {
        return [
            'id' => $product->id,
            'product_name' => $product->product_name,
            'sku' => $product->sku,
            'units' => $product->units,
            'category_name' => optional($product->category)->name,
            'subcategory_name' => optional($product->sub_category)->name,
            'manufacturer' => $product->manufacturer,
            'vendor_name' => optional($product->vendor)->vendor_name,
            'model' => $product->model,
            'location_id' => $product->location_id,
            'description' => $product->description,
            'returnable' => $product->returnable,
            'track_inventory' => $product->track_inventory,
            'opening_stock' => $product->opening_stock,
            'selling_cost' => $product->selling_cost,
            'cost_price' => $product->cost_price,
            'commit_stock_check' => $product->commit_stock_check,
            'project_name' => $product->project_name,
            'weight' => $product->weight,
            'weight_unit' => $product->weight_unit,
            'length' => $product->length,
            'width' => $product->width,
            'depth' => $product->depth,
            'measurement_unit' => $product->measurement_unit,
            'inventory_alert_threshold' => $product->inventory_alert_threshold,
            'status' => $product->status,
        ];
    });

    $headers = [
        "Content-type" => "text/csv",
        "Content-Disposition" => "attachment; filename=$fileName",
        "Pragma" => "no-cache",
        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
        "Expires" => "0"
    ];

    $columns = [
        'id', 'product_name', 'sku', 'units', 'category_name', 'subcategory_name',
        'manufacturer', 'vendor_name', 'model', 'location_id', 'description',
        'returnable', 'track_inventory', 'opening_stock', 'selling_cost', 'cost_price',
        'commit_stock_check', 'project_name', 'weight', 'weight_unit', 'length',
        'width', 'depth', 'measurement_unit', 'inventory_alert_threshold', 'status'
    ];

    $callback = function () use ($products, $columns) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $columns);

        foreach ($products as $product) {
            fputcsv($file, array_values($product));
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}



public function generateTemplateCsvUrl()
{
    $filename = 'csv_tem/product_template.csv';

    // CSV header columns
    // $columns = [
    //     "product_name", "sku", "units", "category_id", "sub_category_id", "manufacturer",
    //     "vendor_id", "model", "location_id", "description","returnable", "track_inventory", "opening_stock",
    //     "selling_cost", "cost_price", "commit_stock_check","project_name",
    //     "weight", "weight_unit", "length", "width",
    //     "depth", "measurement_unit","inventory_alert_threshold","status" 
    // ];
    $columns = [
        "product_name", "sku", "category_id", "sub_category_id", "manufacturer",
        "vendor_id", "model", "description", "location_id", "current_stock", "units","opening_stock_total_stock", "inventory_alert_threshold",
        "selling_cost", "cost_price", "commit_stock_check", "project_name",
        "weight", "weight_unit", "length", "width",
        "depth", "measurement_unit", "returnable", "status"
    ];

    // Open file for writing in local storage
    $filePath = storage_path("app/public/{$filename}");
    $file = fopen($filePath, 'w');
    fputcsv($file, $columns); // Write headers
    fclose($file);

    // Make sure the file is accessible (ensure 'public' disk is linked)
    $url = asset("storage/{$filename}");

    return response()->json([
        'status' => 'success',
        'url' => $url
    ]);
}

}

