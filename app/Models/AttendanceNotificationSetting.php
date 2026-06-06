<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Database\Eloquent\Model;

class AttendanceNotificationSetting extends Model
{
    use LogsAudit;

    protected $fillable = [
        'recipient_name',
        'to_emails',
        'cc_emails',
        'scheduled_time',
        'is_active',
        'notify_pending_leave',
        'notify_missed_punch',
        'notify_absent',
        'notify_late',
        'notify_early_out',
        'notify_missing_roster',
        'notify_ot_threshold',
        'notify_weekend_holiday_punch',
        'notify_repeated_half_day',
        'last_sent_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'to_emails' => 'array',
        'cc_emails' => 'array',
        'is_active' => 'boolean',
        'notify_pending_leave' => 'boolean',
        'notify_missed_punch' => 'boolean',
        'notify_absent' => 'boolean',
        'notify_late' => 'boolean',
        'notify_early_out' => 'boolean',
        'notify_missing_roster' => 'boolean',
        'notify_ot_threshold' => 'boolean',
        'notify_weekend_holiday_punch' => 'boolean',
        'notify_repeated_half_day' => 'boolean',
        'last_sent_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function enabledAlertLabels(): array
    {
        return array_values(array_filter([
            $this->notify_pending_leave ? 'Pending Leave Approvals' : null,
            $this->notify_missed_punch ? 'Missed Punch' : null,
            $this->notify_absent ? 'Absent Employees' : null,
            $this->notify_late ? 'Late Comers' : null,
            $this->notify_early_out ? 'Early Left' : null,
            $this->notify_missing_roster ? 'Missing Upcoming Roster' : null,
            $this->notify_ot_threshold ? 'OT Threshold Exceeded' : null,
            $this->notify_weekend_holiday_punch ? 'Weekend/Holiday Punches' : null,
            $this->notify_repeated_half_day ? 'Repeated Half Days' : null,
        ]));
    }

    public function toEmailList(): array
    {
        return array_values(array_filter($this->to_emails ?? []));
    }

    public function ccEmailList(): array
    {
        return array_values(array_filter($this->cc_emails ?? []));
    }
}