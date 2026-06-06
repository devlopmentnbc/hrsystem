<?php

namespace App\Http\Controllers;

use App\Models\CompanyHoliday;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CompanyHolidayController extends Controller
{
    public function index()
    {
        $holidays = CompanyHoliday::orderBy('holiday_date')->get();

        return view('attendance.holidays.index', compact('holidays'));
    }

    public function create()
    {
        return view('attendance.holidays.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'holiday_name' => 'required|string|max:255',
            'holiday_date' => 'required|date|unique:company_holidays,holiday_date',
            'holiday_type' => 'required|string|max:50',
            'description' => 'nullable|string|max:1000',
            'status' => 'nullable|boolean',
        ]);

        CompanyHoliday::create([
            'holiday_name' => $request->holiday_name,
            'holiday_date' => $request->holiday_date,
            'holiday_type' => $request->holiday_type,
            'description' => $request->description,
            'status' => $request->status ?? 1,
        ]);

        return redirect()
            ->route('attendance.holidays.index')
            ->with('success', 'Company holiday created successfully.');
    }

    public function edit(CompanyHoliday $holiday)
    {
        return view('attendance.holidays.edit', compact('holiday'));
    }

    public function update(Request $request, CompanyHoliday $holiday)
    {
        $request->validate([
            'holiday_name' => 'required|string|max:255',
            'holiday_date' => 'required|date|unique:company_holidays,holiday_date,' . $holiday->id,
            'holiday_type' => 'required|string|max:50',
            'description' => 'nullable|string|max:1000',
            'status' => 'nullable|boolean',
        ]);

        $holiday->update([
            'holiday_name' => $request->holiday_name,
            'holiday_date' => $request->holiday_date,
            'holiday_type' => $request->holiday_type,
            'description' => $request->description,
            'status' => $request->status ?? 0,
        ]);

        return redirect()
            ->route('attendance.holidays.index')
            ->with('success', 'Company holiday updated successfully.');
    }

    public function destroy(CompanyHoliday $holiday)
    {
        $holiday->delete();

        return redirect()
            ->route('attendance.holidays.index')
            ->with('success', 'Company holiday deleted successfully.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'holiday_file' => 'required|file|mimes:csv,txt,xlsx,xls',
        ]);

        $rows = $this->extractRowsFromFile($request->file('holiday_file'));

        if (empty($rows)) {
            return redirect()
                ->route('attendance.holidays.index')
                ->withErrors(['holiday_file' => 'The uploaded file does not contain valid holiday rows.']);
        }

        $imported = 0;
        foreach ($rows as $row) {
            $holidayDate = $this->parseHolidayDate($row['holiday_date'] ?? null);
            if (!$holidayDate || empty($row['holiday_name'])) {
                continue;
            }

            CompanyHoliday::updateOrCreate(
                ['holiday_date' => $holidayDate->toDateString()],
                [
                    'holiday_name' => trim((string) ($row['holiday_name'] ?? '')),
                    'holiday_type' => strtolower(trim((string) ($row['holiday_type'] ?? 'company'))) ?: 'company',
                    'description' => trim((string) ($row['description'] ?? '')) ?: null,
                    'status' => $this->parseStatus($row['status'] ?? 1),
                ]
            );

            $imported++;
        }

        return redirect()
            ->route('attendance.holidays.index')
            ->with('success', "Imported {$imported} holiday rows successfully.");
    }

    private function extractRowsFromFile($file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $rows = [];
        $headers = [];

        if (in_array($extension, ['csv', 'txt'])) {
            $handle = fopen($file->getRealPath(), 'r');
            if ($handle === false) {
                return [];
            }

            while (($line = fgetcsv($handle)) !== false) {
                if (count(array_filter($line, fn ($value) => trim((string) $value) !== '')) === 0) {
                    continue;
                }

                if (empty($headers)) {
                    $headers = $this->normalizeHeaders($line);
                    continue;
                }

                $rows[] = $this->mapRow($headers, $line);
            }

            fclose($handle);
            return $rows;
        }

        $xlsxReaderClass = '\\PhpOffice\\PhpSpreadsheet\\Reader\\Xlsx';
        $xlsReaderClass = '\\PhpOffice\\PhpSpreadsheet\\Reader\\Xls';
        $readerClass = $extension === 'xls' ? $xlsReaderClass : $xlsxReaderClass;

        if (!class_exists($readerClass)) {
            return [];
        }

        $reader = new $readerClass();
        $spreadsheet = $reader->load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($sheet->getRowIterator() as $rowIndex => $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $rowData = [];

            foreach ($cellIterator as $cell) {
                $rowData[] = trim((string) $cell->getValue());
            }

            if (count(array_filter($rowData, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            if ($rowIndex === 1) {
                $headers = $this->normalizeHeaders($rowData);
                continue;
            }

            $rows[] = $this->mapRow($headers, $rowData);
        }

        return $rows;
    }

    private function normalizeHeaders(array $headers): array
    {
        return array_map(function ($value) {
            $normalized = strtolower(trim((string) $value));
            $normalized = str_replace([' ', '-', '/'], '_', $normalized);
            return $normalized;
        }, $headers);
    }

    private function mapRow(array $headers, array $row): array
    {
        $mapped = [];
        foreach ($headers as $index => $header) {
            $mapped[$header] = $row[$index] ?? null;
        }

        return $mapped;
    }

    private function parseHolidayDate($value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseStatus($value): bool
    {
        $normalized = strtolower(trim((string) $value));

        return !in_array($normalized, ['0', 'false', 'inactive', 'no'], true);
    }
}
