@extends('layouts.app')

@section('title', 'Designations')

@section('content')

<div class="content-card">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h4 class="fw-bold mb-1">
                Designations
            </h4>

            <p class="text-muted mb-0">
                Manage designation master data
            </p>

        </div>

        <a
            href="{{ route('designations.create') }}"
            class="btn btn-primary rounded-pill px-4"
        >

            <i class="bi bi-plus-circle"></i>

            Add Designation

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
                    <th>Designation Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th width="120">Action</th>

                </tr>

            </thead>

            <tbody>

                @forelse($designations as $designation)

                    <tr>

                        <td>{{ $designation->id }}</td>

                        <td>
                            {{ $designation->designation_name }}
                        </td>

                        <td>
                            {{ $designation->description }}
                        </td>

                        <td>

                            @if($designation->status)

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
                                href="{{ route('designations.edit', $designation) }}"
                                class="btn btn-sm btn-outline-primary rounded-pill"
                            >

                                <i class="bi bi-pencil"></i>

                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="4" class="text-center">

                            No designations found

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection