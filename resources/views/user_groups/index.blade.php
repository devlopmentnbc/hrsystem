@extends('layouts.app')

@section('title', 'User Groups')

@section('content')
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">User Groups</h4>
            <p class="text-muted mb-0">Create groups and assign menu permissions by action.</p>
        </div>

        @if(auth()->user()->canAccess('user_groups.create'))
            <a href="{{ route('user-groups.create') }}" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-plus-circle"></i>
                Add User Group
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-4">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Group Name</th>
                    <th>Users</th>
                    <th>Permissions</th>
                    <th width="170">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groups as $group)
                    <tr>
                        <td>{{ $group->id }}</td>
                        <td>{{ $group->name }}</td>
                        <td>{{ $group->users_count }}</td>
                        <td>{{ $group->permissions->count() }}</td>
                        <td>
                            @if(auth()->user()->canAccess('user_groups.update'))
                                <a href="{{ route('user-groups.edit', $group) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @endif

                            @if(auth()->user()->canAccess('user_groups.delete') && $group->name !== 'Admin')
                                <form method="POST" action="{{ route('user-groups.destroy', $group) }}" class="d-inline" onsubmit="return confirm('Delete this user group?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No user groups found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
