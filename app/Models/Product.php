<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'thumbnail','product_name', 'sku', 'generated_barcode','generated_qrcode',
        'barcode_number', 'category_id', 'sub_category_id', 'manufacturer',
        'vendor_id', 'model', 'unit_of_measurement_category', 'description',
        'returnable', 'commit_stock_check', 'inventory_alert_threshold', 'location_id',
        'quantity', 'unit_of_measure', 'per_unit_cost', 'total_cost',
        'opening_stock', 'status'
    ];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function sub_category()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
    public function stocksData()
    {
        return $this->hasMany(Stock::class, 'product_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function vendorsdata()
    {
        return $this->belongsTo(Vendor::class);
    }
    
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

}
