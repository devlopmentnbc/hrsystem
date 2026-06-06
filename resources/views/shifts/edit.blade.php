@extends('layouts.app')

@section('title', 'Edit Shift')

@section('content')

<div class="content-card">

    <h4 class="fw-bold mb-4">

        Edit Shift

    </h4>

    <form
        method="POST"
        action="{{ route('shifts.update', $shift->id) }}"
    >

        @csrf
        @method('PUT')
        @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

        <div class="row">

            <div class="col-md-6 mb-4">

                <label class="form-label fw-semibold">

                    Shift Name

                </label>

                <input
                    type="text"
                    name="shift_name"
                    class="form-control"
                    value="{{ old('shift_name', $shift->shift_name) }}"
                    required
                >

            </div>
 
            <div class="col-md-6 mb-4">

                <label class="form-label fw-semibold">

                    Start Time
                </label>
                <input
                    type="time"
                    name="start_time"
                    class="form-control"
                    value="{{ old('start_time', $shift->start_time) }}"
                    required
                >
            </div>
            <div class="col-md-6 mb-4">

                <label class="form-label fw-semibold">

                    End Time
                </label>
                <input
                    type="time"
                    name="end_time"
                    class="form-control"
                    value="{{ old('end_time', $shift->end_time) }}"
                    required
                >
            </div>

            <div class="col-md-6 mb-4">

                <label class="form-label fw-semibold"> Break minutes </label>
                <input
                    type="number"
                    name="break_minutes"
                    class="form-control"
                    value="{{ old('break_minutes', $shift->break_minutes) }}"
                    required
                >
            </div>
            <div class="col-md-6 mb-4">

                <label class="form-label fw-semibold"> Grace Period minutes </label>
                <input
                    type="number"
                    name="grace_period_minutes"
                    class="form-control"
                    value="{{ old('grace_period_minutes', $shift->grace_period_minutes) }}"
                    required
                >
            </div>
            <div class="col-md-6 mb-4">

                <label class="form-label fw-semibold"> OT Start After Minutes </label>
                <input
                    type="number"
                    name="ot_start_after_minutes"
                    class="form-control"
                    value="{{ old('ot_start_after_minutes', $shift->ot_start_after_minutes) }}"
                    required
                >
            </div>  

            <div class="col-md-6 mb-4">

                <label class="form-label fw-semibold"> Full Day Hrs </label>
                <input
                    type="number"
                    name="full_day_hours"
                    class="form-control"
                    value="{{ old('full_day_hours', $shift->full_day_hours) }}"
                    required
                >
            </div>  

                <div class="col-md-6 mb-4">
    
                        <label class="form-label fw-semibold"> Half Day Hrs </label>
                        <input
                            type="number"
                            name="half_day_hours"
                            class="form-control"
                            value="{{ old('half_day_hours', $shift->half_day_hours) }}"
                            required
                        >
                    </div>

                    <div class="col-md-6 mb-4">
                            
                        <label class="form-label fw-semibold"> is Overnight Shift? </label>
                        <select
                            name="is_overnight_shift"
                            class="form-select"
                        >
                        <option value="1" {{ old('is_overnight_shift', $shift->is_overnight_shift) == 1 ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('is_overnight_shift', $shift->is_overnight_shift) == 0 ? 'selected' : '' }}>No</option>
                        </select>
                    </div>  

            <div class="col-12 mb-4">

                <label class="form-label fw-semibold">

                    Remarks

                </label>

                <textarea
                    name="remarks"
                    rows="4"
                    class="form-control"
                >{{ old('remarks', $shift->remarks) }}</textarea>

            </div>

            <div class="col-md-4 mb-4">

                <label class="form-label fw-semibold">

                    Status

                </label>

                <select
                    name="status"
                    class="form-select"
                >

                    <option
                        value="1"
                        {{ $shift->status == 1 ? 'selected' : '' }}
                    >
                        Active
                    </option>

                    <option
                        value="0"
                        {{ $shift->status == 0 ? 'selected' : '' }}
                    >
                        Inactive
                    </option>

                </select>

            </div>

        </div>

        <div class="d-flex gap-3">

            <button
                type="submit"
                class="btn btn-primary rounded-pill px-5"
            >

                Update Shift

            </button>

            <a
                href="{{ route('shifts.index') }}"
                class="btn btn-secondary rounded-pill px-5"
            >

                Cancel

            </a>

        </div>

    </form>

</div>

@endsection 