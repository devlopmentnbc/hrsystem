@extends('layouts.app')

@section('title', 'Leave Requests')

@section('content')
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0">Leave Requests</h5>
            <small class="text-muted">Review and manage leave requests. Edit details or approve pending requests.</small>
        </div>
        <a href="{{ route('attendance.leave_request.create') }}" class="btn btn-primary">New Leave Request</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Employee</th>
                    <th>Leave Type</th>
                    <th>Period</th>
                    <th>Status</th>
                    <th>Requested</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaveRequests as $request)
                    <tr>
                        <td>{{ $request->employee?->employee_name ?? 'Unknown' }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $request->leave_type)) }}</td>
                        <td>{{ $request->start_date->format('Y-m-d') }} → {{ $request->end_date->format('Y-m-d') }}</td>
                        <td>{{ ucfirst($request->status) }}</td>
                        <td>{{ $request->created_at->format('Y-m-d') }}</td>
                        <td class="text-end">
                            <a href="{{ route('attendance.leave_request.edit', $request->id) }}" class="btn btn-sm btn-outline-primary me-2">Edit</a>
                            @if($request->status !== 'approved')
                                <form action="{{ route('attendance.leave_request.approve', $request->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No leave requests found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
