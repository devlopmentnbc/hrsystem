 @extends('layouts.app')

@section('title', 'Shifts')

@section('content')

<div class="content-card">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h4 class="fw-bold mb-1">
                Shifts
            </h4>

            <p class="text-muted mb-0">
                Manage shift master data
            </p>

        </div>

        <a
            href="{{ route('shifts.create') }}"
            class="btn btn-primary rounded-pill px-4"
        >

            <i class="bi bi-plus-circle"></i>

            Add Shift

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
                    <th>Shift Name</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Break Minutes</th>
                    <th>Grace Period minutes</th>
                    <th>OT Start After Minutes</th>
                    <th>Full Day Hrs</th>
                    <th>Half Day Hrs</th>
                    <th>Remarks</th>
                    <th>Status</th>
                    <th width="120">Action</th>

                </tr>

            </thead>

            <tbody>

                @forelse($shifts as $shift)

                    <tr>

                        <td>{{ $shift->id }}</td>

                        <td>
                            {{ $shift->shift_name }}
                        </td>

                        <td>
                            {{ $shift->start_time }}
                        </td>

                        <td>
                            {{ $shift->end_time }}
                        </td>

                        <td>
                            {{ $shift->break_minutes }}
                        </td>

                        <td>
                            {{ $shift->grace_period_minutes }}
                        </td>

                        <td>
                            {{ $shift->ot_start_after_minutes }}
                        </td>

                        <td>
                            {{ $shift->full_day_hours }}
                        </td>

                        <td>
                            {{ $shift->half_day_hours }}
                        </td>

                        <td>
                            {{ $shift->remarks }}
                        </td>

                        <td>

                            @if($shift->status)

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
                                href="{{ route('shifts.edit', $shift) }}"
                                class="btn btn-sm btn-outline-primary rounded-pill"
                            >

                                <i class="bi bi-pencil"></i>

                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="12" class="text-center">

                            No shifts found

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection