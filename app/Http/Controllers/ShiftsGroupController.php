<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\ShiftsGroup;
use App\Models\Employees;
use App\Models\Shifts;
use App\Models\ShiftGroupEmployeeAssignment;

class ShiftsGroupController extends Controller
{
    public function index()
    {
        $shiftsGroups = ShiftsGroup::withCount(['activeEmployeeAssignments as employees_count', 'shifts'])->get();
        return view('shifts_groups.index', compact('shiftsGroups'));
    }

    public function create()
    {
        $employees = Employees::orderBy('employee_name')->get();
        $shifts = Shifts::orderBy('shift_name')->get();
        $categories = ShiftsGroup::whereNotNull('category')
            ->where('category', '!=', '')
            ->orderBy('category')
            ->pluck('category')
            ->unique();

        return view('shifts_groups.create', compact('employees', 'shifts', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'group_name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'new_category' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'status' => 'required|boolean',
            'assignment_start_date' => 'nullable|date',
            'assignment_end_date' => 'nullable|date|after_or_equal:assignment_start_date',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'integer|exists:employees,id',
            'shift_ids' => 'nullable|array',
            'shift_ids.*' => 'integer|exists:shifts,id',
        ]);

        $employeeIds = array_values(array_unique($request->input('employee_ids', [])));
        $shiftIds = array_values(array_unique($request->input('shift_ids', [])));
        [$assignmentStartDate, $assignmentEndDate] = $this->resolveAssignmentPeriod($request, $employeeIds);
        $this->ensureNoEmployeeGroupOverlap($employeeIds, $assignmentStartDate, $assignmentEndDate);

        DB::transaction(function () use ($request, $employeeIds, $shiftIds, $assignmentStartDate, $assignmentEndDate) {
            $group = ShiftsGroup::create([
                'group_name' => $request->group_name,
                'category' => $request->new_category ? $request->new_category : $request->category,
                'remarks' => $request->remarks,
                'status' => $request->status,
                'last_updated_by' => auth()->id(),
                'last_updated_at' => now(),
            ]);

            $this->replaceGroupAssignmentsForPeriod($group, $employeeIds, $assignmentStartDate, $assignmentEndDate);
            $group->shifts()->sync($shiftIds);
        });

        return redirect()->route('shifts_groups.index')->with('success', 'Shift group created successfully.');
    }

    public function edit(ShiftsGroup $shifts_group)
    {
        $employees = Employees::orderBy('employee_name')->get();
        $shifts = Shifts::orderBy('shift_name')->get();
        $categories = ShiftsGroup::whereNotNull('category')
            ->where('category', '!=', '')
            ->orderBy('category')
            ->pluck('category')
            ->unique();

        $selectedEmployeeIds = $shifts_group->activeEmployeeAssignments()->pluck('employee_id')->toArray();
        $selectedShiftIds = $shifts_group->shifts()->pluck('shifts.id')->toArray();
        $assignmentHistory = $shifts_group->groupAssignments()
            ->with('employee:id,employee_name,employee_code')
            ->orderByDesc('effective_start_date')
            ->get();

        return view('shifts_groups.edit', compact('shifts_group', 'employees', 'shifts', 'selectedEmployeeIds', 'selectedShiftIds', 'categories', 'assignmentHistory'));
    }

    public function update(Request $request, ShiftsGroup $shifts_group)
    {
        $request->validate([
            'group_name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'new_category' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'status' => 'required|boolean',
            'assignment_start_date' => 'nullable|date',
            'assignment_end_date' => 'nullable|date|after_or_equal:assignment_start_date',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'integer|exists:employees,id',
            'shift_ids' => 'nullable|array',
            'shift_ids.*' => 'integer|exists:shifts,id',
        ]);

        $employeeIds = array_values(array_unique($request->input('employee_ids', [])));
        $shiftIds = array_values(array_unique($request->input('shift_ids', [])));
        [$assignmentStartDate, $assignmentEndDate] = $this->resolveAssignmentPeriod($request, $employeeIds);
        $this->ensureNoEmployeeGroupOverlap($employeeIds, $assignmentStartDate, $assignmentEndDate, $shifts_group->id);

        DB::transaction(function () use ($request, $shifts_group, $employeeIds, $shiftIds, $assignmentStartDate, $assignmentEndDate) {
            $shifts_group->update([
                'group_name' => $request->group_name,
                'category' => $request->new_category ? $request->new_category : $request->category,
                'remarks' => $request->remarks,
                'status' => $request->status,
                'last_updated_by' => auth()->id(),
                'last_updated_at' => now(),
            ]);

            $this->replaceGroupAssignmentsForPeriod($shifts_group, $employeeIds, $assignmentStartDate, $assignmentEndDate);
            $shifts_group->shifts()->sync($shiftIds);
        });

        return redirect()->route('shifts_groups.index')->with('success', 'Shift group updated successfully.');
    }

