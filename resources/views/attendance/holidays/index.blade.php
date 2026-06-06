@extends('layouts.app')

@section('title', 'Company Holidays')

@section('content')
<div class="content-card">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Company Holidays</h4>
            <p class="text-muted mb-0">Manage company holiday dates used in attendance reports.</p>
        </div>

        <a href="{{ route('attendance.holidays.create') }}" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-plus-circle"></i>
            Add Holiday
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-4">
            {{ session('success') }}
        </div>
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

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                <div>
                    <h6 class="fw-bold mb-1">Bulk Holiday Import</h6>
                    <p class="text-muted mb-0 small">Upload CSV, TXT, XLSX, or XLS with columns: holiday_name, holiday_date, holiday_type, description, status.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('attendance.holidays.import') }}" enctype="multipart/form-data" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Holiday File</label>
                    <input type="file" name="holiday_file" class="form-control" accept=".csv,.txt,.xlsx,.xls" required>
                </div>
                <div class="col-md-4 d-grid">
                    <button type="submit" class="btn btn-outline-primary rounded-pill px-4">
                        <i class="bi bi-upload"></i>
                        Import Holidays
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Holiday</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th width="120">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($holidays as $holiday)
                    <tr>
                        <td>{{ $holiday->id }}</td>
                        <td>{{ $holiday->holiday_name }}</td>
                        <td>{{ optional($holiday->holiday_date)->format('Y-m-d') }}</td>
                        <td>
                            @php
                                $typeClass = match(strtolower($holiday->holiday_type)) {
                                    'public' => 'danger',
                                    'special' => 'info',
                                    default => 'primary',
                                };
                            @endphp
                            <span class="badge bg-{{ $typeClass }} rounded-pill">{{ ucfirst($holiday->holiday_type) }}</span>
                        </td>
                        <td>{{ $holiday->description ?: '-' }}</td>
                        <td>
                            @if($holiday->status)
                                <span class="badge bg-success rounded-pill">Active</span>
                            @else
                                <span class="badge bg-danger rounded-pill">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('attendance.holidays.edit', $holiday) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('attendance.holidays.destroy', $holiday) }}" class="d-inline" onsubmit="return confirm('Delete this holiday?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No company holidays found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
