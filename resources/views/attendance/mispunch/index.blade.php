@extends('layouts.app')

@section('title', 'Mispunch Corrections')

@section('content')
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Mispunch Corrections</h4>
            <p class="text-muted mb-0">Detect incomplete attendance punches and create authorized corrections.</p>
        </div>
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

    <form method="GET" class="row gy-3 mb-4">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Employee</label>
            <select name="employee_id" class="form-select">
                <option value="">All employees</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->employee_name }} ({{ $employee->employee_code }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Start Date</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">End Date</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
        </div>
        <div class="col-md-2 d-grid align-self-end">
            <button type="submit" class="btn btn-primary rounded-pill px-4">Filter</button>
        </div>
    </form>

    <h5 class="fw-bold mb-3">Detected Mispunch Candidates</h5>
    <div class="table-responsive mb-4">
        <table class="table table-sm table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>First Punch</th>
                    <th>Last Punch</th>
                    <th>Punch Count</th>
                    <th>Correction</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mispunchCandidates as $candidate)
                    <tr>
                        <td>{{ optional($candidate->employee)->employee_name ?? 'Unknown Employee' }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($candidate->correction_date)->format('Y-m-d') }}</td>
                        <td>{{ optional(\Illuminate\Support\Carbon::parse($candidate->first_punch))->format('Y-m-d H:i:s') }}</td>
                        <td>{{ optional(\Illuminate\Support\Carbon::parse($candidate->last_punch))->format('Y-m-d H:i:s') }}</td>
                        <td>{{ $candidate->punch_count }}</td>
                        <td>
                            @if($candidate->existingCorrection)
                                <a href="{{ route('attendance.mispunch.edit', $candidate->existingCorrection) }}" class="btn btn-sm btn-outline-primary">Edit Correction</a>
                            @else
                                <a href="{{ route('attendance.mispunch.create', ['employee_id' => $candidate->employee_id, 'date' => $candidate->correction_date]) }}" class="btn btn-sm btn-primary">Correct</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">No mispunch candidates found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap mb-4">{{ $mispunchCandidates->links() }}</div>

    <h5 class="fw-bold mb-3">Correction History</h5>
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>Corrected In</th>
                    <th>Corrected Out</th>
                    <th>Reason</th>
                    <th>Updated By</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($corrections as $correction)
                    <tr>
                        <td>{{ optional($correction->employee)->employee_name }}</td>
                        <td>{{ optional($correction->correction_date)->format('Y-m-d') }}</td>
                        <td>{{ optional($correction->corrected_check_in)->format('Y-m-d H:i') }}</td>
                        <td>{{ optional($correction->corrected_check_out)->format('Y-m-d H:i') }}</td>
                        <td>{{ $correction->reason }}</td>
                        <td>{{ optional($correction->updater)->name ?? optional($correction->creator)->name ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $correction->status ? 'success' : 'secondary' }}">{{ $correction->status ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td>
                            @if(auth()->user()->canAccess('attendance_mispunch.update'))
                                <a href="{{ route('attendance.mispunch.edit', $correction) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center">No corrections created yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">{{ $corrections->links() }}</div>
</div>
@endsection
