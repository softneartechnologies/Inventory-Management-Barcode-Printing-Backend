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
        'category_id',
        'in_out_date_time',
        'type',
        'purpose',
        'department_id',
        'work_station_id',
        'machine_id',
        'comments',
        'in_quantity',
        'out_quantity',
        'vendor_id',
        'location_id',
        'previous_stock',
        'total_current_stock',
        'threshold',
        
        
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
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function workStation()
    {
        return $this->belongsTo(Workstation::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }


    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}

