<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    //
    protected $fillable = ['product_id', 'current_stock', 'new_stock', 'quantity', 'unit', 'reason_for_update', 'location', 'stock_date', 'vendor', 'category', 'adjustment'];

    // public function product()
    // {
    //     return $this->belongsTo(Product::class);
    // }

    public function product()
{
    return $this->belongsTo(Product::class, 'product_id');
}

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor');
    }


    public function Category()
    {
        return $this->belongsTo(Category::class, 'category');
    }
}
