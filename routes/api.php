<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ManufacturerController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\WeightUnitController;
use App\Http\Controllers\MeasurementUnitController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\CurrencySettingController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\BarcodeSettingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ScanInOutProductController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\DashboardController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('forgot-password', [UserController::class, 'forgotPassword']);
Route::post('verify-otp', [UserController::class, 'verifyOTP']);
Route::post('reset', [UserController::class, 'reset']);


Route::middleware('auth:api')->group(function () {
    Route::post('logout', [UserController::class, 'logout']);
    Route::get('dashboard', [DashboardController::class, 'index']);

    Route::get('/employee', [UserController::class, 'index']);
    // Route::get('/superadmin_show', [SuperAdminController::class, 'superAdminProfileShow']);
    // Route::get('/superadmin/edit_profile', [SuperAdminController::class, 'superAdminEditProfile']);
    // Route::post('/superadmin/update-profile/{id}', [SuperAdminController::class, 'profileUpdate']);
    Route::post('change-password', [UserController::class, 'updatePassword']);


    // category api route
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']); // Get all categories
        Route::post('/add', [CategoryController::class, 'store']); // Create category
        Route::get('/edit/{id}', [CategoryController::class, 'show']); // Get single category
        Route::put('/update/{id}', [CategoryController::class, 'update']); // Update category
        Route::delete('/delete/{id}', [CategoryController::class, 'destroy']); // Delete category
    });

    // sub category api route

    Route::prefix('subcategories')->group(function () {
        Route::get('/', [SubcategoryController::class, 'index']); // Get all subcategories
        Route::post('/add', [SubcategoryController::class, 'store']); // Create subcategory
        Route::get('/edit/{id}', [SubcategoryController::class, 'show']); // Get single subcategory
        Route::put('/update/{id}', [SubcategoryController::class, 'update']); // Update subcategory
        Route::delete('/delete/{id}', [SubcategoryController::class, 'destroy']); // Delete subcategory
    });


    Route::prefix('brands')->group(function () {
        Route::get('/', [BrandController::class, 'index']); // Get all brands
        Route::post('/add', [BrandController::class, 'store']); // Create brand
        Route::get('/edit/{id}', [BrandController::class, 'show']); // Get single brand
        Route::put('/update/{id}', [BrandController::class, 'update']); // Update brand
        Route::delete('/delete/{id}', [BrandController::class, 'destroy']); // Delete brand
    });

    // manufacture api route


Route::prefix('manufacturers')->group(function () {
    Route::get('/', [ManufacturerController::class, 'index']); // Get all manufacturers
    Route::post('/add', [ManufacturerController::class, 'store']); // Create manufacturer
    Route::get('/edit/{id}', [ManufacturerController::class, 'show']); // Get single manufacturer
    Route::put('/update/{id}', [ManufacturerController::class, 'update']); // Update manufacturer
    Route::delete('/delete/{id}', [ManufacturerController::class, 'destroy']); // Delete manufacturer
});


// unit api route 

Route::prefix('units')->group(function () {
    Route::get('/', [UnitController::class, 'index']); // Get all units
    Route::post('/add', [UnitController::class, 'store']); // Create unit
    Route::get('/edit/{id}', [UnitController::class, 'show']); // Get single unit
    Route::put('/update/{id}', [UnitController::class, 'update']); // Update unit
    Route::delete('/delete/{id}', [UnitController::class, 'destroy']); // Delete unit
});

// weight-units

Route::prefix('weight-units')->group(function () {
    Route::get('/', [WeightUnitController::class, 'index']); // Get all weight units
    Route::post('/add', [WeightUnitController::class, 'store']); // Create weight unit
    Route::get('/edit/{id}', [WeightUnitController::class, 'show']); // Get single weight unit
    Route::put('/update/{id}', [WeightUnitController::class, 'update']); // Update weight unit
    Route::delete('/delete/{id}', [WeightUnitController::class, 'destroy']); // Delete weight unit
});

// measurement-units 

Route::prefix('measurement-units')->group(function () {
    Route::get('/', [MeasurementUnitController::class, 'index']); // Get all measurement units
    Route::post('/add', [MeasurementUnitController::class, 'store']); // Create measurement unit
    Route::get('/edit/{id}', [MeasurementUnitController::class, 'show']); // Get single measurement unit
    Route::put('/update/{id}', [MeasurementUnitController::class, 'update']); // Update measurement unit
    Route::delete('/delete/{id}', [MeasurementUnitController::class, 'destroy']); // Delete measurement unit
});

// employees


