<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        AccessControl::syncPermissions();

        $allPermissions = Permission::query()->pluck('name')->all();

        $adminRole = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);
        $adminRole->syncPermissions($allPermissions);

        $hrRole = Role::firstOrCreate([
            'name' => 'HR',
            'guard_name' => 'web',
        ]);
        $hrRole->syncPermissions([
            'dashboard.view',
            'employees.view',
            'attendance_leave_requests.view',
            'attendance_leave_requests.create',
            'attendance_leave_requests.update',
            'attendance_timesheet_upload.view',
            'attendance_timesheet_upload.create',
            'attendance_timesheet_records.view',
            'attendance_reports.view',
            'attendance_reports.print',
            'attendance_holidays.view',
        ]);

        $auditorRole = Role::firstOrCreate([
            'name' => 'Auditor',
            'guard_name' => 'web',
        ]);
        $auditorRole->syncPermissions([
            'dashboard.view',
            'attendance_timesheet_records.view',
            'attendance_reports.view',
            'attendance_reports.print',
            'audit_logs.view',
        ]);

        $firstUser = User::query()->orderBy('id')->first();
        if ($firstUser && !$firstUser->hasRole('Admin')) {
            $firstUser->syncRoles(['Admin']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}