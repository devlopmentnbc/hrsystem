@extends('layouts.app')

@section('title', 'Shift Groups')

@section('content')

<div class="content-card">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h4 class="fw-bold mb-1">
                Shift Groups
            </h4>

            <p class="text-muted mb-0">
                Manage shift group master data
            </p>

        </div>

        <a
            href="{{ route('shifts_groups.create') }}"
            class="btn btn-primary rounded-pill px-4"
        >

            <i class="bi bi-plus-circle"></i>

            Add Shift Group

        </a>

    </div>

    @if(session('success'))

        <div class="alert alert-success rounded-4">

            {{ session('success') }}

        </div>

    @endif

    @if(session('warning'))

        <div class="alert alert-warning rounded-4">

            {{ session('warning') }}

        </div>

    @endif

    <div class="table-responsive">

        <table class="table align-middle">

            <thead>

                <tr>

                    <th>ID</th>
                    <th>Shift Group Name</th>
                        <th>Category</th>
                    <th width="120">Action</th>

                </tr>

            </thead>

            <tbody>

                @forelse($shiftsGroups as $shiftGroup)

                    

                    <tr>

                        <td>{{ $shiftGroup->id }}</td>

                        <td>
                            {{ $shiftGroup->group_name }}
                        </td>

                        <td>
                            {{ $shiftGroup->category ?? 'Uncategorized' }}
                        </td>

                        <td>
                            {{ $shiftGroup->employees_count ?? 0 }}
                        </td>

                        <td>
                            {{ $shiftGroup->shifts_count ?? 0 }}
                        </td>

                        <td>
                            {{ $shiftGroup->remarks }}
                        </td>

                        <td>

                            @if($shiftGroup->status)

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
                                href="{{ route('shifts_groups.edit', $shiftGroup) }}"
                                class="btn btn-sm btn-outline-primary rounded-pill"
                            >

                                <i class="bi bi-pencil"></i>

                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="4" class="text-center">

                            No shift groups found

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection