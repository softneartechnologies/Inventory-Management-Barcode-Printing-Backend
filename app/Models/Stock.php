<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    //
    protected $fillable = ['product_id', 'current_stock', 'new_stock', 'quantity', 'unit', 'reason_for_update', 'location', 'stock_date', 'vendor', 'adjustment'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
