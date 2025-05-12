<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UomUnit extends Model
{
    protected $fillable = ['uom_category_id', 'unit_name', 'abbreviation','reference','ratio','rounding','active'];
    //
    public function category() {
        return $this->belongsTo(UomCategory::class);
    }
    
}
