<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Alerts Digest</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f6f8fb; color: #1f2937; margin: 0; padding: 24px;">
    <div style="max-width: 920px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 24px; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);">
        <h2 style="margin-top: 0;">Attendance Alerts Digest</h2>
        <p style="margin-bottom: 4px;">Recipient: {{ $setting->recipient_name ?: implode(', ', $setting->toEmailList()) }}</p>
        <p style="margin: 0 0 4px 0; color: #64748b;">To: {{ implode(', ', $setting->toEmailList()) }}</p>
        @if($setting->ccEmailList())
            <p style="margin: 0 0 4px 0; color: #64748b;">CC: {{ implode(', ', $setting->ccEmailList()) }}</p>
        @endif
        <p style="margin-top: 0; color: #64748b;">Period: {{ $payload['period_start']->format('Y-m-d') }} to {{ $payload['period_end']->format('Y-m-d') }}</p>

        @if($payload['total_records'] === 0)
            <div style="padding: 16px; border-radius: 12px; background: #ecfeff; color: #0f766e; margin-bottom: 20px;">
                No attendance issues were found for the selected alert types in the current month-to-date period.
            </div>
        @endif

        @foreach($payload['sections'] as $section)
            @continue(! $section['enabled'])

            <div style="margin-bottom: 28px;">
                <h3 style="margin-bottom: 10px;">{{ $section['label'] }} ({{ count($section['rows']) }})</h3>

                @if(empty($section['rows']))
                    <div style="padding: 12px 14px; border-radius: 10px; background: #f8fafc; color: #64748b;">No records found.</div>
                @else
                    <table style="width: 100%; border-collapse: collapse; border: 1px solid #e2e8f0;">
                        <thead>
                            <tr style="background: #eff6ff;">
                                <th style="text-align: left; padding: 10px; border-bottom: 1px solid #e2e8f0;">Employee</th>
                                <th style="text-align: left; padding: 10px; border-bottom: 1px solid #e2e8f0;">Department</th>
                                <th style="text-align: left; padding: 10px; border-bottom: 1px solid #e2e8f0;">Count</th>
                                <th style="text-align: left; padding: 10px; border-bottom: 1px solid #e2e8f0;">Dates</th>
                                <th style="text-align: left; padding: 10px; border-bottom: 1px solid #e2e8f0;">Minutes</th>
                                <th style="text-align: left; padding: 10px; border-bottom: 1px solid #e2e8f0;">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($section['rows'] as $row)
                                <tr>
                                    <td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">
                                        <div style="font-weight: 600;">{{ $row['employee_name'] }}</div>
                                        <div style="color: #64748b; font-size: 12px;">{{ $row['employee_code'] }}</div>
                                    </td>
                                    <td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">{{ $row['department'] ?: '-' }}</td>
                                    <td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">{{ $row['count'] }}</td>
                                    <td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">{{ implode(', ', $row['dates']) }}</td>
                                    <td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">{{ $row['total_minutes'] ?: '-' }}</td>
                                    <td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">{{ implode(' | ', array_filter(array_merge($row['labels'] ?? [], $row['remarks'] ?? []))) ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endforeach

        @if(isset($payload['future_roster_end']))
            <p style="margin-top: 12px; color: #64748b; font-size: 12px;">Upcoming roster checks cover dates through {{ $payload['future_roster_end']->format('Y-m-d') }}.</p>
        @endif

        <p style="margin-top: 24px; color: #64748b; font-size: 12px;">Generated at {{ $payload['generated_at']->format('Y-m-d H:i:s') }}.</p>
    </div>
</body>
</html>