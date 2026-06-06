@extends('layouts.app')

@section('title', $setting->exists ? 'Edit Notification Email' : 'Add Notification Email')

@section('content')
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">{{ $setting->exists ? 'Edit Notification Email' : 'Add Notification Email' }}</h4>
            <p class="text-muted mb-0">Choose who receives automated attendance alerts and when the system should send them.</p>
        </div>
        <a href="{{ route('notification-settings.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Back to Notification Emails</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger rounded-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $setting->exists ? route('notification-settings.update', $setting) : route('notification-settings.store') }}">
        @csrf
        @if($setting->exists)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Recipient Name</label>
                <input type="text" name="recipient_name" class="form-control" value="{{ old('recipient_name', $setting->recipient_name) }}" placeholder="HR Manager">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">To Email Addresses</label>
                <textarea name="to_emails" class="form-control" rows="3" placeholder="alerts@company.com&#10;manager@company.com" required>{{ old('to_emails', implode(PHP_EOL, $setting->toEmailList())) }}</textarea>
                <div class="form-text">Add one or more email addresses separated by commas or new lines.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">CC Email Addresses</label>
                <textarea name="cc_emails" class="form-control" rows="3" placeholder="director@company.com&#10;hr@company.com">{{ old('cc_emails', implode(PHP_EOL, $setting->ccEmailList())) }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Send Time</label>
                <input type="time" name="scheduled_time" class="form-control" value="{{ old('scheduled_time', \Illuminate\Support\Str::of($setting->scheduled_time ?: '08:30:00')->substr(0, 5)) }}" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is-active" value="1" {{ old('is_active', $setting->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="is-active">Enable automatic sending</label>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Alert Types</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="notify_pending_leave" id="notify-pending-leave" value="1" {{ old('notify_pending_leave', $setting->notify_pending_leave) ? 'checked' : '' }}>
                            <label class="form-check-label" for="notify-pending-leave">Pending leave approvals</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="notify_missed_punch" id="notify-missed-punch" value="1" {{ old('notify_missed_punch', $setting->notify_missed_punch) ? 'checked' : '' }}>
                            <label class="form-check-label" for="notify-missed-punch">Missed punch employees</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="notify_absent" id="notify-absent" value="1" {{ old('notify_absent', $setting->notify_absent) ? 'checked' : '' }}>
                            <label class="form-check-label" for="notify-absent">Absent employees</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="notify_late" id="notify-late" value="1" {{ old('notify_late', $setting->notify_late) ? 'checked' : '' }}>
                            <label class="form-check-label" for="notify-late">Late comers</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="notify_early_out" id="notify-early-out" value="1" {{ old('notify_early_out', $setting->notify_early_out) ? 'checked' : '' }}>
                            <label class="form-check-label" for="notify-early-out">Early left employees</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="notify_missing_roster" id="notify-missing-roster" value="1" {{ old('notify_missing_roster', $setting->notify_missing_roster) ? 'checked' : '' }}>
                            <label class="form-check-label" for="notify-missing-roster">Employees with no upcoming shift roster</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="notify_ot_threshold" id="notify-ot-threshold" value="1" {{ old('notify_ot_threshold', $setting->notify_ot_threshold) ? 'checked' : '' }}>
                            <label class="form-check-label" for="notify-ot-threshold">Overtime threshold exceeded</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="notify_weekend_holiday_punch" id="notify-weekend-holiday-punch" value="1" {{ old('notify_weekend_holiday_punch', $setting->notify_weekend_holiday_punch) ? 'checked' : '' }}>
                            <label class="form-check-label" for="notify-weekend-holiday-punch">Weekend or holiday punches detected</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="notify_repeated_half_day" id="notify-repeated-half-day" value="1" {{ old('notify_repeated_half_day', $setting->notify_repeated_half_day) ? 'checked' : '' }}>
                            <label class="form-check-label" for="notify-repeated-half-day">Repeated half-day attendance patterns</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info rounded-4 mt-4 mb-0">
            The email digest uses the current month-to-date period. Missing roster checks look ahead {{ \App\Support\AttendanceNotificationService::MISSING_ROSTER_LOOKAHEAD_DAYS ?? 7 }} days from the send date.
        </div>

        <div class="mt-4 text-end">
            <button type="submit" class="btn btn-primary rounded-pill px-4">{{ $setting->exists ? 'Update Setting' : 'Save Setting' }}</button>
        </div>
    </form>
</div>
@endsection