@extends('layouts.app')

@section('title', 'Add Employee')

@section('content')

    <div class="content-card">

        <h4 class="fw-bold mb-4">
            Add Employee
        </h4>

        <form method="POST" action="{{ route('employees.store') }}">

            @csrf
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="row">

                <!-- Employee Name -->
                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">

                        Employee Name

                    </label>

                    <input type="text" name="employee_name" class="form-control" required>

                </div>

                <!-- Employee Code -->
                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">

                        Employee Code

                    </label>

                    <input type="text" name="employee_code" class="form-control">

                </div>

                <!-- Department -->
                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">

                        Department

                    </label>

                    <select name="department_id" class="form-select">

                        <option value="">
                            Select Department
                        </option>

                        @foreach($departments as $department)

                            <option value="{{ $department->id }}">
                                {{ $department->department_name }}
                            </option>
                        @endforeach
                    </select>

                </div>

                <!-- Designation -->
                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">

                        Designation

                    </label>

                    <select name="designation_id" class="form-select">

                        <option value="">
                            Select Designation
                        </option>

                        @foreach($designations as $designation)

                            <option value="{{ $designation->id }}">
                                {{ $designation->designation_name }}
                            </option>
                        @endforeach
                    </select>

                </div>


                <!-- Joining Date -->
                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">

                        Joining Date
                    </label>
                    <input type="date" name="date_joined" class="form-control" required>
                </div>

                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">

                        Date of Resignation
                    </label>
                    <input type="date" name="date_resigned" class="form-control">
                </div>

                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">

                        EPF Number
                    </label>
                    <input type="text" name="epf_number" class="form-control">
                </div>

                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">

                        NIC Number
                    </label>
                    <input type="text" name="nic_number" class="form-control">
                </div>

                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">

                        Gender
                    </label>
                    <select name="gender" class="form-select">
                        <option value=""></option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>

                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">

                        Address
                    </label>
                    <textarea name="address" class="form-control" rows="3"></textarea>
                </div>



                <!-- Description -->
                <div class="col-12 mb-4">

                    <label class="form-label fw-semibold">

                        Remarks

                    </label>

                    <textarea name="remarks" class="form-control" rows="4"></textarea>

                </div>

                <!-- Status -->
                <div class="col-md-4 mb-4">

                    <label class="form-label fw-semibold">

                        Status

                    </label>

                    <select name="status" class="form-select">

                        <option value="1">
                            Active
                        </option>

                        <option value="0">
                            Inactive
                        </option>

                    </select>

                </div>

            </div>

            <!-- Buttons -->
            <div class="d-flex gap-3">

                <button type="submit" class="btn btn-primary rounded-pill px-5">

                    Save Employee

                </button>

                <a href="{{ route('employees.index') }}" class="btn btn-secondary rounded-pill px-5">

                    Cancel

                </a>

            </div>

        </form>

    </div>

@endsection