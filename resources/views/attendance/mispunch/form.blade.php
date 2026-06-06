@extends('layouts.app')

@section('title', $correction ? 'Edit Mispunch Correction' : 'Create Mispunch Correction')

@section('content')
<div class="content-card">
    <h4 class="fw-bold mb-4">{{ $correction ? 'Edit Mispunch Correction' : 'Create Mispunch Correction' }}</h4>

    @if ($errors->any())
        <div class="alert alert-danger rounded-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card"><div class="card-body">
                <h6 class="text-muted mb-2">Employee</h6>
                <div class="fw-semibold">{{ $employee->employee_name }}</div>
                <small>{{ $employee->employee_code }}</small>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card"><div class="card-body">
                <h6 class="text-muted mb-2">Correction Date</h6>
                <div class="fw-semibold">{{ $correctionDate->format('Y-m-d') }}</div>
            </div></div>
        </div>
    </div>

    <div class="alert alert-info rounded-4">
        <strong>Original punches:</strong>
        @if($original['punches']->count())
            {{ $original['punches']->pluck('recorded_at')->map(fn($d) => $d->format('Y-m-d H:i:s'))->implode(' | ') }}
        @else
            No raw punches found for this date.
        @endif
    </div>

    <form method="POST" action="{{ $correction ? route('attendance.mispunch.update', $correction) : route('attendance.mispunch.store') }}">
        @csrf
        @if($correction)
            @method('PUT')
        @endif

        @unless($correction)
            <input type="hidden" name="employee_id" value="{{ $employee->id }}">
            <input type="hidden" name="correction_date" value="{{ $correctionDate->toDateString() }}">
        @endunless

        <div class="row">
            <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold">Corrected Check In</label>
                <input type="datetime-local" name="corrected_check_in" class="form-control" value="{{ old('corrected_check_in', optional($correction?->corrected_check_in)->format('Y-m-d\TH:i') ?? optional($original['original_check_in'])->format('Y-m-d\TH:i')) }}" required>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold">Corrected Check Out</label>
                <input type="datetime-local" name="corrected_check_out" class="form-control" value="{{ old('corrected_check_out', optional($correction?->corrected_check_out)->format('Y-m-d\TH:i') ?? optional($original['original_check_out'])->format('Y-m-d\TH:i')) }}" required>
            </div>
            @if($correction)
                <div class="col-md-4 mb-4">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="1" {{ old('status', $correction->status ? '1' : '0') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('status', $correction->status ? '1' : '0') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            @endif
            <div class="col-12 mb-4">
                <label class="form-label fw-semibold">Reason</label>
                <textarea name="reason" class="form-control" rows="4" required>{{ old('reason', $correction?->reason) }}</textarea>
            </div>
        </div>

        <div class="d-flex gap-3">
            <button type="submit" class="btn btn-primary rounded-pill px-5">{{ $correction ? 'Update Correction' : 'Save Correction' }}</button>
            <a href="{{ route('attendance.mispunch.index') }}" class="btn btn-secondary rounded-pill px-5">Cancel</a>
        </div>
    </form>
</div>
@endsection
