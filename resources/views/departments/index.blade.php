@extends('layouts.app')

@section('title', 'Departments')

@section('content')

<div class="content-card">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h4 class="fw-bold mb-1">
                Departments
            </h4>

            <p class="text-muted mb-0">
                Manage department master data
            </p>

        </div>

        <a
            href="{{ route('departments.create') }}"
            class="btn btn-primary rounded-pill px-4"
        >

            <i class="bi bi-plus-circle"></i>

            Add Department

        </a>

    </div>

    @if(session('success'))

        <div class="alert alert-success rounded-4">

            {{ session('success') }}

        </div>

    @endif

    <div class="table-responsive">

        <table class="table align-middle">

            <thead>

                <tr>

                    <th>ID</th>
                    <th>Department</th>
                    <th>Code</th>
                    <th>Status</th>
                    <th width="120">Action</th>

                </tr>

            </thead>

            <tbody>

                @forelse($departments as $department)

                    <tr>

                        <td>{{ $department->id }}</td>

                        <td>
                            {{ $department->department_name }}
                        </td>

                        <td>
                            {{ $department->department_code }}
                        </td>

                        <td>

                            @if($department->status)

                                <span class="badge bg-success rounded-pill">
                                    Active
                                </span>

                            @else

                                <span class="badge bg-danger rounded-pill">
                                    Inactive
                                </span>

                            @endif

                        </td>

                        <td>

                            <a
                                href="{{ route('departments.edit', $department) }}"
                                class="btn btn-sm btn-outline-primary rounded-pill"
                            >

                                <i class="bi bi-pencil"></i>

                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="4" class="text-center">

                            No departments found

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection