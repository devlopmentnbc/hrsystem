<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Model;

class Designations extends Model
{
    use LogsAudit;

    protected $fillable = [

        'designation_name', 
        'description',
        'status',
    ];
}
