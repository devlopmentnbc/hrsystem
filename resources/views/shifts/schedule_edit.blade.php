@extends('layouts.app')

@section('title', 'Edit Shift Schedule')

@section('content')
<div class="content-card">
    <h4 class="fw-bold">Edit Shift Schedule</h4>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="schedule-edit-form" method="POST" action="{{ route('shifts_schedule.update', $schedule->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Schedule Name</label>
            <input type="text" name="schedule_name" class="form-control" value="{{ old('schedule_name', $schedule->schedule_name) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Shift Group Category</label>
            <select name="group_category" id="edit-group-category" class="form-select" required>
                <option value="">Select one category</option>
                @foreach($allGroupCategories as $category)
                    <option value="{{ $category }}" {{ old('group_category', $schedule->group_category) === $category ? 'selected' : '' }}>{{ $category }}</option>
                @endforeach
            </select>
            <div class="form-text">Only one schedule can exist for the same category during an overlapping date range.</div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Start Date</label>
                <input type="date" id="edit-start-date" name="start_date" class="form-control {{ $schedule->is_active ? 'bg-light' : '' }}" value="{{ old('start_date', $schedule->start_date->toDateString()) }}" @if($schedule->is_active) readonly data-locked="1" @endif required>
            </div>
            <div class="col-md-6">
                <label class="form-label">End Date</label>
                <input type="date" id="edit-end-date" name="end_date" class="form-control {{ $schedule->is_active ? 'bg-light' : '' }}" value="{{ old('end_date', $schedule->end_date->toDateString()) }}" @if($schedule->is_active) readonly data-locked="1" @endif required>
            </div>
        </div>

        @if($schedule->is_active)
            <div class="alert alert-warning rounded-4">
                This schedule is active, so its date range is locked. Deactivate it first if the period needs to change.
            </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-lg-7">
                <div class="alert alert-info rounded-4 mb-0" id="edit-booking-summary">
                    Existing schedules and unavailable date ranges for this category will appear here.
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Existing Schedules for Category</h6>
                        <div id="edit-existing-category-schedules" class="small text-muted">No category selected.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <h6 class="fw-bold mb-1">Booked Range Calendar</h6>
                        <small class="text-muted">Preview booked dates for this category before saving the updated schedule.</small>
                    </div>
                    <div class="booking-pill-list" id="edit-booking-range-badges"></div>
                </div>
                <div id="edit-booking-range-calendar" class="schedule-calendar-grid"></div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>Shift / Day</th>
                        @foreach($days as $day)
                            <th>
                                <div>{{ $day->format('D') }}</div>
                                <div class="fw-semibold">{{ $day->format('d-M') }}</div>
                            </th>
                        @endforeach
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="schedule-edit-rows-body">
                    @forelse($rows as $i => $r)
                        <tr class="schedule-row" data-row-index="{{ $i }}">
                            <td class="text-start align-middle">
                                <select name="schedule[rows][{{ $i }}][shift_id]" class="form-select mb-2">
                                    <option value="">Select shift</option>
                                    @foreach($shifts as $shift)
                                        <option value="{{ $shift->id }}" @if($shift->id == $r['shift_id']) selected @endif>{{ $shift->shift_name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('g:i A') }})</option>
                                    @endforeach
                                </select>
                                <div class="text-muted small">Choose the shift for this row.</div>
                            </td>
                            @foreach($days as $day)
                                @php $date = $day->toDateString(); $selected = $r['days'][$date] ?? []; @endphp
                                <td class="align-top">
                                    <select multiple class="form-select form-select-sm" name="schedule[rows][{{ $i }}][days][{{ $date }}][work_groups][]">
                                        @foreach($shiftsGroups as $group)
                                            <option value="{{ $group->id }}" @if(in_array($group->id, $selected)) selected @endif>{{ $group->group_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            @endforeach
                            <td>
                                <button type="button" class="btn btn-sm btn-danger remove-shift-row">Remove</button>
                            </td>
                        </tr>
                    @empty
                        <tr class="schedule-row" data-row-index="0">
                            <td class="text-start align-middle">
                                <select name="schedule[rows][0][shift_id]" class="form-select mb-2">
                                    <option value="">Select shift</option>
                                    @foreach($shifts as $shift)
                                        <option value="{{ $shift->id }}">{{ $shift->shift_name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('g:i A') }})</option>
                                    @endforeach
                                </select>
                                <div class="text-muted small">Choose the shift for this row.</div>
                            </td>
                            @foreach($days as $day)
                                <td class="align-top">
                                    <select multiple class="form-select form-select-sm" name="schedule[rows][0][days][{{ $day->toDateString() }}][work_groups][]">
                                        @foreach($shiftsGroups as $group)
                                            <option value="{{ $group->id }}">{{ $group->group_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            @endforeach
                            <td>
                                <button type="button" class="btn btn-sm btn-danger remove-shift-row">Remove</button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tbody class="table-secondary">
                    <tr>
                        <td colspan="{{ count($days) + 2 }}" class="text-start fw-semibold">Off Duty Rows</td>
                    </tr>
                </tbody>
                <tbody id="schedule-edit-off-body">
                    @php $offSelected = $offRow['days'] ?? []; @endphp
                    <tr class="off-schedule-row" data-row-index="0">
                        <td class="text-start align-middle">
                            <div class="fw-semibold">Off Duty Groups</div>
                            <div class="text-muted small">Mark groups that are off for each date.</div>
                        </td>
                        @foreach($days as $day)
                            @php $d = $day->toDateString(); $sel = $offSelected[$d] ?? []; @endphp
                            <td class="align-top">
                                <select multiple class="form-select form-select-sm" name="schedule[off_rows][0][days][{{ $d }}][]">
                                    @foreach($shiftsGroups as $group)
                                        <option value="{{ $group->id }}" @if(in_array($group->id, $sel)) selected @endif>{{ $group->group_name }}</option>
                                    @endforeach
                                </select>
                            </td>
                        @endforeach
                        <td>
                            <button type="button" class="btn btn-sm btn-danger remove-off-row">Remove</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <button type="button" id="add-edit-shift-row" class="btn btn-outline-primary rounded-pill px-4">Add Shift Row</button>
                <button type="button" id="add-edit-off-row" class="btn btn-outline-secondary rounded-pill px-4 ms-2">Add Off Duty Row</button>
            </div>
        </div>

        <div class="text-end">
            <a href="{{ route('shifts_schedule.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
            <button id="save-edit-schedule-button" class="btn btn-primary">Save</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rowsBody = document.getElementById('schedule-edit-rows-body');
        const offBody = document.getElementById('schedule-edit-off-body');
        const addRowBtn = document.getElementById('add-edit-shift-row');
        const addOffBtn = document.getElementById('add-edit-off-row');
        const categorySelect = document.getElementById('edit-group-category');
        const startDateInput = document.getElementById('edit-start-date');
        const endDateInput = document.getElementById('edit-end-date');
        const saveButton = document.getElementById('save-edit-schedule-button');
        const bookingSummary = document.getElementById('edit-booking-summary');
        const existingSchedulesContainer = document.getElementById('edit-existing-category-schedules');
        const bookingCalendar = document.getElementById('edit-booking-range-calendar');
        const bookingBadges = document.getElementById('edit-booking-range-badges');
        const availabilityUrl = @json(route('shifts_schedule.availability'));
        const groupsByCategory = @json($groupsByCategory);
        const currentScheduleId = {{ $schedule->id }};
        const datesLocked = {{ $schedule->is_active ? 'true' : 'false' }};
        let availabilityRequestId = 0;
        let availabilityState = {
            schedules: [],
            conflicts: [],
            booked_dates: {},
            loading: false,
            error: null,
        };

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatDisplayDate(value) {
            if (!value) {
                return '';
            }

            return new Date(`${value}T00:00:00`).toLocaleDateString(undefined, {
                month: 'short',
                day: 'numeric',
            });
        }

        function createDateRange(startDate, endDate) {
            if (!startDate || !endDate) {
                return [];
            }

            const dates = [];
            const cursor = new Date(`${startDate}T00:00:00`);
            const last = new Date(`${endDate}T00:00:00`);

            const formatDateKey = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');

                return `${year}-${month}-${day}`;
            };

            while (cursor <= last) {
                dates.push(formatDateKey(cursor));
                cursor.setDate(cursor.getDate() + 1);
            }

            return dates;
        }

        function populateGroupSelect(select, category) {
            if (!select) return;

            const previousValues = Array.from(select.selectedOptions).map(option => option.value);
            const options = groupsByCategory[category] || [];
            select.innerHTML = options.map(group => `<option value="${group.id}">${escapeHtml(group.group_name)}</option>`).join('');

            Array.from(select.options).forEach(option => {
                if (previousValues.includes(option.value)) {
                    option.selected = true;
                }
            });
        }

        function syncAllGroupSelects() {
            document.querySelectorAll('#schedule-edit-form select[name*="[work_groups][]"], #schedule-edit-form select[name*="[off_rows]"]').forEach(select => {
                populateGroupSelect(select, categorySelect.value);
            });
        }

        function getDuplicateAssignments() {
            const selectedMap = new Map();
            const duplicates = [];

            document.querySelectorAll('#schedule-edit-form select[name*="[days]"]').forEach(select => {
                const match = select.name.match(/\[days\]\[([^\]]+)\]/);
                const dateKey = match ? match[1] : null;
                if (!dateKey) return;

                Array.from(select.selectedOptions).forEach(option => {
                    const key = `${dateKey}|${option.value}`;
                    if (selectedMap.has(key)) {
                        duplicates.push({ date: dateKey, group: option.textContent.trim() });
                    } else {
                        selectedMap.set(key, true);
                    }
                });
            });

            return duplicates;
        }

        function renderExistingSchedules(category, schedules) {
            if (!category) {
                existingSchedulesContainer.innerHTML = '<span class="text-muted">No category selected.</span>';
                return;
            }

            if (!schedules.length) {
                existingSchedulesContainer.innerHTML = '<span class="text-success">No other schedules for this category.</span>';
                return;
            }

            existingSchedulesContainer.innerHTML = schedules.map(schedule => `
                <div class="border rounded-3 p-2 mb-2">
                    <div class="fw-semibold">${escapeHtml(schedule.schedule_name)}</div>
                    <div>${escapeHtml(schedule.start_date)} → ${escapeHtml(schedule.end_date)}</div>
                    <div><span class="badge ${schedule.is_active ? 'bg-success' : 'bg-secondary'}">${schedule.is_active ? 'Active' : 'Inactive'}</span></div>
                </div>
            `).join('');
        }

        function renderBookingBadges(conflicts, bookedDates) {
            if (!bookingBadges) {
                return;
            }

            const overlapDates = Object.entries(bookedDates).filter(([, entries]) => entries.length > 1).length;
            const activeDates = Object.entries(bookedDates).filter(([, entries]) => entries.some(entry => entry.is_active)).length;
            const badges = [];

            if (conflicts.length) {
                badges.push(`<span class="badge text-bg-danger">${conflicts.length} booked range${conflicts.length > 1 ? 's' : ''}</span>`);
            }

            if (overlapDates) {
                badges.push(`<span class="badge text-bg-warning">${overlapDates} overlapping date${overlapDates > 1 ? 's' : ''}</span>`);
            }

            if (activeDates) {
                badges.push(`<span class="badge text-bg-success">${activeDates} active date${activeDates > 1 ? 's' : ''}</span>`);
            }

            bookingBadges.innerHTML = badges.length
                ? badges.join('')
                : '<span class="badge text-bg-light border">No booked dates</span>';
        }

        function renderBookingCalendar(startDate, endDate, bookedDates) {
            if (!bookingCalendar) {
                return;
            }

            const dates = createDateRange(startDate, endDate);

            if (!dates.length) {
                bookingCalendar.innerHTML = '<div class="text-muted small">Select a valid start and end date to preview availability.</div>';
                return;
            }

            bookingCalendar.innerHTML = dates.map(date => {
                const entries = bookedDates[date] || [];
                const hasOverlap = entries.length > 1;
                const hasActive = entries.some(entry => entry.is_active);
                const statusClass = hasOverlap ? 'overlap' : (entries.length ? 'booked' : 'free');
                const tooltipText = entries.length
                    ? entries.map(entry => `${entry.schedule_name} (${entry.start_date} to ${entry.end_date})${entry.is_active ? ' - Active' : ''}`).join(' | ')
                    : 'Available';

                return `
                    <div class="schedule-day-card ${statusClass} ${hasActive ? 'active-booking' : ''}" title="${escapeHtml(tooltipText)}">
                        <div class="small text-muted">${escapeHtml(new Date(`${date}T00:00:00`).toLocaleDateString(undefined, { weekday: 'short' }))}</div>
                        <div class="fw-semibold">${escapeHtml(formatDisplayDate(date))}</div>
                        <div>
                            <span class="badge ${hasOverlap ? 'text-bg-danger' : (entries.length ? 'text-bg-warning' : 'text-bg-success')}">
                                ${hasOverlap ? `${entries.length} schedules` : (entries.length ? 'Booked' : 'Free')}
                            </span>
                        </div>
                    </div>
                `;
            }).join('');
        }

        async function refreshAvailability() {
            const category = categorySelect.value;
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;
            const requestId = ++availabilityRequestId;

            if (!category || !startDate || !endDate) {
                availabilityState = {
                    schedules: [],
                    conflicts: [],
                    booked_dates: {},
                    loading: false,
                    error: null,
                };
                renderExistingSchedules(category, []);
                renderBookingBadges([], {});
                renderBookingCalendar(startDate, endDate, {});
                updateEditState();
                return;
            }

            availabilityState = {
                ...availabilityState,
                loading: true,
                error: null,
            };
            updateEditState();

            const params = new URLSearchParams({
                group_category: category,
                start_date: startDate,
                end_date: endDate,
                ignore_schedule_id: String(currentScheduleId),
            });

            try {
                const response = await fetch(`${availabilityUrl}?${params.toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('Unable to load schedule availability.');
                }

                const data = await response.json();
                if (requestId !== availabilityRequestId) {
                    return;
                }

                availabilityState = {
                    schedules: data.schedules || [],
                    conflicts: data.conflicts || [],
                    booked_dates: data.booked_dates || {},
                    loading: false,
                    error: null,
                };
            } catch (error) {
                if (requestId !== availabilityRequestId) {
                    return;
                }

                availabilityState = {
                    schedules: [],
                    conflicts: [],
                    booked_dates: {},
                    loading: false,
                    error: error.message || 'Unable to load schedule availability.',
                };
            }

            renderExistingSchedules(category, availabilityState.schedules);
            renderBookingBadges(availabilityState.conflicts, availabilityState.booked_dates);
            renderBookingCalendar(startDate, endDate, availabilityState.booked_dates);
            updateEditState();
        }

        function updateEditState() {
            const category = categorySelect.value;
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;
            const schedules = availabilityState.schedules || [];
            const overlap = (availabilityState.conflicts || [])[0] || null;
            const duplicates = getDuplicateAssignments();

            bookingSummary.className = 'alert rounded-4 mb-0';
            startDateInput.classList.remove('is-invalid');
            endDateInput.classList.remove('is-invalid');

            if (!category) {
                bookingSummary.classList.add('alert-info');
                bookingSummary.textContent = 'Select a category to view booked ranges.';
                saveButton.disabled = true;
                return;
            }

            if (availabilityState.loading) {
                bookingSummary.classList.add('alert-info');
                bookingSummary.textContent = 'Loading booked ranges for the selected category.';
                saveButton.disabled = true;
                return;
            }

            if (availabilityState.error) {
                bookingSummary.classList.add('alert-warning');
                bookingSummary.textContent = availabilityState.error;
                saveButton.disabled = true;
                return;
            }

            if (overlap) {
                bookingSummary.classList.add('alert-danger');
                bookingSummary.textContent = `Selected date range overlaps with existing schedule "${overlap.schedule_name}" (${overlap.start_date} to ${overlap.end_date}).`;
                startDateInput.classList.add('is-invalid');
                endDateInput.classList.add('is-invalid');
            } else if (duplicates.length) {
                bookingSummary.classList.add('alert-danger');
                bookingSummary.textContent = `Duplicate assignment detected for ${duplicates[0].group} on ${duplicates[0].date}. Remove duplicate group selections before saving.`;
            } else {
                bookingSummary.classList.add('alert-success');
                bookingSummary.textContent = schedules.length
                    ? 'Category loaded. Existing schedules for this category are shown on the right. Non-overlapping range is ready to save.'
                    : 'No conflicting schedules for this category. You can save this schedule.';
            }

            saveButton.disabled = Boolean(!category || overlap || duplicates.length);
        }

        function nextIndex(container, selector) {
            return container.querySelectorAll(selector).length;
        }

        addRowBtn?.addEventListener('click', function () {
            const idx = nextIndex(rowsBody, '.schedule-row');
            const template = rowsBody.querySelector('.schedule-row').outerHTML
                .replace(/data-row-index="[0-9]+"/g, 'data-row-index="' + idx + '"')
                .replace(/schedule\[rows\]\[[0-9]+\]/g, 'schedule[rows][' + idx + ']');
            rowsBody.insertAdjacentHTML('beforeend', template);
            syncAllGroupSelects();
            updateEditState();
        });

        addOffBtn?.addEventListener('click', function () {
            const idx = nextIndex(offBody, '.off-schedule-row');
            const template = offBody.querySelector('.off-schedule-row').outerHTML
                .replace(/data-row-index="[0-9]+"/g, 'data-row-index="' + idx + '"')
                .replace(/schedule\[off_rows\]\[[0-9]+\]/g, 'schedule[off_rows][' + idx + ']');
            offBody.insertAdjacentHTML('beforeend', template);
            syncAllGroupSelects();
            updateEditState();
        });

        rowsBody.addEventListener('click', function (e) {
            const btn = e.target.closest('.remove-shift-row');
            if (btn) {
                const row = btn.closest('.schedule-row');
                if (rowsBody.querySelectorAll('.schedule-row').length > 1) {
                    row.remove();
                }
                updateEditState();
            }
        });

        offBody.addEventListener('click', function (e) {
            const btn = e.target.closest('.remove-off-row');
            if (btn) {
                const row = btn.closest('.off-schedule-row');
                row?.remove();
                updateEditState();
            }
        });

        document.addEventListener('change', function (e) {
            if (e.target === categorySelect) {
                syncAllGroupSelects();
            }

            if (
                e.target === categorySelect ||
                e.target === startDateInput ||
                e.target === endDateInput ||
                e.target.matches('#schedule-edit-form select[name*="[days]"]')
            ) {
                if (e.target === categorySelect || e.target === startDateInput || e.target === endDateInput) {
                    refreshAvailability();
                    return;
                }

                updateEditState();
            }
        });

        [startDateInput, endDateInput].forEach(input => {
            if (datesLocked && input) {
                ['keydown', 'input', 'click'].forEach(eventName => {
                    input.addEventListener(eventName, function (event) {
                        event.preventDefault();
                    });
                });
            }
        });

        syncAllGroupSelects();
        refreshAvailability();
    });
</script>
@endpush
