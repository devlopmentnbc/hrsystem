<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\LeaveRequest;
use App\Models\Employees;
use App\Models\AttendanceTimesheet;
use App\Models\AttendanceCorrection;
use App\Models\AuditLog;
use App\Models\CompanyHoliday;
use App\Models\ShiftScheduleAssignment;

class AttendanceController extends Controller
{
    public function index()
    {
        $leaveRequests = LeaveRequest::with('employee')->latest()->get();

        return view('attendance.leave_requests.index', compact('leaveRequests'));
    }

    public function uploadTimesheet()
    {
        return view('attendance.timesheet_upload');
    }

    public function storeTimesheet(Request $request)
    {
        $request->validate([
            'timesheet_file' => 'required|file|mimes:csv,txt,xls,xlsx',
        ]);

        $file = $request->file('timesheet_file');
        $extension = strtolower($file->getClientOriginalExtension());
        $rows = [];

        if ($extension === 'csv' || $extension === 'txt') {
            $handle = fopen($file->getRealPath(), 'r');
            if ($handle === false) {
                return redirect()->back()->withErrors(['timesheet_file' => 'Unable to read uploaded file.']);
            }

            $headers = [];
            while (($line = fgetcsv($handle)) !== false) {
                if (count(array_filter($line, fn ($value) => trim((string) $value) !== '')) === 0) {
                    continue;
                }

                if (empty($headers)) {
                    $headers = array_map(fn ($value) => trim((string) $value), $line);
                    continue;
                }

                $rows[] = $line;
            }
            fclose($handle);
        } else {
            $xlsxReaderClass = '\\PhpOffice\\PhpSpreadsheet\\Reader\\Xlsx';
            $xlsReaderClass = '\\PhpOffice\\PhpSpreadsheet\\Reader\\Xls';
            $readerClass = $extension === 'xls' ? $xlsReaderClass : $xlsxReaderClass;

            if (! class_exists($readerClass)) {
                return redirect()->back()->withErrors(['timesheet_file' => 'Excel support requires phpoffice/phpspreadsheet.']);
            }

            $reader = new $readerClass();

            $spreadsheet = $reader->load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                $rowData = [];
                foreach ($cellIterator as $cell) {
                    $rowData[] = trim((string) $cell->getValue());
                }

                if ($rowIndex === 1) {
                    $headers = array_map(fn ($value) => trim((string) $value), $rowData);
                    continue;
                }

                if (count(array_filter($rowData, fn ($value) => trim((string) $value) !== '')) === 0) {
                    continue;
                }

                $rows[] = $rowData;
            }
        }

        if (empty($rows)) {
            return redirect()->back()->withErrors(['timesheet_file' => 'The uploaded file does not contain any valid attendance rows.']);
        }

        $imported = 0;
        DB::transaction(function () use ($rows, &$imported) {
            $records = [];
            foreach ($rows as $row) {
                $personId = $row[0] ?? null;
                $name = $row[1] ?? null;
                $department = $row[2] ?? null;
                $timeValue = $row[3] ?? null;
                $status = $row[4] ?? null;
                $checkpoint = $row[5] ?? null;
                $customName = $row[6] ?? null;
                $dataSource = $row[7] ?? null;
                $handlingType = $row[8] ?? null;
                $temperature = $row[9] ?? null;
                $abnormal = $row[10] ?? null;

                if (! $personId || ! $timeValue) {
                    continue;
                }

                $recordDate = null;
                try {
                    $recordDate = Carbon::parse($timeValue);
                } catch (\Exception $exception) {
                    $recordDate = null;
                }

                $employeeId = Employees::where('employee_code', $personId)
                    ->orWhere('employee_name', $name)
                    ->value('id');

                $records[] = [
                    'person_id' => $personId,
                    'employee_id' => $employeeId,
                    'name' => $name,
                    'department' => $department,
                    'recorded_at' => $recordDate,
                    'attendance_status' => $status,
                    'attendance_checkpoint' => $checkpoint,
                    'custom_name' => $customName,
                    'data_source' => $dataSource,
                    'handling_type' => $handlingType,
                    'temperature' => $temperature,
                    'abnormal' => $abnormal,
                    'imported_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (! empty($records)) {
                AttendanceTimesheet::upsert(
                    $records,
                    ['person_id', 'recorded_at'],
                    [
                        'employee_id',
                        'name',
                        'department',
                        'attendance_status',
                        'attendance_checkpoint',
                        'custom_name',
                        'data_source',
                        'handling_type',
                        'temperature',
                        'abnormal',
                        'imported_by',
                        'updated_at',
                    ]
                );
                $imported = count($records);
            }
        });

        return redirect()
            ->route('attendance.timesheet.upload')
            ->with('success', tap("Imported {$imported} attendance rows successfully.", function () use ($imported) {
                AuditLog::record('attendance_import', null, [], ['imported_rows' => $imported], 'Attendance timesheet imported');
            }));
    }

    public function viewTimesheetRecords(Request $request)
    {
        $employees = Employees::where('status', 1)->orderBy('employee_name')->get();
        $types = AttendanceTimesheet::query()
            ->select('attendance_status')
            ->whereNotNull('attendance_status')
            ->distinct()
            ->orderBy('attendance_status')
            ->pluck('attendance_status');

        $query = AttendanceTimesheet::with('employee');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('attendance_status')) {
            $query->where('attendance_status', $request->attendance_status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('recorded_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('recorded_at', '<=', $request->end_date);
        }

        if ($request->filled('time_period')) {
            switch ($request->time_period) {
                case 'morning':
                    $query->whereTime('recorded_at', '>=', '06:00:00')
                        ->whereTime('recorded_at', '<', '12:00:00');
                    break;
                case 'afternoon':
                    $query->whereTime('recorded_at', '>=', '12:00:00')
                        ->whereTime('recorded_at', '<', '18:00:00');
                    break;
                case 'evening':
                    $query->whereTime('recorded_at', '>=', '18:00:00')
                        ->whereTime('recorded_at', '<', '23:59:59');
                    break;
                case 'night':
                    $query->whereTime('recorded_at', '>=', '00:00:00')
                        ->whereTime('recorded_at', '<', '06:00:00');
                    break;
            }
        }

        $records = $query->orderByDesc('recorded_at')->paginate(25)->withQueryString();

        return view('attendance.timesheet_records', compact('employees', 'types', 'records'));
    }

    public function attendanceProcessing(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());
        $employees = Employees::where('status', 1)
            ->orderBy('employee_name')
            ->get(['id', 'employee_name', 'employee_code']);

        return view('attendance.processing', compact('startDate', 'endDate', 'employees'));
    }

