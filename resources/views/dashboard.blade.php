@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="row g-4 mb-4">

    <div class="col-md-6 col-xl-3">
        <div class="content-card text-center">
            <i class="bi bi-people-fill text-primary fs-1"></i>
            <h2 class="fw-bold mt-3">{{ $totalEmployees }}</h2>
            <p class="text-muted mb-0">Active Employees</p>
            <small class="text-muted">Across {{ $departmentCount }} departments</small>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="content-card text-center">
            <i class="bi bi-fingerprint text-success fs-1"></i>
            <h2 class="fw-bold mt-3">{{ $presentOnReferenceDate }}</h2>
            <p class="text-muted mb-0">Present on Latest Attendance Date</p>
            <small class="text-muted">{{ $referenceDate ? \Illuminate\Support\Carbon::parse($referenceDate)->format('Y-m-d') : 'No attendance imported yet' }}</small>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="content-card text-center">
            <i class="bi bi-person-x-fill text-danger fs-1"></i>
            <h2 class="fw-bold mt-3">{{ $absentOnReferenceDate }}</h2>
            <p class="text-muted mb-0">Missing on Latest Attendance Date</p>
            <small class="text-muted">Compared against active employee master</small>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="content-card text-center">
            <i class="bi bi-calendar-check text-warning fs-1"></i>
            <h2 class="fw-bold mt-3">{{ $pendingLeaves }}</h2>
            <p class="text-muted mb-0">Pending Leave Requests</p>
            <small class="text-muted">Approved: {{ $approvedLeaves }}</small>
        </div>
    </div>

</div>

<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="content-card text-center">
            <i class="bi bi-file-earmark-bar-graph text-info fs-1"></i>
            <h2 class="fw-bold mt-3">{{ $attendanceRowsThisMonth }}</h2>
            <p class="text-muted mb-0">Attendance Rows This Month</p>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="content-card text-center">
            <i class="bi bi-person-badge text-secondary fs-1"></i>
            <h2 class="fw-bold mt-3">{{ $activeUsers }}</h2>
            <p class="text-muted mb-0">Active System Users</p>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="content-card text-center">
            <i class="bi bi-calendar-event text-primary fs-1"></i>
            <h2 class="fw-bold mt-3">{{ $holidayCount }}</h2>
            <p class="text-muted mb-0">Active Company Holidays</p>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="content-card text-center">
            <i class="bi bi-diagram-3 text-success fs-1"></i>
            <h2 class="fw-bold mt-3">{{ $shiftGroupCount }}</h2>
            <p class="text-muted mb-0">Shift Groups</p>
            <small class="text-muted">Shifts: {{ $shiftCount }}</small>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="content-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">Recent Attendance</h5>
                <a href="{{ route('attendance.timesheet.records') }}" class="btn btn-primary rounded-pill px-4">View All</a>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>First Punch</th>
                            <th>Last Punch</th>
                            <th>Punches</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAttendance as $attendance)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $attendance['employee_name'] }}</div>
                                    <small class="text-muted">{{ $attendance['employee_code'] }}</small>
                                </td>
                                <td>{{ $attendance['attendance_date']->format('Y-m-d') }}</td>
                                <td>{{ $attendance['first_punch']->format('h:i A') }}</td>
                                <td>{{ $attendance['last_punch']->format('h:i A') }}</td>
                                <td>{{ $attendance['punch_count'] }}</td>
                                <td>
                                    <span class="badge bg-{{ $attendance['badge'] }} rounded-pill px-3 py-2">
                                        {{ $attendance['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No attendance records available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="content-card h-100 mb-4">
            <h5 class="fw-bold mb-4">Recent Leave Requests</h5>

            @forelse($latestLeaves as $leave)
                <div class="border rounded-4 p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="fw-semibold">{{ optional($leave->employee)->employee_name ?? 'Unknown Employee' }}</div>
                            <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $leave->leave_type)) }}</small>
                        </div>
                        @php
                            $badgeClass = $leave->status === 'approved' ? 'success' : ($leave->status === 'pending' ? 'warning text-dark' : 'secondary');
                        @endphp
                        <span class="badge {{ str_contains($badgeClass, 'text-dark') ? 'bg-warning text-dark' : 'bg-' . $badgeClass }}">
                            {{ ucfirst($leave->status) }}
                        </span>
                    </div>
                    <div class="small text-muted mt-2">
                        {{ optional($leave->start_date)->format('Y-m-d') }} to {{ optional($leave->end_date)->format('Y-m-d') }}
                    </div>
                    <div class="small mt-2">{{ $leave->reason ?: 'No reason provided' }}</div>
                </div>
            @empty
                <div class="text-muted">No leave requests found.</div>
            @endforelse
        </div>

        <div class="content-card">
            <h5 class="fw-bold mb-4">System Snapshot</h5>
            <div class="d-flex justify-content-between mb-3">
                <span class="text-muted">Departments</span>
                <strong>{{ $departmentCount }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-3">
                <span class="text-muted">Shifts</span>
                <strong>{{ $shiftCount }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-3">
                <span class="text-muted">Shift Groups</span>
                <strong>{{ $shiftGroupCount }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-3">
                <span class="text-muted">Active Users</span>
                <strong>{{ $activeUsers }}</strong>
            </div>
            <div class="d-flex justify-content-between">
                <span class="text-muted">Company Holidays</span>
                <strong>{{ $holidayCount }}</strong>
            </div>
        </div>
    </div>
</div>

@endsection
