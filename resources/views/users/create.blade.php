@extends('layouts.app')

@section('title', 'Add User')

@section('content')
<div class="content-card">
    <h4 class="fw-bold mb-4">Add User</h4>

    @if ($errors->any())
        <div class="alert alert-danger rounded-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('users.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold">User Group</label>
                <select name="role_name" class="form-select" required>
                    <option value="">Select group</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ old('role_name') === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold">Status</label>
                <select name="is_active" class="form-select" required>
                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <div class="d-flex gap-3">
            <button type="submit" class="btn btn-primary rounded-pill px-5">Save User</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary rounded-pill px-5">Cancel</a>
        </div>
    </form>
</div>
@endsection
