<style>
    .sidebar {
        width: 270px;
        height: 100vh;
        background: linear-gradient(180deg, #2563eb, #4f46e5, #7c3aed);
        color: white;
        position: fixed;
        left: 0;
        top: 0;
        padding: 25px;
        z-index: 999;
        overflow-y: auto;
        overflow-x: hidden;
        scrollbar-width: thin;
    }

    .sidebar .logo {
        display: flex;
        align-items: center;
        margin-bottom: 40px;
    }

    .sidebar .logo-icon {
        width: 60px;
        height: 60px;
        border-radius: 20px;
        background: rgba(255, 255, 255, .15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        backdrop-filter: blur(10px);
    }

    .sidebar .logo-text {
        margin-left: 15px;
    }

    .sidebar .logo-text h4 {
        margin: 0;
        font-weight: 700;
    }

    .sidebar .logo-text small {
        color: rgba(255, 255, 255, .7);
    }

    .menu-title {
        font-size: 13px;
        color: rgba(255, 255, 255, .7);
        margin-bottom: 15px;
        text-transform: uppercase;
    }

    .sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sidebar-menu li {
        margin-bottom: 10px;
    }

    .sidebar-menu a {
        text-decoration: none;
        color: white;
        display: flex;
        align-items: center;
        padding: 14px 18px;
        border-radius: 16px;
        transition: .3s;
        font-weight: 500;
    }

    .sidebar-menu a:hover,
    .sidebar-menu .active {
        background: rgba(255, 255, 255, .15);
    }

    .sidebar-menu i {
        font-size: 20px;
        margin-right: 14px;
    }

    @media(max-width:991px) {

        .sidebar {
            width: 100%;
            position: relative;
            min-height: auto;
            height: auto;
            overflow: visible;
            border-radius: 0 0 30px 30px;
        }

    }
</style>

<!-- SIDEBAR -->
<div class="sidebar">

    <!-- Logo -->
    <div class="logo">

        <div class="logo-icon">

            <i class="bi bi-people-fill"></i>

        </div>

        <div class="logo-text">

            <h4>HR System</h4>

            <small>Management Panel</small>

        </div>

    </div>

    <!-- Menu -->
    <div class="menu-title">
        Main Menu
    </div>

    <ul class="sidebar-menu">

        @php
            $authUser = auth()->user();
            $canViewEmployeeMenu = $authUser && $authUser->canAccessAny(['departments.view', 'designations.view', 'employees.view']);
            $canViewUsersMenu = $authUser && $authUser->canAccessAny(['users.view', 'user_groups.view']);
            $canViewAttendanceMenu = $authUser && $authUser->canAccessAny(['attendance_leave_requests.view', 'attendance_timesheet_upload.view', 'attendance_timesheet_records.view', 'attendance_mispunch.view', 'attendance_reports.view', 'attendance_holidays.view']);
            $canViewReportsMenu = $authUser && $authUser->canAccess('attendance_reports.view');
            $canViewShiftMenu = $authUser && $authUser->canAccessAny(['shifts.view', 'shifts_groups.view', 'shift_schedule.view']);
            $canViewAuditLogs = $authUser && $authUser->canAccess('audit_logs.view');
        @endphp

        @if($authUser && $authUser->canAccess('dashboard.view'))
            <li>
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">

                    <i class="bi bi-grid-fill"></i>

                    Dashboard

                </a>
            </li>
        @endif

        @if($canViewReportsMenu)
        <li>
            <a data-bs-toggle="collapse" href="#reportsMenu" role="button"
                class="{{ request()->routeIs('attendance.processing', 'attendance.summary', 'attendance.detail', 'reports.attendance-alerts.*') ? 'active' : '' }}">

                <i class="bi bi-file-earmark-bar-graph"></i>

                Reports

            </a>

            <div class="collapse mt-2 {{ request()->routeIs('attendance.processing', 'attendance.summary', 'attendance.detail', 'reports.attendance-alerts.*') ? 'show' : '' }}" id="reportsMenu">
                <ul class="list-unstyled ms-4">
                    <li class="mb-2">
                        <a href="{{ route('attendance.processing') }}" class="text-white text-decoration-none {{ request()->routeIs('attendance.processing', 'attendance.summary', 'attendance.detail') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-spreadsheet"></i>
                            Attendance Reports
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('reports.attendance-alerts.index') }}" class="text-white text-decoration-none {{ request()->routeIs('reports.attendance-alerts.*') ? 'active' : '' }}">
                            <i class="bi bi-envelope-exclamation"></i>
                            Attendance Alert Reports
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        @endif

        @if($canViewEmployeeMenu)
        <li>

            <a data-bs-toggle="collapse" href="#employeeMenu" role="button">

                <i class="bi bi-people"></i>

                Employees

            </a>

            <div class="collapse mt-2" id="employeeMenu">

                <ul class="list-unstyled ms-4">

                    @if($authUser->canAccess('departments.view'))

                    <li class="mb-2">

                        <a href="{{ route('departments.index') }}" class="text-white text-decoration-none">

                            <i class="bi bi-building"></i>

                            Departments

                        </a>

                    </li>
                    @endif
                    @if($authUser->canAccess('designations.view'))
                    <li class="mb-2">

                        <a href="{{ route('designations.index') }}" class="text-white text-decoration-none">

                            <i class="bi bi-briefcase"></i>

                            Designations

                        </a>

                    </li>
                    @endif
                    @if($authUser->canAccess('employees.view'))
                    <li class="mb-2">

                        <a href="{{ route('employees.index') }}" class="text-white text-decoration-none">

                            <i class="bi bi-briefcase"></i>

                            Employees

                        </a>

                    </li>
                    @endif
                    @if($authUser->canAccess('employees.update'))
                    <li class="mb-2">

                        <a href="{{ route('employees.group-changes.index') }}" class="text-white text-decoration-none {{ request()->routeIs('employees.group-changes.*') ? 'active' : '' }}">

                            <i class="bi bi-arrow-left-right"></i>

                            Group Changes

                        </a>

                    </li>
                    @endif
                </ul>

            </div>

        </li>
        @endif

        @if($canViewUsersMenu)
            <li>
                <a data-bs-toggle="collapse" href="#usersMenu" role="button"
                    class="{{ request()->routeIs('users.*', 'user-groups.*', 'notification-settings.*') ? 'active' : '' }}">

                    <i class="bi bi-person-badge"></i>

                    Users

                </a>

                <div class="collapse mt-2 {{ request()->routeIs('users.*', 'user-groups.*', 'notification-settings.*') ? 'show' : '' }}" id="usersMenu">
                    <ul class="list-unstyled ms-4">
                        @if($authUser->canAccess('users.view'))
                            <li class="mb-2">
                                <a href="{{ route('users.index') }}" class="text-white text-decoration-none {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                    <i class="bi bi-people-fill"></i>
                                    User Management
                                </a>
                            </li>
                        @endif

                        @if($authUser->canAccess('user_groups.view'))
                            <li class="mb-2">
                                <a href="{{ route('user-groups.index') }}" class="text-white text-decoration-none {{ request()->routeIs('user-groups.*') ? 'active' : '' }}">
                                    <i class="bi bi-diagram-3"></i>
                                    User Groups
                                </a>
                            </li>
                        @endif

                        @if($authUser->canAccess('users.update'))
                            <li class="mb-2">
                                <a href="{{ route('notification-settings.index') }}" class="text-white text-decoration-none {{ request()->routeIs('notification-settings.*') ? 'active' : '' }}">
                                    <i class="bi bi-envelope-paper"></i>
                                    Notification Emails
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </li>
        @endif

        @if($canViewAttendanceMenu)
        <li>
            <a data-bs-toggle="collapse" href="#attendanceMenu" role="button"
                class="{{ request()->routeIs('attendance.*') ? 'active' : '' }}">

                <i class="bi bi-fingerprint"></i>

                Attendance

            </a>

            <div class="collapse mt-2 {{ request()->routeIs('attendance.*') ? 'show' : '' }}" id="attendanceMenu">

                <ul class="list-unstyled ms-4">

                    @if($authUser->canAccess('attendance_leave_requests.view'))

                    <li class="mb-2">

                        <a href="{{ route('attendance.leave_request.index') }}" class="text-white text-decoration-none {{ request()->routeIs('attendance.leave_request.*') ? 'active' : '' }}">

                            <i class="bi bi-calendar-check"></i>

                            Leave Requests

                        </a>
                    </li>
                    @endif
                    @if($authUser->canAccess('attendance_timesheet_upload.view'))
                    <li class="mb-2">

                        <a href="{{ route('attendance.timesheet.upload') }}" class="text-white text-decoration-none {{ request()->routeIs('attendance.timesheet.upload') ? 'active' : '' }}">

                            <i class="bi bi-file-earmark-arrow-up"></i>

                            Time Sheet Upload

                        </a>

                    </li>
                    @endif
                    @if($authUser->canAccess('attendance_timesheet_records.view'))
                    <li class="mb-2">

                        <a href="{{ route('attendance.timesheet.records') }}" class="text-white text-decoration-none {{ request()->routeIs('attendance.timesheet.records') ? 'active' : '' }}">

                            <i class="bi bi-table"></i>

                            Time Sheet Records

                        </a>

                    </li>
                    @endif
                    @if($authUser->canAccess('attendance_mispunch.view'))
                    <li class="mb-2">

                        <a href="{{ route('attendance.mispunch.index') }}" class="text-white text-decoration-none {{ request()->routeIs('attendance.mispunch.*') ? 'active' : '' }}">

                            <i class="bi bi-pencil-square"></i>

                            Mispunch Corrections

                        </a>

                    </li>
                    @endif
                    @if($authUser->canAccess('attendance_reports.view'))
                    <li class="mb-2">

                        <a href="{{ route('attendance.processing') }}" class="text-white text-decoration-none {{ request()->routeIs('attendance.processing', 'attendance.summary', 'attendance.detail') ? 'active' : '' }}">

                            <i class="bi bi-file-earmark-spreadsheet"></i>

                            Attendance Reports

                        </a>

                    </li>
                    @endif
                    @if($authUser->canAccess('attendance_holidays.view'))
                    <li class="mb-2">

                        <a href="{{ route('attendance.holidays.index') }}" class="text-white text-decoration-none {{ request()->routeIs('attendance.holidays.*') ? 'active' : '' }}">

                            <i class="bi bi-calendar-event"></i>

                            Company Holidays

                        </a>

                    </li>
                    @endif
                </ul>

            </div>
        </li>
        @endif

        @if($canViewAuditLogs)
        <li>
            <a href="{{ route('audit-logs.index') }}" class="{{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                <i class="bi bi-journal-text"></i>
                Audit Logs
            </a>
        </li>
        @endif

        <li>
            <a href="#">

                <i class="bi bi-clock-history"></i>

                OT Management

            </a>
        </li>

        @if($canViewShiftMenu)
        <li>
            <a  href="#shiftMenu" data-bs-toggle="collapse" role="button">

                <i class="bi bi-calendar-event"></i>

                Shift Rosters

            </a>

            <div class="collapse mt-2" id="shiftMenu">

                <ul class="list-unstyled ms-4">

                    @if($authUser->canAccess('shifts.view'))

                    <li class="mb-2">

                        <a href="{{ route('shifts.index') }}" class="text-white text-decoration-none">

                            <i class="bi bi-building"></i>

                            Shifts

                        </a>

                    </li>
                    @endif
                    @if($authUser->canAccess('shifts_groups.view'))
                    <li class="mb-2">

                        <a href="{{ route('shifts_groups.index') }}" class="text-white text-decoration-none">

                            <i class="bi bi-briefcase"></i>

                            Shift Groups

                        </a>

                    </li>
                    @endif
                    @if($authUser->canAccess('shift_schedule.view'))
                    <li class="mb-2">

                        <a href="{{ route('shifts_schedule.index') }}" class="text-white text-decoration-none">

                            <i class="bi bi-clock"></i>

                            Schedule Shift Groups

                        </a>

                    </li>
                    @endif
                </ul>
            </div>
        </li>
        @endif

        <li>
            <a href="#">

                <i class="bi bi-file-earmark-bar-graph"></i>

                Reports

            </a>
        </li>

        <li>
            <a href="#">

                <i class="bi bi-gear"></i>

                Settings

            </a>
        </li>

    </ul>

</div>