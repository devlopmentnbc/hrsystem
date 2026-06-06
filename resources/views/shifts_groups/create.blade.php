@extends('layouts.app')

@section('title', 'Add Shift Group ')

@section('content')

<div class="content-card">

    <h4 class="fw-bold mb-4">
        Add Shift Group 
    </h4>

    <form
        method="POST"
        action="{{ route('shifts_groups.store') }}"
    >

        @csrf

        <div class="row">

            <!-- Shift Group Name -->
            <div class="col-md-6 mb-4">

                <label class="form-label fw-semibold">

                    Shift Group Name

                </label>

                <input
                    type="text"
                    name="group_name"
                    class="form-control"
                    value="{{ old('group_name') }}"
                    required
                >

                @error('group_name')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror

            </div>

            <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold">
                    Shift Group Category
                </label>
                <select name="category" id="category-select" class="form-select">
                    <option value="">Uncategorized</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" {{ old('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                    @endforeach
                    <option value="__new__" {{ old('category') === '__new__' || old('new_category') ? 'selected' : '' }}>Add new category...</option>
                </select>
                @error('category')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
                <input
                    type="text"
                    name="new_category"
                    id="new-category-input"
                    class="form-control mt-2"
                    placeholder="Enter new category"
                    value="{{ old('new_category') }}"
                    style="display: none;"
                >
                @error('new_category')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 mb-4">

                <label class="form-label fw-semibold">

                    Status

                </label>

                <select
                    name="status"
                    class="form-select"
                >

                    <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>
                        Active
                    </option>

                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>
                        Inactive
                    </option>

                </select>

            </div>

            <div class="col-md-4 mb-4">
                <label class="form-label fw-semibold">
                    Assignment Start Date
                </label>
                <input
                    type="date"
                    name="assignment_start_date"
                    class="form-control"
                    value="{{ old('assignment_start_date', now()->toDateString()) }}"
                >
                <div class="form-text">Required when assigning employees to this group.</div>
                @error('assignment_start_date')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 mb-4">
                <label class="form-label fw-semibold">
                    Assignment End Date
                </label>
                <input
                    type="date"
                    name="assignment_end_date"
                    class="form-control"
                    value="{{ old('assignment_end_date') }}"
                >
                <div class="form-text">Leave blank for an open-ended active assignment.</div>
                @error('assignment_end_date')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
            </div>

            <!-- Employees -->
            <div class="col-12 mb-4">

                <label class="form-label fw-semibold">
                    Employees
                </label>

                <div class="input-group mb-3">
                    <input
                        id="employee-search-input"
                        type="text"
                        class="form-control"
                        placeholder="Type employee name or code"
                        list="employee-options"
                    >
                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        id="employee-add-button"
                    >
                        Add
                    </button>
                </div>

                <datalist id="employee-options">
                    @foreach($employees as $employee)
                        <option
                            value="{{ $employee->employee_name }} ({{ $employee->employee_code }})"
                            data-id="{{ $employee->id }}"
                            data-code="{{ $employee->employee_code }}"
                        >
                        </option>
                    @endforeach
                </datalist>

                <div
                    id="selected-employees"
                    class="d-flex flex-wrap gap-2 mb-2"
                >
                    @foreach($employees->whereIn('id', old('employee_ids', [])) as $selectedEmployee)
                        <span class="badge bg-primary text-white selected-employee-item" data-id="{{ $selectedEmployee->id }}">
                            {{ $selectedEmployee->employee_name }} ({{ $selectedEmployee->employee_code }})
                            <button type="button" class="btn-close btn-close-white btn-sm ms-2 remove-employee-button" aria-label="Remove"></button>
                            <input type="hidden" name="employee_ids[]" value="{{ $selectedEmployee->id }}">
                        </span>
                    @endforeach
                </div>

                <div class="form-text">
                    Type or choose an employee, then click Add. Selected employees will be assigned only for the effective period above, and overlapping assignments in other groups are blocked.
                </div>

                @error('employee_ids')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror

            </div>

            <!-- Remarks -->
            <div class="col-12 mb-4">

                <label class="form-label fw-semibold">

                    Remarks

                </label>

                <textarea
                    name="remarks"
                    class="form-control"
                    rows="4"
                >{{ old('remarks') }}</textarea>

                @error('remarks')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror

            </div>

        </div>

        <!-- Buttons -->
        <div class="d-flex gap-3">

            <button
                type="submit"
                class="btn btn-primary rounded-pill px-5"
            >

                Save Shift Group

            </button>

            <a
                href="{{ route('shifts_groups.index') }}"
                class="btn btn-secondary rounded-pill px-5"
            >

                Cancel

            </a>

        </div>

    </form>

