<?php

namespace App\Http\Controllers;

use App\Models\AttendanceNotificationSetting;
use App\Support\AccessControl;
use App\Support\AttendanceNotificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AttendanceNotificationSettingController extends Controller
{
    private function authorizePermission(string $permission): void
    {
        AccessControl::ensureAdminSetup();

        abort_unless(auth()->check() && auth()->user()->canAccess($permission), 403);
    }

    public function index()
    {
        $this->authorizePermission('users.update');

        $settings = AttendanceNotificationSetting::orderBy('recipient_name')->orderBy('id')->get();

        return view('notification_settings.index', compact('settings'));
    }

    public function create()
    {
        $this->authorizePermission('users.update');

        return view('notification_settings.form', [
            'setting' => new AttendanceNotificationSetting([
                'to_emails' => [],
                'cc_emails' => [],
                'scheduled_time' => '08:30:00',
                'is_active' => true,
                'notify_pending_leave' => true,
                'notify_missed_punch' => true,
                'notify_absent' => true,
                'notify_late' => true,
                'notify_early_out' => true,
                'notify_missing_roster' => true,
                'notify_ot_threshold' => true,
                'notify_weekend_holiday_punch' => true,
                'notify_repeated_half_day' => true,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizePermission('users.update');

        $data = $this->validatePayload($request);

        AttendanceNotificationSetting::create($data + [
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('notification-settings.index')->with('success', 'Notification email setting created successfully.');
    }

    public function edit(AttendanceNotificationSetting $notification_setting)
    {
        $this->authorizePermission('users.update');

        return view('notification_settings.form', [
            'setting' => $notification_setting,
        ]);
    }

    public function update(Request $request, AttendanceNotificationSetting $notification_setting)
    {
        $this->authorizePermission('users.update');

        $data = $this->validatePayload($request);

        $notification_setting->update($data + [
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('notification-settings.index')->with('success', 'Notification email setting updated successfully.');
    }

    public function destroy(AttendanceNotificationSetting $notification_setting)
    {
        $this->authorizePermission('users.update');

        $notification_setting->delete();

        return redirect()->route('notification-settings.index')->with('success', 'Notification email setting deleted successfully.');
    }

    public function sendNow(AttendanceNotificationSetting $notification_setting, AttendanceNotificationService $service)
    {
        $this->authorizePermission('users.update');

        $service->sendNotification($notification_setting, now()->copy());

        return redirect()->route('notification-settings.index')->with('success', 'Notification email sent successfully.');
    }

    private function validatePayload(Request $request): array
    {
        $data = $request->validate([
            'recipient_name' => 'nullable|string|max:255',
            'to_emails' => 'required|string',
            'cc_emails' => 'nullable|string',
            'scheduled_time' => 'required|date_format:H:i',
            'is_active' => 'nullable|boolean',
            'notify_pending_leave' => 'nullable|boolean',
            'notify_missed_punch' => 'nullable|boolean',
            'notify_absent' => 'nullable|boolean',
            'notify_late' => 'nullable|boolean',
            'notify_early_out' => 'nullable|boolean',
            'notify_missing_roster' => 'nullable|boolean',
            'notify_ot_threshold' => 'nullable|boolean',
            'notify_weekend_holiday_punch' => 'nullable|boolean',
            'notify_repeated_half_day' => 'nullable|boolean',
        ]);

        $toEmails = $this->parseEmails($data['to_emails'] ?? '');
        $ccEmails = $this->parseEmails($data['cc_emails'] ?? '');

        if (empty($toEmails)) {
            throw ValidationException::withMessages([
                'to_emails' => 'Add at least one To email address.',
            ]);
        }

        $payload = [
            'recipient_name' => $data['recipient_name'] ?? null,
            'to_emails' => $toEmails,
            'cc_emails' => $ccEmails,
            'scheduled_time' => ($data['scheduled_time'] ?? '08:30') . ':00',
            'is_active' => $request->boolean('is_active'),
            'notify_pending_leave' => $request->boolean('notify_pending_leave'),
            'notify_missed_punch' => $request->boolean('notify_missed_punch'),
            'notify_absent' => $request->boolean('notify_absent'),
            'notify_late' => $request->boolean('notify_late'),
            'notify_early_out' => $request->boolean('notify_early_out'),
            'notify_missing_roster' => $request->boolean('notify_missing_roster'),
            'notify_ot_threshold' => $request->boolean('notify_ot_threshold'),
            'notify_weekend_holiday_punch' => $request->boolean('notify_weekend_holiday_punch'),
            'notify_repeated_half_day' => $request->boolean('notify_repeated_half_day'),
        ];

        if (! $payload['notify_pending_leave']
            && ! $payload['notify_missed_punch']
            && ! $payload['notify_absent']
            && ! $payload['notify_late']
            && ! $payload['notify_early_out']
            && ! $payload['notify_missing_roster']
            && ! $payload['notify_ot_threshold']
            && ! $payload['notify_weekend_holiday_punch']
            && ! $payload['notify_repeated_half_day']) {
            throw ValidationException::withMessages([
                'notify_missed_punch' => 'Select at least one attendance alert type.',
            ]);
        }

        return $payload;
    }

    private function parseEmails(string $value): array
    {
        $emails = collect(preg_split('/[\s,;]+/', $value) ?: [])
            ->map(fn ($email) => trim((string) $email))
            ->filter()
            ->unique()
            ->values();

        $invalid = $emails->filter(fn ($email) => ! filter_var($email, FILTER_VALIDATE_EMAIL));

        if ($invalid->isNotEmpty()) {
            throw ValidationException::withMessages([
                'to_emails' => 'Invalid email address detected: ' . $invalid->implode(', '),
            ]);
        }

        return $emails->all();
    }
}