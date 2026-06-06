@extends('layouts.app')

@section('title', 'Add Shift')

@section('content')

    <div class="content-card">

        <h4 class="fw-bold mb-4">
            Add Shifts
        </h4>

        <form method="POST" action="{{ route('shifts.store') }}">

            @csrf

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">

                <!-- Shift Name -->
                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">

                        Shift Name

                    </label>

                    <input type="text" name="shift_name" class="form-control" required>

                </div>
 

                <!-- Start Time -->
                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">

                        Start Time
                    </label>
                    <input type="time" name="start_time" class="form-control" required>
                </div>
                <!-- End Time -->
                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">

                        End Time
                    </label>
                    <input type="time" name="end_time" class="form-control" required>
                </div>
                <!-- Break Minutes -->
                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold"> Break minutes </label>
                    <input type="number" name="break_minutes" class="form-control" required>
                </div>
                <!-- Grace Period minutes -->
                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold"> Grace Period minutes </label>
                    <input type="number" name="grace_period_minutes" class="form-control" required>
                </div>
                <!-- OT Start After Minutes -->
                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold"> OT Start After Minutes </label>
                    <input type="number" name="ot_start_after_minutes" class="form-control" required>
                </div>
                <!-- Full Day Hrs -->
                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold"> Full Day Hrs </label>
                    <input type="number" name="full_day_hrs" class="form-control" required>
                </div>
                <!-- Half Day Hrs -->
                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold"> Half Day Hrs </label>
                    <input type="number" name="half_day_hrs" class="form-control" required>
                </div>
                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">

                        Is Overnight Shift?

                    </label>

                    <select name="is_overnight_shift" class="form-select">

                        <option value="1">
                            Yes
                        </option>

                        <option value="0">
                            No
                        </option>

                    </select>

                </div>

                <!-- Description -->
                <div class="col-12 mb-4">

                    <label class="form-label fw-semibold">

                        Remarks

                    </label>

                    <textarea name="description" class="form-control" rows="4"></textarea>

                </div>

                <!-- Status -->
                <div class="col-md-4 mb-4">

                    <label class="form-label fw-semibold">

                        Status

                    </label>

                    <select name="status" class="form-select">

                        <option value="1">
                            Active
                        </option>

                        <option value="0">
                            Inactive
                        </option>

                    </select>

                </div>

            </div>

            <!-- Buttons -->
            <div class="d-flex gap-3">

                <button type="submit" class="btn btn-primary rounded-pill px-5">

                    Save Shift

                </button>

                <a href="{{ route('shifts.index') }}" class="btn btn-secondary rounded-pill px-5">

                    Cancel

                </a>

            </div>

        </form>

    </div>

@endsection