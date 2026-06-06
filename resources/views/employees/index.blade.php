@extends('layouts.app')

@section('title', 'Employees')

@section('content')

<div class="content-card">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h4 class="fw-bold mb-1">
                Employees
            </h4>

            <p class="text-muted mb-0">
                Manage Employee master data
            </p>

        </div>

        <a
            href="{{ route('employees.create') }}"
            class="btn btn-primary rounded-pill px-4"
        >

            <i class="bi bi-plus-circle"></i>

            Add Employee

        </a>

        <a
            href="{{ route('employees.group-changes.index') }}"
            class="btn btn-outline-primary rounded-pill px-4 ms-2"
        >

            <i class="bi bi-arrow-left-right"></i>

            Group Changes

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
                    <th>Employee Code</th>
                    <th>Employee Name</th>
                    <th>Department</th>
                    <th>Designation</th>    
                    <th>Joining Date</th>  
                    <th>Gender</th>
                    <th>Remarks</th>
                    <th>Status</th>
                    <th width="120">Action</th>

                </tr>

            </thead>

            <tbody>

                @forelse($employees as $employee)

                    <tr>

                        <td>{{ $employee->id }}</td>
                        <td>
                            {{ $employee->employee_code }}
                        </td>
                        <td>
                            {{ $employee->employee_name }}
                        </td>
                        <td>
                            {{ $employee->department->department_name ?? '-' }}
                        </td>
                        <td>
                            {{ $employee->designation->designation_name ?? '-' }}
                        </td>
                        <td>
                            {{ $employee->date_joined ? $employee->date_joined->format('Y-m-d') : '-' }}
                        </td>
                        <td>
                            {{ $employee->gender }}
                        </td>
                        <td>
                            {{ $employee->remarks }}
                        </td>

                        <td>

                            @if($employee->status)

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
                                href="{{ route('employees.edit', $employee) }}"
                                class="btn btn-sm btn-outline-primary rounded-pill"
                            >

                                <i class="bi bi-pencil"></i>

                            </a>

                            <a
                                href="{{ route('employees.group-changes.create', $employee) }}"
                                class="btn btn-sm btn-outline-secondary rounded-pill ms-1"
                                title="Change Group"
                            >

                                <i class="bi bi-arrow-left-right"></i>

                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="4" class="text-center">

                            No Employees found

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection