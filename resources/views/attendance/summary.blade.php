@extends('layouts.app')

@section('title', 'Attendance Summary Report')

@section('content')
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0">Attendance Summary Report</h5>
            <small class="text-muted">{{ $startDate->format('Y-m-d') }} to {{ $endDate->format('Y-m-d') }}</small>
        </div>
        <a href="{{ route('attendance.processing') }}" class="btn btn-outline-secondary">Back to Processing</a>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle text-nowrap">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Emp Code</th>
                    <th>Emp Name</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th class="text-center">Total Working Days</th>
                    <th class="text-center bg-success bg-opacity-10">Present Days</th>
                    <th class="text-center bg-danger bg-opacity-10">Absent Days</th>
                    <th class="text-center bg-warning bg-opacity-10">Leave Days</th>
                    <th class="text-center bg-primary bg-opacity-10">Half Days</th>
                    <th class="text-center">Holidays</th>
                    <th class="text-center bg-info bg-opacity-10">Off Days</th>
                    <th class="text-center">Late Count</th>
                    <th class="text-center">Total Late Min</th>
                    <th class="text-center">Early Out Count</th>
                    <th class="text-center">Total Early Out Min</th>
                    <th class="text-center">Total Work Hrs</th>
                    <th class="text-center">Total OT Hrs</th>
                    <th class="text-center">Missed Punch Days</th>
                    <th class="text-center">Attendance %</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($summary as $empId => $data)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $data['employee']->employee_code }}</td>
                        <td class="fw-semibold">{{ $data['employee']->employee_name }}</td>
                        <td>{{ optional($data['employee']->department)->department_name }}</td>
                        <td>{{ optional($data['employee']->designation)->designation_name }}</td>
                        <td class="text-center">{{ $data['total_working_days'] }}</td>
                        <td class="text-center bg-success bg-opacity-10">
                            <strong>{{ $data['present_days'] }}</strong>
                        </td>
                        <td class="text-center bg-danger bg-opacity-10">
                            <strong>{{ $data['absent_days'] }}</strong>
                        </td>
                        <td class="text-center bg-warning bg-opacity-10">
                            <strong>{{ $data['leave_days'] }}</strong>
                        </td>
                        <td class="text-center bg-primary bg-opacity-10">
                            <strong>{{ $data['half_days'] }}</strong>
                        </td>
                        <td class="text-center">
                            <div>{{ $data['holidays'] }}</div>
                            @if($data['holidays'] > 0)
                                <div class="mt-1 d-flex gap-1 justify-content-center flex-wrap">
                                    @if(($data['holiday_type_counts']['company'] ?? 0) > 0)
                                        <span class="badge bg-primary">Company {{ $data['holiday_type_counts']['company'] }}</span>
                                    @endif
                                    @if(($data['holiday_type_counts']['public'] ?? 0) > 0)
                                        <span class="badge bg-danger">Public {{ $data['holiday_type_counts']['public'] }}</span>
                                    @endif
                                    @if(($data['holiday_type_counts']['special'] ?? 0) > 0)
                                        <span class="badge bg-info">Special {{ $data['holiday_type_counts']['special'] }}</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="text-center bg-info bg-opacity-10">
                            <strong>{{ $data['off_days'] }}</strong>
                        </td>
                        <td class="text-center">{{ $data['late_count'] }}</td>
                        <td class="text-center">{{ $data['total_late_min'] }}</td>
                        <td class="text-center">{{ $data['early_out_count'] }}</td>
                        <td class="text-center">{{ $data['total_early_out_min'] }}</td>
                        <td class="text-center">{{ number_format($data['total_work_hrs'], 2) }}</td>
                        <td class="text-center">{{ number_format($data['total_ot_hrs'], 2) }}</td>
                        <td class="text-center">{{ $data['missed_punch_days'] }}</td>
                        <td class="text-center">
                            @if($data['attendance_percent'] >= 80)
                                <span class="badge bg-success">{{ $data['attendance_percent'] }}%</span>
                            @elseif($data['attendance_percent'] >= 60)
                                <span class="badge bg-warning">{{ $data['attendance_percent'] }}%</span>
                            @else
                                <span class="badge bg-danger">{{ $data['attendance_percent'] }}%</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('attendance.detail', ['employeeId' => $empId, 'start_date' => $startDate->toDateString(), 'end_date' => $endDate->toDateString()]) }}" class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="21" class="text-center">No employee data found.</td>
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
                    <strong>Present / Half Day:</strong> Derived from worked net hours against shift thresholds
                </div>
            </div>
            <div class="col-md-3">
                <div class="alert alert-danger bg-opacity-10 mb-0">
                    <strong>Absent / Missed Punch:</strong> Absent if no punch, missed punch if only one punch
                </div>
            </div>
            <div class="col-md-3">
                <div class="alert alert-warning bg-opacity-10 mb-0">
                    <strong>Leave / Off / Holiday:</strong> Leave from approved requests, off days from roster/weekends, holidays from company holiday master
                </div>
            </div>
            <div class="col-md-3">
                <div class="alert alert-info bg-opacity-10 mb-0">
                    <strong>Attendance %:</strong> (Present + Half Day × 0.5) / Total Working Days
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
