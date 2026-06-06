<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Employees;
use App\Models\ShiftGroupEmployeeAssignment;
use App\Models\ShiftsGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeGroupChangeController extends Controller
{
    public function index(Request $request)
    {
        $referenceDate = $request->filled('effective_date')
            ? Carbon::parse($request->input('effective_date'))->startOfDay()
            : Carbon::today();

        $selectedEmployeeId = $request->filled('employee_id') ? (int) $request->input('employee_id') : null;
        $selectedGroupId = $request->filled('current_group_id') ? (int) $request->input('current_group_id') : null;

        $employeesQuery = Employees::with(['groupAssignments.shiftGroup'])
            ->orderBy('employee_name');

        if ($selectedEmployeeId) {
            $employeesQuery->whereKey($selectedEmployeeId);
        }

        if ($selectedGroupId) {
            $employeesQuery->whereHas('groupAssignments', function ($query) use ($selectedGroupId, $referenceDate) {
                $query->where('shifts_group_id', $selectedGroupId)
                    ->where(function ($innerQuery) use ($referenceDate) {
                        $innerQuery->whereNull('effective_start_date')
                            ->orWhereDate('effective_start_date', '<=', $referenceDate->toDateString());
                    })
                    ->where(function ($innerQuery) use ($referenceDate) {
                        $innerQuery->whereNull('effective_end_date')
                            ->orWhereDate('effective_end_date', '>=', $referenceDate->toDateString());
                    });
            });
        }

        $employees = $employeesQuery
            ->paginate(20)
            ->through(function (Employees $employee) use ($referenceDate) {
                $employee->currentGroupAssignments = $employee->activeGroupAssignmentsOn($referenceDate)
                    ->loadMissing('shiftGroup')
                    ->sortBy(fn (ShiftGroupEmployeeAssignment $assignment) => [
                        $assignment->shiftGroup?->category ?? '',
                        $assignment->shiftGroup?->group_name ?? '',
                    ])
                    ->values();

                return $employee;
            })
            ->withQueryString();

        $employeeOptions = Employees::orderBy('employee_name')
            ->get(['id', 'employee_name', 'employee_code']);

        $groupOptions = ShiftsGroup::where('status', true)
            ->orderBy('category')
            ->orderBy('group_name')
            ->get(['id', 'group_name', 'category']);

        return view('employees.group_changes.index', compact(
            'employees',
            'referenceDate',
            'employeeOptions',
            'groupOptions',
            'selectedEmployeeId',
            'selectedGroupId'
        ));
    }

    public function create(Employees $employee)
    {
        $referenceDate = Carbon::today();

        $employee->load(['groupAssignments.shiftGroup']);

        $activeAssignments = $employee->activeGroupAssignmentsOn($referenceDate)
            ->loadMissing('shiftGroup')
            ->sortBy('effective_start_date')
            ->values();

        $assignmentHistory = $employee->groupAssignments
            ->loadMissing('shiftGroup')
            ->sortByDesc('effective_start_date')
            ->values();

        $groups = ShiftsGroup::where('status', true)
            ->orderBy('category')
            ->orderBy('group_name')
            ->get();

        return view('employees.group_changes.form', compact('employee', 'activeAssignments', 'assignmentHistory', 'groups', 'referenceDate'));
    }

    public function store(Request $request, Employees $employee)
    {
        $data = $request->validate([
            'shifts_group_id' => 'required|exists:shifts_groups,id',
            'effective_start_date' => 'required|date',
            'effective_end_date' => 'nullable|date|after_or_equal:effective_start_date',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $newStart = Carbon::parse($data['effective_start_date'])->startOfDay();
        $newEnd = $request->filled('effective_end_date')
            ? Carbon::parse($data['effective_end_date'])->startOfDay()
            : null;

        $newGroup = ShiftsGroup::findOrFail($data['shifts_group_id']);

        $overlappingAssignments = ShiftGroupEmployeeAssignment::with('shiftGroup')
            ->where('employee_id', $employee->id)
            ->overlapping($newStart, $newEnd)
            ->orderBy('effective_start_date')
            ->get();

        $assignmentsToClose = $overlappingAssignments->filter(function (ShiftGroupEmployeeAssignment $assignment) use ($newStart) {
            $existingStart = $assignment->effective_start_date ? Carbon::parse($assignment->effective_start_date) : null;

            return ! $existingStart || $existingStart->lt($newStart);
        })->values();

        $blockingAssignments = $overlappingAssignments->reject(function (ShiftGroupEmployeeAssignment $assignment) use ($newStart) {
            $existingStart = $assignment->effective_start_date ? Carbon::parse($assignment->effective_start_date) : null;

            return ! $existingStart || $existingStart->lt($newStart);
        })->values();

        if ($blockingAssignments->isNotEmpty()) {
            throw ValidationException::withMessages([
                'effective_start_date' => $blockingAssignments->map(function (ShiftGroupEmployeeAssignment $assignment) {
                    $groupName = $assignment->shiftGroup?->group_name ?? 'another group';
                    $start = $assignment->effective_start_date ? Carbon::parse($assignment->effective_start_date)->toDateString() : 'open';
                    $end = $assignment->effective_end_date ? Carbon::parse($assignment->effective_end_date)->toDateString() : 'open';

                    return 'A future or same-day assignment already exists in ' . $groupName . ' from ' . $start . ' to ' . $end . '. Adjust that assignment first.';
                })->all(),
            ]);
        }

        $oldValues = [
            'closed_assignments' => $assignmentsToClose->map(fn (ShiftGroupEmployeeAssignment $assignment) => [
                'group' => $assignment->shiftGroup?->group_name,
                'effective_start_date' => $assignment->effective_start_date ? Carbon::parse($assignment->effective_start_date)->toDateString() : null,
                'effective_end_date' => $assignment->effective_end_date ? Carbon::parse($assignment->effective_end_date)->toDateString() : null,
            ])->all(),
        ];

        DB::transaction(function () use ($employee, $newStart, $newEnd, $newGroup, $assignmentsToClose) {
            foreach ($assignmentsToClose as $assignment) {
                $assignment->update([
                    'effective_end_date' => $newStart->copy()->subDay()->toDateString(),
                    'updated_at' => now(),
                ]);
            }

            ShiftGroupEmployeeAssignment::create([
                'employee_id' => $employee->id,
                'shifts_group_id' => $newGroup->id,
                'effective_start_date' => $newStart->toDateString(),
                'effective_end_date' => $newEnd?->toDateString(),
            ]);
        });

        AuditLog::record('employee_group_changed', $employee, $oldValues, [
            'new_group' => $newGroup->group_name,
            'new_group_category' => $newGroup->category,
            'effective_start_date' => $newStart->toDateString(),
            'effective_end_date' => $newEnd?->toDateString(),
            'remarks' => $data['remarks'] ?? null,
        ], 'Employee shift group changed');

        return redirect()
            ->route('employees.group-changes.create', $employee)
            ->with('success', 'Employee group updated successfully.');
    }
}