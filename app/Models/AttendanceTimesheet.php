<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employees;
use App\Models\User;

class AttendanceTimesheet extends Model
{
    use HasFactory;
    use LogsAudit;

    protected $fillable = [
        'person_id',
        'employee_id',
        'name',
        'department',
        'recorded_at',
        'attendance_status',
        'attendance_checkpoint',
        'custom_name',
        'data_source',
        'handling_type',
        'temperature',
        'abnormal',
        'imported_by',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id');
    }

    public function importer()
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}
