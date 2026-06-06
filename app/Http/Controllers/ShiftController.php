<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Shifts;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shifts::all();
        return view('shifts.index', compact('shifts'));
    }

    public function create()
    {
        return view('shifts.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'shift_name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($request) {
                    $isOvernight = $request->input('is_overnight_shift') == '1';
                    if ($isOvernight) {
                        return;
                    }

                    $start = Carbon::createFromFormat('H:i', $request->start_time);
                    $end = Carbon::createFromFormat('H:i', $value);

                    if (! $start || ! $end) {
                        return;
                    }

                    if (! $end->gt($start)) {
                        $fail('The end time must be a time after the start time.');
                    }
                },
            ],
            'break_minutes' => 'nullable|integer|min:0',
            'grace_period_minutes' => 'nullable|integer|min:0',
            'ot_start_after_minutes' => 'nullable|integer|min:0',
            'is_overnight_shift' => 'nullable|in:0,1',
            'full_day_hrs' => 'nullable|numeric|min:0',
            'half_day_hrs' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ];

        $messages = [
            'start_time.date_format' => 'The start time must be a valid time (HH:MM).',
            'end_time.date_format' => 'The end time must be a valid time (HH:MM).',
            'end_time.after' => 'The end time must be a time after the start time.',
        ];

        $request->validate($rules, $messages);

        Shifts::create([
            'shift_name' => $request->shift_name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'break_minutes' => $request->break_minutes ?? 0,
            'grace_period_minutes' => $request->grace_period_minutes ?? 0,
            'ot_start_after_minutes' => $request->ot_start_after_minutes ?? 0,
            'night_shift' => ($request->is_overnight_shift ?? 0) ? true : false,
            'full_day_hours' => $request->full_day_hrs ?? 8.00,
            'half_day_hours' => $request->half_day_hrs ?? 4.00,
            'remarks' => $request->description,
            'status' => true,
            'last_updated_by' => auth()->id(),
            'last_updated_at' => now(),
        ]);

        return redirect()->route('shifts.index')->with('success', 'Shift created successfully.');
    }

    public function edit($id)
    {
        $shift = Shifts::findOrFail($id);
        return view('shifts.edit', compact('shift'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'shift_name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($request) {
                    $isOvernight = $request->input('is_overnight_shift') == '1';
                    if ($isOvernight) {
                        return;
                    }

                    $start = Carbon::createFromFormat('H:i', $request->start_time);
                    $end = Carbon::createFromFormat('H:i', $value);

                    if (! $start || ! $end) {
                        return;
                    }

                    if (! $end->gt($start)) {
                        $fail('The end time must be a time after the start time.');
                    }
                },
            ],
            'break_minutes' => 'nullable|integer|min:0',
            'grace_period_minutes' => 'nullable|integer|min:0',
            'ot_start_after_minutes' => 'nullable|integer|min:0',
            'is_overnight_shift' => 'nullable|in:0,1',
            'full_day_hrs' => 'nullable|numeric|min:0',
            'half_day_hrs' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
        ];

        $messages = [
            'start_time.date_format' => 'The start time must be a valid time (HH:MM).',
            'end_time.date_format' => 'The end time must be a valid time (HH:MM).',
            'end_time.after' => 'The end time must be a time after the start time.',
        ];

        $request->validate($rules, $messages);

        $shift = Shifts::findOrFail($id);
        $shift->update([
            'shift_name' => $request->shift_name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'break_minutes' => $request->break_minutes ?? 0,
            'grace_period_minutes' => $request->grace_period_minutes ?? 0,
            'ot_start_after_minutes' => $request->ot_start_after_minutes ?? 0,
            'night_shift' => ($request->is_overnight_shift ?? 0) ? true : false,
            'full_day_hours' => $request->full_day_hrs ?? 8.00,
            'half_day_hours' => $request->half_day_hrs ?? 4.00,
            'remarks' => $request->description,
            'status' => $request->status ? true : false,
            'last_updated_by' => auth()->id(),
            'last_updated_at' => now(),
        ]);

        return redirect()->route('shifts.index')->with('success', 'Shift updated successfully.');
    }
}
