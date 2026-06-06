<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Models\Shifts;
use App\Models\ShiftsGroup;
use App\Models\ShiftSchedule;
use App\Models\ShiftScheduleAssignment;

class ShiftScheduleController extends Controller
{
    public function availability(Request $request)
    {
        $data = $request->validate([
            'group_category' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'ignore_schedule_id' => 'nullable|integer',
        ]);

        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);
        if ($start->gt($end)) {
            [$start, $end] = [$end, $start];
        }

        $query = ShiftSchedule::query()
            ->where('group_category', $data['group_category'])
            ->orderBy('start_date');

        if (!empty($data['ignore_schedule_id'])) {
            $query->where('id', '!=', $data['ignore_schedule_id']);
        }

        $schedules = $query->get();

        $bookedDates = [];
        $conflicts = [];

        foreach ($schedules as $schedule) {
            $scheduleStart = Carbon::parse($schedule->start_date);
            $scheduleEnd = Carbon::parse($schedule->end_date);

            if ($scheduleStart->lte($end) && $scheduleEnd->gte($start)) {
                $conflicts[] = [
                    'id' => $schedule->id,
                    'schedule_name' => $schedule->schedule_name,
                    'start_date' => $scheduleStart->toDateString(),
                    'end_date' => $scheduleEnd->toDateString(),
                    'is_active' => (bool) $schedule->is_active,
                ];
            }

            $cursor = $scheduleStart->copy()->max($start);
            $last = $scheduleEnd->copy()->min($end);
            while ($cursor->lte($last)) {
                $key = $cursor->toDateString();
                $bookedDates[$key][] = [
                    'id' => $schedule->id,
                    'schedule_name' => $schedule->schedule_name,
                    'is_active' => (bool) $schedule->is_active,
                    'start_date' => $scheduleStart->toDateString(),
                    'end_date' => $scheduleEnd->toDateString(),
                ];
                $cursor->addDay();
            }
        }

