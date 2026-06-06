@extends('layouts.app')

@section('title', 'Edit Department')

@section('content')

<div class="content-card">

    <h4 class="fw-bold mb-4">

        Edit Department

    </h4>

    <form
        method="POST"
        action="{{ route('departments.update', $department->id) }}"
    >

        @csrf
        @method('PUT')

        <div class="row">

            <div class="col-md-6 mb-4">

                <label class="form-label fw-semibold">

                    Department Name

                </label>

                <input
                    type="text"
                    name="department_name"
                    class="form-control"
                    value="{{ old('department_name', $department->department_name) }}"
                    required
                >

            </div>

            <div class="col-md-6 mb-4">

                <label class="form-label fw-semibold">

                    Department Code

                </label>

                <input
                    type="text"
                    name="department_code"
                    class="form-control"
                    value="{{ old('department_code', $department->department_code) }}"
                >

            </div>

            <div class="col-12 mb-4">

                <label class="form-label fw-semibold">

                    Description

                </label>

                <textarea
                    name="description"
                    rows="4"
                    class="form-control"
                >{{ old('description', $department->description) }}</textarea>

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
                        {{ $department->status == 1 ? 'selected' : '' }}
                    >
                        Active
                    </option>

                    <option
                        value="0"
                        {{ $department->status == 0 ? 'selected' : '' }}
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

                Update Department

            </button>

            <a
                href="{{ route('departments.index') }}"
                class="btn btn-secondary rounded-pill px-5"
            >

                Cancel

            </a>

        </div>

    </form>

</div>

@endsection 