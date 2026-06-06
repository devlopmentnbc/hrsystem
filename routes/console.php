<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('attendance:send-scheduled-notifications {--now=}', function () {
    $referenceTime = $this->option('now')
        ? Carbon::parse((string) $this->option('now'))
        : now();

    $sent = app(\App\Support\AttendanceNotificationService::class)->sendDueNotifications($referenceTime);

    $this->info('Processed ' . $sent . ' scheduled attendance notification email(s).');
})->purpose('Send due attendance alert emails based on configured schedules');

Schedule::call(function () {
    app(\App\Support\AttendanceNotificationService::class)->sendDueNotifications(now()->copy());
})->everyMinute()->name('attendance-scheduled-notifications');
