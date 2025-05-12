<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UomCategory extends Model
{
    //
    protected $fillable = ['name'];
    public function units() {
        return $this->hasMany(UomUnit::class);
    }
    
}
