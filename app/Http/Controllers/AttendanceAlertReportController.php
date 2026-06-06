<?php

namespace App\Http\Controllers;

use App\Support\AccessControl;
use App\Support\AttendanceNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceAlertReportController extends Controller
{
    private function authorizePermission(string $permission): void
    {
        AccessControl::ensureAdminSetup();

        abort_unless(auth()->check() && auth()->user()->canAccess($permission), 403);
    }

    public function index(Request $request, AttendanceNotificationService $service)
    {
        $this->authorizePermission('attendance_reports.view');

        [$startDate, $endDate] = $this->resolvePeriod($request);
        $options = $this->resolveAlertOptions($request, $service);
        $payload = $service->buildReportPayload($options, $startDate, $endDate);

        return view('reports.attendance_alerts.index', compact('payload', 'startDate', 'endDate', 'options'));
    }

    public function export(Request $request, string $format, AttendanceNotificationService $service)
    {
        $this->authorizePermission('attendance_reports.view');

        [$startDate, $endDate] = $this->resolvePeriod($request);
        $options = $this->resolveAlertOptions($request, $service);
        $payload = $service->buildReportPayload($options, $startDate, $endDate);

        if ($format === 'pdf') {
            $pdfFacadeClass = '\\Barryvdh\\DomPDF\\Facade\\Pdf';
            $pdf = $pdfFacadeClass::loadView('reports.attendance_alerts.pdf', compact('payload', 'startDate', 'endDate', 'options'));

            return $pdf->download('attendance-alerts-' . $endDate->format('Ymd') . '.pdf');
        }

        if ($format === 'xlsx') {
            return $this->downloadExcel($payload, $endDate);
        }

        abort(404);
    }

    private function resolvePeriod(Request $request): array
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : now()->startOfMonth();
        $endDate = isset($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->startOfDay()
            : now()->startOfDay();

        return [$startDate, $endDate];
    }

    private function resolveAlertOptions(Request $request, AttendanceNotificationService $service): array
    {
        $defaults = $service->defaultAlertOptions();

        $selected = [];
        foreach ($defaults as $key => $value) {
            $selected[$key] = $request->has($key)
                ? $request->boolean($key)
                : $value;
        }

        return $selected;
    }

    private function downloadExcel(array $payload, Carbon $endDate)
    {
        $spreadsheetClass = '\\PhpOffice\\PhpSpreadsheet\\Spreadsheet';
        $writerClass = '\\PhpOffice\\PhpSpreadsheet\\Writer\\Xlsx';

        $spreadsheet = new $spreadsheetClass();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Attendance Alerts');

        $row = 1;
        $sheet->setCellValue('A' . $row, 'Attendance Alerts Report');
        $row += 1;
        $sheet->setCellValue('A' . $row, 'Period: ' . $payload['period_start']->format('Y-m-d') . ' to ' . $payload['period_end']->format('Y-m-d'));
        $row += 2;

        foreach ($payload['sections'] as $section) {
            if (! $section['enabled']) {
                continue;
            }

            $sheet->setCellValue('A' . $row, $section['label']);
            $row++;
            $sheet->fromArray(['Employee Code', 'Employee Name', 'Department', 'Designation', 'Count', 'Dates', 'Minutes', 'Labels', 'Remarks'], null, 'A' . $row);
            $row++;

            if (empty($section['rows'])) {
                $sheet->setCellValue('A' . $row, 'No records found');
                $row += 2;
                continue;
            }

            foreach ($section['rows'] as $reportRow) {
                $sheet->fromArray([
                    $reportRow['employee_code'] ?? '',
                    $reportRow['employee_name'] ?? '',
                    $reportRow['department'] ?? '',
                    $reportRow['designation'] ?? '',
                    $reportRow['count'] ?? 0,
                    implode(', ', $reportRow['dates'] ?? []),
                    $reportRow['total_minutes'] ?? 0,
                    implode(', ', $reportRow['labels'] ?? []),
                    implode(' | ', $reportRow['remarks'] ?? []),
                ], null, 'A' . $row);
                $row++;
            }

            $row += 2;
        }

        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'attendance-alerts-');
        $writer = new $writerClass($spreadsheet);
        $writer->save($tempFile);

        return response()->download($tempFile, 'attendance-alerts-' . $endDate->format('Ymd') . '.xlsx')->deleteFileAfterSend(true);
    }
}