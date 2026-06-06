@extends('layouts.app')

@section('title', 'Attendance Processing')

@section('content')
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0">Attendance Processing</h5>
            <small class="text-muted">Process and analyze attendance records with shift schedules and leave data.</small>
        </div>
        <a href="{{ route('attendance.leave_request.index') }}" class="btn btn-outline-secondary">Back to Attendance</a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">Employee Summary Report</h6>
                </div>
                <div class="card-body">
                    <p>View aggregated attendance summary for all employees including present days, absent days, and leaves.</p>
                    <form method="GET" action="{{ route('attendance.summary') }}">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $startDate }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $endDate }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">View Summary Report</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">Employee Detail Report</h6>
                </div>
                <div class="card-body">
                    <p>View detailed daily attendance records for a specific employee with shift and leave information.</p>
                    <form method="GET" id="detail-form">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Employee</label>
                            <select name="employee_id" class="form-select" id="employee-select" required>
                                <option value="">Select employee</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $startDate }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $endDate }}" required>
                        </div>
                        <button type="submit" class="btn btn-info w-100">View Detail Report</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info mt-4">
        <strong>Note:</strong> The system automatically handles overnight shifts. For shifts where end time is earlier than start time (e.g., 22:00 to 06:00), attendance records are correctly mapped to the assigned date.
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Populate employee dropdown
        const employeeSelect = document.getElementById('employee-select');
        const employees = @json($employees);
        
        employees.forEach(emp => {
            const option = document.createElement('option');
            option.value = emp.id;
            option.textContent = emp.employee_name + ' (' + emp.employee_code + ')';
            employeeSelect.appendChild(option);
        });

        // Handle detail form submission
        const detailForm = document.getElementById('detail-form');
        detailForm.addEventListener('submit', function (e) {
            if (!employeeSelect.value) {
                e.preventDefault();
                alert('Please select an employee');
                return;
            }
            this.action = '/attendance/detail/' + employeeSelect.value;
        });
    });
</script>

@endsection
