<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarcodeSetting extends Model
{
    //
    protected $fillable = [
        'product_name', 'sku',
        'barcode_number', 'category_id', 'sub_category_id', 'manufacturer',
        'vendor_id', 'model', 'unit_of_measurement_category', 'description',
        'returnable', 'commit_stock_check', 'inventory_alert_threshold', 'location_id',
        'quantity', 'unit_of_measure', 'per_unit_cost', 'total_cost',
        'opening_stock',
    ];
}
