@extends('layouts.app')

@section('title', 'Change Employee Group')

@section('content')

<div class="content-card">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Change Employee Group</h4>
            <p class="text-muted mb-0">Create a dated group transfer for one employee and preserve history automatically.</p>
        </div>
        <a href="{{ route('employees.group-changes.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Back to Group Changes</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-4">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger rounded-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Employee Summary</h6>
                    <div class="mb-2"><span class="text-muted">Code:</span> {{ $employee->employee_code }}</div>
                    <div class="mb-2"><span class="text-muted">Name:</span> {{ $employee->employee_name }}</div>
                    <div class="mb-2"><span class="text-muted">Department:</span> {{ $employee->department?->department_name ?? '-' }}</div>
                    <div><span class="text-muted">Designation:</span> {{ $employee->designation?->designation_name ?? '-' }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Current Active Group</h6>
                    @forelse($activeAssignments as $assignment)
                        <div class="border rounded-3 p-3 mb-2">
                            <div class="fw-semibold">{{ $assignment->shiftGroup?->group_name ?? '-' }}</div>
                            <div class="small text-muted">Category: {{ $assignment->shiftGroup?->category ?? '-' }}</div>
                            <div class="small text-muted">Active from {{ $assignment->effective_start_date ? \Illuminate\Support\Carbon::parse($assignment->effective_start_date)->toDateString() : '-' }} to {{ $assignment->effective_end_date ? \Illuminate\Support\Carbon::parse($assignment->effective_end_date)->toDateString() : 'Open' }}</div>
                        </div>
                    @empty
                        <div class="text-muted">No active group assignment on {{ $referenceDate->toDateString() }}.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('employees.group-changes.store', $employee) }}" class="mb-4">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">New Shift Group</label>
                <select name="shifts_group_id" class="form-select" required>
                    <option value="">Select shift group</option>
                    @foreach($groups->groupBy(fn ($group) => $group->category ?: 'Uncategorized') as $category => $categoryGroups)
                        <optgroup label="{{ $category }}">
                            @foreach($categoryGroups as $group)
                                <option value="{{ $group->id }}" {{ old('shifts_group_id') == $group->id ? 'selected' : '' }}>{{ $group->group_name }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Effective Start Date</label>
                <input type="date" name="effective_start_date" class="form-control" value="{{ old('effective_start_date', now()->toDateString()) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Effective End Date</label>
                <input type="date" name="effective_end_date" class="form-control" value="{{ old('effective_end_date') }}">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Remarks</label>
                <textarea name="remarks" class="form-control" rows="3" placeholder="Optional note for the audit log only.">{{ old('remarks') }}</textarea>
            </div>
        </div>

        <div class="alert alert-info rounded-4 mt-4 mb-0">
            Saving this change closes the employee's currently active assignment one day before the new start date and adds the new group from the selected start date.
        </div>

        <div class="mt-4 text-end">
            <button type="submit" class="btn btn-primary rounded-pill px-4">Save Group Change</button>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Assignment History</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Group</th>
                            <th>Category</th>
                            <th>Effective Start</th>
                            <th>Effective End</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignmentHistory as $assignment)
                            <tr>
                                <td>{{ $assignment->shiftGroup?->group_name ?? '-' }}</td>
                                <td>{{ $assignment->shiftGroup?->category ?? '-' }}</td>
                                <td>{{ $assignment->effective_start_date ? \Illuminate\Support\Carbon::parse($assignment->effective_start_date)->toDateString() : '-' }}</td>
                                <td>{{ $assignment->effective_end_date ? \Illuminate\Support\Carbon::parse($assignment->effective_end_date)->toDateString() : 'Open' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No group history found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection