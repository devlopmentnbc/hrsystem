<?php

namespace App\Support;

use App\Mail\AttendanceNotificationDigestMail;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceNotificationSetting;
use App\Models\AttendanceTimesheet;
use App\Models\CompanyHoliday;
use App\Models\Employees;
use App\Models\LeaveRequest;
use App\Models\ShiftScheduleAssignment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class AttendanceNotificationService
{
    private const HALF_DAY_PATTERN_THRESHOLD = 3;
    public const MISSING_ROSTER_LOOKAHEAD_DAYS = 7;

    public function sendDueNotifications(Carbon|\DateTimeInterface|string|null $referenceTime = null): int
    {
        $now = $referenceTime instanceof Carbon
            ? $referenceTime->copy()
            : ($referenceTime instanceof \DateTimeInterface
                ? Carbon::instance($referenceTime)
                : ($referenceTime ? Carbon::parse($referenceTime) : now()->copy()));
        $scheduledTime = $now->format('H:i:00');

        $settings = AttendanceNotificationSetting::query()
            ->where('is_active', true)
            ->whereTime('scheduled_time', '<=', $scheduledTime)
            ->where(function ($query) use ($now) {
                $query->whereNull('last_sent_at')
                    ->orWhereDate('last_sent_at', '<', $now->toDateString());
            })
            ->orderBy('scheduled_time')
            ->get();

        $sentCount = 0;

        foreach ($settings as $setting) {
            try {
                $this->sendNotification($setting, $now);

                $setting->forceFill([
                    'last_sent_at' => $now->copy(),
                ])->saveQuietly();

                $sentCount++;
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return $sentCount;
    }

    public function sendNotification(AttendanceNotificationSetting $setting, Carbon|\DateTimeInterface|string|null $referenceTime = null): void
    {
        $referenceDate = $referenceTime instanceof Carbon
            ? $referenceTime->copy()->startOfDay()
            : ($referenceTime instanceof \DateTimeInterface
                ? Carbon::instance($referenceTime)->startOfDay()
                : ($referenceTime ? Carbon::parse($referenceTime)->startOfDay() : now()->copy()->startOfDay()));
        $payload = $this->buildDigestPayload($setting, $referenceDate->copy()->startOfMonth(), $referenceDate, $referenceDate);

        $mailer = Mail::to($setting->toEmailList());

        if ($setting->ccEmailList()) {
            $mailer->cc($setting->ccEmailList());
        }

        $mailer->send(new AttendanceNotificationDigestMail($setting, $payload));
    }

    public function buildReportPayload(array $options, Carbon $startDate, Carbon $endDate): array
    {
        return $this->buildDigestPayload($options, $startDate, $endDate, $endDate);
    }

    public function buildDigestPayload(AttendanceNotificationSetting|array $setting, Carbon $startDate, Carbon $endDate, ?Carbon $referenceDate = null): array
    {
        $referenceDate = $referenceDate ? $referenceDate->copy() : $endDate->copy();
        $alertOptions = $this->resolveAlertOptions($setting);
        $futureRosterEndDate = $referenceDate->copy()->addDays(self::MISSING_ROSTER_LOOKAHEAD_DAYS);
        $maxRangeEndDate = $endDate->copy()->max($futureRosterEndDate);

        $employees = Employees::where('status', 1)
            ->with(['groupAssignments', 'department', 'designation'])
            ->orderBy('employee_name')
            ->get();

        $employeeIds = $employees->pluck('id');

        $assignments = ShiftScheduleAssignment::whereBetween('scheduled_date', [$startDate->toDateString(), $maxRangeEndDate->toDateString()])
            ->with('shift')
            ->get();

        $approvedLeaveRequests = LeaveRequest::where('status', 'approved')
            ->whereIn('employee_id', $employeeIds)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($subQuery) use ($startDate, $endDate) {
                        $subQuery->whereDate('start_date', '<=', $startDate)
                            ->whereDate('end_date', '>=', $endDate);
                    });
            })
            ->get()
            ->groupBy('employee_id');

        $pendingLeaveRequests = LeaveRequest::where('status', 'pending')
            ->whereIn('employee_id', $employeeIds)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($subQuery) use ($startDate, $endDate) {
                        $subQuery->whereDate('start_date', '<=', $startDate)
                            ->whereDate('end_date', '>=', $endDate);
                    });
            })
            ->get();

        $holidayMap = CompanyHoliday::where('status', true)
            ->whereBetween('holiday_date', [$startDate, $maxRangeEndDate])
            ->get()
            ->keyBy(fn ($holiday) => $holiday->holiday_date->toDateString());

        $punchesByEmployee = AttendanceTimesheet::whereIn('employee_id', $employeeIds)
            ->whereBetween('recorded_at', [$startDate->copy()->startOfDay(), $maxRangeEndDate->copy()->addDay()->endOfDay()])
            ->orderBy('recorded_at')
            ->get()
            ->groupBy('employee_id');

        $punchesByEmployeeDate = $punchesByEmployee
            ->map(function (Collection $punches) {
                return $punches->groupBy(fn (AttendanceTimesheet $punch) => $punch->recorded_at->toDateString());
            });

        $corrections = AttendanceCorrection::whereIn('employee_id', $employeeIds)
            ->where('status', true)
            ->whereBetween('correction_date', [$startDate, $endDate])
            ->latest('updated_at')
            ->get()
            ->groupBy(fn (AttendanceCorrection $correction) => $correction->employee_id . '|' . Carbon::parse($correction->correction_date)->toDateString())
            ->map(fn (Collection $items) => $items->first());

        $buckets = [
            'pending_leave' => [],
            'missed_punch' => [],
            'absent' => [],
            'late' => [],
            'early_out' => [],
            'missing_roster' => [],
            'overtime' => [],
            'weekend_holiday_punch' => [],
            'half_day' => [],
        ];

        if ($alertOptions['notify_pending_leave']) {
            foreach ($pendingLeaveRequests as $leaveRequest) {
                $employee = $employees->firstWhere('id', $leaveRequest->employee_id);
                if (! $employee) {
                    continue;
                }

                /** @var Employees $employee */
                $this->addIssue($buckets['pending_leave'], $employee, Carbon::parse($leaveRequest->start_date), [
                    'label' => Carbon::parse($leaveRequest->start_date)->toDateString() . ' to ' . Carbon::parse($leaveRequest->end_date)->toDateString() . ' (' . $leaveRequest->leave_type . ')',
                    'remarks' => $leaveRequest->reason,
                ]);
            }
        }

        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            foreach ($employees as $employee) {
                /** @var \App\Models\Employees $employee */
                $employeeGroupIds = $employee->activeShiftGroupIdsOn($currentDate);
                $dayAssignments = $assignments->filter(fn ($assignment) =>
                    $assignment->scheduled_date === $currentDate->toDateString()
                    && in_array($assignment->shifts_group_id, $employeeGroupIds)
                );

                $shiftAssignment = $dayAssignments->first(fn ($assignment) =>
                    $assignment->assignment_type === 'work' && ! is_null($assignment->shift_id) && ! is_null($assignment->shift)
                );

                $offOnDate = $dayAssignments->contains(fn ($assignment) => $assignment->assignment_type === 'off');
                $holidayOnDate = $holidayMap->get($currentDate->toDateString());
                $weekendOnDate = $currentDate->isWeekend() && ! $shiftAssignment && ! $offOnDate && ! $holidayOnDate;
                $leaveOnDate = isset($approvedLeaveRequests[$employee->id])
                    ? $approvedLeaveRequests[$employee->id]->first(fn ($leaveRequest) =>
                        $currentDate->between(Carbon::parse($leaveRequest->start_date), Carbon::parse($leaveRequest->end_date))
                    )
                    : null;

                $datePunches = $punchesByEmployeeDate->get($employee->id, collect())->get($currentDate->toDateString(), collect());

                $metrics = $this->buildAttendanceMetrics(
                    $employee,
                    $currentDate,
                    $shiftAssignment,
                    $leaveOnDate,
                    $offOnDate,
                    $holidayOnDate,
                    $weekendOnDate,
                    $punchesByEmployee->get($employee->id, collect()),
                    $corrections->get($employee->id . '|' . $currentDate->toDateString())
                );

                if ($alertOptions['notify_missed_punch'] && ($metrics['status'] ?? null) === 'Incomplete') {
                    $this->addIssue($buckets['missed_punch'], $employee, $currentDate, [
                        'remarks' => $metrics['remarks'] ?? null,
                    ]);
                }

                if ($alertOptions['notify_absent'] && ($metrics['status'] ?? null) === 'Absent') {
                    $this->addIssue($buckets['absent'], $employee, $currentDate, [
                        'remarks' => $metrics['remarks'] ?? null,
                    ]);
                }

                if ($alertOptions['notify_late'] && (int) ($metrics['late_min'] ?? 0) > 0) {
                    $this->addIssue($buckets['late'], $employee, $currentDate, [
                        'minutes' => (int) $metrics['late_min'],
                        'remarks' => $metrics['remarks'] ?? null,
                    ]);
                }

                if ($alertOptions['notify_early_out'] && (int) ($metrics['early_out_min'] ?? 0) > 0) {
                    $this->addIssue($buckets['early_out'], $employee, $currentDate, [
                        'minutes' => (int) $metrics['early_out_min'],
                        'remarks' => $metrics['remarks'] ?? null,
                    ]);
                }

                if ($alertOptions['notify_ot_threshold'] && (int) ($metrics['ot_min'] ?? 0) > 0) {
                    $this->addIssue($buckets['overtime'], $employee, $currentDate, [
                        'minutes' => (int) $metrics['ot_min'],
                        'remarks' => $metrics['remarks'] ?? null,
                    ]);
                }

                if ($alertOptions['notify_weekend_holiday_punch'] && $datePunches->isNotEmpty() && ($holidayOnDate || $currentDate->isWeekend())) {
                    $label = $holidayOnDate ? 'Holiday punch' : 'Weekend punch';
                    $this->addIssue($buckets['weekend_holiday_punch'], $employee, $currentDate, [
                        'label' => $label,
                        'remarks' => $label . ' detected with ' . $datePunches->count() . ' punch(es)',
                    ]);
                }

                if ($alertOptions['notify_repeated_half_day'] && ($metrics['status'] ?? null) === 'Half Day') {
                    $this->addIssue($buckets['half_day'], $employee, $currentDate, [
                        'remarks' => $metrics['remarks'] ?? null,
                    ]);
                }
            }

            $currentDate->addDay();
        }

        if ($alertOptions['notify_missing_roster']) {
            $futureDate = $referenceDate->copy()->addDay();
            while ($futureDate <= $futureRosterEndDate) {
                foreach ($employees as $employee) {
                    /** @var \App\Models\Employees $employee */
                    if ($futureDate->isWeekend() || $holidayMap->has($futureDate->toDateString())) {
                        continue;
                    }

                    $employeeGroupIds = $employee->activeShiftGroupIdsOn($futureDate);
                    $futureAssignments = $assignments->filter(fn ($assignment) =>
                        $assignment->scheduled_date === $futureDate->toDateString()
                        && in_array($assignment->shifts_group_id, $employeeGroupIds)
                    );

                    if ($futureAssignments->isEmpty()) {
                        $this->addIssue($buckets['missing_roster'], $employee, $futureDate, [
                            'remarks' => 'No work/off roster assigned for upcoming date',
                        ]);
                    }
                }

                $futureDate->addDay();
            }
        }

        $buckets['half_day'] = collect($buckets['half_day'])
            ->filter(fn (array $row) => ($row['count'] ?? 0) >= self::HALF_DAY_PATTERN_THRESHOLD)
            ->all();

        $sections = [
            [
                'key' => 'pending_leave',
                'label' => 'Pending Leave Approvals',
                'enabled' => $alertOptions['notify_pending_leave'],
                'rows' => $this->finalizeBucket($buckets['pending_leave']),
            ],
            [
                'key' => 'missed_punch',
                'label' => 'Missed Punch',
                'enabled' => $alertOptions['notify_missed_punch'],
                'rows' => $this->finalizeBucket($buckets['missed_punch']),
            ],
            [
                'key' => 'absent',
                'label' => 'Absent Employees',
                'enabled' => $alertOptions['notify_absent'],
                'rows' => $this->finalizeBucket($buckets['absent']),
            ],
            [
                'key' => 'late',
                'label' => 'Late Comers',
                'enabled' => $alertOptions['notify_late'],
                'rows' => $this->finalizeBucket($buckets['late']),
            ],
            [
                'key' => 'early_out',
                'label' => 'Early Left Employees',
                'enabled' => $alertOptions['notify_early_out'],
                'rows' => $this->finalizeBucket($buckets['early_out']),
            ],
            [
                'key' => 'missing_roster',
                'label' => 'Employees With No Upcoming Shift Roster',
                'enabled' => $alertOptions['notify_missing_roster'],
                'rows' => $this->finalizeBucket($buckets['missing_roster']),
            ],
            [
                'key' => 'overtime',
                'label' => 'Overtime Threshold Exceeded',
                'enabled' => $alertOptions['notify_ot_threshold'],
                'rows' => $this->finalizeBucket($buckets['overtime']),
            ],
            [
                'key' => 'weekend_holiday_punch',
                'label' => 'Weekend Or Holiday Punches',
                'enabled' => $alertOptions['notify_weekend_holiday_punch'],
                'rows' => $this->finalizeBucket($buckets['weekend_holiday_punch']),
            ],
            [
                'key' => 'half_day',
                'label' => 'Repeated Half-Day Attendance Patterns',
                'enabled' => $alertOptions['notify_repeated_half_day'],
                'rows' => $this->finalizeBucket($buckets['half_day']),
            ],
        ];

        return [
            'reference_date' => $referenceDate,
            'period_start' => $startDate,
            'period_end' => $endDate,
            'future_roster_end' => $futureRosterEndDate,
            'sections' => $sections,
            'total_records' => collect($sections)->sum(fn (array $section) => count($section['rows'])),
            'generated_at' => now(),
        ];
    }

    private function addIssue(array &$bucket, Employees $employee, Carbon $date, array $attributes = []): void
    {
        if (! isset($bucket[$employee->id])) {
            $bucket[$employee->id] = [
                'employee_code' => $employee->employee_code,
                'employee_name' => $employee->employee_name,
                'department' => optional($employee->department)->department_name,
                'designation' => optional($employee->designation)->designation_name,
                'count' => 0,
                'total_minutes' => 0,
                'dates' => [],
                'remarks' => [],
            ];
        }

        $bucket[$employee->id]['count']++;
        $bucket[$employee->id]['dates'][] = $date->toDateString();
        $bucket[$employee->id]['total_minutes'] += (int) ($attributes['minutes'] ?? 0);

        if (! empty($attributes['label'])) {
            $bucket[$employee->id]['labels'][] = $attributes['label'];
        }

        if (! empty($attributes['remarks'])) {
            $bucket[$employee->id]['remarks'][] = $attributes['remarks'];
        }
    }

    private function finalizeBucket(array $bucket): array
    {
        return collect($bucket)
            ->map(function (array $row) {
                $row['dates'] = collect($row['dates'])->unique()->sort()->values()->all();
                $row['labels'] = collect($row['labels'] ?? [])->filter()->unique()->values()->all();
                $row['remarks'] = collect($row['remarks'])->filter()->unique()->values()->all();

                return $row;
            })
            ->sortByDesc(fn (array $row) => [$row['count'], $row['total_minutes'], $row['employee_name']])
            ->values()
            ->all();
    }

    public function defaultAlertOptions(): array
    {
        return [
            'notify_pending_leave' => true,
            'notify_missed_punch' => true,
            'notify_absent' => true,
            'notify_late' => true,
            'notify_early_out' => true,
            'notify_missing_roster' => true,
            'notify_ot_threshold' => true,
            'notify_weekend_holiday_punch' => true,
            'notify_repeated_half_day' => true,
        ];
    }

    private function resolveAlertOptions(AttendanceNotificationSetting|array $setting): array
    {
        if ($setting instanceof AttendanceNotificationSetting) {
            return [
                'notify_pending_leave' => (bool) $setting->notify_pending_leave,
                'notify_missed_punch' => (bool) $setting->notify_missed_punch,
                'notify_absent' => (bool) $setting->notify_absent,
                'notify_late' => (bool) $setting->notify_late,
                'notify_early_out' => (bool) $setting->notify_early_out,
                'notify_missing_roster' => (bool) $setting->notify_missing_roster,
                'notify_ot_threshold' => (bool) $setting->notify_ot_threshold,
                'notify_weekend_holiday_punch' => (bool) $setting->notify_weekend_holiday_punch,
                'notify_repeated_half_day' => (bool) $setting->notify_repeated_half_day,
            ];
        }

        return array_replace($this->defaultAlertOptions(), $setting);
    }

    private function buildAttendanceMetrics(
        Employees $employee,
        Carbon $date,
        $shiftAssignment,
        $leaveOnDate,
        bool $offOnDate,
        $holidayOnDate,
        bool $weekendOnDate,
        Collection $employeePunches,
        ?AttendanceCorrection $correction
    ): array {
        $base = [
            'shift_name' => null,
            'late_min' => 0,
            'early_out_min' => 0,
            'work_minutes' => 0,
            'work_hours' => 0,
            'break_min' => 0,
            'net_work_minutes' => 0,
            'net_work_hours' => 0,
            'ot_min' => 0,
            'ot_hours' => 0,
            'status' => 'No Shift',
            'remarks' => '-',
            'punch_count' => 0,
            'is_overnight' => false,
            'is_missed_punch' => false,
        ];

        if ($holidayOnDate) {
            $base['status'] = 'Holiday';
            $base['remarks'] = $holidayOnDate->holiday_name;

            return $base;
        }

        if ($weekendOnDate) {
            $base['status'] = 'Weekend';
            $base['remarks'] = 'Weekend off';

            return $base;
        }

        if ($leaveOnDate) {
            $base['status'] = 'Leave';
            $base['remarks'] = $leaveOnDate->reason ?: 'Approved leave';

            return $base;
        }

        if ($offOnDate) {
            $base['status'] = 'Off Duty';
            $base['remarks'] = 'Rostered off day';

            return $base;
        }

        if (! $shiftAssignment || ! $shiftAssignment->shift) {
            return $base;
        }

        $shift = $shiftAssignment->shift;
        $base['shift_name'] = $shift->shift_name;
        $startTime = Carbon::parse($shift->start_time);
        $endTime = Carbon::parse($shift->end_time);
        $isOvernight = $endTime->lessThan($startTime);
        $base['is_overnight'] = $isOvernight;

        $shiftStartDt = $date->copy()->setTimeFromTimeString($shift->start_time);
        $shiftEndDt = $isOvernight
            ? $date->copy()->addDay()->setTimeFromTimeString($shift->end_time)
            : $date->copy()->setTimeFromTimeString($shift->end_time);

        $punches = $employeePunches
            ->filter(fn (AttendanceTimesheet $punch) =>
                $punch->recorded_at && $punch->recorded_at->between($shiftStartDt, $shiftEndDt)
            )
            ->values();

        $base['punch_count'] = $punches->count();
        $base['break_min'] = (int) ($shift->break_minutes ?? 0);

        if ($correction && $correction->corrected_check_in && $correction->corrected_check_out) {
            $checkIn = $correction->corrected_check_in;
            $checkOut = $correction->corrected_check_out;

            return $this->finalizeWorkedShiftMetrics($base, $shiftStartDt, $shiftEndDt, $shift, $checkIn, $checkOut, 'Corrected mispunch: ' . $correction->reason);
        }

        if ($punches->isEmpty()) {
            $base['status'] = 'Absent';
            $base['remarks'] = 'No punch';

            return $base;
        }

        $checkIn = $punches->first()->recorded_at;
        $checkOut = $punches->count() > 1 ? $punches->last()->recorded_at : null;

        $base['late_min'] = max(0, $shiftStartDt->diffInMinutes($checkIn, false));

        if (! $checkOut) {
            $base['status'] = 'Incomplete';
            $base['is_missed_punch'] = true;
            $base['remarks'] = 'Missed punch';

            return $base;
        }

        $metrics = $this->finalizeWorkedShiftMetrics($base, $shiftStartDt, $shiftEndDt, $shift, $checkIn, $checkOut);

        $remarks = [];
        if ($metrics['late_min'] > 0) {
            $remarks[] = 'Late by ' . $metrics['late_min'] . ' min';
        }
        if ($metrics['early_out_min'] > 0) {
            $remarks[] = 'Early out by ' . $metrics['early_out_min'] . ' min';
        }
        if ($metrics['ot_min'] > 0) {
            $remarks[] = 'OT ' . $metrics['ot_min'] . ' min';
        }

        $metrics['remarks'] = empty($remarks) ? 'OK' : implode(', ', $remarks);

        return $metrics;
    }

    private function finalizeWorkedShiftMetrics(array $base, Carbon $shiftStartDt, Carbon $shiftEndDt, $shift, Carbon $checkIn, Carbon $checkOut, ?string $remarks = null): array
    {
        $base['late_min'] = max(0, $shiftStartDt->diffInMinutes($checkIn, false));
        $base['early_out_min'] = max(0, $checkOut->diffInMinutes($shiftEndDt, false));
        $base['work_minutes'] = max(0, $checkIn->diffInMinutes($checkOut, false));
        $base['work_hours'] = round($base['work_minutes'] / 60, 2);
        $base['net_work_minutes'] = max(0, $base['work_minutes'] - $base['break_min']);
        $base['net_work_hours'] = round($base['net_work_minutes'] / 60, 2);

        $scheduledWorkMinutes = max(0, $shiftStartDt->diffInMinutes($shiftEndDt) - $base['break_min']);
        $otThresholdMinutes = $scheduledWorkMinutes + (int) ($shift->ot_start_after_minutes ?? 0);
        $base['ot_min'] = max(0, $base['net_work_minutes'] - $otThresholdMinutes);
        $base['ot_hours'] = round($base['ot_min'] / 60, 2);

        $fullDayMinutes = (float) ($shift->full_day_hours ?? 8) * 60;
        $halfDayMinutes = (float) ($shift->half_day_hours ?? 4) * 60;

        if ($base['net_work_minutes'] >= $fullDayMinutes) {
            $base['status'] = 'Present';
        } elseif ($base['net_work_minutes'] >= $halfDayMinutes) {
            $base['status'] = 'Half Day';
        } else {
            $base['status'] = 'Absent';
        }

        if ($remarks) {
            $base['remarks'] = $remarks;
        }

        return $base;
    }
}