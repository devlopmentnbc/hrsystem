@extends('layouts.app')

@section('title', 'Notification Emails')

@section('content')
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Notification Emails</h4>
            <p class="text-muted mb-0">Schedule automatic attendance alert emails for missed punch, absence, late comers, and early left employees.</p>
        </div>

        <a href="{{ route('notification-settings.create') }}" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-plus-circle"></i>
            Add Notification Email
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-4">{{ session('success') }}</div>
    @endif

    <div class="alert alert-info rounded-4">
        The system prepares a month-to-date digest up to the day the email is sent. Real emails require a working mail driver in your environment; currently the default mailer is <strong>{{ config('mail.default') }}</strong>.
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Recipient</th>
                    <th>To</th>
                    <th>CC</th>
                    <th>Send Time</th>
                    <th>Alerts</th>
                    <th>Status</th>
                    <th>Last Sent</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($settings as $setting)
                    <tr>
                        <td>{{ $setting->recipient_name ?: '-' }}</td>
                        <td>{{ implode(', ', $setting->toEmailList()) }}</td>
                        <td>{{ $setting->ccEmailList() ? implode(', ', $setting->ccEmailList()) : '-' }}</td>
                        <td>{{ \Illuminate\Support\Str::of($setting->scheduled_time)->substr(0, 5) }}</td>
                        <td>{{ implode(', ', $setting->enabledAlertLabels()) }}</td>
                        <td>
                            @if($setting->is_active)
                                <span class="badge bg-success rounded-pill">Active</span>
                            @else
                                <span class="badge bg-secondary rounded-pill">Paused</span>
                            @endif
                        </td>
                        <td>{{ $setting->last_sent_at?->format('Y-m-d H:i') ?? 'Never' }}</td>
                        <td class="text-end">
                            <a href="{{ route('notification-settings.edit', $setting) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <form method="POST" action="{{ route('notification-settings.send-now', $setting) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success rounded-pill" title="Send Now">
                                    <i class="bi bi-send"></i>
                                </button>
                            </form>

                            <form method="POST" action="{{ route('notification-settings.destroy', $setting) }}" class="d-inline" onsubmit="return confirm('Delete this notification email setting?');">
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
                        <td colspan="8" class="text-center text-muted">No notification email settings found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection