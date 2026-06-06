@extends('layouts.app')

@section('title', 'Add Department')

@section('content')

<div class="content-card">

    <h4 class="fw-bold mb-4">
        Add Department
    </h4>

    <form
        method="POST"
        action="{{ route('departments.store') }}"
    >

        @csrf

        <div class="row">

            <!-- Department Name -->
            <div class="col-md-6 mb-4">

                <label class="form-label fw-semibold">

                    Department Name

                </label>

                <input
                    type="text"
                    name="department_name"
                    class="form-control"
                    required
                >

            </div>

            <!-- Department Code -->
            <div class="col-md-6 mb-4">

                <label class="form-label fw-semibold">

                    Department Code

                </label>

                <input
                    type="text"
                    name="department_code"
                    class="form-control"
                >

            </div>

            <!-- Description -->
            <div class="col-12 mb-4">

                <label class="form-label fw-semibold">

                    Description

                </label>

                <textarea
                    name="description"
                    class="form-control"
                    rows="4"
                ></textarea>

            </div>

            <!-- Status -->
            <div class="col-md-4 mb-4">

                <label class="form-label fw-semibold">

                    Status

                </label>

                <select
                    name="status"
                    class="form-select"
                >

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

            <button
                type="submit"
                class="btn btn-primary rounded-pill px-5"
            >

                Save Department

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