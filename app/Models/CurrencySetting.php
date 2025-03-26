<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencySetting extends Model
{
    //
    protected $fillable = ['currency_name', 'currency_code', 'symbol', 'default_status'];
}
