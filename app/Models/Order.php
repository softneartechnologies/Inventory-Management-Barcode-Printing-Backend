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
        'location_id',
        'quantity',
        'current_date',
        'deleted',
        'category_id',
        'total_current_stock',
        'order_by',
        'status'
    ];

    public function product()
{
    return $this->belongsTo(Product::class);
}

 public function category()
    {
        return $this->belongsTo(Category::class);
    }

     public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'order_by');
    }
}
