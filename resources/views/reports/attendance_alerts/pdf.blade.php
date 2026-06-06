<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Alerts Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        h1, h2 { margin: 0 0 8px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; vertical-align: top; }
        th { background: #eff6ff; text-align: left; }
        .muted { color: #64748b; }
    </style>
</head>
<body>
    <h1>Attendance Alerts Report</h1>
    <p class="muted">Period: {{ $payload['period_start']->format('Y-m-d') }} to {{ $payload['period_end']->format('Y-m-d') }}</p>

    @foreach($payload['sections'] as $section)
        @continue(! $section['enabled'])

        <h2>{{ $section['label'] }} ({{ count($section['rows']) }})</h2>
        <table>
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
                        <td>{{ $row['employee_name'] }} ({{ $row['employee_code'] }})</td>
                        <td>{{ $row['department'] ?: '-' }}</td>
                        <td>{{ $row['count'] }}</td>
                        <td>{{ implode(', ', $row['dates']) }}</td>
                        <td>{{ $row['total_minutes'] ?: '-' }}</td>
                        <td>{{ implode(' | ', array_filter(array_merge($row['labels'] ?? [], $row['remarks'] ?? []))) ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endforeach
</body>
</html>