</div>

@php
    $employeeJson = $employees->map(function ($employee) {
        return [
            'id' => $employee->id,
            'label' => $employee->employee_name . ' (' . $employee->employee_code . ')',
            'code' => $employee->employee_code,
        ];
    });
@endphp

<script>
    (function () {
        const employees = @json($employeeJson);

        const selectedContainer = document.getElementById('selected-employees');
        const searchInput = document.getElementById('employee-search-input');
        const addButton = document.getElementById('employee-add-button');
        const categorySelect = document.getElementById('category-select');
        const newCategoryInput = document.getElementById('new-category-input');
        const selectedIds = new Set(@json(old('employee_ids', [])));

        function refreshCategoryInput() {
            if (!categorySelect) return;
            if (categorySelect.value === '__new__' || newCategoryInput.value.trim() !== '') {
                newCategoryInput.style.display = 'block';
                newCategoryInput.focus();
            } else {
                newCategoryInput.style.display = 'none';
            }
        }

        categorySelect?.addEventListener('change', refreshCategoryInput);
        refreshCategoryInput();

        selectedContainer.addEventListener('click', function (event) {
            if (!event.target.classList.contains('remove-employee-button')) {
                return;
            }
            const badge = event.target.closest('.selected-employee-item');
            if (!badge) {
                return;
            }
            const id = badge.dataset.id;
            selectedIds.delete(String(id));
            badge.remove();
            const hiddenInput = document.querySelector(`input[type=hidden][data-employee-id='${id}']`);
            if (hiddenInput) {
                hiddenInput.remove();
            }
        });

        function createHiddenInput(id) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'employee_ids[]';
            input.value = id;
            input.dataset.employeeId = id;
            return input;
        }

        function createBadge(employee) {
            const badge = document.createElement('span');
            badge.className = 'badge bg-primary text-white selected-employee-item d-inline-flex align-items-center';
            badge.dataset.id = employee.id;
            badge.textContent = employee.label;

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'btn-close btn-close-white btn-sm ms-2 remove-employee-button';
            removeButton.setAttribute('aria-label', 'Remove');
            removeButton.addEventListener('click', function () {
                selectedIds.delete(String(employee.id));
                badge.remove();
                const hiddenInput = document.querySelector(`input[type=hidden][data-employee-id='${employee.id}']`);
                if (hiddenInput) hiddenInput.remove();
            });

            badge.appendChild(removeButton);
            badge.appendChild(createHiddenInput(employee.id));
            selectedContainer.appendChild(badge);
        }

        function addEmployeeByText(text) {
            const match = employees.find(function (employee) {
                return employee.label === text || employee.code === text;
            });

            if (!match) {
                const partial = employees.find(function (employee) {
                    return employee.label.toLowerCase().includes(text.toLowerCase()) || employee.code.toLowerCase().includes(text.toLowerCase());
                });
                if (partial) {
                    return addEmployee(partial);
                }
                alert('Please choose a valid employee from the list.');
                return;
            }

            addEmployee(match);
        }

        function addEmployee(employee) {
            if (selectedIds.has(String(employee.id))) {
                searchInput.value = '';
                return;
            }
            selectedIds.add(String(employee.id));
            createBadge(employee);
            searchInput.value = '';
        }

        addButton.addEventListener('click', function () {
            const value = searchInput.value.trim();
            if (!value) {
                return;
            }
            addEmployeeByText(value);
        });

        searchInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                addButton.click();
            }
        });
    })();
</script>

@endsection