    public function attendanceSummary(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $employees = Employees::where('status', 1)
            ->with(['groupAssignments', 'department', 'designation'])
            ->orderBy('employee_name')
            ->get();

        $leaveRequests = LeaveRequest::where('status', 'approved')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($subQ) use ($startDate, $endDate) {
                      $subQ->whereDate('start_date', '<=', $startDate)
                           ->whereDate('end_date', '>=', $endDate);
                  });
            })
            ->get()
            ->groupBy('employee_id');

        $holidayMap = CompanyHoliday::where('status', true)
            ->whereBetween('holiday_date', [$startDate, $endDate])
            ->get()
            ->keyBy(fn ($holiday) => $holiday->holiday_date->toDateString());

        $summary = [];
        foreach ($employees as $employee) {
            $summary[$employee->id] = [
                'employee' => $employee,
                'total_working_days' => 0,
                'present_days' => 0,
                'absent_days' => 0,
                'leave_days' => 0,
                'half_days' => 0,
                'holidays' => 0,
                'off_days' => 0,
                'holiday_type_counts' => [
                    'company' => 0,
                    'public' => 0,
                    'special' => 0,
                ],
                'late_count' => 0,
                'total_late_min' => 0,
                'early_out_count' => 0,
                'total_early_out_min' => 0,
                'total_work_hrs' => 0,
                'total_ot_hrs' => 0,
                'missed_punch_days' => 0,
                'attendance_percent' => 0,
            ];
        }

        $assignments = ShiftScheduleAssignment::whereBetween('scheduled_date', [$startDate, $endDate])
            ->with('shift')
            ->get();

        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            foreach ($employees as $employee) {
                /** @var \App\Models\Employees $employee */
                $employeeGroupIds = $employee->activeShiftGroupIdsOn($currentDate);
                $dayAssignments = $assignments->filter(function ($a) use ($currentDate, $employeeGroupIds) {
                    return $a->scheduled_date === $currentDate->toDateString()
                        && in_array($a->shifts_group_id, $employeeGroupIds);
                });

                $shiftAssignment = $dayAssignments->first(fn ($a) =>
                    $a->assignment_type === 'work' && !is_null($a->shift_id) && !is_null($a->shift)
                );

                $offOnDate = $dayAssignments->contains(fn ($a) =>
                    $a->assignment_type === 'off'
                );

                $holidayOnDate = $holidayMap->get($currentDate->toDateString());
                $weekendOnDate = $currentDate->isWeekend() && !$shiftAssignment && !$offOnDate && !$holidayOnDate;

                if ($shiftAssignment && $shiftAssignment->shift) {
                    $summary[$employee->id]['total_working_days']++;
                }

                $leaveOnDate = null;
                if (isset($leaveRequests[$employee->id])) {
                    $leaveOnDate = $leaveRequests[$employee->id]->first(fn ($lr) =>
                        $currentDate->between(
                            Carbon::parse($lr->start_date),
                            Carbon::parse($lr->end_date)
                        )
                    );
                }

                if ($offOnDate) {
                    $summary[$employee->id]['off_days']++;
                    continue;
                }

                if ($holidayOnDate) {
                    $summary[$employee->id]['holidays']++;
                    $holidayType = strtolower($holidayOnDate->holiday_type ?? 'company');
                    $summary[$employee->id]['holiday_type_counts'][$holidayType] = ($summary[$employee->id]['holiday_type_counts'][$holidayType] ?? 0) + 1;
                    if ($shiftAssignment && $shiftAssignment->shift) {
                        $summary[$employee->id]['total_working_days']--;
                    }
                    continue;
                }

                if ($weekendOnDate) {
                    $summary[$employee->id]['off_days']++;
                    continue;
                }

                if ($leaveOnDate) {
                    $summary[$employee->id]['leave_days']++;
                    continue;
                }

                $metrics = $this->buildAttendanceMetrics($employee, $currentDate, $shiftAssignment, $leaveOnDate, $offOnDate, $holidayOnDate, $weekendOnDate);

                if (($metrics['late_min'] ?? 0) > 0) {
                    $summary[$employee->id]['late_count']++;
                }

                if (($metrics['early_out_min'] ?? 0) > 0) {
                    $summary[$employee->id]['early_out_count']++;
                }

                $summary[$employee->id]['total_late_min'] += $metrics['late_min'] ?? 0;
                $summary[$employee->id]['total_early_out_min'] += $metrics['early_out_min'] ?? 0;
                $summary[$employee->id]['total_work_hrs'] += $metrics['net_work_hours'] ?? 0;
                $summary[$employee->id]['total_ot_hrs'] += $metrics['ot_hours'] ?? 0;

                if (($metrics['status'] ?? null) === 'Present') {
                    $summary[$employee->id]['present_days']++;
                } elseif (($metrics['status'] ?? null) === 'Half Day') {
                    $summary[$employee->id]['half_days']++;
                } elseif (($metrics['status'] ?? null) === 'Incomplete') {
                    $summary[$employee->id]['missed_punch_days']++;
                } elseif (($metrics['status'] ?? null) === 'Absent') {
                    $summary[$employee->id]['absent_days']++;
                }
            }

            $currentDate->addDay();
        }

        foreach ($summary as $employeeId => $data) {
            $attendanceScore = $data['present_days'] + ($data['half_days'] * 0.5);
            $summary[$employeeId]['total_work_hrs'] = round($data['total_work_hrs'], 2);
            $summary[$employeeId]['total_ot_hrs'] = round($data['total_ot_hrs'], 2);
            $summary[$employeeId]['attendance_percent'] = $data['total_working_days'] > 0
                ? round(($attendanceScore / $data['total_working_days']) * 100, 2)
                : 0;
        }

        return view('attendance.summary', compact('summary', 'startDate', 'endDate'));
    }

    public function attendanceDetail(Request $request, $employeeId)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $employee = Employees::with(['groupAssignments', 'attendanceRecords', 'department', 'designation'])
            ->findOrFail($employeeId);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $assignments = ShiftScheduleAssignment::whereBetween('scheduled_date', [$startDate, $endDate])
            ->with('shift')
            ->get();

        $leaveRequests = LeaveRequest::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($subQ) use ($startDate, $endDate) {
                      $subQ->whereDate('start_date', '<=', $startDate)
                           ->whereDate('end_date', '>=', $endDate);
                  });
            })
            ->get();

        $holidayMap = CompanyHoliday::where('status', true)
            ->whereBetween('holiday_date', [$startDate, $endDate])
            ->get()
            ->keyBy(fn ($holiday) => $holiday->holiday_date->toDateString());

        $details = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->toDateString();

            // Check leave
            $leaveOnDate = $leaveRequests->filter(fn ($lr) =>
                $currentDate->between(
                    Carbon::parse($lr->start_date),
                    Carbon::parse($lr->end_date)
                )
            )->first();

            $employeeGroupIds = $employee->activeShiftGroupIdsOn($currentDate);

            $dayAssignments = $assignments->filter(fn ($a) =>
                $a->scheduled_date === $dateStr
                && in_array($a->shifts_group_id, $employeeGroupIds)
            );

            $shiftOnDate = $dayAssignments->first(fn ($a) =>
                $a->assignment_type === 'work' && !is_null($a->shift_id) && !is_null($a->shift)
            );

            $offOnDate = $dayAssignments->contains(fn ($a) =>
                $a->assignment_type === 'off'
            );

            $holidayOnDate = $holidayMap->get($dateStr);
            $weekendOnDate = $currentDate->isWeekend() && !$shiftOnDate && !$offOnDate && !$holidayOnDate;

            $attendanceMetrics = $this->buildAttendanceMetrics($employee, $currentDate, $shiftOnDate, $leaveOnDate, $offOnDate, $holidayOnDate, $weekendOnDate);

            $details[] = [
                'date' => $currentDate->copy(),
                'shift' => $shiftOnDate,
                'off_duty' => $offOnDate,
                'holiday' => $holidayOnDate,
                'weekend' => $weekendOnDate,
                'leave' => $leaveOnDate,
                'attendance' => $attendanceMetrics,
            ];

            $currentDate->addDay();
        }

        return view('attendance.detail', compact('employee', 'details', 'startDate', 'endDate'));
    }

    private function buildAttendanceMetrics($employee, $date, $shiftAssignment, $leaveOnDate = null, $offOnDate = false, $holidayOnDate = null, $weekendOnDate = false)
    {
        $base = [
            'employee_code' => $employee->employee_code,
            'employee_name' => $employee->employee_name,
            'department' => optional($employee->department)->department_name,
            'designation' => optional($employee->designation)->designation_name,
            'date' => $date->copy(),
            'shift_name' => null,
            'shift_start' => null,
            'shift_end' => null,
            'check_in' => null,
            'check_out' => null,
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
            'is_leave' => false,
            'is_off_duty' => false,
            'is_holiday' => false,
            'holiday_type' => null,
            'holiday_color' => 'primary',
            'is_missed_punch' => false,
            'is_weekend' => false,
        ];

        if ($holidayOnDate) {
            $base['is_holiday'] = true;
            $base['holiday_type'] = strtolower($holidayOnDate->holiday_type ?? 'company');
            $base['holiday_color'] = match ($base['holiday_type']) {
                'public' => 'danger',
                'special' => 'info',
                default => 'primary',
            };
            $base['status'] = 'Holiday';
            $base['remarks'] = $holidayOnDate->holiday_name . ($holidayOnDate->description ? ' - ' . $holidayOnDate->description : '');

            return $base;
        }

        if ($weekendOnDate) {
            $base['is_weekend'] = true;
            $base['status'] = 'Weekend';
            $base['remarks'] = 'Weekend off';

            return $base;
        }

        if ($leaveOnDate) {
            $base['is_leave'] = true;
            $base['status'] = 'Leave';
            $base['remarks'] = $leaveOnDate->reason ?: 'Approved leave';

            return $base;
        }

        if ($offOnDate) {
            $base['is_off_duty'] = true;
            $base['status'] = 'Off Duty';
            $base['remarks'] = 'Rostered off day';

            return $base;
        }

        if (!$shiftAssignment || !$shiftAssignment->shift) {
            return $base;
        }

        $correction = AttendanceCorrection::where('employee_id', $employee->id)
            ->whereDate('correction_date', $date->toDateString())
            ->where('status', true)
            ->latest('updated_at')
            ->first();

        $shift = $shiftAssignment->shift;
        $base['shift_name'] = $shift->shift_name;
        $startTime = Carbon::parse($shift->start_time);
        $endTime = Carbon::parse($shift->end_time);
        $isOvernight = $endTime->lessThan($startTime);
        $base['is_overnight'] = $isOvernight;

        if ($isOvernight) {
            $shiftStartDt = $date->copy()->setTimeFromTimeString($shift->start_time);
            $shiftEndDt = $date->copy()->addDay()->setTimeFromTimeString($shift->end_time);
        } else {
            $shiftStartDt = $date->copy()->setTimeFromTimeString($shift->start_time);
            $shiftEndDt = $date->copy()->setTimeFromTimeString($shift->end_time);
        }

        $base['shift_start'] = $shiftStartDt->copy();
        $base['shift_end'] = $shiftEndDt->copy();

        $punches = AttendanceTimesheet::where('employee_id', $employee->id)
            ->whereBetween('recorded_at', [$shiftStartDt, $shiftEndDt])
            ->orderBy('recorded_at')
            ->get();

        $base['punch_count'] = $punches->count();
        $base['break_min'] = (int) ($shift->break_minutes ?? 0);

        if ($correction && $correction->corrected_check_in && $correction->corrected_check_out) {
            $checkIn = $correction->corrected_check_in;
            $checkOut = $correction->corrected_check_out;
            $base['check_in'] = $checkIn;
            $base['check_out'] = $checkOut;
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

            $base['remarks'] = 'Corrected mispunch: ' . $correction->reason;

            return $base;
        }

        if ($punches->isEmpty()) {
            $base['status'] = 'Absent';
            $base['remarks'] = 'No punch';

            return $base;
        }

        $checkIn = $punches->first()->recorded_at;
        $checkOut = $punches->count() > 1 ? $punches->last()->recorded_at : null;

        $base['check_in'] = $checkIn;
        $base['check_out'] = $checkOut;
        $base['late_min'] = max(0, $shiftStartDt->diffInMinutes($checkIn, false));

        if ($checkOut) {
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

            $remarks = [];
            if ($base['late_min'] > 0) {
                $remarks[] = 'Late by ' . $base['late_min'] . ' min';
            }
            if ($base['early_out_min'] > 0) {
                $remarks[] = 'Early out by ' . $base['early_out_min'] . ' min';
            }
            if ($base['ot_min'] > 0) {
                $remarks[] = 'OT ' . $base['ot_min'] . ' min';
            }

            $base['remarks'] = empty($remarks) ? 'OK' : implode(', ', $remarks);
        } else {
            $base['status'] = 'Incomplete';
            $base['is_missed_punch'] = true;
            $base['remarks'] = 'Missed punch';
        }

        return $base;
    }

    public function createLeaveRequest()
    {
        $employees = Employees::where('status', 1)->orderBy('employee_name')->get();

        return view('attendance.leave_request', compact('employees'));
    }

    public function storeLeaveRequest(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type' => 'required|in:full_day,half_day,short_leave',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:1000',
        ]);

        LeaveRequest::create([
            'user_id' => auth()->id(),
            'employee_id' => $data['employee_id'],
            'leave_type' => $data['leave_type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'reason' => $data['reason'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('attendance.leave_request.index')
            ->with('success', 'Leave request submitted successfully.');
    }

    public function editLeaveRequest($id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        $employees = Employees::where('status', 1)->orderBy('employee_name')->get();

        return view('attendance.leave_request_edit', compact('leaveRequest', 'employees'));
    }

    public function updateLeaveRequest(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type' => 'required|in:full_day,half_day,short_leave',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:1000',
        ]);

        $leaveRequest->update([
            'employee_id' => $data['employee_id'],
            'leave_type' => $data['leave_type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'reason' => $data['reason'] ?? null,
        ]);

        return redirect()
            ->route('attendance.leave_request.index')
            ->with('success', 'Leave request updated successfully.');
    }

    public function approveLeaveRequest($id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        $leaveRequest->status = 'approved';
        $leaveRequest->save();

        return redirect()
            ->route('attendance.leave_request.index')
            ->with('success', 'Leave request approved.');
    }
}
