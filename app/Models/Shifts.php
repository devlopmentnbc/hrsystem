<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Model;

class Shifts extends Model
{
    use LogsAudit;

    protected $fillable = [
        'shift_name',
        'start_time',
        'end_time',
        'break_minutes',
        'grace_period_minutes',
        'ot_start_after_minutes',
        'night_shift',
        'full_day_hours',   
        'half_day_hours',
        'remarks',
        'status',
        'last_updated_by',
        'last_updated_at',
    ];

    public function shiftsGroups()
    {
        return $this->belongsToMany(ShiftsGroup::class, 'shift_group_shift', 'shift_id', 'shifts_group_id')->withTimestamps();
    }
}
