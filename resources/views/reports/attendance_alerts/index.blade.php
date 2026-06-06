@extends('layouts.app')

@section('title', 'Attendance Alerts Report')

@section('content')
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Attendance Alerts Report</h4>
            <p class="text-muted mb-0">Review attendance alert patterns and export them to Excel or PDF.</p>
        </div>
    </div>

    <form method="GET" action="{{ route('reports.attendance-alerts.index') }}" class="row g-3 mb-4 align-items-end">
        <div class="col-md-3">
            <label class="form-label fw-semibold">Start Date</label>
            <input type="date" name="start_date" class="form-control" value="{{ $startDate->toDateString() }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">End Date</label>
            <input type="date" name="end_date" class="form-control" value="{{ $endDate->toDateString() }}" required>
        </div>
        @foreach($options as $optionKey => $enabled)
            <div class="col-md-3">
                <div class="form-check mt-4 pt-2">
                    <input class="form-check-input" type="checkbox" id="{{ $optionKey }}" name="{{ $optionKey }}" value="1" {{ $enabled ? 'checked' : '' }}>
                    <label class="form-check-label" for="{{ $optionKey }}">{{ \Illuminate\Support\Str::of($optionKey)->replace('notify_', '')->replace('_', ' ')->title() }}</label>
                </div>
            </div>
        @endforeach
        <div class="col-12 d-flex flex-wrap gap-2 justify-content-end">
            <button type="submit" class="btn btn-primary rounded-pill px-4">Load Report</button>
            <a href="{{ route('reports.attendance-alerts.export', ['format' => 'xlsx'] + request()->query()) }}" class="btn btn-outline-success rounded-pill px-4">Export Excel</a>
            <a href="{{ route('reports.attendance-alerts.export', ['format' => 'pdf'] + request()->query()) }}" class="btn btn-outline-danger rounded-pill px-4">Export PDF</a>
        </div>
    </form>

    @foreach($payload['sections'] as $section)
        @continue(! $section['enabled'])

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">{{ $section['label'] }}</h6>
                    <span class="badge text-bg-primary">{{ count($section['rows']) }}</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Count</th>
                                <th>Dates</th>
                                <th>Minutes</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($section['rows'] as $row)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $row['employee_name'] }}</div>
                                        <div class="small text-muted">{{ $row['employee_code'] }}</div>
                                    </td>
                                    <td>{{ $row['department'] ?: '-' }}</td>
                                    <td>{{ $row['count'] }}</td>
                                    <td>{{ implode(', ', $row['dates']) }}</td>
                                    <td>{{ $row['total_minutes'] ?: '-' }}</td>
                                    <td>{{ implode(' | ', array_filter(array_merge($row['labels'] ?? [], $row['remarks'] ?? []))) ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection