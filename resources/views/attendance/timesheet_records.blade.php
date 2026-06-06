@extends('layouts.app')

@section('title', 'Time Sheet Records')

@section('content')
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0">Time Sheet Records</h5>
            <small class="text-muted">Filter attendance records by employee, type, date range, and time period.</small>
        </div>
        <div>
            <a href="{{ route('attendance.timesheet.upload') }}" class="btn btn-outline-secondary me-2">Upload Time Sheet</a>
            <a href="{{ route('attendance.leave_request.index') }}" class="btn btn-outline-secondary">Back to Leave Requests</a>
        </div>
    </div>

    <form method="GET" action="{{ route('attendance.timesheet.records') }}" class="row gy-3 mb-4">
        <div class="col-md-3">
            <label class="form-label fw-semibold">Employee</label>
            <select name="employee_id" class="form-select">
                <option value="">All employees</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->employee_name }} ({{ $employee->employee_code }})</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label fw-semibold">Record Type</label>
            <select name="attendance_status" class="form-select">
                <option value="">All statuses</option>
                @foreach($types as $type)
                    <option value="{{ $type }}" {{ request('attendance_status') === $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label fw-semibold">Start Date</label>
            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
        </div>

        <div class="col-md-2">
            <label class="form-label fw-semibold">End Date</label>
            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
        </div>

        <div class="col-md-2">
            <label class="form-label fw-semibold">Time Period</label>
            <select name="time_period" class="form-select">
                <option value="">Any period</option>
                <option value="morning" {{ request('time_period') === 'morning' ? 'selected' : '' }}>Morning (06:00-12:00)</option>
                <option value="afternoon" {{ request('time_period') === 'afternoon' ? 'selected' : '' }}>Afternoon (12:00-18:00)</option>
                <option value="evening" {{ request('time_period') === 'evening' ? 'selected' : '' }}>Evening (18:00-24:00)</option>
                <option value="night" {{ request('time_period') === 'night' ? 'selected' : '' }}>Night (00:00-06:00)</option>
            </select>
        </div>

        <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary rounded-pill px-4">Filter Records</button>
        </div>
    </form>

    <div class="mb-3">
        <span class="text-muted">Showing {{ $records->total() }} record{{ $records->total() === 1 ? '' : 's' }}.</span>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Person ID</th>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Recorded At</th>
                    <th>Status</th>
                    <th>Checkpoint</th>
                    <th>Source</th>
                    <th>Handling</th>
                    <th>Temp</th>
                    <th>Abnormal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td>{{ $loop->iteration + ($records->currentPage() - 1) * $records->perPage() }}</td>
                        <td>{{ $record->person_id }}</td>
                        <td>{{ optional($record->employee)->employee_name ?? $record->name }}</td>
                        <td>{{ $record->department }}</td>
                        <td>{{ optional($record->recorded_at)->format('Y-m-d H:i:s') }}</td>
                        <td>{{ $record->attendance_status }}</td>
                        <td>{{ $record->attendance_checkpoint }}</td>
                        <td>{{ $record->data_source }}</td>
                        <td>{{ $record->handling_type }}</td>
                        <td>{{ $record->temperature }}</td>
                        <td>{{ $record->abnormal }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center">No attendance records match the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">
        {{ $records->links() }}
    </div>
</div>
@endsection
