<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScanInOutProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'issue_from_user_id',
        'employee_id',
        'in_out_date_time',
        'type',
        'purpose',
        'department_id',
        'work_station_id',
        'machine_id',
        'comments',
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
    
    public function user()
    {
        return $this->belongsTo(User::class, 'issue_from_user_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}

