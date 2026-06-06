<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Model;

class ShiftScheduleAssignment extends Model
{
    use LogsAudit;

    protected $table = 'shift_schedule_assignments';

    protected $fillable = [
        'shift_schedule_id',
        'shift_id',
        'scheduled_date',
        'shifts_group_id',
        'assignment_type',
    ];

    public function shiftSchedule()
    {
        return $this->belongsTo(ShiftSchedule::class, 'shift_schedule_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shifts::class, 'shift_id');
    }

    public function group()
    {
        return $this->belongsTo(ShiftsGroup::class, 'shifts_group_id');
    }
}
