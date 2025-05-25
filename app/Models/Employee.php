<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    //
    protected $fillable = ['employee_id','employee_name', 'department', 'work_station', 'status','access_for_login'];
}
