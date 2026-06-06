@extends('layouts.app')

@section('title', 'Add Designation')

@section('content')

<div class="content-card">

    <h4 class="fw-bold mb-4">
        Add Designation
    </h4>

    <form
        method="POST"
        action="{{ route('designations.store') }}"
    >

        @csrf

        <div class="row">

            <!-- Designation Name -->
            <div class="col-md-6 mb-4">

                <label class="form-label fw-semibold">

                    Designation Name

                </label>

                <input
                    type="text"
                    name="designation_name"
                    class="form-control"
                    required
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

                Save Designation

            </button>

            <a
                href="{{ route('designations.index') }}"
                class="btn btn-secondary rounded-pill px-5"
            >

                Cancel

            </a>

        </div>

    </form>

</div>

@endsection