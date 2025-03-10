@extends('layouts.app')

@section('title', 'Edit Class Schedule')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Data</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.schedules.index') }}">Class Schedule</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Edit Class Schedule</h6>

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.schedules.update', $schedule->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="course_code" class="form-label">Course Code</label>
                                <input type="text" class="form-control" id="course_code" name="course_code"
                                    value="{{ old('course_code', $schedule->course_code) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label for="course_name" class="form-label">Course Name</label>
                                <input type="text" class="form-control" id="course_name" name="course_name"
                                    value="{{ old('course_name', $schedule->course_name) }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="lecturer_id" class="form-label">Lecturer</label>
                                <select class="form-select" id="lecturer_id" name="lecturer_id" required>
                                    <option value="">Select Lecturer</option>
                                    @foreach ($lecturers as $lecturer)
                                        <option value="{{ $lecturer->id }}"
                                            {{ old('lecturer_id', $schedule->lecturer_id) == $lecturer->id ? 'selected' : '' }}>
                                            {{ $lecturer->user ? $lecturer->user->name : 'Unknown' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="room" class="form-label">Room</label>
                                <input type="text" class="form-control" id="room" name="room"
                                    value="{{ old('room', $schedule->room) }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="semester" class="form-label">Semester</label>
                                <input type="text" class="form-control" id="semester" name="semester"
                                    value="{{ old('semester', $schedule->semester) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label for="academic_year" class="form-label">Academic Year</label>
                                <input type="text" class="form-control" id="academic_year" name="academic_year"
                                    value="{{ old('academic_year', $schedule->academic_year) }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Select Day</label>
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($days as $day)
                                            <button type="button"
                                                class="btn {{ old('day', $schedule->day) == $day ? 'btn-primary' : 'btn-outline-primary' }} day-btn"
                                                data-day="{{ $day }}">
                                                {{ $day }}
                                            </button>
                                        @endforeach
                                    </div>
                                    <input type="hidden" name="day" id="selected_day"
                                        value="{{ old('day', $schedule->day) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 time-slots-container"
                            style="{{ old('day', $schedule->day) ? 'display: block;' : 'display: none;' }}">
                            <label class="form-label">Select Time Slots (Multiple Allowed)</label>
                            <div class="mb-2">
                                <div class="alert alert-info">
                                    <i class="icon-info-circle"></i> You can select multiple time slots for this class.
                                    Click on each slot you want to select.
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="row g-2 time-slots">
                                        @foreach ($timeSlots as $slot)
                                            <div class="col-md-3 col-sm-4 col-6 mb-2">
                                                <button type="button"
                                                    class="btn {{ in_array($slot, $selectedTimeSlots) ? 'btn-secondary' : 'btn-outline-secondary' }} time-slot-btn w-100"
                                                    data-slot="{{ $slot }}">
                                                    {{ $slot }}
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="selected-slots-container mt-3"
                                        style="{{ count($selectedTimeSlots) > 0 ? 'display: block;' : 'display: none;' }}">
                                        <p class="fw-bold">Selected Time Slots:</p>
                                        <div class="selected-slots-list d-flex flex-wrap gap-2">
                                            @foreach ($selectedTimeSlots as $slot)
                                                <div class="badge bg-primary p-2 d-flex align-items-center">
                                                    <span>{{ $slot }}</span>
                                                    <button type="button" class="btn-close btn-close-white ms-2"
                                                        data-slot="{{ $slot }}" aria-label="Remove"></button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div id="time_slots_error" class="text-danger mt-2" style="display: none;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- This is the container where the hidden time_slots inputs will be added -->
                        <div id="time_slots_inputs">
                            @foreach ($selectedTimeSlots as $slot)
                                <input type="hidden" name="time_slots[]" value="{{ $slot }}">
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Schedule</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dayButtons = document.querySelectorAll('.day-btn');
            const timeSlotButtons = document.querySelectorAll('.time-slot-btn');
            const selectedDayInput = document.getElementById('selected_day');
            const timeSlotsContainer = document.querySelector('.time-slots-container');
            const roomInput = document.getElementById('room');
            const selectedSlotsContainer = document.querySelector('.selected-slots-container');
            const selectedSlotsList = document.querySelector('.selected-slots-list');
            const timeSlotErrorDiv = document.getElementById('time_slots_error');
            const timeSlotInputsContainer = document.getElementById('time_slots_inputs');

            // Store selected time slots
            let selectedTimeSlots = @json($selectedTimeSlots);

            // Day button click handler
            dayButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Reset all buttons
                    dayButtons.forEach(btn => {
                        btn.classList.remove('btn-primary');
                        btn.classList.add('btn-outline-primary');
                    });

                    // Set selected button
                    this.classList.remove('btn-outline-primary');
                    this.classList.add('btn-primary');

                    // Set value to hidden input
                    selectedDayInput.value = this.dataset.day;

                    // Show time slots
                    timeSlotsContainer.style.display = 'block';

                    // Reset time slot selection
                    timeSlotButtons.forEach(btn => {
                        btn.classList.remove('btn-secondary');
                        btn.classList.add('btn-outline-secondary');
                        btn.classList.remove('booked');
                        btn.disabled = false;
                        btn.innerHTML = btn.dataset.slot;
                    });

                    // Clear selected slots
                    selectedTimeSlots = [];
                    updateSelectedSlotsList();

                    // Check available time slots
                    checkAvailability();
                });
            });

            // Time slot button click handler
            timeSlotButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (this.disabled) return;

                    const slot = this.dataset.slot;

                    // Check if the slot is already selected
                    const slotIndex = selectedTimeSlots.indexOf(slot);

                    // Toggle selection (add or remove)
                    if (slotIndex === -1) {
                        // Add slot to selected list
                        selectedTimeSlots.push(slot);
                        this.classList.remove('btn-outline-secondary');
                        this.classList.add('btn-secondary');
                    } else {
                        // Remove slot from selected list
                        selectedTimeSlots.splice(slotIndex, 1);
                        this.classList.remove('btn-secondary');
                        this.classList.add('btn-outline-secondary');
                    }

                    // Update display of selected slots
                    updateSelectedSlotsList();
                });
            });

            // Function to update the list of selected slots
            function updateSelectedSlotsList() {
                // Clear the time slots inputs container
                timeSlotInputsContainer.innerHTML = '';

                // Clear the visual list
                selectedSlotsList.innerHTML = '';

                // Hide error message if any
                timeSlotErrorDiv.style.display = 'none';

                if (selectedTimeSlots.length > 0) {
                    // Show the container
                    selectedSlotsContainer.style.display = 'block';

                    // Add each selected slot to the list
                    selectedTimeSlots.forEach((slot, index) => {
                        // Create badge for the slot
                        const badge = document.createElement('div');
                        badge.className = 'badge bg-primary p-2 d-flex align-items-center';
                        badge.innerHTML = `
                            <span>${slot}</span>
                            <button type="button" class="btn-close btn-close-white ms-2" data-slot="${slot}" aria-label="Remove"></button>
                        `;
                        selectedSlotsList.appendChild(badge);

                        // Create hidden input for each time slot
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `time_slots[]`; // Use array notation for multiple slots
                        input.value = slot;
                        timeSlotInputsContainer.appendChild(input);

                        // Add click handler to the close button
                        badge.querySelector('.btn-close').addEventListener('click', function() {
                            const slotToRemove = this.dataset.slot;
                            const index = selectedTimeSlots.indexOf(slotToRemove);

                            if (index !== -1) {
                                selectedTimeSlots.splice(index, 1);

                                // Update button state
                                timeSlotButtons.forEach(btn => {
                                    if (btn.dataset.slot === slotToRemove) {
                                        btn.classList.remove('btn-secondary');
                                        btn.classList.add('btn-outline-secondary');
                                    }
                                });

                                // Update the list
                                updateSelectedSlotsList();
                            }
                        });
                    });
                } else {
                    // Hide the container if no slots selected
                    selectedSlotsContainer.style.display = 'none';
                }
            }

            // Room or day change handler
            roomInput.addEventListener('change', checkAvailability);

            function checkAvailability() {
                const room = roomInput.value;
                const day = selectedDayInput.value;
                const scheduleId = '{{ $schedule->id }}';

                if (!room || !day) return;

                fetch(`{{ route('admin.schedules.check-availability') }}?room=${room}&day=${day}&schedule_id=${scheduleId}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Reset all time slots first
                        timeSlotButtons.forEach(btn => {
                            // If this slot is in our selected slots, keep it selected
                            if (selectedTimeSlots.includes(btn.dataset.slot)) {
                                btn.classList.add('btn-secondary');
                                btn.classList.remove('btn-outline-secondary');
                            } else {
                                btn.classList.remove('btn-secondary');
                                btn.classList.add('btn-outline-secondary');
                            }

                            btn.classList.remove('booked');
                            btn.disabled = false;
                            btn.innerHTML = btn.dataset.slot;
                        });

                        // Mark booked slots
                        data.bookedSlots.forEach(slot => {
                            const startTime = slot.start_time;
                            const endTime = slot.end_time;
                            const slotString = `${startTime} - ${endTime}`;

                            timeSlotButtons.forEach(btn => {
                                if (btn.dataset.slot === slotString && !selectedTimeSlots
                                    .includes(slotString)) {
                                    btn.classList.add('booked');
                                    btn.disabled = true;
                                    btn.innerHTML =
                                        `<span>${slotString}</span><br><small>Booked by ${slot.lecturer_name}</small>`;
                                }
                            });
                        });
                    })
                    .catch(error => console.error('Error checking availability:', error));
            }

            // Form submission
            document.querySelector('form').addEventListener('submit', function(e) {
                if (selectedTimeSlots.length === 0) {
                    e.preventDefault();
                    timeSlotErrorDiv.textContent = 'Please select at least one time slot.';
                    timeSlotErrorDiv.style.display = 'block';
                    return false;
                }
                return true;
            });

            // Add event listeners to close buttons that were created server-side
            document.querySelectorAll('.selected-slots-list .btn-close').forEach(btn => {
                btn.addEventListener('click', function() {
                    const slotToRemove = this.dataset.slot;
                    const index = selectedTimeSlots.indexOf(slotToRemove);

                    if (index !== -1) {
                        selectedTimeSlots.splice(index, 1);

                        // Update button state
                        timeSlotButtons.forEach(btn => {
                            if (btn.dataset.slot === slotToRemove) {
                                btn.classList.remove('btn-secondary');
                                btn.classList.add('btn-outline-secondary');
                            }
                        });

                        // Update the list
                        updateSelectedSlotsList();
                    }
                });
            });

            // Initial check for availability
            if (roomInput.value && selectedDayInput.value) {
                checkAvailability();
            }
        });
    </script>

    <style>
        .day-btn {
            min-width: 100px;
        }

        .time-slot-btn {
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .time-slot-btn.booked {
            background-color: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
            font-size: 0.8rem;
        }

        .time-slot-btn small {
            font-size: 0.7rem;
        }

        .selected-slots-list .badge {
            margin-right: 8px;
            margin-bottom: 8px;
        }

        .btn-close {
            font-size: 0.6rem;
            padding: 2px;
        }
    </style>
@endpush
