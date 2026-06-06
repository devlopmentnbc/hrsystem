@extends('layouts.app')

@section('title', 'Time Sheet Upload')

@section('content')
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0">Time Sheet Upload</h5>
            <small class="text-muted">Upload attendance records from CSV or Excel in the expected time sheet format.</small>
        </div>
        <a href="{{ route('attendance.leave_request.index') }}" class="btn btn-outline-secondary">Back to Leave Requests</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-4">
        <div class="alert alert-info">
            <strong>Upload format:</strong> Person ID, Name, Department, Time, Attendance Status, Attendance Check Point, Custom Name, Data Source, Handling Type, Temperature, Abnormal.
            <br>
            Use CSV with the same header order shown below. Excel support is allowed if the server has `phpoffice/phpspreadsheet` installed.
        </div>
    </div>

    <form action="{{ route('attendance.timesheet.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label">Upload File</label>
            <input type="file" name="timesheet_file" class="form-control" accept=".csv,.txt,.xls,.xlsx" required>
        </div>

        <button type="submit" class="btn btn-primary">Upload Time Sheet</button>
    </form>

    <div class="mt-5">
        <h6>Sample header</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Person ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Time</th>
                        <th>Attendance Status</th>
                        <th>Attendance Check Point</th>
                        <th>Custom Name</th>
                        <th>Data Source</th>
                        <th>Handling Type</th>
                        <th>Temperature</th>
                        <th>Abnormal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>EPE001</td>
                        <td>Lasitha Pathum</td>
                        <td>EcoProtect_Oyamaduwa</td>
                        <td>2026-05-27 21:20:30</td>
                        <td>None</td>
                        <td>Ware House_Door1_Entrance Card Reader1</td>
                        <td>-</td>
                        <td>Original Records</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
