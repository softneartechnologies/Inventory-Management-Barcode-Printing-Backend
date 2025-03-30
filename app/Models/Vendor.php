<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;
    protected $fillable = [
        'vendor_name',
        'company_name',
        'phone_number',
        'email',
        'billing_address',
        'shipping_address',
    ];
    //
}
