<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'product_id',
        'sku',
        'current_stock',
        'threshold_count',
        'location',
    ];

    public function product()
{
    return $this->belongsTo(Product::class);
}
}
