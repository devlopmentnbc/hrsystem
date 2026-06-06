@extends('layouts.app')

@section('title', 'Edit Leave Request')

@section('content')
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0">Edit Leave Request</h5>
            <small class="text-muted">Update leave details before approval.</small>
        </div>
        <a href="{{ route('attendance.leave_request.index') }}" class="btn btn-outline-secondary">Back to Requests</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('attendance.leave_request.update', $leaveRequest->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Employee</label>
                <input type="text" id="employee-search" list="employee-options" name="employee_search" class="form-control mb-2" placeholder="Type to search employee..." autocomplete="off" value="{{ old('employee_search', $leaveRequest->employee?->employee_name ? $leaveRequest->employee->employee_name . ' (' . $leaveRequest->employee->employee_code . ')' : '') }}">
                <datalist id="employee-options">
                    @foreach($employees as $employee)
                        <option value="{{ $employee->employee_name }} ({{ $employee->employee_code }})" data-id="{{ $employee->id }}"></option>
                    @endforeach
                </datalist>
                <input type="hidden" name="employee_id" id="employee-id" value="{{ old('employee_id', $leaveRequest->employee_id) }}">
                <div class="form-text">Start typing employee name or code, select from suggestions.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Leave Type</label>
                <select name="leave_type" class="form-select">
                    <option value="">Select leave type</option>
                    <option value="full_day" {{ old('leave_type', $leaveRequest->leave_type) === 'full_day' ? 'selected' : '' }}>Full Day</option>
                    <option value="half_day" {{ old('leave_type', $leaveRequest->leave_type) === 'half_day' ? 'selected' : '' }}>Half Day</option>
                    <option value="short_leave" {{ old('leave_type', $leaveRequest->leave_type) === 'short_leave' ? 'selected' : '' }}>Short Leave</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $leaveRequest->start_date->toDateString()) }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $leaveRequest->end_date->toDateString()) }}">
            </div>

            <div class="col-12">
                <label class="form-label">Reason</label>
                <textarea name="reason" rows="4" class="form-control" placeholder="Optional reason for the request">{{ old('reason', $leaveRequest->reason) }}</textarea>
            </div>

            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary px-4">Save Changes</button>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const employeeSearch = document.getElementById('employee-search');
        const employeeIdInput = document.getElementById('employee-id');
        const employeeDatalist = document.getElementById('employee-options');
        
        // Build a map of employee display text to ID
        const employeeMap = {};
        if (employeeDatalist) {
            Array.from(employeeDatalist.options).forEach(option => {
                employeeMap[option.value.trim()] = option.getAttribute('data-id');
            });
        }

        if (employeeSearch && employeeIdInput) {
            // When user selects an option, update the hidden employee_id
            employeeSearch.addEventListener('change', function () {
                const selectedId = employeeMap[this.value.trim()] || '';
                employeeIdInput.value = selectedId;
            });
            
            // Also check on input for real-time feedback
            employeeSearch.addEventListener('input', function () {
                const selectedId = employeeMap[this.value.trim()] || '';
                if (selectedId) {
                    employeeIdInput.value = selectedId;
                }
            });
        }
    });
</script>
@endsection
