<?php

namespace App\Support;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AccessControl
{
    public static function actions(): array
    {
        return [
            'view' => 'View',
            'create' => 'Add',
            'update' => 'Update',
            'delete' => 'Delete',
            'print' => 'Print',
        ];
    }

    public static function modules(): array
    {
        return [
            'dashboard' => 'Dashboard',
            'departments' => 'Departments',
            'designations' => 'Designations',
            'employees' => 'Employees',
            'users' => 'Users',
            'user_groups' => 'User Groups',
            'shifts' => 'Shifts',
            'shifts_groups' => 'Shift Groups',
            'shift_schedule' => 'Shift Schedule',
            'attendance_leave_requests' => 'Leave Requests',
            'attendance_timesheet_upload' => 'Time Sheet Upload',
            'attendance_timesheet_records' => 'Time Sheet Records',
            'attendance_mispunch' => 'Mispunch Corrections',
            'attendance_reports' => 'Attendance Reports',
            'attendance_holidays' => 'Company Holidays',
            'audit_logs' => 'Audit Logs',
        ];
    }

    public static function permissionName(string $module, string $action): string
    {
        return $module . '.' . $action;
    }

    public static function permissions(): array
    {
        $permissions = [];

        foreach (array_keys(static::modules()) as $module) {
            foreach (array_keys(static::actions()) as $action) {
                $permissions[] = static::permissionName($module, $action);
            }
        }

        return $permissions;
    }

    public static function syncPermissions(): void
    {
        foreach (static::permissions() as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public static function ensureAdminSetup(): void
    {
        static::syncPermissions();

        $adminRole = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

        $adminRole->syncPermissions(Permission::query()->pluck('name')->all());

        $firstUser = User::query()->orderBy('id')->first();
        if ($firstUser && !$firstUser->hasRole('Admin')) {
            $firstUser->syncRoles([$adminRole->name]);
        }
    }

    public static function groupedPermissions(): array
    {
        $rows = [];
        foreach (static::modules() as $key => $label) {
            $permissions = [];
            foreach (static::actions() as $actionKey => $actionLabel) {
                $permissions[$actionKey] = static::permissionName($key, $actionKey);
            }

            $rows[] = [
                'key' => $key,
                'label' => $label,
                'permissions' => $permissions,
            ];
        }

        return $rows;
    }
}
