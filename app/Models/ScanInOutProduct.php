<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScanInOutProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'employee_id',
        'in_out_date_time',
        'type',
        'in_quantity',
        'out_quantity',
        'vendor_id'
        
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}

