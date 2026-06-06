<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ShiftsGroup extends Model
{
    use LogsAudit;

    protected $table = 'shifts_groups';

    protected $fillable = [
        'group_name',
        'category',
        'remarks',
        'status',
        'last_updated_by',
        'last_updated_at',
    ];

    public function employees()
    {
        return $this->belongsToMany(Employees::class, 'shifts_group_employee', 'shifts_group_id', 'employee_id')
            ->withPivot(['effective_start_date', 'effective_end_date'])
            ->withTimestamps();
    }

    public function groupAssignments()
    {
        return $this->hasMany(\App\Models\ShiftGroupEmployeeAssignment::class, 'shifts_group_id');
    }

    public function activeEmployeeAssignments()
    {
        $today = Carbon::today()->toDateString();

        return $this->groupAssignments()
            ->where(function ($query) use ($today) {
                $query->whereNull('effective_start_date')
                    ->orWhereDate('effective_start_date', '<=', $today);
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('effective_end_date')
                    ->orWhereDate('effective_end_date', '>=', $today);
            });
    }

    public function shifts()
    {
        return $this->belongsToMany(Shifts::class, 'shift_group_shift', 'shifts_group_id', 'shift_id')->withTimestamps();
    }
}
