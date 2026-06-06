<?php

namespace App\Mail;

use App\Models\AttendanceNotificationSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AttendanceNotificationDigestMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public \App\Models\AttendanceNotificationSetting $setting,
        public array $payload,
    ) {
    }

    public function build(): self
    {
        $periodEnd = $this->payload['period_end']->format('Y-m-d');

        return $this->subject('Attendance Alerts Digest - ' . $periodEnd)
            ->view('emails.attendance_notification_digest');
    }
}