    private function resolveAssignmentPeriod(Request $request, array $employeeIds): array
    {
        if (empty($employeeIds)) {
            return [null, null];
        }

        if (! $request->filled('assignment_start_date')) {
            throw ValidationException::withMessages([
                'assignment_start_date' => 'Effective start date is required when assigning employees to a shift group.',
            ]);
        }

        return [
            Carbon::parse($request->input('assignment_start_date'))->startOfDay(),
            $request->filled('assignment_end_date')
                ? Carbon::parse($request->input('assignment_end_date'))->startOfDay()
                : null,
        ];
    }

    private function ensureNoEmployeeGroupOverlap(array $employeeIds, ?Carbon $startDate, ?Carbon $endDate, ?int $ignoreGroupId = null): void
    {
        if (empty($employeeIds) || ! $startDate) {
            return;
        }

        $conflicts = ShiftGroupEmployeeAssignment::with([
                'employee:id,employee_name,employee_code',
                'shiftGroup:id,group_name',
            ])
            ->whereIn('employee_id', $employeeIds)
            ->when($ignoreGroupId, fn ($query) => $query->where('shifts_group_id', '!=', $ignoreGroupId))
            ->overlapping($startDate, $endDate)
            ->get();

        if ($conflicts->isEmpty()) {
            return;
        }

        $messages = $conflicts
            ->map(function (ShiftGroupEmployeeAssignment $assignment) {
                $employeeName = $assignment->employee?->employee_name ?? 'Employee';
                $employeeCode = $assignment->employee?->employee_code ? ' (' . $assignment->employee->employee_code . ')' : '';
                $groupName = $assignment->shiftGroup?->group_name ?? 'another shift group';
                $start = $assignment->effective_start_date ? Carbon::parse($assignment->effective_start_date)->toDateString() : 'open';
                $end = $assignment->effective_end_date ? Carbon::parse($assignment->effective_end_date)->toDateString() : 'open';

                return $employeeName . $employeeCode . ' already has an overlapping assignment in ' . $groupName . ' from ' . $start . ' to ' . $end . '.';
            })
            ->unique()
            ->values()
            ->all();

        throw ValidationException::withMessages([
            'employee_ids' => $messages,
        ]);
    }

    private function replaceGroupAssignmentsForPeriod(ShiftsGroup $group, array $employeeIds, ?Carbon $startDate, ?Carbon $endDate): void
    {
        if (! $startDate) {
            return;
        }

        $overlappingAssignments = ShiftGroupEmployeeAssignment::where('shifts_group_id', $group->id)
            ->overlapping($startDate, $endDate)
            ->get();

        $preservedRows = [];
        foreach ($overlappingAssignments as $assignment) {
            $existingStart = $assignment->effective_start_date ? Carbon::parse($assignment->effective_start_date) : null;
            $existingEnd = $assignment->effective_end_date ? Carbon::parse($assignment->effective_end_date) : null;

            if ($existingStart && $existingStart->lt($startDate)) {
                $preservedRows[] = [
                    'shifts_group_id' => $assignment->shifts_group_id,
                    'employee_id' => $assignment->employee_id,
                    'effective_start_date' => $existingStart->toDateString(),
                    'effective_end_date' => $startDate->copy()->subDay()->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if ($endDate && (! $existingEnd || $existingEnd->gt($endDate))) {
                $tailStart = $endDate->copy()->addDay();

                $preservedRows[] = [
                    'shifts_group_id' => $assignment->shifts_group_id,
                    'employee_id' => $assignment->employee_id,
                    'effective_start_date' => $tailStart->toDateString(),
                    'effective_end_date' => $existingEnd?->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $assignment->delete();
        }

        if (! empty($preservedRows)) {
            ShiftGroupEmployeeAssignment::insert($preservedRows);
        }

        if (empty($employeeIds)) {
            return;
        }

        ShiftGroupEmployeeAssignment::insert(array_map(fn ($employeeId) => [
            'shifts_group_id' => $group->id,
            'employee_id' => $employeeId,
            'effective_start_date' => $startDate->toDateString(),
            'effective_end_date' => $endDate?->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ], $employeeIds));
    }
}
