@extends('layouts.app')

@section('title', 'Add Company Holiday')

@section('content')
<div class="content-card">
    <h4 class="fw-bold mb-4">Add Company Holiday</h4>

    <form method="POST" action="{{ route('attendance.holidays.store') }}">
        @csrf

        <div class="row">
            <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold">Holiday Name</label>
                <input type="text" name="holiday_name" class="form-control" value="{{ old('holiday_name') }}" required>
            </div>

            <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold">Holiday Date</label>
                <input type="date" name="holiday_date" class="form-control" value="{{ old('holiday_date') }}" required>
            </div>

            <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold">Holiday Type</label>
                <select name="holiday_type" class="form-select" required>
                    <option value="company" {{ old('holiday_type') === 'company' ? 'selected' : '' }}>Company</option>
                    <option value="public" {{ old('holiday_type') === 'public' ? 'selected' : '' }}>Public</option>
                    <option value="special" {{ old('holiday_type') === 'special' ? 'selected' : '' }}>Special</option>
                </select>
            </div>

            <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold">Status</label>
                <select name="status" class="form-select">
                    <option value="1" selected>Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <div class="col-12 mb-4">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
            </div>
        </div>

        <div class="d-flex gap-3">
            <button type="submit" class="btn btn-primary rounded-pill px-5">Save Holiday</button>
            <a href="{{ route('attendance.holidays.index') }}" class="btn btn-secondary rounded-pill px-5">Cancel</a>
        </div>
    </form>
</div>
@endsection
