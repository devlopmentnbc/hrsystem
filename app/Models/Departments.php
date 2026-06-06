<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Model;

class Departments extends Model
{
    use LogsAudit;

    protected $fillable = [

        'department_name',
        'department_code',
        'description',
        'status',

    ];

    
}