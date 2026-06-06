<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Model;

class CompanyHoliday extends Model
{
    use LogsAudit;

    protected $fillable = [
        'holiday_name',
        'holiday_date',
        'holiday_type',
        'description',
        'status',
    ];

    protected $casts = [
        'holiday_date' => 'date',
        'status' => 'boolean',
    ];
}
