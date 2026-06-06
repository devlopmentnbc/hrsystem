@extends('layouts.app')

@section('title', 'Shift Schedule')

@section('content')

<div class="content-card">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h4 class="fw-bold mb-1">
                Shift Schedule
            </h4>

            <p class="text-muted mb-0">
                Schedule shifts and assign shift groups or duty off teams for each day.
            </p>

        </div>

    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-4">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger rounded-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="GET" action="{{ route('shifts_schedule.index') }}" class="row gy-3 align-items-end mb-4">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Start Date</label>
            <input type="date" id="filter-start-date" name="start_date" class="form-control" value="{{ $startDate }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">End Date</label>
            <input type="date" id="filter-end-date" name="end_date" class="form-control" value="{{ $endDate }}" required>
        </div>
        <div class="col-md-4 d-grid align-self-start">
            <button type="submit" id="load-period-button" class="btn btn-primary rounded-pill px-4">Load Period</button>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Shift Group Categories</label>
            <select name="group_category" id="filter-group-category" class="form-select" required>
                <option value="">Select one category</option>
                @foreach($allGroupCategories as $category)
                    <option value="{{ $category }}" {{ $selectedGroupCategory === $category ? 'selected' : '' }}>{{ $category }}</option>
                @endforeach
            </select>
            <div class="form-text">
                Select one category. Only one schedule can exist for the same category within an overlapping date range.
            </div>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="alert alert-info rounded-4 mb-0" id="schedule-booking-summary">
                Select a category to view existing schedules and unavailable date ranges.
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Existing Schedules for Category</h6>
                    <div id="existing-category-schedules" class="small text-muted">No category selected.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h6 class="fw-bold mb-1">Booked Range Calendar</h6>
                    <small class="text-muted">Dates in the selected period are marked as free, booked, or overlapping.</small>
                </div>
                <div class="booking-pill-list" id="booking-range-badges"></div>
            </div>
            <div id="booking-range-calendar" class="schedule-calendar-grid"></div>
        </div>
    </div>

    <div class="mb-4">
        <h5 class="mb-2">Saved Schedules</h5>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Period</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($savedSchedules as $s)
                        <tr>
                            <td>{{ $s->schedule_name }}</td>
                            <td>{{ $s->group_category ?? '-' }}</td>
                            <td>{{ $s->start_date }} → {{ $s->end_date }}</td>
                            <td>{{ $s->is_active ? 'Active' : 'Inactive' }}</td>
                            <td class="text-end">
                                <a href="{{ route('shifts_schedule.edit', $s->id) }}" class="btn btn-sm btn-outline-primary me-2">Edit</a>
                                <form method="POST" action="{{ route('shifts_schedule.toggle', $s->id) }}" style="display:inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-secondary">{{ $s->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <form id="schedule-form" method="POST" action="{{ route('shifts_schedule.store') }}">
        @csrf
        <input type="hidden" name="group_category" value="{{ old('group_category', $selectedGroupCategory) }}">
        <input type="hidden" name="start_date" value="{{ $startDate }}">
        <input type="hidden" name="end_date" value="{{ $endDate }}">

        <div class="row mb-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Schedule Name</label>
                <input type="text" name="schedule_name" class="form-control" placeholder="Supervisor Schedule" required>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" id="add-shift-row" class="btn btn-outline-primary rounded-pill px-4 me-2">Add Shift Row</button>
                <button type="button" id="add-off-row" class="btn btn-outline-secondary rounded-pill px-4">Add Off Duty Row</button>
            </div>
        </div>

        @if(! $shiftsGroups->count() && $selectedGroupCategory)
            <div class="alert alert-warning">
                No shift groups are available for the selected category. Change your category filter or add matching groups first.
            </div>
        @endif

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
                <tbody id="schedule-rows-body">
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
                                <label class="form-label small mb-1">Working Groups</label>
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
                </tbody>
                <tbody class="table-secondary">
                    <tr>
                        <td colspan="{{ count($days) + 2 }}" class="text-start fw-semibold">Off Duty Rows</td>
                    </tr>
                </tbody>
                <tbody id="off-rows-body">
                    <tr class="off-schedule-row" data-row-index="0">
                        <td class="text-start align-middle">
                            <div class="fw-semibold">Off Duty Groups</div>
                            <div class="text-muted small">Mark groups that are off for each date.</div>
                        </td>
                        @foreach($days as $day)
                            <td class="align-top">
                                <select multiple class="form-select form-select-sm" name="schedule[off_rows][0][days][{{ $day->toDateString() }}][]">
                                    @foreach($shiftsGroups as $group)
                                        <option value="{{ $group->id }}">{{ $group->group_name }}</option>
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

        <div class="mt-4 text-end">
            <button type="button" id="save-schedule-button" class="btn btn-success rounded-pill px-4">Save Schedule</button>
        </div>
    </form>

    <template id="shift-row-template">
        <tr class="schedule-row" data-row-index="__ROW_INDEX__">
            <td class="text-start align-middle">
                <select name="schedule[rows][__ROW_INDEX__][shift_id]" class="form-select mb-2">
                    <option value="">Select shift</option>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}">{{ $shift->shift_name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('g:i A') }})</option>
                    @endforeach
                </select>
                <div class="text-muted small">Choose the shift for this row.</div>
            </td>
            @foreach($days as $day)
                <td class="align-top">
                    <label class="form-label small mb-1">Working Groups</label>
                    <select multiple class="form-select form-select-sm" name="schedule[rows][__ROW_INDEX__][days][{{ $day->toDateString() }}][work_groups][]">
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
    </template>

    <template id="off-row-template">
        <tr class="off-schedule-row" data-row-index="__OFF_ROW_INDEX__">
            <td class="text-start align-middle">
                <div class="fw-semibold">Off Duty Groups</div>
                <div class="text-muted small">Mark groups that are off for each date.</div>
            </td>
            @foreach($days as $day)
                <td class="align-top">
                    <select multiple class="form-select form-select-sm" name="schedule[off_rows][__OFF_ROW_INDEX__][days][{{ $day->toDateString() }}][]">
                        @foreach($shiftsGroups as $group)
                            <option value="{{ $group->id }}">{{ $group->group_name }}</option>
                        @endforeach
                    </select>
                </td>
            @endforeach
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-off-row">Remove</button>
            </td>
        </tr>
    </template>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const scheduleForm = document.getElementById('schedule-form');
        const saveButton = document.getElementById('save-schedule-button');
        const filterCategorySelect = document.getElementById('filter-group-category');
        const filterStartDate = document.getElementById('filter-start-date');
        const filterEndDate = document.getElementById('filter-end-date');
        const bookingSummary = document.getElementById('schedule-booking-summary');
        const existingSchedulesContainer = document.getElementById('existing-category-schedules');
        const bookingCalendar = document.getElementById('booking-range-calendar');
        const bookingBadges = document.getElementById('booking-range-badges');
        const loadPeriodButton = document.getElementById('load-period-button');
        const addRowButton = document.getElementById('add-shift-row');
        const addOffRowButton = document.getElementById('add-off-row');
        const rowsBody = document.getElementById('schedule-rows-body');
        const offRowsBody = document.getElementById('off-rows-body');
        const rowTemplate = document.getElementById('shift-row-template').innerHTML;
        const offRowTemplate = document.getElementById('off-row-template').innerHTML;
        const hiddenCategoryInput = scheduleForm?.querySelector('input[name="group_category"]');
        const hiddenStartDateInput = scheduleForm?.querySelector('input[name="start_date"]');
        const hiddenEndDateInput = scheduleForm?.querySelector('input[name="end_date"]');
        const availabilityUrl = @json(route('shifts_schedule.availability'));
        const groupsByCategory = @json($groupsByCategory);
        let nextRowIndex = 1;
        let nextOffRowIndex = 1;
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

        function renderExistingSchedules(category, schedules) {
            if (!category) {
                existingSchedulesContainer.innerHTML = '<span class="text-muted">No category selected.</span>';
                return;
            }

            if (!schedules.length) {
                existingSchedulesContainer.innerHTML = '<span class="text-success">No existing schedules for this category.</span>';
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
            const activeCategory = filterCategorySelect?.value || '';
            const selectedStart = filterStartDate?.value || '';
            const selectedEnd = filterEndDate?.value || '';
            const requestId = ++availabilityRequestId;

            if (!activeCategory || !selectedStart || !selectedEnd) {
                availabilityState = {
                    schedules: [],
                    conflicts: [],
                    booked_dates: {},
                    loading: false,
                    error: null,
                };
                renderExistingSchedules(activeCategory, []);
                renderBookingBadges([], {});
                renderBookingCalendar(selectedStart, selectedEnd, {});
                updateBookingState();
                return;
            }

            availabilityState = {
                ...availabilityState,
                loading: true,
                error: null,
            };
            updateBookingState();

            const params = new URLSearchParams({
                group_category: activeCategory,
                start_date: selectedStart,
                end_date: selectedEnd,
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

            renderExistingSchedules(activeCategory, availabilityState.schedules);
            renderBookingBadges(availabilityState.conflicts, availabilityState.booked_dates);
            renderBookingCalendar(selectedStart, selectedEnd, availabilityState.booked_dates);
            updateBookingState();
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

        function syncAllGroupSelects(category) {
            document.querySelectorAll('select[name*="[work_groups][]"], select[name*="[off_rows]"]').forEach(select => {
                populateGroupSelect(select, category);
            });
        }

        function getDuplicateAssignments() {
            const selectedMap = new Map();
            const duplicates = [];

            document.querySelectorAll('#schedule-form select[name*="[days]"]').forEach(select => {
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

        function updateBookingState() {
            const activeCategory = filterCategorySelect?.value || '';
            const selectedStart = filterStartDate?.value || '';
            const selectedEnd = filterEndDate?.value || '';
            const loadedCategory = hiddenCategoryInput?.value || '';
            const loadedStart = hiddenStartDateInput?.value || '';
            const loadedEnd = hiddenEndDateInput?.value || '';
            const schedules = availabilityState.schedules || [];
            const overlap = (availabilityState.conflicts || [])[0] || null;
            const staleBuilder = activeCategory !== loadedCategory || selectedStart !== loadedStart || selectedEnd !== loadedEnd;
            const duplicates = getDuplicateAssignments();

            filterStartDate?.classList.remove('is-invalid');
            filterEndDate?.classList.remove('is-invalid');
            bookingSummary.className = 'alert rounded-4 mb-0';

            if (!activeCategory) {
                bookingSummary.classList.add('alert-info');
                bookingSummary.textContent = 'Select a category to view existing schedules and unavailable date ranges.';
                if (saveButton) saveButton.disabled = true;
                return;
            }

            if (availabilityState.loading) {
                bookingSummary.classList.add('alert-info');
                bookingSummary.textContent = 'Loading booked ranges for the selected category.';
                if (saveButton) saveButton.disabled = true;
                return;
            }

            if (availabilityState.error) {
                bookingSummary.classList.add('alert-warning');
                bookingSummary.textContent = availabilityState.error;
                if (saveButton) saveButton.disabled = true;
                return;
            }

            if (overlap) {
                bookingSummary.classList.add('alert-danger');
                bookingSummary.textContent = `Selected date range overlaps with existing schedule "${overlap.schedule_name}" (${overlap.start_date} to ${overlap.end_date}).`;
                filterStartDate?.classList.add('is-invalid');
                filterEndDate?.classList.add('is-invalid');
            } else if (staleBuilder) {
                bookingSummary.classList.add('alert-warning');
                bookingSummary.textContent = 'Date range or category changed. Click "Load Period" to refresh the schedule builder before saving.';
            } else if (duplicates.length) {
                const firstDuplicate = duplicates[0];
                bookingSummary.classList.add('alert-danger');
                bookingSummary.textContent = `Duplicate assignment detected for ${firstDuplicate.group} on ${firstDuplicate.date}. Remove duplicate group selections before saving.`;
            } else {
                bookingSummary.classList.add('alert-success');
                bookingSummary.textContent = schedules.length
                    ? 'Selected category loaded. Existing booked ranges are shown at the right. Non-overlapping range is ready to save.'
                    : 'No booked ranges for this category. You can create a schedule for the selected period.';
            }

            if (saveButton) {
                saveButton.disabled = Boolean(overlap || staleBuilder || duplicates.length || !activeCategory);
            }

            if (loadPeriodButton) {
                loadPeriodButton.disabled = !activeCategory || !selectedStart || !selectedEnd;
            }
        }

        addRowButton.addEventListener('click', function () {
            const cloneHtml = rowTemplate.replace(/__ROW_INDEX__/g, nextRowIndex);
            rowsBody.insertAdjacentHTML('beforeend', cloneHtml);
            syncAllGroupSelects(filterCategorySelect?.value || hiddenCategoryInput?.value || '');
            nextRowIndex++;
            updateBookingState();
        });

        addOffRowButton.addEventListener('click', function () {
            const cloneHtml = offRowTemplate.replace(/__OFF_ROW_INDEX__/g, nextOffRowIndex);
            offRowsBody.insertAdjacentHTML('beforeend', cloneHtml);
            syncAllGroupSelects(filterCategorySelect?.value || hiddenCategoryInput?.value || '');
            nextOffRowIndex++;
            updateBookingState();
        });

        rowsBody.addEventListener('click', function (event) {
            const removeButton = event.target.closest('.remove-shift-row');
            if (removeButton) {
                const row = removeButton.closest('.schedule-row');
                if (rowsBody.querySelectorAll('.schedule-row').length > 1) {
                    row.remove();
                } else {
                    alert('At least one shift row is required.');
                }
                updateBookingState();
            }
        });

        offRowsBody.addEventListener('click', function (event) {
            const removeButton = event.target.closest('.remove-off-row');
            if (removeButton) {
                const row = removeButton.closest('.off-schedule-row');
                row?.remove();
                updateBookingState();
            }
        });

        document.addEventListener('change', function (event) {
            if (event.target.matches('#schedule-form select[name*="[days]"]') || event.target === filterCategorySelect || event.target === filterStartDate || event.target === filterEndDate) {
                if (event.target === filterCategorySelect) {
                    syncAllGroupSelects(filterCategorySelect.value);
                }

                if (event.target === filterCategorySelect || event.target === filterStartDate || event.target === filterEndDate) {
                    refreshAvailability();
                    return;
                }

                updateBookingState();
            }
        });

        if (scheduleForm && saveButton) {
            saveButton.addEventListener('click', function () {
                updateBookingState();
                if (saveButton.disabled) {
                    return;
                }

                const action = scheduleForm.getAttribute('action') || '';
                console.log('Schedule form action:', action);
                if (action.indexOf('shifts-schedule') === -1 && action.indexOf('shifts_schedule') === -1) {
                    alert('Unexpected schedule form submission target: ' + action);
                    return;
                }

                scheduleForm.submit();
            });

            scheduleForm.addEventListener('submit', function () {
                console.log('Submitting schedule-form to', this.action);
            });
        }

        syncAllGroupSelects(filterCategorySelect?.value || hiddenCategoryInput?.value || '');
        refreshAvailability();
    });
</script>

@endsection