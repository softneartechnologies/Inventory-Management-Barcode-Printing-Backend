<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarcodeSetting extends Model
{
    //
    protected $fillable = [
        'sku', 'product_name', 'description', 'units', 'category_id', 'sub_category_id',
        'manufacturer', 'vendor_id', 'model', 'returnable', 'cost_price', 'selling_cost',
        'weight', 'weight_unit', 'length', 'width', 'depth', 'measurement_unit',
    ];
}
