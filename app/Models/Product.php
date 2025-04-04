<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'product_name', 'sku', 'generated_barcode','barcode_number',
        'generated_qrcode', 'units', 'category', 'sub_category',
        'manufacturer', 'vendor', 'model', 'weight', 'weight_unit',
        'location_id', 'thumbnail', 'description', 'returnable',
        'track_inventory', 'opening_stock', 'selling_cost', 'cost_price',
        'commit_stock_check', 'project_name', 'length', 'width', 'depth',
        'measurement_unit', 'inventory_alert_threshold', 'status'
    ];

    protected $casts = [
        'returnable' => 'boolean',
        'track_inventory' => 'boolean',
        'commit_stock_check' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

}
