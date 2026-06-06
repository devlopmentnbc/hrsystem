@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Audit Logs</h4>
            <p class="text-muted mb-0">View the change history across the system for auditing purposes.</p>
        </div>
    </div>

    <form method="GET" class="row gy-3 mb-4">
        <div class="col-md-2">
            <label class="form-label fw-semibold">Event</label>
            <select name="event" class="form-select">
                <option value="">All</option>
                @foreach($events as $event)
                    <option value="{{ $event }}" {{ request('event') === $event ? 'selected' : '' }}>{{ $event }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">User</label>
            <select name="user_id" class="form-select">
                <option value="">All</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Model</label>
            <select name="model" class="form-select">
                <option value="">All</option>
                @foreach($models as $model)
                    <option value="{{ $model }}" {{ request('model') === $model ? 'selected' : '' }}>{{ class_basename($model) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">Start Date</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">End Date</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
        </div>
        <div class="col-md-1 d-grid align-self-end">
            <button type="submit" class="btn btn-primary rounded-pill">Go</button>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search description or changed values">
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Date Time</th>
                    <th>User</th>
                    <th>Event</th>
                    <th>Entity</th>
                    <th>Description</th>
                    <th>Old Values</th>
                    <th>New Values</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td>{{ optional($log->user)->name ?? 'System' }}</td>
                        <td><span class="badge bg-secondary">{{ $log->event }}</span></td>
                        <td>{{ $log->auditable_type ? class_basename($log->auditable_type) . ' #' . $log->auditable_id : '-' }}</td>
                        <td>{{ $log->description ?: '-' }}</td>
                        <td><pre class="small mb-0">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre></td>
                        <td><pre class="small mb-0">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center">No audit logs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">{{ $logs->links() }}</div>
</div>
@endsection
