<?php

namespace App\Http\Controllers;

use App\Models\AttendanceTimesheet;
use App\Models\CompanyHoliday;
use App\Models\Departments;
use App\Models\Employees;
use App\Models\LeaveRequest;
use App\Models\Shifts;
use App\Models\ShiftsGroup;
use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        AccessControl::ensureAdminSetup();
        abort_unless(auth()->check() && auth()->user()->canAccess('dashboard.view'), 403);

        $totalEmployees = Employees::where('status', 1)->count();
        $activeUsers = User::where('is_active', true)->count();
        $departmentCount = Departments::count();
        $shiftCount = Shifts::count();
        $shiftGroupCount = ShiftsGroup::count();
        $holidayCount = CompanyHoliday::where('status', true)->count();
        $pendingLeaves = LeaveRequest::where('status', 'pending')->count();
        $approvedLeaves = LeaveRequest::where('status', 'approved')->count();
        $attendanceRowsThisMonth = AttendanceTimesheet::whereBetween('recorded_at', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ])->count();

        $latestAttendanceAt = AttendanceTimesheet::max('recorded_at');
        $referenceDate = $latestAttendanceAt ? Carbon::parse($latestAttendanceAt)->toDateString() : null;

        $presentOnReferenceDate = $referenceDate
            ? AttendanceTimesheet::whereDate('recorded_at', $referenceDate)
                ->whereNotNull('employee_id')
                ->distinct('employee_id')
                ->count('employee_id')
            : 0;

        $absentOnReferenceDate = max(0, $totalEmployees - $presentOnReferenceDate);

        $recentAttendanceRaw = AttendanceTimesheet::query()
            ->selectRaw('employee_id, DATE(recorded_at) as attendance_date, MIN(recorded_at) as first_punch, MAX(recorded_at) as last_punch, COUNT(*) as punch_count')
            ->whereNotNull('employee_id')
            ->groupBy('employee_id', DB::raw('DATE(recorded_at)'))
            ->orderByDesc('attendance_date')
            ->orderByDesc(DB::raw('MAX(recorded_at)'))
            ->limit(10)
            ->get();

        $employeeMap = Employees::whereIn('id', $recentAttendanceRaw->pluck('employee_id')->filter()->unique()->all())
            ->get()
            ->keyBy('id');

        $recentAttendance = $recentAttendanceRaw->map(function ($row) use ($employeeMap) {
            $employee = $employeeMap->get($row->employee_id);
            $firstPunch = Carbon::parse($row->first_punch);
            $lastPunch = Carbon::parse($row->last_punch);
            $status = $row->punch_count > 1 ? 'Complete' : 'Missed Punch';
            $badge = $row->punch_count > 1 ? 'success' : 'warning';

            return [
                'employee_name' => $employee?->employee_name ?? 'Unknown Employee',
                'employee_code' => $employee?->employee_code ?? '-',
                'attendance_date' => Carbon::parse($row->attendance_date),
                'first_punch' => $firstPunch,
                'last_punch' => $lastPunch,
                'punch_count' => (int) $row->punch_count,
                'status' => $status,
                'badge' => $badge,
            ];
        });

        $latestLeaves = LeaveRequest::with('employee')
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'totalEmployees',
            'activeUsers',
            'departmentCount',
            'shiftCount',
            'shiftGroupCount',
            'holidayCount',
            'pendingLeaves',
            'approvedLeaves',
            'attendanceRowsThisMonth',
            'referenceDate',
            'presentOnReferenceDate',
            'absentOnReferenceDate',
            'recentAttendance',
            'latestLeaves'
        ));
    }
}
