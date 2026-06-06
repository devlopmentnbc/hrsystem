<?php

namespace App\Http\Controllers;

use App\Models\AttendanceCorrection;
use App\Models\AttendanceTimesheet;
use App\Models\AuditLog;
use App\Models\Employees;
use App\Models\ShiftScheduleAssignment;
use App\Support\AccessControl;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceCorrectionController extends Controller
{
    private function authorizePermission(string $permission): void
    {
        AccessControl::ensureAdminSetup();
        abort_unless(auth()->check() && auth()->user()->canAccess($permission), 403);
    }

    public function index(Request $request)
    {
        $this->authorizePermission('attendance_mispunch.view');

        $employees = Employees::where('status', 1)->orderBy('employee_name')->get();

        $candidateQuery = AttendanceTimesheet::query()
            ->selectRaw('employee_id, DATE(recorded_at) as correction_date, MIN(recorded_at) as first_punch, MAX(recorded_at) as last_punch, COUNT(*) as punch_count')
            ->whereNotNull('employee_id')
            ->groupBy('employee_id', DB::raw('DATE(recorded_at)'))
            ->havingRaw('COUNT(*) = 1');

        if ($request->filled('employee_id')) {
            $candidateQuery->where('employee_id', $request->employee_id);
        }

        if ($request->filled('start_date')) {
            $candidateQuery->whereDate('recorded_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $candidateQuery->whereDate('recorded_at', '<=', $request->end_date);
        }

        $mispunchCandidates = $candidateQuery
            ->orderByDesc('correction_date')
            ->paginate(15, ['*'], 'candidates_page')
            ->through(function ($row) {
                $row->employee = Employees::find($row->employee_id);
                $row->existingCorrection = AttendanceCorrection::where('employee_id', $row->employee_id)
                    ->whereDate('correction_date', $row->correction_date)
                    ->first();

                return $row;
            });

        $corrections = AttendanceCorrection::with(['employee', 'creator', 'updater'])
            ->latest('correction_date')
            ->paginate(15, ['*'], 'corrections_page');

        return view('attendance.mispunch.index', compact('employees', 'mispunchCandidates', 'corrections'));
    }

    public function create(Request $request)
    {
        $this->authorizePermission('attendance_mispunch.create');

        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
        ]);

        $employee = Employees::findOrFail($data['employee_id']);
        $correctionDate = Carbon::parse($data['date']);
        $existingCorrection = AttendanceCorrection::where('employee_id', $employee->id)
            ->whereDate('correction_date', $correctionDate)
            ->first();

        if ($existingCorrection) {
            return redirect()->route('attendance.mispunch.edit', $existingCorrection);
        }

        $original = $this->buildOriginalPunchData($employee->id, $correctionDate);

        return view('attendance.mispunch.form', [
            'employee' => $employee,
            'correctionDate' => $correctionDate,
            'correction' => null,
            'original' => $original,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizePermission('attendance_mispunch.create');

        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'correction_date' => 'required|date',
            'corrected_check_in' => 'required|date',
            'corrected_check_out' => 'required|date|after:corrected_check_in',
            'reason' => 'required|string|max:1000',
        ]);

        $original = $this->buildOriginalPunchData((int) $data['employee_id'], Carbon::parse($data['correction_date']));

        $correction = AttendanceCorrection::create([
            'employee_id' => $data['employee_id'],
            'correction_date' => $data['correction_date'],
            'shift_schedule_assignment_id' => $original['shift_assignment_id'],
            'original_check_in' => $original['original_check_in'],
            'original_check_out' => $original['original_check_out'],
            'corrected_check_in' => $data['corrected_check_in'],
            'corrected_check_out' => $data['corrected_check_out'],
            'reason' => $data['reason'],
            'status' => true,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        AuditLog::record('mispunch_corrected', $correction, [
            'original_check_in' => optional($original['original_check_in'])?->toDateTimeString(),
            'original_check_out' => optional($original['original_check_out'])?->toDateTimeString(),
        ], [
            'corrected_check_in' => Carbon::parse($data['corrected_check_in'])->toDateTimeString(),
            'corrected_check_out' => Carbon::parse($data['corrected_check_out'])->toDateTimeString(),
            'reason' => $data['reason'],
        ], 'Mispunch corrected');

        return redirect()->route('attendance.mispunch.index')->with('success', 'Mispunch corrected successfully.');
    }

    public function edit(AttendanceCorrection $mispunch)
    {
        $this->authorizePermission('attendance_mispunch.update');

        return view('attendance.mispunch.form', [
            'employee' => $mispunch->employee,
            'correctionDate' => $mispunch->correction_date,
            'correction' => $mispunch,
            'original' => [
                'punches' => collect(),
                'original_check_in' => $mispunch->original_check_in,
                'original_check_out' => $mispunch->original_check_out,
                'shift_assignment_id' => $mispunch->shift_schedule_assignment_id,
            ],
        ]);
    }

    public function update(Request $request, AttendanceCorrection $mispunch)
    {
        $this->authorizePermission('attendance_mispunch.update');

        $data = $request->validate([
            'corrected_check_in' => 'required|date',
            'corrected_check_out' => 'required|date|after:corrected_check_in',
            'reason' => 'required|string|max:1000',
            'status' => 'required|boolean',
        ]);

        $oldValues = [
            'corrected_check_in' => optional($mispunch->corrected_check_in)?->toDateTimeString(),
            'corrected_check_out' => optional($mispunch->corrected_check_out)?->toDateTimeString(),
            'reason' => $mispunch->reason,
            'status' => $mispunch->status,
        ];

        $mispunch->update([
            'corrected_check_in' => $data['corrected_check_in'],
            'corrected_check_out' => $data['corrected_check_out'],
            'reason' => $data['reason'],
            'status' => (bool) $data['status'],
            'updated_by' => auth()->id(),
        ]);

        AuditLog::record('mispunch_updated', $mispunch, $oldValues, [
            'corrected_check_in' => Carbon::parse($data['corrected_check_in'])->toDateTimeString(),
            'corrected_check_out' => Carbon::parse($data['corrected_check_out'])->toDateTimeString(),
            'reason' => $data['reason'],
            'status' => (bool) $data['status'],
        ], 'Mispunch correction updated');

        return redirect()->route('attendance.mispunch.index')->with('success', 'Mispunch correction updated successfully.');
    }

    private function buildOriginalPunchData(int $employeeId, Carbon $date): array
    {
        $start = $date->copy()->startOfDay();
        $end = $date->copy()->endOfDay();

        $punches = AttendanceTimesheet::where('employee_id', $employeeId)
            ->whereBetween('recorded_at', [$start, $end])
            ->orderBy('recorded_at')
            ->get();

        $employee = Employees::with('groupAssignments')->find($employeeId);
        $shiftAssignment = null;
        if ($employee) {
            $groupIds = $employee->activeShiftGroupIdsOn($date);
            $shiftAssignment = ShiftScheduleAssignment::whereDate('scheduled_date', $date->toDateString())
                ->when(! empty($groupIds), fn ($query) => $query->whereIn('shifts_group_id', $groupIds), fn ($query) => $query->whereRaw('1 = 0'))
                ->where('assignment_type', 'work')
                ->first();
        }

        return [
            'punches' => $punches,
            'original_check_in' => $punches->first()?->recorded_at,
            'original_check_out' => $punches->count() > 1 ? $punches->last()?->recorded_at : null,
            'shift_assignment_id' => $shiftAssignment?->id,
        ];
    }
}
