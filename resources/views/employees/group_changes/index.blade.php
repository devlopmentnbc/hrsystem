@extends('layouts.app')

@section('title', 'Employee Group Changes')

@section('content')

<div class="content-card">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Employee Group Changes</h4>
            <p class="text-muted mb-0">Move employees between shift groups without editing the group master.</p>
        </div>
        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Back to Employees</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-4">{{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('employees.group-changes.index') }}" class="row g-3 align-items-end mb-4">
        <div class="col-lg-4 col-md-6">
            <label class="form-label fw-semibold">Employee</label>
            <select name="employee_id" class="form-select">
                <option value="">All employees</option>
                @foreach($employeeOptions as $employeeOption)
                    <option value="{{ $employeeOption->id }}" {{ $selectedEmployeeId === $employeeOption->id ? 'selected' : '' }}>
                        {{ $employeeOption->employee_name }} ({{ $employeeOption->employee_code }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-4 col-md-6">
            <label class="form-label fw-semibold">Current Group</label>
            <select name="current_group_id" class="form-select">
                <option value="">All active groups</option>
                @foreach($groupOptions->groupBy(fn ($group) => $group->category ?: 'Uncategorized') as $category => $groups)
                    <optgroup label="{{ $category }}">
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" {{ $selectedGroupId === $group->id ? 'selected' : '' }}>{{ $group->group_name }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 col-md-6">
            <label class="form-label fw-semibold">Effective Date</label>
            <input type="date" name="effective_date" class="form-control" value="{{ request('effective_date', $referenceDate->toDateString()) }}">
        </div>
        <div class="col-lg-2 col-md-6 d-flex gap-2">
            <button type="submit" class="btn btn-primary rounded-pill px-4 flex-grow-1">Filter</button>
            <a href="{{ route('employees.group-changes.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Employee Code</th>
                    <th>Employee Name</th>
                    <th>Current Group</th>
                    <th>Current Category</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    @php
                        $activeAssignments = $employee->currentGroupAssignments ?? collect();
                        $primaryAssignment = $activeAssignments->first();
                    @endphp
                    <tr>
                        <td>{{ $employee->employee_code }}</td>
                        <td>{{ $employee->employee_name }}</td>
                        <td>
                            @if($activeAssignments->count())
                                @foreach($activeAssignments as $assignment)
                                    <div>{{ $assignment->shiftGroup?->group_name ?? '-' }}</div>
                                @endforeach
                            @else
                                <span class="text-muted">No active group</span>
                            @endif
                        </td>
                        <td>{{ $primaryAssignment?->shiftGroup?->category ?? '-' }}</td>
                        <td>
                            @if($employee->status)
                                <span class="badge bg-success rounded-pill">Active</span>
                            @else
                                <span class="badge bg-danger rounded-pill">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('employees.group-changes.create', $employee) }}" class="btn btn-sm btn-primary rounded-pill px-3">Change Group</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No employees found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">
        {{ $employees->links() }}
    </div>

</div>

@endsection