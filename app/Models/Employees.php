<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Employees extends Model
{
    use LogsAudit;

    protected $casts = [
        'date_joined' => 'date',
        'date_resigned' => 'date',
        'last_updated_at' => 'datetime',
    ];

    protected $fillable = [
        'employee_code',
        'designation_id',
        'department_id',
        'employee_name',
        'epf_number',
        'nic_number',
        'date_joined',
        'date_resigned',
        'gender',
        'address',
        'remarks',
        'status',
        'last_updated_by',
        'last_updated_at',
    ];

    public function designation()
    {
        return $this->belongsTo(Designations::class);
    }

    public function department()
    {
        return $this->belongsTo(Departments::class);
    }

    public function shiftsGroups()
    {
        return $this->belongsToMany(ShiftsGroup::class, 'shifts_group_employee', 'employee_id', 'shifts_group_id')
            ->withPivot(['effective_start_date', 'effective_end_date'])
            ->withTimestamps();
    }

    public function groupAssignments()
    {
        return $this->hasMany(\App\Models\ShiftGroupEmployeeAssignment::class, 'employee_id');
    }

    public function activeGroupAssignmentsOn(Carbon|string $date)
    {
        $targetDate = $date instanceof Carbon ? $date : Carbon::parse($date);
        $assignments = $this->relationLoaded('groupAssignments')
            ? $this->groupAssignments
            : $this->groupAssignments()->get();

        return $assignments->filter(fn (\App\Models\ShiftGroupEmployeeAssignment $assignment) => $assignment->isActiveOn($targetDate));
    }

    public function activeShiftGroupIdsOn(Carbon|string $date): array
    {
        return $this->activeGroupAssignmentsOn($date)
            ->pluck('shifts_group_id')
            ->unique()
            ->values()
            ->all();
    }

    public function shiftSchedules()
    {
        return $this->hasMany(ShiftScheduleAssignment::class, 'employee_id');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceTimesheet::class, 'employee_id');
    }
}
