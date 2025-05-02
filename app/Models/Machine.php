<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    //
    protected $fillable = ['department_id', 'workstation_id','name', 'description'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function workstation()
    {
        return $this->belongsTo(Workstation::class);
    }
}
