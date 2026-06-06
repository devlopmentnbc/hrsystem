@extends('layouts.app')

@section('title', 'Edit Designation')

@section('content')

<div class="content-card">

    <h4 class="fw-bold mb-4">

        Edit Designation

    </h4>

    <form
        method="POST"
        action="{{ route('designations.update', $designation->id) }}"
    >

        @csrf
        @method('PUT')

        <div class="row">

            <div class="col-md-6 mb-4">

                <label class="form-label fw-semibold">

                    Designation Name

                </label>

                <input
                    type="text"
                    name="designation_name"
                    class="form-control"
                    value="{{ old('designation_name', $designation->designation_name) }}"
                    required
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
                >{{ old('description', $designation->description) }}</textarea>

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
                        {{ $designation->status == 1 ? 'selected' : '' }}
                    >
                        Active
                    </option>

                    <option
                        value="0"
                        {{ $designation->status == 0 ? 'selected' : '' }}
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

                Update Designation

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