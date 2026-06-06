<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Model;

class ShiftSchedule extends Model
{
    use LogsAudit;

    protected $fillable = [
        'schedule_name',
        'group_category',
        'start_date',
        'end_date',
        'created_by',
        'last_updated_by',
        'last_updated_at',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function assignments()
    {
        return $this->hasMany(ShiftScheduleAssignment::class, 'shift_schedule_id');
    }
}
