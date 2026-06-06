@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Users</h4>
            <p class="text-muted mb-0">Create users and assign them to a user group.</p>
        </div>

        @if(auth()->user()->canAccess('users.create'))
            <a href="{{ route('users.create') }}" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-plus-circle"></i>
                Add User
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
                    <th>Name</th>
                    <th>Email</th>
                    <th>User Group</th>
                    <th>Status</th>
                    <th width="160">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->roles->pluck('name')->implode(', ') ?: '-' }}</td>
                        <td>
                            @if($user->is_active)
                                <span class="badge bg-success rounded-pill">Active</span>
                            @else
                                <span class="badge bg-danger rounded-pill">Inactive</span>
                            @endif
                        </td>
                        <td>
                            @if(auth()->user()->canAccess('users.update'))
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @endif

                            @if(auth()->user()->canAccess('users.delete') && auth()->id() !== $user->id)
                                <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Delete this user?');">
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
                        <td colspan="6" class="text-center">No users found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
