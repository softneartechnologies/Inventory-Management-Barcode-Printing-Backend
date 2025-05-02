<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workstation extends Model
{
    //
    protected $fillable = ['department_id', 'name', 'description'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
