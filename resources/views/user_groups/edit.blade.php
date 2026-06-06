@extends('layouts.app')

@section('title', 'Edit User Group')

@section('content')
<div class="content-card">
    <h4 class="fw-bold mb-4">Edit User Group</h4>

    @if ($errors->any())
        <div class="alert alert-danger rounded-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('user-groups.update', $group) }}">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="form-label fw-semibold">Group Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $group->name) }}" required>
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th class="text-start">Menu / Module</th>
                        @foreach(\App\Support\AccessControl::actions() as $actionLabel)
                            <th>{{ $actionLabel }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($permissionMatrix as $row)
                        <tr>
                            <td class="text-start fw-semibold">{{ $row['label'] }}</td>
                            @foreach($row['permissions'] as $permission)
                                <td>
                                    <input type="checkbox" name="permissions[]" value="{{ $permission }}" {{ in_array($permission, old('permissions', $assignedPermissions)) ? 'checked' : '' }} @if($group->name === 'Admin') checked disabled @endif>
                                    @if($group->name === 'Admin')
                                        <input type="hidden" name="permissions[]" value="{{ $permission }}">
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex gap-3">
            <button type="submit" class="btn btn-primary rounded-pill px-5">Update User Group</button>
            <a href="{{ route('user-groups.index') }}" class="btn btn-secondary rounded-pill px-5">Cancel</a>
        </div>
    </form>
</div>
@endsection
