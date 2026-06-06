@extends('layouts.app')

@section('title', 'Edit Employee')

@section('content')

<div class="content-card">

    <h4 class="fw-bold mb-4">

        Edit Employee

    </h4>

    <form
        method="POST"
        action="{{ route('employees.update', $employee->id) }}"
    >

        @csrf
        @method('PUT')

        <div class="row">

            <div class="col-md-6 mb-4">

                <label class="form-label fw-semibold">

                    Employee Name

                </label>

                <input
                    type="text"
                    name="employee_name"
                    class="form-control"
                    value="{{ old('employee_name', $employee->employee_name) }}"
                    required
                >

            </div>
            <div class="col-md-6 mb-4">

                <label class="form-label fw-semibold">

                    Employee Code

                </label>

                <input
                    type="text"
                    name="employee_code"
                    class="form-control"
                    value="{{ old('employee_code', $employee->employee_code) }}"
                >

            </div>
            <div class="col-md-6 mb-4">

                <label class="form-label fw-semibold">

                    Department

                </label>

                <select
                    name="department_id"
                    class="form-select"
                >

                    <option value="">

                        Select Department

                    </option>

                    @foreach($departments as $department)
                        <option
                            value="{{ $department->id }}"
                            {{ $employee->department_id == $department->id ? 'selected' : '' }}
                        >
                            {{ $department->department_name }}
                        </option>
                    @endforeach

                </select>

            </div>
            <div class="col-md-6 mb-4">

                <label class="form-label fw-semibold">

                    Designation 
                </label>
                <select
                    name="designation_id"
                    class="form-select"
                >

                    <option value="">

                        Select Designation

                    </option>

                    @foreach($designations as $designation)
                        <option
                            value="{{ $designation->id }}"
                            {{ $employee->designation_id == $designation->id ? 'selected' : '' }}
                        >
                            {{ $designation->designation_name }}
                        </option>
                    @endforeach

                </select>

            </div>
            <div class="col-md-6 mb-4">

                <label class="form-label fw-semibold">

                    Joining Date
                </label>
                <input
                    type="date"
                    name="date_joined"
                    class="form-control"
                    value="{{ old('date_joined', $employee->date_joined) }}"
                    required
                >
            </div>  
                <div class="col-md-6 mb-4">
    
                    <label class="form-label fw-semibold">
    
                        Date of Resignation
                    </label>
                    <input
                        type="date"
                        name="date_resigned"
                        class="form-control"
                        value="{{ old('date_resigned', $employee->date_resigned) }}"
                    >
                </div>  
                <div class="col-md-6 mb-4">
    
                    <label class="form-label fw-semibold">
                        EPF Number
                    </label>    
                    <input
                        type="text"
                        name="epf_number"
                        class="form-control"
                        value="{{ old('epf_number', $employee->epf_number) }}"
                    >
                </div>
                <div class="col-md-6 mb-4">
    
                    <label class="form-label fw-semibold">
                        NIC Number
                    </label>    
                    <input
                        type="text"
                        name="nic_number"
                        class="form-control"
                        value="{{ old('nic_number', $employee->nic_number) }}"
                    >
                </div>
                <div class="col-md-6 mb-4">
    
                    <label class="form-label fw-semibold">
                        Gender
                    </label>    
                    <select
                        name="gender"
                        class="form-select"
                    >
                        <option value="">
                            Select Gender
                        </option>
                        <option value="male" {{ $employee->gender == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ $employee->gender == 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                    </div>
                <div class="col-md-6 mb-4">
    
                    <label class="form-label fw-semibold">
                        Address
                    </label>    
                    <textarea
                        name="address"
                        rows="4"
                        class="form-control"
                         
                    >{{ old('address', $employee->address) }}
                    </textarea>
                </div>
                

            <div class="col-12 mb-4">

                <label class="form-label fw-semibold">

                    Remarks

                </label>

                <textarea
                    name="description"
                    rows="4"
                    class="form-control"
                >{{ old('description', $employee->remarks) }}</textarea>

            </div>

            <div class="col-md-4 mb-4">

                <label class="form-label fw-semibold">

                    Status

                </label>

                <select
                    name="status"
                    class="form-select"
                >

                    <option
                        value="1"
                        {{ $employee->status == 1 ? 'selected' : '' }}
                    >
                        Active
                    </option>

                    <option
                        value="0"
                        {{ $employee->status == 0 ? 'selected' : '' }}
                    >
                        Inactive
                    </option>

                </select>

            </div>

        </div>

        <div class="d-flex gap-3">

            <button
                type="submit"
                class="btn btn-primary rounded-pill px-5"
            >

                Update Employee

            </button>

            <a
                href="{{ route('employees.index') }}"
                class="btn btn-secondary rounded-pill px-5"
            >

                Cancel

            </a>

        </div>

    </form>

</div>

@endsection 