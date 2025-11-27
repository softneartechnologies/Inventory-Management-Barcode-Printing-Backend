<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class InventoryAdjustmentReports extends Model
{
    use HasFactory;
    protected $fillable = ['product_id', 'current_stock', 'new_stock', 'quantity', 'unit_of_measure', 'reason_for_update', 'location_id', 'stock_date', 'vendor_id','category_id', 'adjustment','status', 'approval_date', 'total_cost', 'per_unit_cost'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
        public function vendor()
        {
            return $this->belongsTo(Vendor::class);
        }
    
    
        public function Category()
        {
            return $this->belongsTo(Category::class);
        }


        public function location()
        {
            return $this->belongsTo(Location::class);
        }
}
