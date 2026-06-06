<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceCorrection extends Model
{
    use LogsAudit;

    protected $fillable = [
        'employee_id',
        'correction_date',
        'shift_schedule_assignment_id',
        'original_check_in',
        'original_check_out',
        'corrected_check_in',
        'corrected_check_out',
        'reason',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'correction_date' => 'date',
        'original_check_in' => 'datetime',
        'original_check_out' => 'datetime',
        'corrected_check_in' => 'datetime',
        'corrected_check_out' => 'datetime',
        'status' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employees::class, 'employee_id');
    }

    public function shiftAssignment(): BelongsTo
    {
        return $this->belongsTo(ShiftScheduleAssignment::class, 'shift_schedule_assignment_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
