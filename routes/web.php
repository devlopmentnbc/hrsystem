<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeGroupChangeController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\ShiftsGroupController;
use App\Http\Controllers\ShiftScheduleController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceAlertReportController;
use App\Http\Controllers\AttendanceNotificationSettingController;
use App\Http\Controllers\CompanyHolidayController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AttendanceCorrectionController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\UserGroupController;

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    // Login Page
    Route::get('/login', [UserController::class, 'index'])
        ->name('login');

    // Login Submit
    Route::post('/login', [UserController::class, 'authenticate'])
        ->name('login.submit');

});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Logout
    Route::post('/logout', [UserController::class, 'logout'])
        ->name('logout');

    Route::get('/users', [UserManagementController::class, 'index'])
        ->name('users.index');

    Route::get('/users/create', [UserManagementController::class, 'create'])
        ->name('users.create');

    Route::post('/users', [UserManagementController::class, 'store'])
        ->name('users.store');

    Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])
        ->name('users.edit');

    Route::put('/users/{user}', [UserManagementController::class, 'update'])
        ->name('users.update');

    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])
        ->name('users.destroy');

    Route::get('/notification-settings', [AttendanceNotificationSettingController::class, 'index'])
        ->name('notification-settings.index');

    Route::get('/notification-settings/create', [AttendanceNotificationSettingController::class, 'create'])
        ->name('notification-settings.create');

    Route::post('/notification-settings', [AttendanceNotificationSettingController::class, 'store'])
        ->name('notification-settings.store');

    Route::get('/notification-settings/{notification_setting}/edit', [AttendanceNotificationSettingController::class, 'edit'])
        ->name('notification-settings.edit');

    Route::put('/notification-settings/{notification_setting}', [AttendanceNotificationSettingController::class, 'update'])
        ->name('notification-settings.update');

    Route::delete('/notification-settings/{notification_setting}', [AttendanceNotificationSettingController::class, 'destroy'])
        ->name('notification-settings.destroy');

    Route::post('/notification-settings/{notification_setting}/send-now', [AttendanceNotificationSettingController::class, 'sendNow'])
        ->name('notification-settings.send-now');

    Route::get('/user-groups', [UserGroupController::class, 'index'])
        ->name('user-groups.index');

    Route::get('/user-groups/create', [UserGroupController::class, 'create'])
        ->name('user-groups.create');

    Route::post('/user-groups', [UserGroupController::class, 'store'])
        ->name('user-groups.store');

    Route::get('/user-groups/{user_group}/edit', [UserGroupController::class, 'edit'])
        ->name('user-groups.edit');

    Route::put('/user-groups/{user_group}', [UserGroupController::class, 'update'])
        ->name('user-groups.update');

    Route::delete('/user-groups/{user_group}', [UserGroupController::class, 'destroy'])
        ->name('user-groups.destroy');


    /*
|--------------------------------------------------------------------------
| Departments
|--------------------------------------------------------------------------
*/

    Route::get('/departments', [DepartmentController::class, 'index'])
        ->name('departments.index');

    Route::get('/departments/create', [DepartmentController::class, 'create'])
        ->name('departments.create');

    Route::post('/departments/store', [DepartmentController::class, 'store'])
        ->name('departments.store');


    Route::get('/departments/{department}/edit', [DepartmentController::class, 'edit'])
        ->name('departments.edit');

    Route::put('/departments/{department}', [DepartmentController::class, 'update'])
        ->name('departments.update');



    /*
|--------------------------------------------------------------------------
| Designations
|--------------------------------------------------------------------------
*/

    Route::get('/designations', [DesignationController::class, 'index'])
        ->name('designations.index');

    Route::get('/designations/create', [DesignationController::class, 'create'])
        ->name('designations.create');

    Route::post('/designations/store', [DesignationController::class, 'store'])
        ->name('designations.store');


    Route::get('/designations/{designation}/edit', [DesignationController::class, 'edit'])
        ->name('designations.edit');

    Route::put('/designations/{designation}', [DesignationController::class, 'update'])
        ->name('designations.update');


    /*
   |--------------------------------------------------------------------------
   | Employees
   |--------------------------------------------------------------------------
   */

    Route::get('/employees', [EmployeeController::class, 'index'])
        ->name('employees.index');

    Route::get('/employees/create', [EmployeeController::class, 'create'])
        ->name('employees.create');

    Route::post('/employees/store', [EmployeeController::class, 'store'])
        ->name('employees.store');


    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])
        ->name('employees.edit');

    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])
        ->name('employees.update');

    Route::get('/employees/group-changes', [EmployeeGroupChangeController::class, 'index'])
        ->name('employees.group-changes.index');

    Route::get('/employees/{employee}/group-change', [EmployeeGroupChangeController::class, 'create'])
        ->name('employees.group-changes.create');

    Route::post('/employees/{employee}/group-change', [EmployeeGroupChangeController::class, 'store'])
        ->name('employees.group-changes.store');


        /*
   |--------------------------------------------------------------------------
   | Shifts
   |--------------------------------------------------------------------------
   */

    Route::get('/shifts', [ShiftController::class, 'index'])
        ->name('shifts.index');

    Route::get('/shifts/create', [ShiftController::class, 'create'])
        ->name('shifts.create');

    Route::post('/shifts/store', [ShiftController::class, 'store'])
        ->name('shifts.store');


    Route::get('/shifts/{shift}/edit', [ShiftController::class, 'edit'])
        ->name('shifts.edit');

    Route::put('/shifts/{shift}', [ShiftController::class, 'update'])
        ->name('shifts.update');

    /*
   |--------------------------------------------------------------------------
   | Shifts Groups
   |--------------------------------------------------------------------------
   */

    Route::get('/shifts_groups', [ShiftsGroupController::class, 'index'])
        ->name('shifts_groups.index');

    Route::get('/shifts_groups/create', [ShiftsGroupController::class, 'create'])
        ->name('shifts_groups.create');

    Route::post('/shifts_groups/store', [ShiftsGroupController::class, 'store'])
        ->name('shifts_groups.store');


    Route::get('/shifts_groups/{shifts_group}/edit', [ShiftsGroupController::class, 'edit'])
        ->name('shifts_groups.edit');

    Route::put('/shifts_groups/{shifts_group}', [ShiftsGroupController::class, 'update'])
        ->name('shifts_groups.update');

    Route::get('/attendance/leave-requests', [AttendanceController::class, 'index'])
        ->name('attendance.leave_request.index');

    Route::get('/attendance/leave-requests/create', [AttendanceController::class, 'createLeaveRequest'])
        ->name('attendance.leave_request.create');

    Route::post('/attendance/leave-requests', [AttendanceController::class, 'storeLeaveRequest'])
        ->name('attendance.leave_request.store');

    Route::get('/attendance/leave-requests/{id}/edit', [AttendanceController::class, 'editLeaveRequest'])
        ->name('attendance.leave_request.edit');

    Route::put('/attendance/leave-requests/{id}', [AttendanceController::class, 'updateLeaveRequest'])
        ->name('attendance.leave_request.update');

    Route::post('/attendance/leave-requests/{id}/approve', [AttendanceController::class, 'approveLeaveRequest'])
        ->name('attendance.leave_request.approve');

    Route::get('/attendance/timesheet', [AttendanceController::class, 'uploadTimesheet'])
        ->name('attendance.timesheet.upload');

    Route::post('/attendance/timesheet', [AttendanceController::class, 'storeTimesheet'])
        ->name('attendance.timesheet.store');

    Route::get('/attendance/timesheet/records', [AttendanceController::class, 'viewTimesheetRecords'])
        ->name('attendance.timesheet.records');

    Route::get('/attendance/mispunch', [AttendanceCorrectionController::class, 'index'])
        ->name('attendance.mispunch.index');

    Route::get('/attendance/mispunch/create', [AttendanceCorrectionController::class, 'create'])
        ->name('attendance.mispunch.create');

    Route::post('/attendance/mispunch', [AttendanceCorrectionController::class, 'store'])
        ->name('attendance.mispunch.store');

    Route::get('/attendance/mispunch/{mispunch}/edit', [AttendanceCorrectionController::class, 'edit'])
        ->name('attendance.mispunch.edit');

    Route::put('/attendance/mispunch/{mispunch}', [AttendanceCorrectionController::class, 'update'])
        ->name('attendance.mispunch.update');

    Route::get('/attendance/processing', [AttendanceController::class, 'attendanceProcessing'])
        ->name('attendance.processing');

    Route::get('/attendance/summary', [AttendanceController::class, 'attendanceSummary'])
        ->name('attendance.summary');

    Route::get('/attendance/detail/{employeeId}', [AttendanceController::class, 'attendanceDetail'])
        ->name('attendance.detail');

    Route::get('/reports/attendance-alerts', [AttendanceAlertReportController::class, 'index'])
        ->name('reports.attendance-alerts.index');

    Route::get('/reports/attendance-alerts/export/{format}', [AttendanceAlertReportController::class, 'export'])
        ->whereIn('format', ['xlsx', 'pdf'])
        ->name('reports.attendance-alerts.export');

    Route::get('/attendance/holidays', [CompanyHolidayController::class, 'index'])
        ->name('attendance.holidays.index');

    Route::get('/attendance/holidays/create', [CompanyHolidayController::class, 'create'])
        ->name('attendance.holidays.create');

    Route::post('/attendance/holidays', [CompanyHolidayController::class, 'store'])
        ->name('attendance.holidays.store');

    Route::post('/attendance/holidays/import', [CompanyHolidayController::class, 'import'])
        ->name('attendance.holidays.import');

    Route::get('/attendance/holidays/{holiday}/edit', [CompanyHolidayController::class, 'edit'])
        ->name('attendance.holidays.edit');

    Route::put('/attendance/holidays/{holiday}', [CompanyHolidayController::class, 'update'])
        ->name('attendance.holidays.update');

    Route::delete('/attendance/holidays/{holiday}', [CompanyHolidayController::class, 'destroy'])
        ->name('attendance.holidays.destroy');

    Route::get('/audit-logs', [AuditLogController::class, 'index'])
        ->name('audit-logs.index');

    Route::get('/shifts-schedule', [ShiftScheduleController::class, 'index'])
        ->name('shifts_schedule.index');

    Route::get('/shifts-schedule/availability', [ShiftScheduleController::class, 'availability'])
        ->name('shifts_schedule.availability');

    Route::post('/shifts-schedule', [ShiftScheduleController::class, 'store'])
        ->name('shifts_schedule.store');

    Route::get('/shifts-schedule/{id}/edit', [ShiftScheduleController::class, 'edit'])
        ->name('shifts_schedule.edit');

    Route::put('/shifts-schedule/{id}', [ShiftScheduleController::class, 'update'])
        ->name('shifts_schedule.update');

    Route::post('/shifts-schedule/{id}/toggle', [ShiftScheduleController::class, 'toggleActive'])
        ->name('shifts_schedule.toggle');

});

/*
|--------------------------------------------------------------------------
| Default Redirect
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    return redirect('/login');

});



