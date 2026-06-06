@extends('layouts.app')

@section('title', 'Attendance Detail Report')

@section('content')
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0">Attendance Detail Report</h5>
            <small class="text-muted">{{ $employee->employee_name }} ({{ $employee->employee_code }}) | {{ $startDate->format('Y-m-d') }} to {{ $endDate->format('Y-m-d') }}</small>
        </div>
        <a href="{{ route('attendance.processing') }}" class="btn btn-outline-secondary">Back to Processing</a>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Employee</h6>
                    <p class="fw-semibold mb-0">{{ $employee->employee_name }}</p>
                    <small>{{ $employee->employee_code }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Department</h6>
                    <p class="fw-semibold mb-0">{{ optional($employee->department)->department_name ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Designation</h6>
                    <p class="fw-semibold mb-0">{{ optional($employee->designation)->designation_name ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Period</h6>
                    <p class="fw-semibold mb-0">{{ $startDate->diffInDays($endDate) + 1 }} Days</p>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle text-nowrap">
            <thead class="table-light">
                <tr>
                    <th>Emp Code</th>
                    <th>Emp Name</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Date</th>
                    <th>Shift Name</th>
                    <th>Shift Start</th>
                    <th>Shift End</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Late Min</th>
                    <th>Early Out Min</th>
                    <th>Work Hrs</th>
                    <th>Break Min</th>
                    <th>Net Work Hrs</th>
                    <th>OT Min</th>
                    <th>OT Hrs</th>
                    <th>Status</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($details as $detail)
                    @php
                        $row = $detail['attendance'];
                        $statusBg = 'bg-light';
                        $statusText = $row['status'] ?? 'No Shift';
                        $statusBadge = 'secondary';

                        if (!empty($detail['holiday'])) {
                            $statusBg = match($row['holiday_color'] ?? 'primary') {
                                'danger' => 'bg-danger bg-opacity-10',
                                'info' => 'bg-info bg-opacity-10',
                                default => 'bg-primary bg-opacity-10',
                            };
                            $statusText = $row['status'];
                            $statusBadge = $row['holiday_color'] ?? 'primary';
                        } elseif (!empty($detail['weekend']) || !empty($row['is_weekend'])) {
                            $statusBg = '';
                            $statusText = $row['status'];
                            $statusBadge = 'warning';
                        } elseif ($detail['leave']) {
                            $statusBg = 'bg-warning bg-opacity-10';
                            $statusText = $row['status'];
                            $statusBadge = 'warning';
                        } elseif ($detail['off_duty']) {
                            $statusBg = 'bg-info bg-opacity-10';
                            $statusText = $row['status'];
                            $statusBadge = 'info';
                        } elseif ($detail['shift'] && $detail['shift']->shift) {
                            if (in_array($row['status'], ['Present', 'Half Day'])) {
                                $statusBg = 'bg-success bg-opacity-10';
                                $statusText = $row['status'];
                                $statusBadge = 'success';
                            } elseif ($row['status'] === 'Incomplete') {
                                $statusBg = 'bg-warning bg-opacity-10';
                                $statusText = $row['status'];
                                $statusBadge = 'warning';
                            } else {
                                $statusBg = 'bg-danger bg-opacity-10';
                                $statusText = $row['status'];
                                $statusBadge = 'danger';
                            }
                        }
                    @endphp
                    <tr class="{{ $statusBg }}" @if(!empty($detail['weekend']) || !empty($row['is_weekend'])) style="background-color:#fff3cd;" @endif>
                        <td>{{ $row['employee_code'] ?? '-' }}</td>
                        <td class="fw-semibold">{{ $row['employee_name'] ?? '-' }}</td>
                        <td>{{ $row['department'] ?? '-' }}</td>
                        <td>{{ $row['designation'] ?? '-' }}</td>
                        <td class="fw-semibold">{{ $detail['date']->format('Y-m-d') }}</td>
                        <td>
                            {{ $row['shift_name'] ?? '-' }}
                            @if($row['is_overnight'])
                                <span class="badge bg-info ms-1">Overnight</span>
                            @endif
                        </td>
                        <td>{{ $row['shift_start'] ? $row['shift_start']->format('Y-m-d H:i') : '-' }}</td>
                        <td>{{ $row['shift_end'] ? $row['shift_end']->format('Y-m-d H:i') : '-' }}</td>
                        <td>{{ $row['check_in'] ? $row['check_in']->format('Y-m-d H:i:s') : '-' }}</td>
                        <td>{{ $row['check_out'] ? $row['check_out']->format('Y-m-d H:i:s') : '-' }}</td>
                        <td class="text-end">{{ $row['late_min'] ?? 0 }}</td>
                        <td class="text-end">{{ $row['early_out_min'] ?? 0 }}</td>
                        <td class="text-end">{{ number_format($row['work_hours'] ?? 0, 2) }}</td>
                        <td class="text-end">{{ $row['break_min'] ?? 0 }}</td>
                        <td class="text-end">{{ number_format($row['net_work_hours'] ?? 0, 2) }}</td>
                        <td class="text-end">{{ $row['ot_min'] ?? 0 }}</td>
                        <td class="text-end">{{ number_format($row['ot_hours'] ?? 0, 2) }}</td>
                        <td>
                            <span class="badge bg-{{ $statusBadge }}">{{ $statusText }}</span>
                            @if(!empty($row['is_holiday']) && !empty($row['holiday_type']))
                                <span class="badge bg-{{ $row['holiday_color'] }} ms-1">{{ ucfirst($row['holiday_type']) }}</span>
                            @endif
                            @if(!empty($row['is_weekend']))
                                <span class="badge text-dark ms-1" style="background-color:#ffe082;">Weekend</span>
                            @endif
                        </td>
                        <td>
                            {{ $row['remarks'] ?? '-' }}
                            @php
                                $canCorrectMispunch = auth()->check() && auth()->user()->canAccessAny(['attendance_mispunch.create', 'attendance_mispunch.update']);
                            @endphp
                            @if($canCorrectMispunch && in_array($row['status'], ['Incomplete', 'Absent']))
                                <div class="mt-2">
                                    <a href="{{ route('attendance.mispunch.create', ['employee_id' => $employee->id, 'date' => $detail['date']->toDateString()]) }}" class="btn btn-sm btn-outline-primary">Correct Mispunch</a>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="19" class="text-center">No attendance data found for this employee in the selected period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <h6 class="mb-3">Legend</h6>
        <div class="row">
            <div class="col-md-3">
                <div class="alert alert-success bg-opacity-10 mb-0">
                    <strong>Present:</strong> Attendance recorded during shift time
                </div>
            </div>
            <div class="col-md-3">
                <div class="alert alert-danger bg-opacity-10 mb-0">
                    <strong>Absent:</strong> No attendance during assigned shift
                </div>
            </div>
            <div class="col-md-3">
                <div class="alert alert-warning bg-opacity-10 mb-0">
                    <strong>On Leave:</strong> Approved leave request
                </div>
            </div>
            <div class="col-md-3">
                <div class="alert alert-primary bg-opacity-10 mb-0">
                    <strong>Holiday / Overnight:</strong> Company holiday dates override absence; overnight shifts cross midnight
                </div>
            </div>
            <div class="col-md-12 mt-3">
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-primary">Company Holiday</span>
                    <span class="badge bg-danger">Public Holiday</span>
                    <span class="badge bg-info">Special Holiday</span>
                    <span class="badge text-dark" style="background-color:#ffe082;">Weekend</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
