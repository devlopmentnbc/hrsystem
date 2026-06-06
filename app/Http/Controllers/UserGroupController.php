<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Support\AccessControl;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserGroupController extends Controller
{
    private function authorizePermission(string $permission): void
    {
        AccessControl::ensureAdminSetup();

        abort_unless(auth()->check() && auth()->user()->canAccess($permission), 403);
    }

    public function index()
    {
        $this->authorizePermission('user_groups.view');

        $groups = Role::withCount('users')->with('permissions')->orderBy('name')->get();

        return view('user_groups.index', compact('groups'));
    }

    public function create()
    {
        $this->authorizePermission('user_groups.create');

        AccessControl::syncPermissions();
        $permissionMatrix = AccessControl::groupedPermissions();

        return view('user_groups.create', compact('permissionMatrix'));
    }

    public function store(Request $request)
    {
        $this->authorizePermission('user_groups.create');

        AccessControl::syncPermissions();

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $group = Role::create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        $group->syncPermissions($data['permissions'] ?? []);

        AuditLog::record('user_group_created', null, [], [
            'group_name' => $group->name,
            'permissions' => $data['permissions'] ?? [],
        ], 'User group created');

        return redirect()->route('user-groups.index')->with('success', 'User group created successfully.');
    }

    public function edit(Role $user_group)
    {
        $this->authorizePermission('user_groups.update');

        AccessControl::syncPermissions();
        $permissionMatrix = AccessControl::groupedPermissions();
        $assignedPermissions = $user_group->permissions->pluck('name')->all();

        return view('user_groups.edit', [
            'group' => $user_group,
            'permissionMatrix' => $permissionMatrix,
            'assignedPermissions' => $assignedPermissions,
        ]);
    }

    public function update(Request $request, Role $user_group)
    {
        $this->authorizePermission('user_groups.update');

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $user_group->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $oldValues = [
            'name' => $user_group->name,
            'permissions' => $user_group->permissions->pluck('name')->all(),
        ];

        $user_group->update([
            'name' => $data['name'],
        ]);

        $user_group->syncPermissions($data['permissions'] ?? []);

        if ($user_group->name === 'Admin') {
            $user_group->syncPermissions(Permission::query()->pluck('name')->all());
        }

        AuditLog::record('user_group_updated', null, $oldValues, [
            'name' => $user_group->name,
            'permissions' => $user_group->permissions->pluck('name')->all(),
        ], 'User group updated');

        return redirect()->route('user-groups.index')->with('success', 'User group updated successfully.');
    }

    public function destroy(Role $user_group)
    {
        $this->authorizePermission('user_groups.delete');

        abort_if($user_group->name === 'Admin', 422, 'Admin group cannot be deleted.');
        abort_if($user_group->users()->exists(), 422, 'This group is assigned to users and cannot be deleted.');

        AuditLog::record('user_group_deleted', null, [
            'name' => $user_group->name,
            'permissions' => $user_group->permissions->pluck('name')->all(),
        ], [], 'User group deleted');

        $user_group->delete();

        return redirect()->route('user-groups.index')->with('success', 'User group deleted successfully.');
    }
}
