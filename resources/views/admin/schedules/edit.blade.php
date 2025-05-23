@extends('layouts.app')

@section('title', 'Edit Jadwal Perkuliahan')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">General</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.schedules.index') }}">Jadwal Perkuliahan</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Edit Jadwal Perkuliahan</h6>

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
                                <label for="course_id" class="form-label">Mata Kuliah</label>
                                <select class="form-select" id="course_id" name="course_id" required>
                                    <option value="">Select mata kuliah</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}"
                                            {{ old('course_id', $schedule->course_id) == $course->id ? 'selected' : '' }}>
                                            {{ $course->code }} - {{ $course->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- classroom --}}
                            <div class="col-md-6">
                                <label for="classroom_id" class="form-label">Ruang</label>
                                <select class="form-select" id="classroom_id" name="classroom_id" required>
                                    <option value="">Select Ruang</option>
                                    @foreach ($classrooms as $classroom)
                                        <option value="{{ $classroom->id }}"
                                            data-study-program-id="{{ $classroom->study_program_id }}"
                                            {{ old('classroom_id', $schedule->classroom_id) == $classroom->id ? 'selected' : '' }}>
                                            {{ $classroom->name }} - {{ $classroom->studyProgram->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="study_program_id" id="study_program_id"
                                    value="{{ old('study_program_id', $schedule->study_program_id) }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="lecturer_id" class="form-label">Dosen</label>
                                <select class="form-select" id="lecturer_id" name="lecturer_id" required>
                                    <option value="">Select Dosen</option>
                                    @foreach ($lecturers as $lecturer)
                                        <option value="{{ $lecturer->id }}"
                                            {{ old('lecturer_id', $schedule->lecturer_id) == $lecturer->id ? 'selected' : '' }}>
                                            {{ $lecturer->user ? $lecturer->user->name : 'Unknown' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="room" class="form-label">Ruang</label>
                                <select class="form-select" id="room" name="room" required>
                                    <option value="">Select Ruang</option>
                                    <option value="RT01" {{ old('room', $schedule->room) == 'RT01' ? 'selected' : '' }}>
                                        RT01</option>
                                    <option value="RT02" {{ old('room', $schedule->room) == 'RT02' ? 'selected' : '' }}>
                                        RT02</option>
                                    <option value="RT03" {{ old('room', $schedule->room) == 'RT03' ? 'selected' : '' }}>
                                        RT03</option>
                                    <option value="RT04" {{ old('room', $schedule->room) == 'RT04' ? 'selected' : '' }}>
                                        RT04</option>
                                    <option value="LPR01" {{ old('room', $schedule->room) == 'LPR01' ? 'selected' : '' }}>
                                        LPR01</option>
                                    <option value="LPR02" {{ old('room', $schedule->room) == 'LPR02' ? 'selected' : '' }}>
                                        LPR02</option>
                                    <option value="LT01" {{ old('room', $schedule->room) == 'LT01' ? 'selected' : '' }}>
                                        LT01</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="semester" class="form-label">Academik Term</label>
                                <select class="form-select" id="semester" name="semester" required>
                                    <option value="">Select Academik Term</option>
                                    @for ($i = 1; $i <= 8; $i++)
                                        <option value="{{ $i }}"
                                            {{ old('semester', $schedule->semester) == $i ? 'selected' : '' }}>
                                            {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                                <small class="text-muted">Semester ke- (1-8 untuk program 4 tahun)</small>
                            </div>

                            <div class="col-md-6">
                                <label for="semester_id" class="form-label">Semester</label>
                                <select class="form-select" id="semester_id" name="semester_id" required>
                                    <option value="">Select Semester</option>
                                    @foreach ($semesters as $semester)
                                        <option value="{{ $semester->id }}"
                                            {{ old('semester_id', $schedule->semester_id) == $semester->id ? 'selected' : '' }}
                                            {{ $semester->is_active ? 'selected' : '' }}>
                                            {{ $semester->name }} ({{ $semester->academic_year }})
                                            {{ $semester->is_active ? '- Active' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="total_weeks" class="form-label">Total Minggu</label>
                                <input type="number" class="form-control" id="total_weeks" name="total_weeks"
                                    value="{{ old('total_weeks', $schedule->total_weeks ?? 16) }}" min="1"
                                    max="52" required>
                                <small class="text-muted">Jumlah minggu untuk jadwal ini (contoh: 16 minggu dalam satu
                                    semester)</small>
                            </div>
                            <div class="col-md-6">
                                <label for="meetings_per_week" class="form-label">>Jumlah Pertemuan</label>
                                <input type="number" class="form-control" id="meetings_per_week" name="meetings_per_week"
                                    value="{{ old('meetings_per_week', $schedule->meetings_per_week ?? 1) }}"
                                    min="1" max="7" required>
                                <small class="text-muted">Jumlah pertemuan kelas per minggu</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Select Hari</label>
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($days as $day)
                                            <button type="button"
                                                class="btn {{ $schedule->day === $day ? 'btn-primary' : 'btn-outline-primary' }} day-btn"
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

                        <div class="mb-3 time-slots-container" style="{{ $schedule->day ? '' : 'display: none;' }}">
                            <label class="form-label">Select Time Slots (Multiple Allowed)</label>
                            <div class="mb-2">
                                <div class="alert alert-info">
                                    <i class="icon-info-circle"></i> Kamu bisa memilih lebih dari satu slot waktu untuk
                                    pertemuan yang sama. Misalnya, jika kamu ingin mengadakan kuliah dari jam 08:00 - 10:00
                                    dan juga dari jam 10:00 - 12:00, kamu bisa memilih kedua slot tersebut.
                                </div>
                                <div class="d-flex gap-3 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="btn btn-outline-secondary me-2" style="width: 40px; height: 20px;">
                                        </div>
                                        <small>Tersedia</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="btn btn-secondary me-2" style="width: 40px; height: 20px;"></div>
                                        <small>Dipilih</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="btn booked me-2"
                                            style="width: 40px; height: 20px; background-color: #ffebee; border-color: #ffcdd2;">
                                        </div>
                                        <small>Tidak Tersedia</small>
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
                                        <p class="fw-bold mb-2">Selected Slot Waktu:</p>
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
                                class="btn btn-sm btn-secondary me-2">Batal</a>
                            <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
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
            const classroomSelect = document.getElementById('classroom_id');
            const studyProgramIdInput = document.getElementById('study_program_id');

            // Store selected time slots - initialize with existing time slots
            let selectedTimeSlots = @json($selectedTimeSlots ?? []);

            // Set study program ID when classroom is selected
            classroomSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption && selectedOption.dataset.studyProgramId) {
                    studyProgramIdInput.value = selectedOption.dataset.studyProgramId;
                } else {
                    studyProgramIdInput.value = '';
                }
            });

            // Initialize study program ID if classroom is pre-selected
            if (classroomSelect.value) {
                const selectedOption = classroomSelect.options[classroomSelect.selectedIndex];
                if (selectedOption && selectedOption.dataset.studyProgramId) {
                    studyProgramIdInput.value = selectedOption.dataset.studyProgramId;
                }
            }

            // Initialize selected time slots
            if (selectedTimeSlots.length > 0) {
                // Mark buttons as selected
                timeSlotButtons.forEach(btn => {
                    if (selectedTimeSlots.includes(btn.dataset.slot)) {
                        btn.classList.remove('btn-outline-secondary');
                        btn.classList.add('btn-secondary');
                    }
                });

                // Update the list display
                updateSelectedSlotsList();

                // Check availability for conflicts
                checkAvailability();
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
            // Add lecturer change handler
            const lecturerSelect = document.getElementById('lecturer_id');
            lecturerSelect.addEventListener('change', checkAvailability);

            function checkAvailability() {
                const room = roomInput.value;
                const day = selectedDayInput.value;
                const lecturer_id = document.getElementById('lecturer_id').value;
                const schedule_id = {{ $schedule->id }};

                if (!room || !day) return;

                fetch(`{{ route('admin.schedules.check-availability') }}?room=${room}&day=${day}&lecturer_id=${lecturer_id}&exclude_id=${schedule_id}`, {
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
                            // Preserve selected status
                            const isSelected = selectedTimeSlots.includes(btn.dataset.slot);

                            if (!isSelected) {
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
                            const conflictType = slot.type || 'room';

                            timeSlotButtons.forEach(btn => {
                                if (btn.dataset.slot === slotString) {
                                    // Only mark as booked if not already selected
                                    if (!selectedTimeSlots.includes(slotString)) {
                                        btn.classList.add('booked');
                                        btn.disabled = true;

                                        let infoText = '';
                                        if (conflictType === 'room') {
                                            infoText = ` Room booked`;
                                        } else {
                                            infoText = ` Lecturer booked`;
                                        }

                                        btn.innerHTML =
                                            `<span>${slotString}</span>&nbsp;|&nbsp;<small>${infoText}</small>`;
                                    }
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