Route::prefix('employees')->group(function () {
    Route::get('/', [EmployeeController::class, 'index']); // Get all employees
    Route::post('/add', [EmployeeController::class, 'store']); // Create employee
    Route::get('/edit/{id}', [EmployeeController::class, 'show']); // Get single employee
    Route::put('/update/{id}', [EmployeeController::class, 'update']); // Update employee
    Route::delete('/delete/{id}', [EmployeeController::class, 'destroy']); // Delete employee
});

// currencies


Route::prefix('currencies')->group(function () {
    Route::get('/', [CurrencySettingController::class, 'index']); // Get all currencies
    Route::post('/add', [CurrencySettingController::class, 'store']); // Create currency
    Route::get('/edit/{id}', [CurrencySettingController::class, 'show']); // Get single currency
    Route::put('/update/{id}', [CurrencySettingController::class, 'update']); // Update currency
    Route::delete('/delete/{id}', [CurrencySettingController::class, 'destroy']); // Delete currency
});


// vendor api
Route::prefix('vendor')->group(function () {
    Route::get('/', [VendorController::class, 'index']); // Get all currencies
    Route::post('/add', [VendorController::class, 'store']); // Create currency
    Route::get('/edit/{id}', [VendorController::class, 'show']); // Get single currency
    Route::put('/update/{id}', [VendorController::class, 'update']); // Update currency
    Route::delete('/delete/{id}', [VendorController::class, 'destroy']); // Delete currency
});


// barcode setting
Route::prefix('barcode-settings')->group(function () {
     Route::get('/', [BarcodeSettingController::class, 'show']); // Get single currency
    Route::put('/update', [BarcodeSettingController::class, 'update']); // Update currency
   });

// product 
   Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']); // Get all currencies
    Route::post('/add', [ProductController::class, 'store']); // Create currency
    Route::get('/edit/{id}', [ProductController::class, 'show']); // Get single currency
    Route::get('/view/{id}', [ProductController::class, 'view']); // Get single currency
    Route::put('/update/{id}', [ProductController::class, 'update']); // Update currency
    Route::put('/updateStock/{product_id}', [ProductController::class, 'updateStock']); // Update currency
    Route::get('/editStock/{product_id}', [ProductController::class, 'editStock']); // Update currency
    Route::delete('/delete/{id}', [ProductController::class, 'destroy']); // Delete currency
    Route::post('/addLocations', [ProductController::class, 'createLocation']); // Delete currency
    Route::get('/locationList', [ProductController::class, 'locationList']); // Delete currency

    Route::get('inventoryAlert', [ProductController::class, 'inventoryAlert']); // Get all currencies
    Route::post('/upload-csv', [ProductController::class, 'uploadCSV']);
    
});

Route::prefix('reports')->group(function () {

    Route::get('inventoryAdjustments', [ProductController::class, 'inventoryAdjustmentsReport']); // Get all currencies
    
});

Route::get('inventorySummaryReport', [ScanInOutProductController::class, 'inventorySummaryReport']); // Get all currencies
Route::get('productScaned/{id}', [ScanInOutProductController::class, 'productScaned']); // Get all currencies


Route::prefix('order')->group(function () {

    Route::get('/', [OrderController::class, 'index']); // Get all currencies
    Route::post('/add', [OrderController::class, 'store']); // Create currency
});

Route::prefix('scan-in')->group(function () {

    // Route::get('/', [ScanInOutProductController::class, 'index']); // Get all currencies
    Route::post('/add', [ScanInOutProductController::class, 'storeIn']); // Create currency
});
Route::get('/employee/issuance/history', [ScanInOutProductController::class, 'employeeIssuanceHistory']); // Get all currencies

Route::prefix('scan-out')->group(function () {

    // Route::get('/', [ScanInOutProductController::class, 'index']); // Get all currencies
    Route::post('/add', [ScanInOutProductController::class, 'storeOut']); // Create currency
});
Route::get('recent_scan', [ScanInOutProductController::class, 'index']); // Get all currencies


// Route::prefix('roles')->group(function () {
//     Route::get('/', [RoleController::class, 'index']); // List roles
//     Route::post('/add', [RoleController::class, 'store']); // Add role
//     Route::get('/edit/{id}', [RoleController::class, 'show']); // Show role
//     Route::put('/update/{id}', [RoleController::class, 'update']); // Update role
//     Route::delete('/delete/{id}', [RoleController::class, 'destroy']); // Delete role
// });

Route::get('/roles', [RolePermissionController::class, 'getRoles']);
    Route::post('/roles/add', [RolePermissionController::class, 'createRole']);
    Route::get('/roles/edit_permission/{roleId}', [RolePermissionController::class, 'getRoleDetails']);
    Route::post('/roles/update_permission/{roleId}', [RolePermissionController::class, 'updateRole']);
    Route::delete('/roles/delete/{roleId}', [RolePermissionController::class, 'deleteRole']);

});