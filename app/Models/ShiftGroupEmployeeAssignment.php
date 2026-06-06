<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ShiftGroupEmployeeAssignment extends Model
{
    protected $table = 'shifts_group_employee';

    protected $fillable = [
        'shifts_group_id',
        'employee_id',
        'effective_start_date',
        'effective_end_date',
    ];

    protected $casts = [
        'effective_start_date' => 'date',
        'effective_end_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id');
    }

    public function shiftGroup()
    {
        return $this->belongsTo(ShiftsGroup::class, 'shifts_group_id');
    }

    public function scopeOverlapping(Builder $query, Carbon|string $startDate, Carbon|string|null $endDate = null): Builder
    {
        $start = $startDate instanceof Carbon ? $startDate->toDateString() : Carbon::parse($startDate)->toDateString();
        $end = $endDate ? ($endDate instanceof Carbon ? $endDate->toDateString() : Carbon::parse($endDate)->toDateString()) : null;

        return $query
            ->where(function (Builder $builder) use ($start) {
                $builder->whereNull('effective_end_date')
                    ->orWhereDate('effective_end_date', '>=', $start);
            })
            ->when($end, function (Builder $builder) use ($end) {
                $builder->where(function (Builder $innerBuilder) use ($end) {
                    $innerBuilder->whereNull('effective_start_date')
                        ->orWhereDate('effective_start_date', '<=', $end);
                });
            });
    }

    public function isActiveOn(Carbon|string $date): bool
    {
        $targetDate = $date instanceof Carbon ? $date->toDateString() : Carbon::parse($date)->toDateString();
        $start = $this->effective_start_date ? Carbon::parse($this->effective_start_date)->toDateString() : null;
        $end = $this->effective_end_date ? Carbon::parse($this->effective_end_date)->toDateString() : null;

        return (! $start || $start <= $targetDate)
            && (! $end || $end >= $targetDate);
    }
}