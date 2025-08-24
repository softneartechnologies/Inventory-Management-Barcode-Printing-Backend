<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Location;
use App\Models\Stock;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\UomCategory;
use App\Models\Manufacturer;
use App\Models\UomUnit;
use App\Models\Vendor;
use App\Models\ScanInOutProduct;
use App\Models\Unit;
use App\Models\InventoryAdjustmentReports;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use App\Models\BarcodeSetting;
use Illuminate\Support\Facades\Log;






class ProductController extends Controller
{
    // public function index()
    // {
        
    //     $products = Product::with('category:id,name','vendor:id,vendor_name',
    //     'sub_category:id,name')->orderBy('id', 'desc')->get();
    
        
    //         $products = $products->map(function ($product) {
    //         // Get all product attributes + add category name
    //         $data = $product->toArray();
    //         $data['category_name'] = optional($product->category)->name;
    //         $data['subcategory_name'] = optional($product->subcategory)->name;
    //         $data['vendor_name'] = optional($product->vendor)->vendor_name;
            
    //         if(!empty($product->thumbnail)){
    //         $data['product_images'] = url($product->thumbnail); 
    //         }else{
    //              $data['product_images'] = url('/storage/default_image/default_imagess.jpg'); 
    //         }
    
    //         return $data;
    //     });
    
    //     return response()->json(['products' => $products], 200);
    // }
    

    // public function index(Request $request)
    // {
    //     $totalcount =$countproducts = Product::with('category:id,name','vendor:id,vendor_name',
    //     'sub_category:id,name')->orderBy('id', 'desc')->count();

    //     $query = Product::with(['category:id,name', 'vendor:id,vendor_name', 'sub_category:id,name']);

    //     // ✅ Search functionality
    //     if ($request->has('search') && !empty($request->search)) {
    //         $search = $request->search;
    //         $query->where(function ($q) use ($search) {
    //             $q->where('product_name', 'like', "%$search%")
    //             ->orWhere('description', 'like', "%$search%")
    //              ->orWhereHas('category', function ($catQuery) use ($search) {
    //           $catQuery->where('name', 'like', "%{$search}%");
    //       });
    //         });
    //     }

    
    //     $sortBy = $request->get('sort_by', 'id'); // default column
    //     $sortOrder = $request->get('sort_order', 'desc'); // default order

    //     if ($sortBy === 'category_name') {
    //         $query->orderBy(
    //             Category::select('name')
    //                 ->whereColumn('categories.id', 'products.category_id'),
    //             $sortOrder
    //         );
    //     } else {
    //         $query->orderBy($sortBy, $sortOrder);
    //     }

    //     // ✅ Pagination
    //     $perPage = $request->get('per_page', 10); // default 10 items per page
    //     $products = $query->paginate($perPage);

    //     $products_data = $products->map(function ($product) {
    //         // Get all product attributes + add category name
    //         $data = $product->toArray();
    //         $data['category_name'] = optional($product->category)->name;
    //         $data['subcategory_name'] = optional($product->subcategory)->name;
    //         $data['vendor_name'] = optional($product->vendor)->vendor_name;
            
    //         if(!empty($product->thumbnail)){
    //         $data['product_images'] = url($product->thumbnail); 
    //         }else{
    //              $data['product_images'] = url('/storage/default_image/default_imagess.jpg'); 
    //         }
    
    //         return $data;
    //     });
    
    //     return response()->json(['total_count' =>$totalcount,'products' => $products_data], 200);
    // }

    public function index(Request $request)
{
    $totalcount = Product::count();

    $query = Product::with([
        'category:id,name',
        'vendor:id,vendor_name',
        'sub_category:id,name'
    ]);

    // ✅ Search
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('product_name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhereHas('category', function ($catQuery) use ($search) {
                  $catQuery->where('name', 'like', "%{$search}%");
              });
        });
    }

       // ✅ Filter
    // ✅ Filter
