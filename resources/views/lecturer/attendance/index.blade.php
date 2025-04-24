@extends('layouts.app')

@section('title', 'Attendance Management')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('lecturer.dashboard') }}">Lecturer</a></li>
            <li class="breadcrumb-item active" aria-current="page">Attendance</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Attendance Management</h6>

                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table id="dataTableExample" class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Course</th>
                                    <th>Room</th>
                                    <th>Day</th>
                                    <th>Time Slots</th>
                                    <th>Semester/Year</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($schedules as $key => $schedule)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            <strong>{{ $schedule->course->code }}</strong><br>
                                            <small>{{ $schedule->course->name }}</small>
                                        </td>
                                        <td>{{ $schedule->room }}</td>
                                        <td>{{ $schedule->day }}</td>
                                        <td>
                                            @if ($schedule->timeSlots && $schedule->timeSlots->count() > 0)
                                                @foreach ($schedule->timeSlots as $timeSlot)
                                                    <div>{{ $timeSlot->start_time->format('H:i') }} -
                                                        {{ $timeSlot->end_time->format('H:i') }}</div>
                                                @endforeach
                                            @else
                                                <span class="text-muted">No time slots</span>
                                            @endif
                                        </td>
                                        <td>{{ $schedule->semester }} / {{ $schedule->semesters->name }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-icon-text btn-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#createAttendanceModal{{ $schedule->id }}">
                                                <i data-feather="plus-circle" class="btn-icon-prepend"></i>Create Session
                                            </button>
                                            <a href="{{ route('lecturer.attendance.show', [
                                                'classSchedule' => $schedule->id,
                                                'date' => now()->format('Y-m-d'),
                                            ]) }}"
                                                class="btn btn-sm btn-icon-text btn-info">
                                                <i data-feather="info" class="btn-icon-prepend"></i>View Sessions
                                            </a>

                                            <!-- Inside the create attendance modal -->
                                            <div class="modal fade" id="createAttendanceModal{{ $schedule->id }}"
                                                tabindex="-1"
                                                aria-labelledby="createAttendanceModalLabel{{ $schedule->id }}"
                                                aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title"
                                                                id="createAttendanceModalLabel{{ $schedule->id }}">
                                                                Create Attendance Session - {{ $schedule->course->code }}
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <form action="{{ route('lecturer.attendance.create') }}"
                                                            method="POST">
                                                            @csrf
                                                            <div class="modal-body">
                                                                <input type="hidden" name="class_id"
                                                                    value="{{ $schedule->id }}">

                                                                <div class="mb-3">
                                                                    <label for="date{{ $schedule->id }}"
                                                                        class="form-label">Session Date</label>
                                                                    <input type="date" class="form-control"
                                                                        id="date{{ $schedule->id }}" name="date"
                                                                        value="{{ date('Y-m-d') }}"
                                                                        min="{{ date('Y-m-d') }}" required>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label for="week{{ $schedule->id }}"
                                                                        class="form-label">Week</label>
                                                                    <select class="form-select week-select"
                                                                        id="week{{ $schedule->id }}" name="week"
                                                                        required data-schedule-id="{{ $schedule->id }}">
                                                                        @for ($i = 1; $i <= $schedule->total_weeks; $i++)
                                                                            <option value="{{ $i }}">Week
                                                                                {{ $i }}</option>
                                                                        @endfor
                                                                    </select>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label for="meetings{{ $schedule->id }}"
                                                                        class="form-label">Meeting</label>
                                                                    <select class="form-select meetings-select"
                                                                        id="meetings{{ $schedule->id }}" name="meetings"
                                                                        required data-schedule-id="{{ $schedule->id }}"
                                                                        {{ $schedule->meetings_per_week == 1 ? 'readonly' : '' }}>
                                                                        @for ($i = 1; $i <= $schedule->meetings_per_week; $i++)
                                                                            <option value="{{ $i }}">Meeting
                                                                                {{ $i }}</option>
                                                                        @endfor
                                                                    </select>
                                                                </div>

                                                                <!-- New fields -->
                                                                <div class="mb-3">
                                                                    <label for="total_hours{{ $schedule->id }}"
                                                                        class="form-label">Total Teaching Hours</label>
                                                                    <input type="number" class="form-control"
                                                                        id="total_hours{{ $schedule->id }}"
                                                                        name="total_hours"
                                                                        value="{{ $schedule->timeSlots->count() ?: 4 }}"
                                                                        min="1" max="8" required>
                                                                    <div class="form-text">Number of teaching hours for
                                                                        attendance tracking</div>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label for="tolerance_minutes{{ $schedule->id }}"
                                                                        class="form-label">Lateness Tolerance
                                                                        (minutes)
                                                                    </label>
                                                                    <select class="form-select"
                                                                        id="tolerance_minutes{{ $schedule->id }}"
                                                                        name="tolerance_minutes" required>
                                                                        <option value="15" selected>15 minutes</option>
                                                                        <option value="20">20 minutes</option>
                                                                        <option value="30">30 minutes</option>
                                                                    </select>
                                                                    <div class="form-text">Maximum allowed lateness before
                                                                        being marked absent for an hour</div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-sm btn-secondary"
                                                                    data-bs-dismiss="modal">Close</button>
                                                                <button type="submit"
                                                                    class="btn btn-sm btn-primary session-submit"
                                                                    id="submit-btn-{{ $schedule->id }}"
                                                                    data-schedule-id="{{ $schedule->id }}">
                                                                    Create Session
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No class schedules found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Get current date in YYYY-MM-DD format
            function getCurrentDate() {
                const today = new Date();
                const year = today.getFullYear();
                let month = today.getMonth() + 1;
                let day = today.getDate();

                // Add leading zeros if needed
                month = month < 10 ? '0' + month : month;
                day = day < 10 ? '0' + day : day;

                return `${year}-${month}-${day}`;
            }

            // Set minimum date for all date inputs
            const today = getCurrentDate();
            $('input[type="date"]').attr('min', today);

            // Reset to today's date if a past date is somehow selected
            $('input[type="date"]').on('change', function() {
                if ($(this).val() < today) {
                    $(this).val(today);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Date',
                        text: 'You cannot select a date in the past.',
                        confirmButtonColor: '#3085d6'
                    });
                }
            });

            // Make readonly selects actually behave like readonly
            $('select[readonly]').on('mousedown', function(e) {
                e.preventDefault();
                this.blur();
                return false;
            });

            // Fetch used sessions data via AJAX for each schedule
            @foreach ($schedules as $schedule)
                // Prepare to load used sessions when modal is opened
                $('#createAttendanceModal{{ $schedule->id }}').on('show.bs.modal', function() {
                    // Fetch the existing sessions for this schedule
                    $.ajax({
                        url: '{{ route('lecturer.attendance.get-used-sessions', $schedule->id) }}',
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            const usedSessions = response.usedSessions;
                            updateSessionOptions({{ $schedule->id }}, usedSessions);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching used sessions:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to load attendance data. Please try again.',
                            });
                        }
                    });
                });
            @endforeach

            // Function to update selects based on used sessions
            function updateSessionOptions(scheduleId, usedSessions) {
                const weekSelect = $('#week' + scheduleId);
                const meetingsSelect = $('#meetings' + scheduleId);
                const meetingsPerWeek = {{ $schedule->meetings_per_week }};
                const submitBtn = $('#submit-btn-' + scheduleId);

                // Reset all options
                weekSelect.find('option').prop('disabled', false);
                meetingsSelect.find('option').prop('disabled', false);
                submitBtn.prop('disabled', false);

                // Immediately disable week/meeting combinations that are used
                if (meetingsPerWeek === 1) {
                    // For classes with only 1 meeting per week, we just disable used weeks
                    usedSessions.forEach(session => {
                        weekSelect.find(`option[value="${session.week}"]`).prop('disabled', true);
                    });

                    // If current selection is disabled, select first available
                    if (weekSelect.find('option:selected').prop('disabled')) {
                        weekSelect.find('option:not(:disabled)').first().prop('selected', true);
                    }

                    // If no weeks available, disable submit button
                    if (weekSelect.find('option:not(:disabled)').length === 0) {
                        submitBtn.prop('disabled', true);
                        Swal.fire({
                            icon: 'info',
                            title: 'All Sessions Created',
                            text: 'All available weeks have attendance sessions created.'
                        });
                    }
                } else {
                    // For multiple meetings per week, it's a combination
                    // Create a lookup of used week/meeting combinations
                    const usedCombos = {};
                    usedSessions.forEach(session => {
                        if (!usedCombos[session.week]) {
                            usedCombos[session.week] = [];
                        }
                        usedCombos[session.week].push(session.meetings);
                    });

                    // Function to update meeting options based on selected week
                    function updateMeetingOptions() {
                        const selectedWeek = weekSelect.val();
                        meetingsSelect.find('option').prop('disabled', false);

                        // Disable used meetings for this week
                        if (usedCombos[selectedWeek]) {
                            usedCombos[selectedWeek].forEach(meetings => {
                                meetingsSelect.find(`option[value="${meetings}"]`).prop('disabled', true);
                            });
                        }

                        // If current selection is disabled, select first available
                        if (meetingsSelect.find('option:selected').prop('disabled')) {
                            meetingsSelect.find('option:not(:disabled)').first().prop('selected', true);
                        }

                        // If all meetings for this week are used, select another week
                        if (meetingsSelect.find('option:not(:disabled)').length === 0) {
                            weekSelect.find(`option[value="${selectedWeek}"]`).prop('disabled', true);
                            weekSelect.find('option:not(:disabled)').first().prop('selected', true);
                            updateMeetingOptions(); // Recursively update again with new week
                        }
                    }

                    // Bind change event
                    weekSelect.off('change').on('change', updateMeetingOptions);

                    // Initialize options
                    updateMeetingOptions();

                    // Disable submit if no valid combinations exist
                    if (weekSelect.find('option:not(:disabled)').length === 0) {
                        submitBtn.prop('disabled', true);
                        Swal.fire({
                            icon: 'info',
                            title: 'All Sessions Created',
                            text: 'All available week and meeting combinations have attendance sessions created.'
                        });
                    }
                }
            }
        });
    </script>
@endpush
