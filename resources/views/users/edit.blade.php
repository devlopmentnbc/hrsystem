@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="content-card">
    <h4 class="fw-bold mb-4">Edit User</h4>

    @if ($errors->any())
        <div class="alert alert-danger rounded-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold">New Password</label>
                <input type="password" name="password" class="form-control">
                <small class="text-muted">Leave blank to keep existing password.</small>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold">User Group</label>
                <select name="role_name" class="form-select" required>
                    <option value="">Select group</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ old('role_name', $user->roles->pluck('name')->first()) === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold">Status</label>
                <select name="is_active" class="form-select" required>
                    <option value="1" {{ old('is_active', $user->is_active ? '1' : '0') == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active', $user->is_active ? '1' : '0') == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <div class="d-flex gap-3">
            <button type="submit" class="btn btn-primary rounded-pill px-5">Update User</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary rounded-pill px-5">Cancel</a>
        </div>
    </form>
</div>
@endsection