if ($request->filled('category') || $request->filled('start_date') || $request->filled('end_date')) {
    $category    = $request->category;
    $type_filter = $request->type_filter;
    $start_date  = $request->start_date;
    $end_date    = $request->end_date;

    $query->where(function ($q) use ($category, $start_date, $end_date) {
        
        // ✅ Category filter
        if (!empty($category)) {
            $q->whereHas('category', function ($catQuery) use ($category) {
                $catQuery->where('name', 'like', "%{$category}%");
            });
        }

        // ✅ Date range filter
        if (!empty($start_date) && !empty($end_date)) {
            $q->whereBetween('created_at', [$start_date, $end_date]);
        } elseif (!empty($start_date)) {
            $q->whereDate('created_at', '>=', $start_date);
        } elseif (!empty($end_date)) {
            $q->whereDate('created_at', '<=', $end_date);
        }
    });
}




    // ✅ Sorting - default recent first
    $sortBy = $request->get('sort_by');
    $sortOrder = $request->get('sort_order', 'desc');

    if ($sortBy === 'category_name') {
        $query->orderBy(
            Category::select('name')
                ->whereColumn('categories.id', 'products.category_id'),
            $sortOrder
        );
    } elseif ($sortBy) {
        $query->orderBy($sortBy, $sortOrder);
    } else {
        // Default recent
        $query->latest(); // orders by created_at DESC by default
    }

    // ✅ Pagination
    $perPage = $request->get('per_page', 10);
    $products = $query->paginate($perPage);

    $products_data = $products->map(function ($product) {
        $data = $product->toArray();
        $data['category_name'] = optional($product->category)->name;
        $data['subcategory_name'] = optional($product->sub_category)->name;
        $data['vendor_name'] = optional($product->vendor)->vendor_name;
        $data['product_images'] = !empty($product->thumbnail) 
            ? url($product->thumbnail) 
            : url('/storage/default_image/default_imagess.jpg');
        return $data;
    });

    return response()->json([
        'total_count' => $totalcount,
        'products' => $products_data
    ], 200);
}


     public function searchProduct(Request $request)
    {
        $totalcount =$countproducts = Product::with('category:id,name','vendor:id,vendor_name',
        'sub_category:id,name')->orderBy('id', 'desc')->count();

        $query = Product::with(['category:id,name', 'vendor:id,vendor_name', 'sub_category:id,name']);

        // ✅ Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%$search%")
                ->orWhere('model', 'like', "%$search%")
                ->orWhere('manufacturer', 'like', "%$search%");
                // Add more searchable fields if needed
            });
        }

        // ✅ Sorting functionality
        $sortBy = $request->get('sort_by', 'id'); // Default to 'id'
        $sortOrder = $request->get('sort_order', 'desc'); // Default to 'desc'
        $query->orderBy($sortBy, $sortOrder);

        // ✅ Pagination
        $perPage = $request->get('per_page', 10); // default 10 items per page
        $products = $query->paginate($perPage);

        $products_data = $products->map(function ($product) {
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
    
        return response()->json(['total_count' =>$totalcount,'products' => $products_data], 200);
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

        
    // $validatedData = $request->validate([
    //     // 'thumbnail' => 'required',
    //     'product_name' => 'required|string|max:255',
    //     'sku' => 'required|string|max:255|unique:products',
    //     // 'category_id' => 'required|string',
    //     // 'sub_category_id' => 'required|string',
    //     // 'manufacturer' => 'required|string',
    //     // 'vendor_id' => 'required|string',
    //     // 'model' => 'required|string',
    //     // 'unit_of_measurement_category' => 'required|numeric',
    //     // 'description' => 'required|string',
    //     // 'returnable' => 'boolean',
    //     // 'commit_stock_check' => 'boolean',
    //     // 'inventory_alert_threshold' => 'integer|min:0',
    //     // 'location_id' => 'array',
    //     // 'quantity' => 'nullable|numeric',
    //     // 'unit_of_measure' => 'nullable|string',
    //     // 'per_unit_cost' => 'nullable|numeric',
    //     // 'total_cost' => 'nullable|string',
    //     // 'opening_stock' => 'integer|min:0',
    //     // 'status' => ['required', Rule::in(['active', 'inactive'])],
    // ]);

     $validatedData = $request->validate([
        'thumbnail' => '',
        'product_name' => 'required|string|max:255',
        'sku' => 'required|string|max:255|unique:products',
        'category_id' => 'nullable|string',
        'sub_category_id' => 'nullable|string',
        'manufacturer' => 'nullable|string',
        'vendor_id' => 'nullable|string',
        'model' => 'nullable|string',
        'unit_of_measurement_category' => 'nullable|numeric',
        'description' => 'nullable|string',
        'returnable' => 'boolean',
        'commit_stock_check' => 'boolean',
        'inventory_alert_threshold' => 'integer|min:0',
        'location_id' => 'array',
        'quantity' => 'nullable|numeric',
        'unit_of_measure' => 'nullable|string',
        'per_unit_cost' => 'nullable|numeric',
        'total_cost' => 'nullable|string',
        'opening_stock' => 'integer|min:0',
        'status' => ['nullable', Rule::in(['active', 'inactive'])],
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
            collect($request->storage_location)->pluck('location_id')->all()
        );
        $validatedData['quantity'] = json_encode(
            collect($request->storage_location)->pluck('quantity')->all()
        );
        $validatedData['unit_of_measure'] = json_encode(
            collect($request->storage_location)->pluck('unit_of_measure')->all()
        );
        $validatedData['per_unit_cost'] = json_encode(
            collect($request->storage_location)->pluck('per_unit_cost')->all()
        );
        $validatedData['total_cost'] = json_encode(
            collect($request->storage_location)->pluck('total_cost')->all()
        );
        // $validatedData['location_id'] = json_encode($request->storage_location->location);

        $product = Product::create($validatedData);
        if (!empty($request->storage_location) && is_array($request->storage_location)) {
        foreach ($request->storage_location as $multiData) {
            Stock::create([
                'product_id'    => $product->id,
                'vendor_id'     => $request->vendor_id?? null,
                'category_id'   => $request->category_id?? null,
                'location_id'   => $multiData['location_id']?? null,
                'quantity' => $multiData['quantity']?? null,
                'current_stock' => $multiData['quantity']?? null,
                'unit_of_measure'=> $multiData['unit_of_measure']?? null,
                'per_unit_cost'          => $multiData['per_unit_cost']?? null,
                'total_cost'          => $multiData['total_cost']?? null,
                // 'adjustment' => $multiData['adjustment'],
                'stock_date'    => now(),
            ]);
        }
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
        // if ($stocks->isEmpty()) {
        //     return response()->json(['error' => 'Stock not found for this product'], 404);
        // }
    
        // Get product info from the first stock record
         if (!empty($stocks) && $stocks->isNotEmpty() && $stocks->first()->product) {
    $product = $stocks->first()->product;
    
        // Map each stock record
        $stockDetails = $stocks->map(function ($stock) {
            return [
                'stock_id' => $stock->id,
                'location_d' => $stock->location_id,
                'location' => optional($stock->location)->name?? null, // Safely get location name
                'vendor_id' => $stock->vendor_id,
                'vendor_name' => optional($stock->vendor)->vendor_name ?? null,
                'category' => optional($stock->category)->name?? null,
                'current_stock' => $stock->current_stock?? null,
                'new_stock' => $stock->new_stock?? null,
                'quantity' => $stock->quantity?? null,
                'unit_of_measure' => $stock->unit_of_measure?? null,
                'per_unit_cost'=> $stock->per_unit_cost?? null,
                'total_cost'=> $stock->total_cost?? null,
                'adjustment' => $stock->adjustment?? null,
                'stock_date' => $stock->stock_date?? null,
                
                // 'reason_for_update' => $stock->reason_for_update,
            ];
        });
    }else{
    $stockDetails = [];
    }
    
        $productsss= array(['id' => $product_detail->id,
        'thumbnail'=>$product_detail->thumbnail,
        'product_name' => $product_detail->product_name?? null,
        'sku' => $product_detail->sku,
        'generated_barcode'=>$product_detail->generated_barcode,
        'generated_qrcode'=>$product_detail->generated_qrcode,
        'barcode_number' => $product_detail->barcode_number?? null,
        'category_id'=>$product_detail->category_id,
        'category_name' => $product_detail->category->name ?? null,
        'sub_category_id'=>$product_detail->sub_category_id?? null,
        'subcategory_name' => $product_detail->sub_category->name ?? null,
        'manufacturer'=>$product_detail->manufacturer?? null,
        'vendor_name'=>$product_detail->vendor->vendor_name?? null,
        'vendor_id'=>$product_detail->vendor_id?? null,
        'model'=>$product_detail->model?? null,
        'unit_of_measurement_category'=>$product_detail->unit_of_measurement_category?? null,
        'description'=>$product_detail->description?? null,
        'returnable'=>$product_detail->returnable?? null,
        'commit_stock_check' => $product_detail->commit_stock_check?? null,
        'inventory_alert_threshold' => $product_detail->inventory_alert_threshold?? null,
        'location_id'=>$product_detail->location_id?? null,
        'quantity'=>$product_detail->quantity?? null,
        'unit_of_measure'=>$product_detail->unit_of_measure?? null,
        'per_unit_cost'=>$product_detail->per_unit_cost?? null,
        'total_cost' => $product_detail->total_cost,
        'opening_stock' => $product_detail->opening_stock?? null,
        'status' => $product_detail->status?? null,
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

        //  $uomCategory = UomCategory::where('id', $product_detail->unit_of_measurement_category)->first();
        

        if (!empty($stocks) && $stocks->isNotEmpty() && $stocks->first()->product) {
        $product = $stocks->first()->product;
          
        $categoryValue = $product_detail->unit_of_measurement_category;
        $uomCategory = is_numeric($categoryValue)
    ? UomCategory::where('id', $categoryValue)->first()
    : UomCategory::where('name', $categoryValue)->first();

         
$defaultunit = optional(
    UomUnit::where('uom_category_id', optional($uomCategory)->id)->first()
)->unit_name;

$unitofmeasur = (!empty($product_detail->unit_of_measure) && $product_detail->unit_of_measure != '[]')
    ? $product_detail->unit_of_measure
    : $defaultunit;

    
        $stockDetails = $stocks->map(function ($stock) use ($defaultunit){

            
            return [
                'stock_id' => $stock->id,
                'location_d' => $stock->location_id,
                'location' => optional($stock->location)->name, // Safely get location name
                'vendor_id' => $stock->vendor_id,
                'vendor_name' => optional($stock->vendor)->vendor_name,
                'category' => optional($stock->category)->name,
                'current_stock' => $stock->current_stock,
                'new_stock' => $stock->new_stock,
                'quantity' => $stock->quantity,
                'unit_of_measure' => $stock->unit_of_measure ?? null,
                'reference_unit'=>$defaultunit,
                'per_unit_cost'=> $stock->per_unit_cost,
                'total_cost'=> $stock->total_cost,
                'adjustment' => $stock->adjustment,
                'stock_date' => $stock->stock_date,
            ];
        });
    
        }else{
            $stockDetails =[];
        }

    //   $uomCategory = UomCategory::where('name', $product_detail->unit_of_measurement_category)->first();
     $categoryValue = $product_detail->unit_of_measurement_category;
        $uomCategory = is_numeric($categoryValue)
    ? UomCategory::where('id', $categoryValue)->first()
    : UomCategory::where('name', $categoryValue)->first();

         
$defaultunit = optional(
    UomUnit::where('uom_category_id', optional($uomCategory)->id)->first()
)->unit_name;

$unitofmeasur = (!empty($product_detail->unit_of_measure) && $product_detail->unit_of_measure != '[]')
    ? $product_detail->unit_of_measure
    : $defaultunit;

// print_r($unitofmeasur); die;

        $productsss= array(['id' => $product_detail->id,
        'thumbnail'=>$product_detail->thumbnail,
        'product_name' => $product_detail->product_name,
        'sku' => $product_detail->sku,
        'generated_barcode'=>$product_detail->generated_barcode,
        'generated_qrcode'=>$product_detail->generated_qrcode,
        'barcode_number' => $product_detail->barcode_number,
        'category_id'=>$product_detail->category_id?? null,
        'category_name' => $product_detail->category->name ?? null,
        'sub_category_id'=>$product_detail->sub_category_id,
        'subcategory_name' => $product_detail->sub_category->name ?? null,
        'manufacturer'=>$product_detail->manufacturer?? null,
        'vendor_name'=>$product_detail->vendor->vendor_name ?? null,
        'vendor_id'=>$product_detail->vendor_id?? null,
        'model'=>$product_detail->model?? null,
        'unit_of_measurement_category'=>$product_detail->unit_of_measurement_category?? null,
        'description'=>$product_detail->description?? null,
        'returnable'=>$product_detail->returnable?? null,
        'commit_stock_check' => $product_detail->commit_stock_check?? null,
        'inventory_alert_threshold' => $product_detail->inventory_alert_threshold?? null,
        'location_id'=>$product_detail->location_id?? null,
        'quantity'=>$product_detail->quantity?? null,
        'unit_of_measure'=>$product_detail->unit_of_measure ?? null,
        'reference_unit'=>$unitofmeasur,
        'per_unit_cost'=>$product_detail->per_unit_cost?? null,
        'total_cost' => $product_detail->total_cost?? null,
        'opening_stock' => $product_detail->opening_stock?? null,
        'status' => $product_detail->status?? null,
        'created_at' => $product_detail->created_at,
        'updated_at' => $product_detail->updated_at,
    ]);
        return response()->json([
           
                'product_data'=>$productsss,

            'stock_details' => $stockDetails
        ], 200);
    }


    
        
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
            'user:id,name','machine:id,name','department:id,name','workStation:id,name','location:id,name'
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
                'location' => $record->location->name ?? null,
                'product_name' => $record->product->product_name ?? null,
                'in_quantity' => $record->in_quantity,
                'out_quantity' => $record->out_quantity,
                'previous_stock' => $record->previous_stock,
                'total_current_stock' => $record->total_current_stock,
                'inventory_alert_threshold' => $record->threshold ?? null,
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
        $product = Product::with('stocksData')->find($id);
        // $product = Product::with(['stocksData:*'])->find($id);
    
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $validatedData = $request->validate([
            'thumbnail' => 'nullable',
            'product_name' => 'string|max:255',
            'sku' => 'string|max:255|unique:products,sku,' . $id,
            'category_id' => 'string',
            'sub_category_id' => 'nullable|string',
            'manufacturer' => 'nullable|string',
            'vendor_id' => 'nullable|string',
            'model' => 'nullable|string',
            'unit_of_measurement_category' => 'string',
        
            'description' => 'nullable|string',
            'returnable' => 'boolean',
            'commit_stock_check' => 'boolean',
            'inventory_alert_threshold' => 'integer|min:0',
            'opening_stock' => 'integer',
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);


        $barcodeNumber = $request->sku; // Unique barcode
    if ($barcodeNumber) {
        $barcodeImage = (new DNS1D)->getBarcodePNG($barcodeNumber, 'C39');

        
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
            collect($request->storage_location)->pluck('location_id')->all()
        );
        $validatedData['quantity'] = json_encode(
            collect($request->storage_location)->pluck('quantity')->all()
        );
        $validatedData['unit_of_measure'] = json_encode(
            collect($request->storage_location)->pluck('unit_of_measure')->all()
        );
        $validatedData['per_unit_cost'] = json_encode(
            collect($request->storage_location)->pluck('per_unit_cost')->all()
        );
        $validatedData['total_cost'] = json_encode(
            collect($request->storage_location)->pluck('total_cost')->all()
        );

        $product->update($validatedData);

        $multiLocation = $request->storage_location;
        $product_id=$id;



        // foreach ($multiLocation as $multiData) {
            
        //     $product_location = Stock::where('product_id', $product_id)
        //         ->where('location_id', $multiData['location_id'])
        //         ->first();
        //     $quantity = $multiData['quantity'];
            
        //     if ($product_location) {
                
        //         $currentStock = $multiData['quantity'];
        //         $stockData = [
        //             'current_stock' => $currentStock,
        //             'unit_of_measure' => $multiData['unit_of_measure'] ?? $product_location->unit_of_measure,
        //             'per_unit_cost'=> $multiData['per_unit_cost'],
        //             'total_cost'=> $multiData['total_cost'],
        //             'quantity' => $quantity,
        //             'stock_date' => $validatedRequest['stock_date'] ?? null,
        //             'reason_for_update' => $validatedRequest['reason_for_update'] ?? null,
        //             'vendor_id'     => $request->vendor_id,
        //             'category_id'   => $request->category_id,
        //             'location_id'   => $multiData['location_id'],
        //         ];

        //         $product_location->update($stockData);
        //     } else {
               
        //         $currentStock = $multiData['quantity'] ?? 0;


        //         $stockData = [
        //             'product_id'    => $product->id,
        //             'vendor_id'     => $request->vendor_id,
        //             'category_id'   => $request->category_id,
        //             'location_id'   => $multiData['location_id'],
        //             'quantity' => $quantity,
        //             'current_stock' => $currentStock,
        //            'unit_of_measure'=> $multiData['unit_of_measure'],
        //             'per_unit_cost'          => $multiData['per_unit_cost'],
        //             'total_cost'          => $multiData['total_cost'],
        //             'stock_date' => $validatedRequest['stock_date'] ?? null,
                    
        //         ];

        //         Stock::create($stockData);

        //     }

        // }


            // $product_locationDel = Stock::where('product_id', $product_id)->get();
            // $existingStocks = $product_locationDel->keyBy('location_id');
            // $newLocationIds = [];

            // foreach ($multiLocation as $multiData) {
            //     $locationId = $multiData['location_id'];
            //     $newLocationIds[] = $locationId;

            //     $quantity = $multiData['quantity'] ?? 0;

            //     $stockData = [
            //         'vendor_id' => $request->vendor_id,
            //         'category_id' => $request->category_id,
            //         'location_id' => $locationId,
            //         'quantity' => $quantity,
            //         'current_stock' => $quantity,
            //         'unit_of_measure' => $multiData['unit_of_measure'],
            //         'per_unit_cost' => $multiData['per_unit_cost'],
            //         'total_cost' => $multiData['total_cost'],
            //         'stock_date' => $validatedRequest['stock_date'] ?? null,
            //         'reason_for_update' => $validatedRequest['reason_for_update'] ?? null,
            //     ];

            //     if (isset($existingStocks[$locationId])) {
                    
            //         $existingStocks[$locationId]->update($stockData);
            //     } else {
                    
            //         $stockData['product_id'] = $product_id;
            //         Stock::create($stockData);
            //     }
            // }

            
            // $toDelete = $product_locationDel->whereNotIn('location_id', $newLocationIds);
            // foreach ($toDelete as $oldStock) {
            //     $oldStock->delete();
            // }

            // STEP 1: Set multiLocation from request
$multiLocation = is_array($request->storage_location) ? $request->storage_location : [];

// STEP 2: Existing stocks
$product_locationDel = Stock::where('product_id', $product_id)->get();
$existingStocks = $product_locationDel->keyBy('location_id');

// STEP 3: Loop through locations
$newLocationIds = [];

foreach ($multiLocation as $multiData) {
    $locationId = $multiData['location_id'];
    $newLocationIds[] = $locationId;

    $quantity = $multiData['quantity'] ?? 0;

    $stockData = [
        'vendor_id' => $request->vendor_id,
        'category_id' => $request->category_id,
        'location_id' => $locationId,
        'quantity' => $quantity,
        'current_stock' => $quantity,
        'unit_of_measure' => $multiData['unit_of_measure'],
        'per_unit_cost' => $multiData['per_unit_cost'],
        'total_cost' => $multiData['total_cost'],
        'stock_date' => $validatedRequest['stock_date'] ?? null,
        'reason_for_update' => $validatedRequest['reason_for_update'] ?? null,
    ];

    if (isset($existingStocks[$locationId])) {
        $existingStocks[$locationId]->update($stockData);
    } else {
        $stockData['product_id'] = $product_id;
        Stock::create($stockData);
    }
}

// STEP 4: Delete removed locations
$toDelete = $product_locationDel->whereNotIn('location_id', $newLocationIds);
foreach ($toDelete as $oldStock) {
    $oldStock->delete();
}


            $productUpdate = Product::with('stocksData')->find($id);
       
    return response()->json(['message' => 'Product updated successfully', 'product' => $productUpdate], 200);

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
            'comment' => 'nullable|string',
            'opening_stock' => 'required|string',
            'storage_location' => 'required|array',
            'storage_location.*.current_stock' => 'nullable|numeric',
            'storage_location.*.quantity' => 'required|numeric',
            'storage_location.*.unit_of_measure' => 'nullable|string',
            'storage_location.*.per_unit_cost' => 'nullable|string',
            'storage_location.*.total_cost' => 'nullable|string',
            'storage_location.*.location_id' => 'required|string',
            'storage_location.*.adjustment' => 'required|string|in:add,subtract,select',
        ]);

        $existingLocations = json_decode($product->location_id, true) ?? [];

        // New incoming location data
        $reqproduct_location = [];
        $reqproduct_unit_of_measure = [];
        $reqproduct_per_unit_cost = [];
        $reqproduct_total_cost = [];
        // $newLocations = $validatedRequest['storage_location']['location_id']; // Should be array: [location_id => quantity]

        // Merge or update existing with new
 $multiLocationss = $validatedRequest['storage_location'];
         foreach ($multiLocationss as $multiData) {

                
                  $reqproduct_location[] = $multiData['location_id'];
                  $reqproduct_unit_of_measure[] = $multiData['unit_of_measure'];
                  $reqproduct_per_unit_cost[] = $multiData['per_unit_cost'];
                  $reqproduct_total_cost[] = $multiData['total_cost'];

         }
        // $updatedLocations = array_merge($existingLocations, $reqproduct_location);

        // // Save updated JSON
        $product->location_id = json_encode($reqproduct_location);
        $product->unit_of_measure = json_encode($reqproduct_unit_of_measure);
        $product->per_unit_cost = json_encode($reqproduct_per_unit_cost);
        $product->total_cost = json_encode($reqproduct_total_cost);
        // print_r($product->location_id);die;
        $product->save();


        $multiLocation = $validatedRequest['storage_location'];

        foreach ($multiLocation as $multiData) {
            $product_location = Stock::where('product_id', $product_id)
                ->where('location_id', $multiData['location_id'])
                ->first();

                $currentStock = $product_location->current_stock ?? '0';
               
               $requestAllLocation[] = $multiData['location_id'];


            $quantity = $multiData['quantity'];
            $adjustment = $multiData['adjustment'];

            if ($product_location) {
                // Update existing stock record
                $currentStock = $product_location->current_stock;
                // $currentStock = $multiData['current_stock'];

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
                    'unit_of_measure' => $multiData['unit_of_measure'] ?? $product_location->unit_of_measure,
                    'per_unit_cost'          => $multiData['per_unit_cost'],
                    'total_cost'          => $multiData['total_cost'],
                    'quantity' => $quantity,
                    'adjustment' => $adjustment,
                    'stock_date' => $validatedRequest['stock_date'] ?? null,
                    'vendor_id' => $validatedRequest['vendor_id'] ?? null,
                    'reason_for_update' => $validatedRequest['reason_for_update'] ?? null,
                    'comment' => $validatedRequest['comment'] ?? null,
                ];

                $product_location->update($stockData);


                 $stockData = [
                        'product_id' => $product->id,
                        'category_id' => $product->category_id,
                        'current_stock' => $currentStock,
                        'new_stock' => $newStock,
                        'unit_of_measure' => $multiData['unit_of_measure'] ?? null,
                        'location_id' => $multiData['location_id'],
                        'quantity' => $quantity,
                        'adjustment' => $adjustment,
                        'stock_date' => $validatedRequest['stock_date'] ?? null,
                        'vendor_id' => $validatedRequest['vendor_id'] ?? null,
                        'reason_for_update' => $validatedRequest['reason_for_update'] ?? null,
                    ];
    
                    InventoryAdjustmentReports::create($stockData);


                
        $quantity = $quantity;
             if ($adjustment === 'add') {
                 $productOpeningStock = $product->opening_stock + $quantity;
             }else{
                $productOpeningStock = $product->opening_stock - $quantity;
             }
        $locationIds = json_decode($product->location_id); 
        $quantities = json_decode($product->quantity); 
        // $pdate = array_combine($locationIds, $quantities);
        // $rlocationId = $multiData['location_id']?? '0';
        //  if ($adjustment === 'add') {
        //          $pdate[$rlocationId] = $pdate[$rlocationId] + $quantity;
        //      }else{
        //         $pdate[$rlocationId] = $pdate[$rlocationId] - $quantity;
        //      }

        // Combine arrays only if they are both arrays and same length
            if (is_array($locationIds) && is_array($quantities) && count($locationIds) === count($quantities)) {
                $pdate = array_combine($locationIds, $quantities);
            } else {
                $pdate = [];
            }

            $rlocationId = $multiData['location_id'] ?? '0';

            // Safely add or subtract quantity
            if ($adjustment === 'add') {
                $pdate[$rlocationId] = ($pdate[$rlocationId] ?? 0) + $quantity;
            } else {
                $pdate[$rlocationId] = ($pdate[$rlocationId] ?? 0) - $quantity;
                // $pdate[$rlocationId] = '';
            }


        $updatedQuantities = [];
       foreach ($locationIds as $id) {
    if (isset($pdate[$id])) {
        $updatedQuantities[] = $pdate[$id];
    } else {
        $updatedQuantities[] = 0; // or handle error/skip
    }
}

        $totalQuantity = array_sum($updatedQuantities);
        // Step 6: Update the product

        // $locationIds = json_decode($product->location_id); 

        $product->update([
            'opening_stock' => $productOpeningStock,
            'quantity' => json_encode($updatedQuantities)
        ]);

                
            } else {
                // Create new stock record
                // $currentStock = $multiData['current_stock'] ?? 0;
                 $currentStock = $product_location->current_stock ?? '0';
                
                if ($adjustment === 'add') {
                    $newStock = $currentStock + $quantity;
                    $productOpeningStock = $product->opening_stock + $quantity;
                } else {
                    $newStock = $currentStock - $quantity;
                    $productOpeningStock = $product->opening_stock - $quantity;
                }

                $stockData = [
                    'product_id' => $product->id,
                    'category_id' => $product->category_id,
                    'current_stock' => $newStock,
                    'new_stock' => $newStock,
                    'unit_of_measure' => $multiData['unit_of_measure'] ?? null,
                    'per_unit_cost'          => $multiData['per_unit_cost'],
                    'total_cost'          => $multiData['total_cost'],
                    'location_id' => $multiData['location_id'],
                    'quantity' => $quantity,
                    'adjustment' => $adjustment,
                    'stock_date' => $validatedRequest['stock_date'] ?? null,
                    'vendor_id' => $validatedRequest['vendor_id'] ?? null,
                    'reason_for_update' => $validatedRequest['reason_for_update'] ?? null,
                    'comment' => $validatedRequest['comment'] ?? null,
                ];

                Stock::create($stockData);
           

                 $stockData = [
                        'product_id' => $product->id,
                        'category_id' => $product->category_id,
                        'current_stock' => $newStock,
                        'new_stock' => $newStock,
                        'unit_of_measure' => $multiData['unit_of_measure'] ?? null,
                        'location_id' => $multiData['location_id'],
                        'quantity' => $quantity,
                        'adjustment' => $adjustment,
                        'stock_date' => $validatedRequest['stock_date'] ?? null,
                        'vendor_id' => $validatedRequest['vendor_id'] ?? null,
                        'reason_for_update' => $validatedRequest['reason_for_update'] ?? null,
                    ];
    
                    InventoryAdjustmentReports::create($stockData);


            

        $quantity = $quantity;
             if ($adjustment === 'add') {
                 $productOpeningStock = $product->opening_stock + $quantity;
             }else{
                $productOpeningStock = $product->opening_stock - $quantity;
             }
        $locationIds = json_decode($product->location_id); 
        $quantities = json_decode($product->quantity); 
        // $pdate = array_combine($locationIds, $quantities);
        // $rlocationId = $multiData['location_id'];
        //  if ($adjustment === 'add') {
        //          $pdate[$rlocationId] = $pdate[$rlocationId ?? '0'] + $quantity;
        //      }else{
        //         $pdate[$rlocationId] = $pdate[$rlocationId ?? '0'] - $quantity;
        //      }
        if (is_array($locationIds) && is_array($quantities) && count($locationIds) === count($quantities)) {
                $pdate = array_combine($locationIds, $quantities);
            } else {
                $pdate = [];
            }

            $rlocationId = $multiData['location_id'] ?? '0';

            // Safely add or subtract quantity
            if ($adjustment === 'add') {
                $pdate[$rlocationId] = ($pdate[$rlocationId] ?? 0) + $quantity;
            } else {
                $pdate[$rlocationId] = ($pdate[$rlocationId] ?? 0) - $quantity;
            }
        
        $updatedQuantities = [];
        foreach ($locationIds as $lid) {
            $updatedQuantities[] = $pdate[$lid];
        }

        $totalQuantity = array_sum($updatedQuantities);
        // Step 6: Update the product

        $product->update([
            'opening_stock' => $productOpeningStock,
            'quantity' => json_encode($updatedQuantities)
           
        ]);

            // $product->update(['opening_stock' => $productOpeningStock]);
            // $product->update(['opening_stock' => $validatedRequest['opening_stock']]);
        }
 }
            foreach ($multiLocation as $multiData) {

                
                  $product_location = Stock::where('product_id', $product_id)
                ->where('location_id', $multiData['location_id'])
                ->first();
                // print_r($product_location);die;
               if($adjustment!=='select'){
                $quantity = $multiData['quantity'];
                $adjustment = $multiData['adjustment'];

                $currentStock = $product_location->current_stock;
                
                    if ($adjustment == 'add') {
                        $newStock = $currentStock + $quantity;
                        $productOpeningStock = $product->opening_stock + $quantity;
                    } else if($adjustment == 'subtract') {
                        $newStock = $currentStock - $quantity;
                        $productOpeningStock = $product->opening_stock - $quantity;
                    }
    
                    // $stockData = [
                    //     'product_id' => $product->id,
                    //     'category_id' => $product->category_id,
                    //     'current_stock' => $currentStock,
                    //     'new_stock' => $newStock,
                    //     'unit_of_measure' => $multiData['unit_of_measure'] ?? null,
                    //     'location_id' => $multiData['location_id'],
                    //     'quantity' => $quantity,
                    //     'adjustment' => $adjustment,
                    //     'stock_date' => $validatedRequest['stock_date'] ?? null,
                    //     'vendor_id' => $validatedRequest['vendor_id'] ?? null,
                    //     'reason_for_update' => $validatedRequest['reason_for_update'] ?? null,
                    // ];
    
                    // InventoryAdjustmentReports::create($stockData);
                    
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

    


    // public function inventoryAlert()
    // {
        
    //     // $products = Product::with('category:id,name','vendor:id,vendor_name',
    //     // 'sub_category:id,name')->select('id', 'product_name', 'sku', 'opening_stock', 'location_id', 'inventory_alert_threshold', DB::raw("'Warning' as status"))
    //     //     ->where('opening_stock', '<', DB::raw('inventory_alert_threshold'))
    //     //     ->get();

    //     $products = Product::with('category:id,name', 'vendor:id,vendor_name', 'sub_category:id,name')
    // ->select(
    //     'id',
    //     'product_name',
    //     'sku',
    //     'opening_stock',
    //     'location_id',
    //     'inventory_alert_threshold',
    //     DB::raw("'Warning' as status")
    // )
    // ->whereColumn('opening_stock', '<', 'inventory_alert_threshold')
    // ->get();
    
    //     $inventory_alert = $products->map(function ($product) {
    //         // Decode location IDs (JSON string to array)
    //         $locationIds = json_decode($product->location_id, true);
    
    //         // Get location names from DB
    //         $locationNames = \App\Models\Location::whereIn('id', $locationIds)->pluck('name')->toArray();
    
    //         return [
    //             'id' => $product->id,
    //             'product_id'=>$product->id,
    //             'product_name' => $product->product_name,
    //             'sku' => $product->sku,
    //             'opening_stock' => $product->opening_stock,
    //             'inventory_alert_threshold' => $product->inventory_alert_threshold,
    //             'location_id' => $locationIds,
    //             'location_name' => $locationNames,
    //             'category_name' => $product->category->name, // array of location names
    //             'status' => 'Warning',
    //         ];
    //     });
    
    //     return response()->json(['inventory_alert' => $inventory_alert], 200);

    // }
  
    // public function inventoryAlert()
    // {
    //     // with('category:id,name', 'vendor:id,vendor_name', 'sub_category:id,name');

    //     $products = Product::with('category:id,name', 'vendor:id,vendor_name', 'sub_category:id,name')->select(
    //             'id',
    //             'product_name',
    //             'sku',
    //             'opening_stock',
    //             'model',
    //             'manufacturer',
    //             'location_id',
    //             'category_id',
    //             'inventory_alert_threshold',
    //             'updated_at',
    //             DB::raw("'Warning' as status")
    //         )
    //         ->whereColumn('opening_stock', '<', 'inventory_alert_threshold')
    //         ->get();
    //         // print_r($products);die;
    //     $inventory_alert = $products->map(function ($product) {
    //         // Decode location IDs from JSON
    //         $locationIds = json_decode($product->location_id, true);

    //         // Fetch location names
    //         $locationNames = \App\Models\Location::whereIn('id', $locationIds)->pluck('name')->toArray();
            
    //         return [
    //             'id' => $product->id,
    //             'product_id' => $product->id,
    //             'date_time' => $product->updated_at->format('Y-m-d H:i:s'),
    //             'product_name' => $product->product_name,
    //             'sku' => $product->sku,
    //             'model' => $product->model,
    //             'manufacturer' => $product->manufacturer,
    //             'category_name' => optional($product->category)->name,
    //             'opening_stock' => $product->opening_stock,
    //             'inventory_alert_threshold' => $product->inventory_alert_threshold,
    //             // 'location_id' => $locationIds,
    //             // 'location_name' => $locationNames,
    //             'category_id' => $product->category_id,
    //             'status' => 'Warning',
    //         ];
    //     });

    //     return response()->json(['inventory_alert' => $inventory_alert], 200);
    // }
        public function inventoryAlert(Request $request)
        {

            $querycount = Product::with('category:id,name', 'vendor:id,vendor_name', 'sub_category:id,name')
                ->select(
                    'id',
                    'product_name',
                    'sku',
                    'opening_stock',
                    'model',
                    'manufacturer',
                    'location_id',
                    'category_id',
                    'inventory_alert_threshold',
                    'updated_at',
                    DB::raw("'Warning' as status")
                )
                ->whereColumn('opening_stock', '<', 'inventory_alert_threshold')->count();

            
            // ✅ Base query
            $query = Product::with('category:id,name', 'vendor:id,vendor_name', 'sub_category:id,name')
                ->select(
                    'id',
                    'product_name',
                    'sku',
                    'opening_stock',
                    'model',
                    'manufacturer',
                    'location_id',
                    'category_id',
                    'inventory_alert_threshold',
                    'updated_at',
                    DB::raw("'Warning' as status")
                )
                ->whereColumn('opening_stock', '<', 'inventory_alert_threshold');

            // ✅ Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('manufacturer', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($c) use ($search) {
                        $c->where('name', 'like', "%{$search}%");
                    });
                });
            }
             // ✅ Filter
if ($request->filled('category') || $request->filled('start_date') || $request->filled('end_date')) {
    $category    = $request->category;
    $type_filter = $request->type_filter;
    $start_date  = $request->start_date;
    $end_date    = $request->end_date;

    $query->where(function ($q) use ($category, $start_date, $end_date) {
        
        // ✅ Category filter
        if (!empty($category)) {
            $q->whereHas('category', function ($catQuery) use ($category) {
                $catQuery->where('name', 'like', "%{$category}%");
            });
        }

                // ✅ Date range filter
                if (!empty($start_date) && !empty($end_date)) {
                    $q->whereBetween('created_at', [$start_date, $end_date]);
                } elseif (!empty($start_date)) {
                    $q->whereDate('created_at', '>=', $start_date);
                } elseif (!empty($end_date)) {
                    $q->whereDate('created_at', '<=', $end_date);
                }
            });
        }

            // ✅ Sorting (default latest updated products)
            $sortBy = $request->get('sort_by', 'updated_at');
            $sortOrder = $request->get('sort_order', 'desc');

            $allowedSorts = ['id', 'product_name', 'sku', 'model', 'manufacturer', 'opening_stock', 'inventory_alert_threshold', 'updated_at'];
            if (!in_array($sortBy, $allowedSorts)) {
                $sortBy = 'updated_at';
            }

            $query->orderBy($sortBy, $sortOrder);

            // ✅ Pagination
            $perPage = $request->get('per_page', 10);
            $products = $query->paginate($perPage);

            // ✅ Map data
            $inventory_alert = $products->getCollection()->map(function ($product) {
                $locationIds = json_decode($product->location_id, true) ?? [];
                $locationNames = \App\Models\Location::whereIn('id', $locationIds)->pluck('name')->toArray();

                return [
                    'id' => $product->id,
                    'product_id' => $product->id,
                    'date_time' => $product->updated_at->format('Y-m-d H:i:s'),
                    'product_name' => $product->product_name,
                    'sku' => $product->sku,
                    'model' => $product->model,
                    'manufacturer' => $product->manufacturer,
                    'category_name' => optional($product->category)->name,
                    'opening_stock' => $product->opening_stock,
                    'inventory_alert_threshold' => $product->inventory_alert_threshold,
                    'location_name' => $locationNames,
                    'category_id' => $product->category_id,
                    'status' => 'Warning',
                ];
            });

            // ✅ Replace collection with mapped data
            $products->setCollection($inventory_alert);

            return response()->json([
                'total_count' => $querycount,
                'inventory_alert' => $products
            ], 200);
        }


    // public function inventoryAdjustmentsReport()
    // {


    //     $stocks = InventoryAdjustmentReports::with([
    //         'product.category', // Load category via product
    //         'category:id,name','vendor:id,vendor_name','location:id,name'
    //     ])->where('new_stock', '>', 0)->where('quantity', '>', 0)->orderBy('id', 'desc')->get();


    //     $adjustments = $stocks->map(function ($stock) {
    //         $adjustmentSymbol = $stock->adjustment == 'subtract' ? '-' : '+';
    //         $newStock = $stock->adjustment == 'subtract'
    //             ? $stock->current_stock - $stock->quantity
    //             : $stock->current_stock + $stock->quantity;

    //             // print_r($stock->product->category);die;
    //         return [
    //             'id' => $stock->id,
    //             'in_out_date_time' => $stock->stock_date,
    //             'product_id' => $stock->product_id,
    //             'product_name' => $stock->product->product_name ?? 'N/A',
    //             'sku' => $stock->product->sku ?? 'N/A',
    //             'category_name' => $stock->product->category->name ?? 'N/A',  // Ensure category is not null
    //             'vendor_name' => $stock->vendor->vendor_name ?? 'N/A', // Ensure vendor is not null
    //             'previous_stock' => $stock->current_stock,
    //             'new_stock' => $newStock,
    //             'adjustment' => "{$adjustmentSymbol} {$stock->quantity}",
    //             'reason' => $stock->reason_for_update ?? 'N/A',
    //             'location' => optional($stock->location)->name, 
    //             'stock_date' => $stock->stock_date,
    //             'created_at' => $stock->created_at,
    //             'updated_at' => $stock->updated_at,
    //         ];
    //     });

    //     return response()->json(['inventory_adjustments' => $adjustments], 200);
    // }

  public function inventoryAdjustmentsReport(Request $request)
{
    // ✅ Base query with relationships

     $querycount = InventoryAdjustmentReports::with([
        'product.category',
        'category:id,name',
        'vendor:id,vendor_name',
        'location:id,name'
    ])->where('new_stock', '>', 0)
      ->where('quantity', '>', 0)->count();

    $query = InventoryAdjustmentReports::with([
        'product.category',
        'category:id,name',
        'vendor:id,vendor_name',
        'location:id,name'
    ])->where('new_stock', '>', 0)
      ->where('quantity', '>', 0);

    // ✅ Search functionality
    if ($request->has('search') && !empty($request->search)) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->whereHas('product', function ($p) use ($search) {
                $p->where('product_name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            })
            ->orWhereHas('vendor', function ($v) use ($search) {
                $v->where('vendor_name', 'like', "%{$search}%");
            })
            ->orWhereHas('category', function ($c) use ($search) {
                $c->where('name', 'like', "%{$search}%");
            })
            ->orWhere('reason_for_update', 'like', "%{$search}%")
            ->orWhere('stock_date', 'like', "%{$search}%");
        });
    }

    // ✅ Filter
if ($request->filled('category') || $request->filled('start_date') || $request->filled('end_date')) {
    $category    = $request->category;
    $reason = $request->reason;
    $start_date  = $request->start_date;
    $end_date    = $request->end_date;

    $query->where(function ($q) use ($category,$reason, $start_date, $end_date) {
        
        // ✅ Category filter
        if (!empty($category)) {
            $q->whereHas('category', function ($catQuery) use ($category) {
                $catQuery->where('name', 'like', "%{$category}%");
            });
        }

        if(!empty($reason)){
            $q->where('reason_for_update', 'like', "%{$reason}%");
            
        }
        
        // ✅ Date range filter
        if (!empty($start_date) && !empty($end_date)) {
            $q->whereBetween('created_at', [$start_date, $end_date]);
        } elseif (!empty($start_date)) {
            $q->whereDate('created_at', '>=', $start_date);
        } elseif (!empty($end_date)) {
            $q->whereDate('created_at', '<=', $end_date);
        }
    });
}


    // ✅ Sorting
    $sortBy = $request->get('sort_by', 'id'); // default id
    $sortOrder = $request->get('sort_order', 'desc'); // default desc

    // Prevent sorting by unknown columns directly to avoid SQL injection
    $allowedSorts = ['id', 'stock_date', 'created_at', 'updated_at'];
    if (!in_array($sortBy, $allowedSorts)) {
        $sortBy = 'id';
    }

    $query->orderBy($sortBy, $sortOrder);

    // ✅ Pagination
    $perPage = $request->get('per_page', 10);
    $stocks = $query->paginate($perPage);

    // ✅ Map data
    $adjustments = $stocks->getCollection()->map(function ($stock) {
        $adjustmentSymbol = $stock->adjustment == 'subtract' ? '-' : '+';
        $newStock = $stock->adjustment == 'subtract'
            ? $stock->current_stock - $stock->quantity
            : $stock->current_stock + $stock->quantity;

        return [
            'id' => $stock->id,
            'in_out_date_time' => $stock->stock_date,
            'product_id' => $stock->product_id,
            'product_name' => $stock->product->product_name ?? 'N/A',
            'sku' => $stock->product->sku ?? 'N/A',
            'category_name' => $stock->product->category->name ?? 'N/A',
            'vendor_name' => $stock->vendor->vendor_name ?? 'N/A',
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

    // Replace original collection with mapped one
    $stocks->setCollection($adjustments);

    return response()->json([
        'total_count' => $querycount,
        'inventory_adjustments' => $stocks
    ], 200);
}



    public function recentStockUpdate()
    {
        // Fetch all stock entries with necessary relations
        $stocks = Stock::with([
            'product:id,product_name,sku,opening_stock',
            'category:id,name',
            'vendor:id,vendor_name',
            'location:id,name'
        ])->orderBy('stock_date','desc')->get();
    
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
                'stock_date' => $stock->stock_date,
                'product_name' => $stock->product->product_name,
                'sku' => $stock->product->sku,
                'quantity' => $stock->quantity,
                'adjustment' => $stock->adjustment,
                'reason_for_update' => $stock->reason_for_update,
               
                // 'location_d' => $stock->location_id,
                // 'location' => optional($stock->location)->name, // Safely get location name
                'current_stock' => $stock->current_stock,
                'new_stock' => $stock->new_stock,
                // 'unit' => $stock->unit,
                // 'unit_cost'=> $stock->unit_cost,
                // 'total_cost'=> $stock->total_cost,
                // 'vendor_id' => $stock->vendor_id,
                // 'vendor_name' => optional($stock->vendor)->vendor_name,
                // 'category' => optional($stock->category)->name,
                
            ];
        });
    
        return response()->json([
            // 'product_id' => $product->id,
            // 'product_name' => $product->product_name,
            // 'opening_stock' => $product->opening_stock,
            'recent_stock_update' => $stockDetails
        ], 200);
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

// public function uploadCSV(Request $request)
// {
//     $request->validate([
//         'file' => 'required|mimes:csv,txt|max:2048'
//     ]);

//     $file = $request->file('file');
//     // print_r($file);die;
//     $handle = fopen($file->getPathname(), "r");

//     $header = fgetcsv($handle);
//     // $expectedHeaders = [
//     //     "product_name", "sku", "category_id", "sub_category_id", "manufacturer",
//     //     "vendor_id", "model", "description", "location_id", "current_stock", "stock_unit", "unit_cost", "opening_stock_total_stock", "inventory_alert_threshold", "selling_cost",
//     //     "cost_price", "commit_stock_check", "project_name", "weight",
//     //     "weight_unit", "length", "width", "depth",
//     //     "measurement_unit", "returnable", "status"
//     // ];
//     $expectedHeaders =["Part names","SKU","Stock location","Current stock","Stock unit","Unit cost","Category","Sub category","Manufacture","Vendor","Model","Description","Threshold","Weight","Weight unit","dim_Length","dim_Width","dim_Height","dim_Measurement_Unit","Status"];

//     if ($header !== $expectedHeaders) {
//         return response()->json(['error' => 'Invalid CSV format. Please use the correct template.'], 400);
//     }

//     $products = [];
//     $invalidRows = [];
//     $rowNumber = 2;
//     // $locationNames = json_decode(); 
    
//     while ($row = fgetcsv($handle)) {
        
//         $locationString = $row[8];
//         $locationNames = explode(",", $locationString);
    
//     // print_r($locationNames);die;
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
// // print_r($locationIds);die;

//         if (count($row) !== count($expectedHeaders)) {
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
//         $category = Category::firstOrCreate(['name' => $row[2]], ['name' => $row[2]]);
//         $subcategory = Subcategory::firstOrCreate([
//             'name' => $row[3],
//             'category_id' => $category->id
//         ], [
//             'name' => $row[3],
//             'category_id' => $category->id
//         ]);
//         $vendor = Vendor::firstOrCreate(['vendor_name' =>$row[5]], ['vendor_name' => $row[5]]);
        
        
        
//         $product = Product::create([
//             'product_name' => $row[0],
//             'sku' => $row[1],
//             'generated_barcode' => $barcodeImage,
//             'generated_qrcode' => $qrCodeImage,
//             'units' => $unit->id,
//             'category_id' => $category->id,
//             'sub_category_id' => $subcategory->id,
//             'manufacturer' => $row[5],
//             'vendor_id' => $vendor->id,
//             'model' => $row[6],
//             'description' => $row[7],
//             'returnable' => strtolower($row[24]) === 'yes' ? 1 : 0,
//             'track_inventory' => $row[12],
//             'opening_stock' => (int) $row[12],
//             'selling_cost' => (float) $row[14],
//             'cost_price' => (float) $row[15],
//             'commit_stock_check' => (float) $row[16],
//             'project_name' => $row[17],
//             'location_id' => json_encode($locationIds),
//             'weight' => (float) $row[18],
//             'weight_unit' => $row[19],
//             'length' => (float) $row[20],
//             'width' => (float) $row[21],
//             'depth' => (float) $row[22],
//             'measurement_unit' => $row[23],
//             'barcode_number' => $row[1],
//             'inventory_alert_threshold' => (int) $row[13],
//             'status' => $row[25],
//             // 'thumbnail'=>$thumbnail,
//             'created_at' => now(),
//             'updated_at' => now(),
//         ]);
     
//         $totalStock = 0;
//         foreach ($locationIds as $locationId) {
//             // print_r($locationId);die;
//             $currentStock = (int)$row[9];
//             $totalStock += $currentStock;
    
//             Stock::create([
//                 'product_id'    => $product->id,
//                 'vendor_id'     => $vendor->id,
//                 'category_id'   => $category->id,
//                 'current_stock' => $currentStock,
//                 'unit'          => $unit->name,
//                 // 'unit_cost'     => $row[11],
//                 'location_id'   => $locationId,
//                 'stock_date'    => now(),
//             ]);
//         }
//         $product->opening_stock = $totalStock;
//         $product->opening_stock = $totalStock;
//         $product->save();

//         $rowNumber++;
//     }

//     fclose($handle);

//     return response()->json([
//         'message' => 'CSV uploaded successfully.',
//         'total_rows' => $invalidRows
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
    

            $expectedHeaders = array_map('trim',  [
                "product_name", "sku", "category_id", "sub_category_id","manufacturer",
                "vendor_id", "model", "unit_of_measurement_category", "description", "returnable", "commit_stock_check", "inventory_alert_threshold",
                "opening_stock", "location_id", "quantity",
                "unit_of_measure", "per_unit_cost", "total_cost", "status"
            ]);
            
            $header = array_map('trim', $header);
            
            if ($header !== $expectedHeaders) {
                return response()->json(['error' => 'Invalid CSV format. Please use the correct template.'], 400);
            }
        

            $products = [];
            $invalidRows = [];
            $rowNumber = 2;
            // $locationNames = json_decode(); 
            
            while ($row = fgetcsv($handle)) {
                
        $productName = $row[0] ?? null;
        $sku = $row[1] ?? null;

                if (empty($productName)) {
                    return response()->json([
                'message' => "Row $rowNumber: 'product_name are required."
            ], 422);
                }

                if (empty($sku)) {
                    return response()->json([
                'message' => "Row $rowNumber: sku are required."
            ], 422);
                }

            $locationString = $row[13];
            $locationNames = explode(",", $locationString);
            $joinedString = implode(',', $locationNames);
            $finalArray = json_decode($joinedString, true);
        // print_r($finalArray);die;
        
            $locationIds = [];
            
            if (is_array($finalArray)) {

                foreach ($finalArray as $name) {
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
                // print_r($locationIds);die;

                if (count($row) !== count($expectedHeaders)) {
                    $invalidRows[] = $rowNumber;
                    continue;
                }

                if (empty($row[0]) || empty($row[1])) {
                    $invalidRows[] = $rowNumber;
                    continue;
                }

            //     if (Product::where('sku', $row[1])->exists()) {
            //         // continue;
            //         if (empty($sku)) {
            //         return response()->json([
            //     'message' => "Row $rowNumber: sku are required."
            // ], 422);
            //     }
            //     }

            // if (Product::where('sku', $sku)->exists()) {
                // return response()->json([
                //     'message' => "Row $rowNumber: 'sku' already exists."
                // ], 422);
           
        $product = Product::where('sku', $row[1])->first();

if ($product) {

                    if (!preg_match('/^[A-Z0-9 \-.\$\/\+\%]+$/', $row[1])) {
                    return response()->json([
                        'message' => "Invalid SKU Format value: {$row[1]}"
                    ], 422);
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

                $uomCategory = null;
                $uomUnitsNames = null;
                if (!empty($row[7])) {

                $uomCategory = UomCategory::firstOrCreate(['name' => $row[7]], ['name' => $row[7]]);
                
                $uomUnitString = $row[15];
                $uomNames = explode(",", $uomUnitString);
                $joinedUomString = implode(',', $uomNames);
                $finalUomArray = json_decode($joinedUomString, true);
        
            $uomUnitsNames = [];
                

            if (is_array($finalUomArray)) {

                foreach ($finalUomArray as $name) {
                    $name = trim($name);
                
                    $uomUnitsName = \App\Models\UomUnit::firstOrCreate(
                         ['unit_name' => $name],
                    ['unit_name' => $name, 'uom_category_id' => $uomCategory->id,'abbreviation' => '1','reference' => '1','ratio' => '0.2','rounding'=>'00.5','active'=>'1']
                );
            
                    $uomUnitsNames[] = $uomUnitsName->unit_name;
                }
                } else {
                    // Handle invalid JSON
                    Log::error('Invalid JSON in Uom Unit column:', ['value' => $row[15]]);
                }
                }
                // print_r($locationIds);die;


                $manufacturer = Manufacturer::firstOrCreate(
                    ['name' => $row[4]],
                    ['name' => $row[4], 'description' => 'Description for box','status'=>'active']
                );

                // $category = Category::firstOrCreate(
                //     ['name' => $row[2]],
                //     ['name' => $row[2], 'description' => '']
                // );
                $category = null;

                if (!empty($row[2])) {
                    $category = Category::firstOrCreate(
                        ['name' => $row[2]],
                        ['name' => $row[2], 'description' => '']
                    );
                }

                $subcategory = null;

                if (!empty($row[3])) {
                $subcategory = Subcategory::firstOrCreate([
                    'name' => $row[3],
                    'category_id' => $category->id
                ], [
                    'name' => $row[3],
                    'category_id' => $category->id
                ]);
            }

            $vendor = null;

                if (!empty($row[5])) {

                $vendor = Vendor::firstOrCreate(['vendor_name' =>$row[5]], ['vendor_name' => $row[5]]);
                }
                $product->update([
        // 'product_name' => $row[0],
        // 'generated_barcode' => $barcodeImage,
        // 'generated_qrcode' => $qrCodeImage,
        'category_id' => $category?->id,
        'sub_category_id' => $subcategory?->id,
        'manufacturer' => $manufacturer->name,
        'vendor_id' => $vendor?->id,
        'model' => $row[6],
        'unit_of_measurement_category' => $uomCategory?->id,
        'description' => $row[8],
        'returnable' => strtolower($row[9]) === 'yes' ? 1 : 0,
        'commit_stock_check' => (float) $row[10],
        'inventory_alert_threshold' => (int) $row[11],
        'opening_stock' => (int) $row[12],
        'location_id' => json_encode($locationIds),
        'quantity' => $row[14],
        'unit_of_measure' => $uomUnitsNames ? json_encode($uomUnitsNames) : null,
        'per_unit_cost' => $row[16],
        'total_cost' => $row[17],
        'status' => $row[18],
        'barcode_number' => $row[1],
        'updated_at' => now(),
    ]);
            
                // $product = Product::create([
                //     'product_name' => $row[0],
                //     'sku' => $row[1],
                //     'generated_barcode' => $barcodeImage,
                //     'generated_qrcode' => $qrCodeImage,
                //     'category_id' => $category->id,
                //     'sub_category_id' => $subcategory->id,
                //     'manufacturer' => $manufacturer->name,
                //     'vendor_id' => $vendor->id,
                //     'model' => $row[6],
                //     'unit_of_measurement_category' => $uomCategory->id,
                //     'description' =>$row[8],
                //     'returnable' => strtolower($row[9]) === 'yes' ? 1 : 0,
                //     'commit_stock_check' => (float) $row[10],
                //     'inventory_alert_threshold' => (int) $row[11],
                //     'opening_stock' => (int) $row[12],
                //     'location_id' => json_encode($locationIds),
                //     'quantity' => $row[14],
                //     'unit_of_measure' => json_encode($uomUnitsNames),
                //     'per_unit_cost' =>$row[16],
                //     'total_cost' => $row[17],
                //     'status' => $row[18],
                //     'barcode_number' => $row[1],
                //     'created_at' => now(),
                //     'updated_at' => now(),
                // ]);
            
            $totalStock = 0;
        // Decode JSON string fields (only once)
            $quantities = json_decode($row[14], true);         // e.g. ["50", "60"]
            $unitMeasures = json_decode($row[15], true);       // e.g. ["pcs", "box"]
            $perUnitCosts = json_decode($row[16], true);       // e.g. ["10", "12"]
            $totalCosts = json_decode($row[17], true);         // e.g. ["500", "720"]

            foreach ($locationIds as $index => $locationId) {
                $quantity = isset($quantities[$index]) ? (float)$quantities[$index] : 0;
                $unit_of_measure = isset($unitMeasures[$index]) ? $unitMeasures[$index] : '';
                $perUnitCost = isset($perUnitCosts[$index]) ? (float)$perUnitCosts[$index] : 0;
                $total_cost = isset($totalCosts[$index]) ? (float)$totalCosts[$index] : 0;

                $currentStock = $quantity;
                $totalStock += $currentStock;

                Stock::create([
                    'product_id'       => $product->id?? null,
                    'vendor_id'        => $vendor->id?? null,
                    'category_id'      => $category->id?? null,
                    'current_stock'    => $currentStock?? null,
                    'quantity'         => $quantity?? null,
                    'unit_of_measure'  => $unit_of_measure?? null,
                    'per_unit_cost'    => $perUnitCost?? null,
                    'total_cost'       => $total_cost?? null,
                    'location_id'      => $locationId?? null,
                    'stock_date'       => now(),
                ]);
                }

                $product->opening_stock = $totalStock;
                $product->opening_stock = $totalStock;
                $product->save();


                 }else{
                

                // $barcodeNumber = $row[1];
                // $barcodeImage = (new DNS1D)->getBarcodePNG($barcodeNumber, 'C39');
                // $barcodePath = 'public/barcodes/' . $barcodeNumber . '.png';
                // Storage::put($barcodePath, $barcodeImage);
                // $savedBarcodePath = str_replace('public/', 'storage/', $barcodePath);

                // $productDetails = ['sku' => $row[1]];
                // $qrCodeImage = (new DNS2D)->getBarcodePNG(json_encode($productDetails, JSON_UNESCAPED_UNICODE), 'QRCODE');
                // $qrCodeFile = 'qrcode_' . time() . '_' . uniqid() . '.png';
                // $qrCodePath = 'public/qrcode/' . $qrCodeFile;
                // Storage::put($qrCodePath, $qrCodeImage);
                // $savedQRCodePath = str_replace('public/', 'storage/', $qrCodePath);


                $barcodeNumber = strtoupper(trim($row[1]));

                if (!preg_match('/^[A-Z0-9 \-.\$\/\+\%]+$/', $sku)) {
                    return response()->json([
                        'message' => "Invalid SKU Format value: {$sku}"
                    ], 422);
                }
// print_r($barcodeNumber);die;

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


            //     $uomCategory = UomCategory::firstOrCreate(['name' => $row[7]], ['name' => $row[7]]);
                
            //     $uomUnitString = $row[15];
            //     $uomNames = explode(",", $uomUnitString);
            //     $joinedUomString = implode(',', $uomNames);
            //     $finalUomArray = json_decode($joinedUomString, true);
        
            // $uomUnitsNames = [];
            
            // if (is_array($finalUomArray)) {

            //     foreach ($finalUomArray as $name) {
            //         $name = trim($name);
                
            //         $uomUnitsName = \App\Models\UomUnit::firstOrCreate(
            //              ['unit_name' => $name],
            //         ['unit_name' => $name, 'uom_category_id' => $uomCategory->id,'abbreviation' => '1','reference' => '1','ratio' => '0.2','rounding'=>'00.5','active'=>'1']
            //     );
            
            //         $uomUnitsNames[] = $uomUnitsName->unit_name;
            //     }
            //     } else {
            //         // Handle invalid JSON
            //         Log::error('Invalid JSON in Uom Unit column:', ['value' => $row[15]]);
            //     }
            //     // print_r($locationIds);die;


            //     $manufacturer = Manufacturer::firstOrCreate(
            //         ['name' => $row[4]],
            //         ['name' => $row[4], 'description' => 'Description for box','status'=>'active']
            //     );

            //     $category = Category::firstOrCreate(
            //         ['name' => $row[2]],
            //         ['name' => $row[2], 'description' => '']
            //     );
            //     $subcategory = Subcategory::firstOrCreate([
            //         'name' => $row[3],
            //         'category_id' => $category->id
            //     ], [
            //         'name' => $row[3],
            //         'category_id' => $category->id
            //     ]);
            //     $vendor = Vendor::firstOrCreate(['vendor_name' =>$row[5]], ['vendor_name' => $row[5]]);
                
            
                 $uomCategory = null;
                $uomUnitsNames = null;
                if (!empty($row[7])) {

                $uomCategory = UomCategory::firstOrCreate(['name' => $row[7]], ['name' => $row[7]]);
                
                $uomUnitString = $row[15];
                $uomNames = explode(",", $uomUnitString);
                $joinedUomString = implode(',', $uomNames);
                $finalUomArray = json_decode($joinedUomString, true);
        
            $uomUnitsNames = [];
                

            if (is_array($finalUomArray)) {

                foreach ($finalUomArray as $name) {
                    $name = trim($name);
                
                    $uomUnitsName = \App\Models\UomUnit::firstOrCreate(
                         ['unit_name' => $name],
                    ['unit_name' => $name, 'uom_category_id' => $uomCategory->id,'abbreviation' => '1','reference' => '1','ratio' => '0.2','rounding'=>'00.5','active'=>'1']
                );
            
                    $uomUnitsNames[] = $uomUnitsName->unit_name;
                }
                } else {
                    // Handle invalid JSON
                    Log::error('Invalid JSON in Uom Unit column:', ['value' => $row[15]]);
                }
                }
                // print_r($locationIds);die;


                $manufacturer = Manufacturer::firstOrCreate(
                    ['name' => $row[4]],
                    ['name' => $row[4], 'description' => 'Description for box','status'=>'active']
                );

                // $category = Category::firstOrCreate(
                //     ['name' => $row[2]],
                //     ['name' => $row[2], 'description' => '']
                // );
                $category = null;

                if (!empty($row[2])) {
                    $category = Category::firstOrCreate(
                        ['name' => $row[2]],
                        ['name' => $row[2], 'description' => '']
                    );
                }

                $subcategory = null;

                if (!empty($row[3])) {
                $subcategory = Subcategory::firstOrCreate([
                    'name' => $row[3],
                    'category_id' => $category->id
                ], [
                    'name' => $row[3],
                    'category_id' => $category->id
                ]);
            }

            $vendor = null;

                if (!empty($row[5])) {

                $vendor = Vendor::firstOrCreate(['vendor_name' =>$row[5]], ['vendor_name' => $row[5]]);
                }
                $product = Product::create([
                    'product_name' => $row[0],
                    'sku' => $row[1],
                    'generated_barcode' => $barcodeImage,
                    'generated_qrcode' => $qrCodeImage,
                    'category_id' => $category?->id,
                    'sub_category_id' => $subcategory?->id,
                    'manufacturer' => $manufacturer->name,
                    'vendor_id' => $vendor?->id,
                    'model' => $row[6],
                    'unit_of_measurement_category' => $uomCategory?->id,
                    'description' =>$row[8],
                    'returnable' => strtolower($row[9]) === 'yes' ? 1 : 0,
                    'commit_stock_check' => (float) $row[10],
                    'inventory_alert_threshold' => (int) $row[11],
                    'opening_stock' => (int) $row[12],
                    'location_id' => json_encode($locationIds),
                    'quantity' => $row[14],
                    'unit_of_measure' => $uomUnitsNames ? json_encode($uomUnitsNames) : null,
                    'per_unit_cost' =>$row[16],
                    'total_cost' => $row[17],
                    'status' => $row[18],
                    'barcode_number' => $row[1],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            
            $totalStock = 0;
        // Decode JSON string fields (only once)
            $quantities = json_decode($row[14], true);         // e.g. ["50", "60"]
            $unitMeasures = json_decode($row[15], true);       // e.g. ["pcs", "box"]
            $perUnitCosts = json_decode($row[16], true);       // e.g. ["10", "12"]
            $totalCosts = json_decode($row[17], true);         // e.g. ["500", "720"]

            foreach ($locationIds as $index => $locationId) {
                $quantity = isset($quantities[$index]) ? (float)$quantities[$index] : 0;
                $unit_of_measure = isset($unitMeasures[$index]) ? $unitMeasures[$index] : '';
                $perUnitCost = isset($perUnitCosts[$index]) ? (float)$perUnitCosts[$index] : 0;
                $total_cost = isset($totalCosts[$index]) ? (float)$totalCosts[$index] : 0;

                $currentStock = $quantity;
                $totalStock += $currentStock;

                Stock::create([
                    'product_id'       => $product->id,
                    'vendor_id'        => $vendor->id ?? null,
                    'category_id'      => $category->id?? null,
                    'current_stock'    => $currentStock?? null,
                    'quantity'         => $quantity?? null,
                    'unit_of_measure'  => $unit_of_measure?? null,
                    'per_unit_cost'    => $perUnitCost?? null,
                    'total_cost'       => $total_cost?? null,
                    'location_id'      => $locationId?? null,
                    'stock_date'       => now(),
                ]);
                }

                $product->opening_stock = $totalStock;
                $product->opening_stock = $totalStock;
                $product->save();
            }

                $rowNumber++;
            }


            fclose($handle);

            return response()->json([
                'message' => 'CSV uploaded successfully.',
                'total_rows' => $invalidRows
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

    $columns = [
        'id','product_name', 'sku', 'category_id', 'sub_category_id','manufacturer',
        'vendor_id', 'model', 'unit_of_measurement_category', 'description', 'returnable', 'commit_stock_check', 'inventory_alert_threshold', 'opening_stock', 'location_id', 'quantity',
        'unit_of_measure', 'per_unit_cost', 'total_cost', 'status'
    ];
    // $columns = ['id','product_name','sku','units','category_id','sub_category_id','manufacturer','vendor_id','model','location_id','description','returnable','track_inventory','opening_stock','selling_cost','cost_price','commit_stock_check','project_name','weight','weight_unit','length','width','depth','measurement_unit','inventory_alert_threshold','status'
// ];

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

// $settings = BarcodeSetting::where('value', 1)->pluck('key')->toArray();
$settings = BarcodeSetting::where('value', 1)->pluck('key')->toArray();
//  print_r($settings);die;
$productDetail = [];
if (!empty($settings)) {
    // Dynamically select only the keys that are enabled in settings
    
    

    $product = Product::select($settings)
        ->where('sku', $request->data)
        ->first();
//    print_r($product);die;
        // Return only selected values
       $productDetail = $product->toArray();
   
}
$productName = Product::select('product_name')
        ->where('sku', $request->data)
        ->first();


    $pdf = PDF::loadView('pdf.barcodes', [
        'barcodes' => $barcodes,
        'orientation' => $request->orientation,
        'format' => $request->format,
        'size' => $request->size,
        'sku' => $request->data,
        'data' => json_encode($product),
        'productName' => json_encode($productName),
        'type' => $request->type,
        'count' => $request->count,
        
    ]);

    return $pdf->download('barcodes.pdf');
}




    public function downloadCsv()
    {
        $fileName = 'products.csv';

        $products = Product::with([
            'category:id,name',
            'vendor:id,vendor_name',
            'sub_category:id,name'
        ])->orderBy('id', 'desc')->get();

        $uomCategories = \App\Models\UomCategory::pluck('name', 'id');
            $products = $products->map(function ($product) use ($uomCategories) {
        // $products = $products->map(function ($product) {
            $locationNames = '';
            if (!empty($product->location_id)) {
                $locationIds = json_decode($product->location_id, true);
                if (is_array($locationIds)) {
                    $names = \App\Models\Location::whereIn('id', $locationIds)->pluck('name')->toArray();
                    $locationNames = implode(', ', $names);
                }
            }


            

            return [
                'Product Name' => $product->product_name,
                'SKU' => $product->sku,
                'Category' => optional($product->category)->name,
                'Sub Category' => optional($product->sub_category)->name,
                'Manufacturer' => $product->manufacturer,
                'Vendor' => optional($product->vendor)->vendor_name,
                'Model' => $product->model,
                'Unit of Measurement Category' => $uomCategories[$product->unit_of_measurement_category] ?? '', 
                'Description' => $product->description,
                'Returnable' => $product->returnable ? 'Yes' : 'No',
                'Commit Stock Check' => $product->commit_stock_check,
                'Inventory Alert Threshold' => $product->inventory_alert_threshold,
                'Opening Stock' => $product->opening_stock,
                'Location' => $locationNames,
                'Quantity' => $product->quantity,
                'Unit of Measure' => $product->unit_of_measure,
                'Per Unit Cost' => $product->per_unit_cost,
                'Total Cost' => $product->total_cost,
                'Status' => $product->status,
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
            'Product Name', 'SKU', 'Category', 'Sub Category', 'Manufacturer',
            'Vendor', 'Model', 'Unit of Measurement Category', 'Description', 'Returnable',
            'Commit Stock Check', 'Inventory Alert Threshold', 'Opening Stock', 'Location',
            'Quantity', 'Unit of Measure', 'Per Unit Cost', 'Total Cost', 'Status'
        ];

        $callback = function () use ($products, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns); // headers

            foreach ($products as $product) {
                $row = [];
                foreach ($columns as $col) {
                    $row[] = $product[$col] ?? '';
                }
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }




public function generateTemplateCsvUrl()
{
    $filename = 'csv_tem/product_template.csv';

    // Dummy data (array of stdClass or arrays)
    $products = collect([
        (object)[
            'product_name' => 'Sample Product',
            'sku' => 'SKU123',
            'category' => (object)['name' => 'Electronics'],
            'sub_category' => (object)['name' => 'Mobile'],
            'manufacturer' => 'Samsung',
            'vendor' => (object)['vendor_name' => 'ABC Vendor'],
            'model' => 'ModelX',
            'unit_of_measurement_category' => 'Pieces',
            'description' => 'A sample product',
            'returnable' => '1',
            'commit_stock_check' => '1',
            'inventory_alert_threshold' => 10,
            'opening_stock' => 100,
            'location_id' => json_encode(["indore", "delhi"]),
            'quantity' => json_encode(["50", "50"]),
            'unit_of_measure' => json_encode(["pcs","pcs"]),
            'per_unit_cost' => json_encode(["10", "20"]),
            'total_cost' => json_encode(["150", "150"]),
            'status' => 'active',
        ]
    ]);

    // Map products to desired format
    $products = $products->map(function ($product) {
        return [
            'product_name' => $product->product_name,
            'sku' => $product->sku,
            'category_id' => optional($product->category)->name,
            'sub_category_id' => optional($product->sub_category)->name,
            'manufacturer' => $product->manufacturer,
            'vendor_id' => optional($product->vendor)->vendor_name,
            'model' => $product->model,
            'unit_of_measurement_category' => $product->unit_of_measurement_category,
            'description' => $product->description,
            'returnable' => $product->returnable,
            'commit_stock_check' => $product->commit_stock_check,
            'inventory_alert_threshold' => $product->inventory_alert_threshold,
            'opening_stock' => $product->opening_stock,
            'location_id' => $product->location_id,
            'quantity' => $product->quantity,
            'unit_of_measure' => $product->unit_of_measure,
            'per_unit_cost' => $product->per_unit_cost,
            'total_cost' => $product->total_cost,
            'status' => $product->status,
        ];
    });

    // CSV column headers
    $columns = [
        "product_name", "sku", "category_id", "sub_category_id", "manufacturer",
        "vendor_id", "model", "unit_of_measurement_category", "description", "returnable",
        "commit_stock_check", "inventory_alert_threshold", "opening_stock", "location_id", "quantity",
        "unit_of_measure", "per_unit_cost", "total_cost", "status"
    ];

    //     $columns = [
//         "product_name", "sku", "category_id", "sub_category_id","manufacturer",
//         "vendor_id", "model", "unit_of_measurement_category", "description", "returnable", "commit_stock_check", "inventory_alert_threshold", "opening_stock", "location_id", "quantity",
//         "unit_of_measure", "per_unit_cost", "total_cost", "status"
//     ];

    // Create CSV file
    $filePath = storage_path("app/public/{$filename}");
    if (!file_exists(dirname($filePath))) {
        mkdir(dirname($filePath), 0755, true);
    }

    $file = fopen($filePath, 'w');
    fputcsv($file, $columns); // Headers

    foreach ($products as $product) {
        fputcsv($file, $product);
    }

    fclose($file);

    // Return download URL
    $url = asset("storage/{$filename}");

    return response()->json([
        'status' => 'success',
        'url' => $url
    ]);
}


}

