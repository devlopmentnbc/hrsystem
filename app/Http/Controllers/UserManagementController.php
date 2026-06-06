<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    private function authorizePermission(string $permission): void
    {
        AccessControl::ensureAdminSetup();

        abort_unless(auth()->check() && auth()->user()->canAccess($permission), 403);
    }

    public function index()
    {
        $this->authorizePermission('users.view');

        $users = User::with('roles')->orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $this->authorizePermission('users.create');

        $roles = Role::orderBy('name')->get();

        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $this->authorizePermission('users.create');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:4|confirmed',
            'is_active' => 'required|boolean',
            'role_name' => 'required|string|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => (bool) $data['is_active'],
        ]);

        $user->syncRoles([$data['role_name']]);

        AuditLog::record('user_role_assigned', $user, [], [
            'role_name' => $data['role_name'],
            'is_active' => (bool) $data['is_active'],
        ], 'User created and assigned to user group');

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $this->authorizePermission('users.update');

        $roles = Role::orderBy('name')->get();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizePermission('users.update');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:4|confirmed',
            'is_active' => 'required|boolean',
            'role_name' => 'required|string|exists:roles,name',
        ]);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'is_active' => (bool) $data['is_active'],
        ];

        $oldRoles = $user->roles->pluck('name')->all();

        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);
        $user->syncRoles([$data['role_name']]);

        AuditLog::record('user_role_updated', $user, [
            'roles' => $oldRoles,
            'is_active' => $user->getOriginal('is_active'),
        ], [
            'roles' => [$data['role_name']],
            'is_active' => (bool) $data['is_active'],
        ], 'User profile or group updated');

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->authorizePermission('users.delete');

        abort_if(auth()->id() === $user->id, 422, 'You cannot delete your own account.');

        AuditLog::record('user_deleted', $user, [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name')->all(),
        ], [], 'User deleted');

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
