@extends('layouts.app')

@section('title', 'Create Class Schedule')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Data</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.schedules.index') }}">Class Schedule</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Create Class Schedule</h6>

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.schedules.store') }}">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="course_id" class="form-label">Course</label>
                                <select class="form-select" id="course_id" name="course_id" required>
                                    <option value="">Select Course</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}"
                                            {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->code }} - {{ $course->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="classroom_id" class="form-label">Classroom</label>
                                <select class="form-select" id="classroom_id" name="classroom_id" required>
                                    <option value="">Select Classroom</option>
                                    @foreach ($classrooms as $classroom)
                                        <option value="{{ $classroom->id }}"
                                            {{ old('classroom_id') == $classroom->id ? 'selected' : '' }}>
                                            {{ $classroom->name }} ({{ $classroom->department }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="lecturer_id" class="form-label">Lecturer</label>
                                <select class="form-select" id="lecturer_id" name="lecturer_id" required>
                                    <option value="">Select Lecturer</option>
                                    @foreach ($lecturers as $lecturer)
                                        <option value="{{ $lecturer->id }}"
                                            {{ old('lecturer_id') == $lecturer->id ? 'selected' : '' }}>
                                            {{ $lecturer->user ? $lecturer->user->name : 'Unknown' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="room" class="form-label">Room</label>
                                <select class="form-select" id="room" name="room" required>
                                    <option value="">Select Room</option>
                                    <option value="RT01" {{ old('room') == 'RT01' ? 'selected' : '' }}>RT01</option>
                                    <option value="RT02" {{ old('room') == 'RT02' ? 'selected' : '' }}>RT02</option>
                                    <option value="RT03" {{ old('room') == 'RT03' ? 'selected' : '' }}>RT03</option>
                                    <option value="RT04" {{ old('room') == 'RT04' ? 'selected' : '' }}>RT04</option>
                                    <option value="LPR01" {{ old('room') == 'LPR01' ? 'selected' : '' }}>LPR01</option>
                                    <option value="LPR02" {{ old('room') == 'LPR02' ? 'selected' : '' }}>LPR02</option>
                                    <option value="LT01" {{ old('room') == 'LT01' ? 'selected' : '' }}>LT01</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="semester" class="form-label">Semester</label>
                                <select class="form-select" id="semester" name="semester" required>
                                    <option value="">Select Semester</option>
                                    @for ($i = 1; $i <= 8; $i++)
                                        <option value="{{ $i }}" {{ old('semester') == $i ? 'selected' : '' }}>
                                            {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="academic_year" class="form-label">Academic Year</label>
                                <select class="form-select" id="academic_year" name="academic_year" required>
                                    <option value="">Select Academic Year</option>
                                    <option value="2024/2025 Ganjil"
                                        {{ old('academic_year') == '2024/2025 Ganjil' ? 'selected' : '' }}>2024/2025 Ganjil
                                    </option>
                                    <option value="2024/2025 Genap"
                                        {{ old('academic_year') == '2024/2025 Genap' ? 'selected' : '' }}>2024/2025 Genap
                                    </option>
                                    <option value="2025/2026 Ganjil"
                                        {{ old('academic_year') == '2025/2026 Ganjil' ? 'selected' : '' }}>2025/2026 Ganjil
                                    </option>
                                    <option value="2025/2026 Genap"
                                        {{ old('academic_year') == '2025/2026 Genap' ? 'selected' : '' }}>2025/2026 Genap
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="total_weeks" class="form-label">Total Weeks</label>
                                <input type="number" class="form-control" id="total_weeks" name="total_weeks"
                                    value="{{ old('total_weeks', 16) }}" min="1" max="52" required>
                                <small class="text-muted">Number of weeks for this schedule (e.g., 16 weeks in a
                                    semester)</small>
                            </div>
                            <div class="col-md-6">
                                <label for="meetings_per_week" class="form-label">Meetings Per Week</label>
                                <input type="number" class="form-control" id="meetings_per_week" name="meetings_per_week"
                                    value="{{ old('meetings_per_week', 1) }}" min="1" max="7" required>
                                <small class="text-muted">Number of class meetings per week</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Select Day</label>
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($days as $day)
                                            <button type="button" class="btn btn-outline-primary day-btn"
                                                data-day="{{ $day }}">
                                                {{ $day }}
                                            </button>
                                        @endforeach
                                    </div>
                                    <input type="hidden" name="day" id="selected_day" value="{{ old('day') }}"
                                        required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 time-slots-container" style="display: none;">
                            <label class="form-label">Select Time Slots (Multiple Allowed)</label>
                            <div class="mb-2">
                                <div class="alert alert-info">
                                    <i class="icon-info-circle"></i> You can select multiple time slots for this class.
                                    Click on each slot you want to select.
                                </div>
                                <div class="d-flex gap-3 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="btn btn-outline-secondary me-2" style="width: 40px; height: 20px;">
                                        </div>
                                        <small>Available</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="btn btn-secondary me-2" style="width: 40px; height: 20px;"></div>
                                        <small>Selected</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="btn booked me-2"
                                            style="width: 40px; height: 20px; background-color: #ffebee; border-color: #ffcdd2;">
                                        </div>
                                        <small>Unavailable</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="row g-2 time-slots">
                                        @foreach ($timeSlots as $slot)
                                            <div class="col-md-3 col-sm-4 col-6 mb-2">
                                                <button type="button"
                                                    class="btn btn-outline-secondary time-slot-btn w-100"
                                                    data-slot="{{ $slot }}">
                                                    {{ $slot }}
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="selected-slots-container mt-3" style="display: none;">
                                        <p class="fw-bold mb-2">Selected Time Slots:</p>
                                        <div class="selected-slots-list d-flex flex-wrap gap-2"></div>
                                    </div>
                                    <div id="time_slots_error" class="text-danger mt-2" style="display: none;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- This is the container where the hidden time_slots inputs will be added -->
                        <div id="time_slots_inputs"></div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.schedules.index') }}"
                                class="btn btn-sm btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-sm btn-primary">Create Schedule</button>
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
            let selectedTimeSlots = [];

            // Restore selected day if any
            if (selectedDayInput.value) {
                dayButtons.forEach(btn => {
                    if (btn.dataset.day === selectedDayInput.value) {
                        btn.classList.remove('btn-outline-primary');
                        btn.classList.add('btn-primary');
                        timeSlotsContainer.style.display = 'block';
                    }
                });
            }

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
            // Add this after roomInput event listener
            const lecturerSelect = document.getElementById('lecturer_id');
            lecturerSelect.addEventListener('change', checkAvailability);

            function checkAvailability() {
                const room = roomInput.value;
                const day = selectedDayInput.value;
                const lecturer_id = document.getElementById('lecturer_id').value;

                if (!room || !day) return;

                fetch(`{{ route('admin.schedules.check-availability') }}?room=${room}&day=${day}&lecturer_id=${lecturer_id}`, {
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
                            btn.classList.remove('booked');
                            btn.disabled = false;
                            btn.innerHTML = btn.dataset.slot;
                        });

                        // Mark booked slots
                        data.bookedSlots.forEach(slot => {
                            console.log(slot);
                            const startTime = slot.start_time;
                            const endTime = slot.end_time;
                            const slotString = `${startTime} - ${endTime}`;
                            const conflictType = slot.type || 'room';

                            timeSlotButtons.forEach(btn => {
                                if (btn.dataset.slot === slotString) {
                                    btn.classList.add('booked');
                                    btn.disabled = true;

                                    let infoText = '';
                                    if (conflictType === 'room') {
                                        infoText =
                                            ` Room booked`;
                                    } else {
                                        infoText = ` Lecturer booked`;
                                    }

                                    btn.innerHTML =
                                        `<span>${slotString}</span>&nbsp;|&nbsp;<small>${infoText}</small>`;
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

            // Initial check if values are already set
            if (roomInput.value && selectedDayInput.value) {
                checkAvailability();
            }
        });
    </script>

    <style>
        /* Styles remain the same */
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
            background-color: #ffebee;
            /* Light red background */
            color: #d32f2f;
            /* Dark red text */
            border-color: #ffcdd2;
            /* Red border */
            cursor: not-allowed;
            font-size: 0.8rem;
            position: relative;
            overflow: hidden;
        }

        .time-slot-btn.booked::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: repeating-linear-gradient(-45deg,
                    transparent,
                    transparent 5px,
                    rgba(255, 0, 0, 0.1) 5px,
                    rgba(255, 0, 0, 0.1) 10px);
            pointer-events: none;
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