        return response()->json([
            'schedules' => $schedules->map(fn ($schedule) => [
                'id' => $schedule->id,
                'schedule_name' => $schedule->schedule_name,
                'group_category' => $schedule->group_category,
                'start_date' => Carbon::parse($schedule->start_date)->toDateString(),
                'end_date' => Carbon::parse($schedule->end_date)->toDateString(),
                'is_active' => (bool) $schedule->is_active,
            ])->values(),
            'conflicts' => $conflicts,
            'booked_dates' => $bookedDates,
        ]);
    }

    public function index(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfWeek()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->startOfWeek()->addDays(6)->toDateString());

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if ($start->gt($end)) {
            [$start, $end] = [$end, $start];
        }

        $days = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $days[] = $date->copy();
        }

        $selectedGroupCategory = $request->query('group_category', session()->getOldInput('group_category'));

        $allGroupCategories = ShiftsGroup::where('status', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->orderBy('category')
            ->pluck('category')
            ->unique();

        $groupsByCategory = ShiftsGroup::where('status', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->orderBy('group_name')
            ->get(['id', 'group_name', 'category'])
            ->groupBy('category')
            ->map(fn ($groups) => $groups->map(fn ($group) => [
                'id' => $group->id,
                'group_name' => $group->group_name,
            ])->values())
            ->toArray();

        $shifts = Shifts::orderBy('shift_name')->get();

        $shiftsGroupsQuery = ShiftsGroup::where('status', true)->orderBy('group_name');
        if ($selectedGroupCategory) {
            $shiftsGroupsQuery->where('category', $selectedGroupCategory);
        }
        $shiftsGroups = $shiftsGroupsQuery->get();

        $savedSchedules = ShiftSchedule::orderBy('created_at', 'desc')->get();

        return view('shifts.schedule', compact('shifts', 'shiftsGroups', 'days', 'startDate', 'endDate', 'savedSchedules', 'allGroupCategories', 'selectedGroupCategory', 'groupsByCategory'));
    }

    public function edit($id)
    {
        $schedule = ShiftSchedule::findOrFail($id);

        $start = \Illuminate\Support\Carbon::parse($schedule->start_date);
        $end = \Illuminate\Support\Carbon::parse($schedule->end_date);

        $days = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $days[] = $date->copy();
        }

        $allGroupCategories = ShiftsGroup::where('status', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->orderBy('category')
            ->pluck('category')
            ->unique();

        $groupsByCategory = ShiftsGroup::where('status', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->orderBy('group_name')
            ->get(['id', 'group_name', 'category'])
            ->groupBy('category')
            ->map(fn ($groups) => $groups->map(fn ($group) => [
                'id' => $group->id,
                'group_name' => $group->group_name,
            ])->values())
            ->toArray();

        $shifts = Shifts::orderBy('shift_name')->get();
        $shiftsGroups = ShiftsGroup::where('status', true)
            ->when($schedule->group_category, fn ($query) => $query->where('category', $schedule->group_category))
            ->orderBy('group_name')
            ->get();

        // Build work rows: for each distinct shift used in work assignments, collect groups per date
        $workAssignments = ShiftScheduleAssignment::where('shift_schedule_id', $schedule->id)
            ->where('assignment_type', 'work')
            ->get()
            ->groupBy('shift_id');

        $rows = [];
        foreach ($workAssignments as $shiftId => $assigns) {
            $row = ['shift_id' => $shiftId, 'days' => []];
            foreach ($assigns as $a) {
                $row['days'][$a->scheduled_date][] = $a->shifts_group_id;
            }
            $rows[] = $row;
        }

        // Consolidate off assignments (shift_id IS NULL) into a single off-row per date
        $offAssignments = ShiftScheduleAssignment::where('shift_schedule_id', $schedule->id)
            ->where('assignment_type', 'off')
            ->whereNull('shift_id')
            ->get();

        $offRow = ['days' => []];
        foreach ($offAssignments as $a) {
            $offRow['days'][$a->scheduled_date][] = $a->shifts_group_id;
        }

        $savedSchedules = ShiftSchedule::orderBy('created_at', 'desc')->get();

        return view('shifts.schedule_edit', compact('schedule', 'days', 'shifts', 'shiftsGroups', 'rows', 'offRow', 'allGroupCategories', 'groupsByCategory', 'savedSchedules'));
    }

    public function update(Request $request, $id)
    {
        $schedule = ShiftSchedule::findOrFail($id);

        $request->validate([
            'schedule_name' => 'required|string|max:255',
            'group_category' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'schedule.rows' => 'required|array|min:1',
            'schedule.rows.*.shift_id' => 'required|exists:shifts,id',
            'schedule.rows.*.days.*.work_groups' => 'nullable|array',
            'schedule.rows.*.days.*.work_groups.*' => 'nullable|exists:shifts_groups,id',
            'schedule.off_rows' => 'nullable|array',
            'schedule.off_rows.*.days' => 'nullable|array',
            'schedule.off_rows.*.days.*' => 'nullable|array',
            'schedule.off_rows.*.days.*.*' => 'nullable|exists:shifts_groups,id',
        ]);

        $this->ensureNoOverlap($request->group_category, $request->start_date, $request->end_date, $schedule->id);
        $this->ensureNoDuplicateAssignments($request);

        $schedule->update([
            'schedule_name' => $request->schedule_name,
            'group_category' => $request->group_category,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'last_updated_by' => auth()->id(),
            'last_updated_at' => now(),
        ]);

        // Remove existing assignments for this schedule and re-insert from payload
        ShiftScheduleAssignment::where('shift_schedule_id', $schedule->id)->delete();

        $assignments = [];
        foreach ($request->input('schedule.rows', []) as $rowIndex => $row) {
            $shiftId = $row['shift_id'] ?? null;
            if (! $shiftId || empty($row['days']) || ! is_array($row['days'])) {
                continue;
            }

            foreach ($row['days'] as $date => $dayData) {
                if (! $date || ! is_array($dayData)) {
                    continue;
                }

                $workGroups = $dayData['work_groups'] ?? [];
                if (is_array($workGroups)) {
                    foreach ($workGroups as $groupId) {
                        if (! $groupId) {
                            continue;
                        }

                        $assignments[] = [
                            'shift_schedule_id' => $schedule->id,
                            'shift_id' => $shiftId,
                            'scheduled_date' => $date,
                            'shifts_group_id' => $groupId,
                            'assignment_type' => 'work',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }
        }

        foreach ($request->input('schedule.off_rows', []) as $offRow) {
            if (empty($offRow['days']) || ! is_array($offRow['days'])) {
                continue;
            }

            foreach ($offRow['days'] as $date => $groupIds) {
                if (! $date || ! is_array($groupIds)) {
                    continue;
                }

                foreach ($groupIds as $groupId) {
                    if (! $groupId) {
                        continue;
                    }

                    $assignments[] = [
                        'shift_schedule_id' => $schedule->id,
                        'shift_id' => null,
                        'scheduled_date' => $date,
                        'shifts_group_id' => $groupId,
                        'assignment_type' => 'off',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        if (! empty($assignments)) {
            ShiftScheduleAssignment::insert($assignments);
        }

        return redirect()->route('shifts_schedule.index')->with('success', 'Schedule updated.');
    }

    public function toggleActive($id)
    {
        $schedule = ShiftSchedule::findOrFail($id);
        $schedule->is_active = ! $schedule->is_active;
        $schedule->last_updated_by = auth()->id();
        $schedule->last_updated_at = now();
        $schedule->save();

        return redirect()->back()->with('success', 'Schedule "' . $schedule->schedule_name . '" ' . ($schedule->is_active ? 'activated' : 'deactivated') . '.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'schedule_name' => 'required|string|max:255',
            'group_category' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'schedule.rows' => 'required|array|min:1',
            'schedule.rows.*.shift_id' => 'required|exists:shifts,id',
            'schedule.rows.*.days.*.work_groups' => 'nullable|array',
            'schedule.rows.*.days.*.work_groups.*' => 'nullable|exists:shifts_groups,id',
            'schedule.off_rows' => 'nullable|array',
            'schedule.off_rows.*.days' => 'nullable|array',
            'schedule.off_rows.*.days.*' => 'nullable|array',
            'schedule.off_rows.*.days.*.*' => 'nullable|exists:shifts_groups,id',
        ]);

        $this->ensureNoOverlap($request->group_category, $request->start_date, $request->end_date);
        $this->ensureNoDuplicateAssignments($request);

        $schedule = ShiftSchedule::create([
            'schedule_name' => $request->schedule_name,
            'group_category' => $request->group_category,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'created_by' => auth()->id(),
            'last_updated_by' => auth()->id(),
            'last_updated_at' => now(),
        ]);

        $assignments = [];
        foreach ($request->input('schedule.rows', []) as $rowIndex => $row) {
            $shiftId = $row['shift_id'] ?? null;
            if (! $shiftId || empty($row['days']) || ! is_array($row['days'])) {
                continue;
            }

            foreach ($row['days'] as $date => $dayData) {
                if (! $date || ! is_array($dayData)) {
                    continue;
                }

                $workGroups = $dayData['work_groups'] ?? [];
                if (is_array($workGroups)) {
                    foreach ($workGroups as $groupId) {
                        if (! $groupId) {
                            continue;
                        }

                        $assignments[] = [
                            'shift_schedule_id' => $schedule->id,
                            'shift_id' => $shiftId,
                            'scheduled_date' => $date,
                            'shifts_group_id' => $groupId,
                            'assignment_type' => 'work',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }
        }

        foreach ($request->input('schedule.off_rows', []) as $offRow) {
            if (empty($offRow['days']) || ! is_array($offRow['days'])) {
                continue;
            }

            foreach ($offRow['days'] as $date => $groupIds) {
                if (! $date || ! is_array($groupIds)) {
                    continue;
                }

                foreach ($groupIds as $groupId) {
                    if (! $groupId) {
                        continue;
                    }

                    $assignments[] = [
                        'shift_schedule_id' => $schedule->id,
                        'shift_id' => null,
                        'scheduled_date' => $date,
                        'shifts_group_id' => $groupId,
                        'assignment_type' => 'off',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        if (! empty($assignments)) {
            ShiftScheduleAssignment::insert($assignments);
        }

        return back()->with('success', 'Shift schedule "' . $schedule->schedule_name . '" saved successfully.');
    }

    private function ensureNoOverlap(string $groupCategory, string $startDate, string $endDate, ?int $ignoreScheduleId = null): void
    {
        $query = ShiftSchedule::query()
            ->where('group_category', $groupCategory)
            ->whereDate('start_date', '<=', $endDate)
            ->whereDate('end_date', '>=', $startDate);

        if ($ignoreScheduleId) {
            $query->where('id', '!=', $ignoreScheduleId);
        }

        $conflictingSchedule = $query->first();

        if ($conflictingSchedule) {
            $existingStart = Carbon::parse($conflictingSchedule->start_date)->toDateString();
            $existingEnd = Carbon::parse($conflictingSchedule->end_date)->toDateString();

            throw \Illuminate\Validation\ValidationException::withMessages([
                'group_category' => 'A schedule already exists for category "' . $groupCategory . '" during ' . $existingStart . ' to ' . $existingEnd . '. Overlapping schedules are not allowed.',
            ]);
        }
    }

    private function ensureNoDuplicateAssignments(Request $request): void
    {
        $seen = [];
        $groupNames = ShiftsGroup::pluck('group_name', 'id');

        foreach ($request->input('schedule.rows', []) as $rowIndex => $row) {
            foreach (($row['days'] ?? []) as $date => $dayData) {
                foreach (($dayData['work_groups'] ?? []) as $groupId) {
                    if (! $groupId) {
                        continue;
                    }

                    $key = $date . '|' . $groupId;
                    if (isset($seen[$key])) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'schedule.rows' => 'Duplicate assignment found for group "' . ($groupNames[$groupId] ?? ('#' . $groupId)) . '" on ' . $date . '. Each group can only appear once per date in the schedule.',
                        ]);
                    }

                    $seen[$key] = 'work:' . $rowIndex;
                }
            }
        }

        foreach ($request->input('schedule.off_rows', []) as $rowIndex => $row) {
            foreach (($row['days'] ?? []) as $date => $groupIds) {
                foreach (($groupIds ?? []) as $groupId) {
                    if (! $groupId) {
                        continue;
                    }

                    $key = $date . '|' . $groupId;
                    if (isset($seen[$key])) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'schedule.off_rows' => 'Duplicate assignment found for group "' . ($groupNames[$groupId] ?? ('#' . $groupId)) . '" on ' . $date . '. A group cannot be assigned as both working/off or repeated on the same date.',
                        ]);
                    }

                    $seen[$key] = 'off:' . $rowIndex;
                }
            }
        }
    }
}
