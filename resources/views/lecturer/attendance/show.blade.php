@extends('layouts.app')

@section('title', 'Attendance List')

@push('styles')
    <style>
        .attendance-summary {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
        }

        .attendance-card {
            transition: all 0.2s;
        }

        .attendance-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .hours-badge {
            min-width: 30px;
            display: inline-block;
            text-align: center;
        }

        .hours-input {
            width: 60px;
            text-align: center;
        }

        .cumulative-data {
            background-color: #f3f4f6;
            border-radius: 6px;
            padding: 10px;
        }

        .cumulative-label {
            font-weight: 500;
            color: #6c757d;
        }

        .edit-notes {
            max-height: 100px;
            overflow-y: auto;
        }
    </style>
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('lecturer.dashboard') }}">Lecturer</a></li>
            <li class="breadcrumb-item"><a href="{{ route('lecturer.attendance.index') }}">Attendance</a></li>
            <li class="breadcrumb-item active" aria-current="page">Attendance List</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="card-title">Attendance List - {{ $classSchedule->course->code }}</h6>
                        <div>
                            <button onclick="window.history.back()" class="btn btn-secondary btn-sm btn-icon-text me-2">
                                <i class="btn-icon-prepend" data-feather="arrow-left"></i>Back
                            </button>
                            <a href="{{ route('lecturer.attendance.view_qr', ['classSchedule' => $classSchedule->id, 'date' => $date]) }}"
                                class="btn btn-primary btn-sm btn-icon-text {{ $sessionExists ? '' : 'disabled' }}"
                                type="button" {{ !$sessionExists ? 'onclick="return false;"' : '' }}>
                                <i class="btn-icon-prepend" data-feather="info"></i>View QR
                            </a>
                        </div>
                    </div>

                    @if ($sessionExists)
                        <div class="alert alert-info">
                            <div class="d-flex align-items-center">
                                <i data-feather="info" class="me-2"></i>
                                <div>
                                    <strong>Session Details:</strong> Week {{ $session->week ?? '-' }}, Meeting
                                    {{ $session->meetings ?? '-' }} |
                                    Total Hours: {{ $totalHours ?? 4 }} |
                                    Tolerance: {{ $toleranceMinutes ?? 15 }} minutes
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="mb-3">
                        <form id="dateFilterForm" method="GET"
                            action="{{ route('lecturer.attendance.show', ['classSchedule' => $classSchedule->id]) }}">
                            <div class="d-flex align-items-center">
                                <label for="date" class="me-2">Session Date:</label>
                                <input type="date" class="form-control form-control-sm" id="date" name="date"
                                    value="{{ $date }}" style="width: 200px;">
                                <button type="submit" class="btn btn-sm btn-outline-primary ms-2">Filter</button>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table id="dataTableExample" class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th colspan="5"></th>
                                    <th colspan="4" class="text-center">Cumulative Hours</th>
                                    <th colspan="4" class="text-center">Current Session Hours</th>
                                    {{-- <th>Remarks</th> --}}
                                    <th colspan="2"></th>
                                </tr>
                                <tr>
                                    <th>No</th>
                                    <th>Student NIM</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Check-in</th>
                                    <th class="text-center bg-success-subtle">H</th>
                                    <th class="text-center bg-danger-subtle">A</th>
                                    <th class="text-center bg-warning-subtle">I</th>
                                    <th class="text-center bg-info-subtle">S</th>
                                    <th class="text-center bg-success-subtle">Hadir</th>
                                    <th class="text-center bg-danger-subtle">Alpha</th>
                                    <th class="text-center bg-warning-subtle">Izin</th>
                                    <th class="text-center bg-info-subtle">Sakit</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $key => $attendance)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $attendance->student->nim }}</td>
                                        <td>{{ $attendance->student->user->name }}</td>
                                        <td>
                                            <span
                                                class="badge
                                                @if ($attendance->status == 'present') bg-success
                                                @elseif($attendance->status == 'late') bg-warning
                                                @elseif($attendance->status == 'excused') bg-info
                                                @else bg-danger @endif">
                                                {{ ucfirst($attendance->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($attendance->attendance_time)
                                                {{ \Carbon\Carbon::parse($attendance->attendance_time)->format('H:i') }}
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <!-- Cumulative Hours -->
                                        @php
                                            $cumulativeHours = $cumulativeData[$attendance->student_id] ?? [
                                                'total_present' => 0,
                                                'total_absent' => 0,
                                                'total_permitted' => 0,
                                                'total_sick' => 0,
                                            ];
                                        @endphp
                                        <td class="text-center bg-success-subtle">
                                            <span
                                                class="badge bg-success hours-badge">{{ $cumulativeHours['total_present'] ?? 0 }}</span>
                                        </td>
                                        <td class="text-center bg-danger-subtle">
                                            <span
                                                class="badge bg-danger hours-badge">{{ $cumulativeHours['total_absent'] ?? 0 }}</span>
                                        </td>
                                        <td class="text-center bg-warning-subtle">
                                            <span
                                                class="badge bg-warning hours-badge">{{ $cumulativeHours['total_permitted'] ?? 0 }}</span>
                                        </td>
                                        <td class="text-center bg-info-subtle">
                                            <span
                                                class="badge bg-info hours-badge">{{ $cumulativeHours['total_sick'] ?? 0 }}</span>
                                        </td>

                                        <!-- Current Session Hours -->
                                        <td class="text-center bg-success-subtle">
                                            <span>{{ $attendance->hours_present ?? 0 }}</span>
                                        </td>
                                        <td class="text-center bg-danger-subtle">
                                            <span>{{ $attendance->hours_absent ?? 0 }}</span>
                                        </td>
                                        <td class="text-center bg-warning-subtle">
                                            <span>{{ $attendance->hours_permitted ?? 0 }}</span>
                                        </td>
                                        <td class="text-center bg-info-subtle">
                                            <span>{{ $attendance->hours_sick ?? 0 }}</span>
                                        </td>

                                        {{-- <td>
                                            @if ($attendance->remarks)
                                                <span data-bs-toggle="tooltip" title="{{ $attendance->remarks }}">
                                                    {{ Str::limit($attendance->remarks, 30, '...') }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td> --}}
                                        <td>
                                            <button type="button" class="btn btn-primary btn-icon btn-sm edit-attendance"
                                                data-bs-toggle="modal" title="Edit Attendance"
                                                data-bs-target="#editAttendanceModal"
                                                data-attendance-id="{{ $attendance->id }}"
                                                data-student-name="{{ $attendance->student->user->name }}"
                                                data-student-id="{{ $attendance->student->nim }}"
                                                data-course="{{ $classSchedule->course->code }} - {{ $classSchedule->course->name }}"
                                                data-date="{{ \Carbon\Carbon::parse($attendance->date)->format('l, d F Y') }}"
                                                data-status="{{ $attendance->status }}"
                                                data-remarks="{{ $attendance->remarks }}"
                                                data-hours-present="{{ $attendance->hours_present ?? 0 }}"
                                                data-hours-absent="{{ $attendance->hours_absent ?? 0 }}"
                                                data-hours-permitted="{{ $attendance->hours_permitted ?? 0 }}"
                                                data-hours-sick="{{ $attendance->hours_sick ?? 0 }}"
                                                data-edit-notes="{{ $attendance->edit_notes }}">
                                                <i class="btn-icon-prepend" data-feather="edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="15" class="text-center">No attendance records found for this date
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title">Attendance Summary</h6>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <h6 class="fw-bold mb-3">Current Session</h6>
                                                <table class="table">
                                                    <tr>
                                                        <th>Total Students</th>
                                                        <td>{{ $attendances->count() }}</td>
                                                    </tr>
                                                    <tr class="table-success">
                                                        <th>Present Hours</th>
                                                        <td>{{ $attendances->sum('hours_present') }}</td>
                                                    </tr>
                                                    <tr class="table-danger">
                                                        <th>Absent Hours</th>
                                                        <td>{{ $attendances->sum('hours_absent') }}</td>
                                                    </tr>
                                                    <tr class="table-warning">
                                                        <th>Permitted Hours</th>
                                                        <td>{{ $attendances->sum('hours_permitted') }}</td>
                                                    </tr>
                                                    <tr class="table-info">
                                                        <th>Sick Hours</th>
                                                        <td>{{ $attendances->sum('hours_sick') }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="fw-bold mb-3">Cumulative</h6>
                                                <table class="table">
                                                    <tr>
                                                        <th>Total Students</th>
                                                        <td>{{ $attendances->count() }}</td>
                                                    </tr>
                                                    <tr class="table-success">
                                                        <th>Present Hours</th>
                                                        <td>
                                                            @php
                                                                $totalCumulativePresent = 0;
                                                                foreach ($cumulativeData as $data) {
                                                                    $totalCumulativePresent +=
                                                                        $data['total_present'] ?? 0;
                                                                }
                                                            @endphp
                                                            {{ $totalCumulativePresent }}
                                                        </td>
                                                    </tr>
                                                    <tr class="table-danger">
                                                        <th>Absent Hours</th>
                                                        <td>
                                                            @php
                                                                $totalCumulativeAbsent = 0;
                                                                foreach ($cumulativeData as $data) {
                                                                    $totalCumulativeAbsent +=
                                                                        $data['total_absent'] ?? 0;
                                                                }
                                                            @endphp
                                                            {{ $totalCumulativeAbsent }}
                                                        </td>
                                                    </tr>
                                                    <tr class="table-warning">
                                                        <th>Permitted Hours</th>
                                                        <td>
                                                            @php
                                                                $totalCumulativePermitted = 0;
                                                                foreach ($cumulativeData as $data) {
                                                                    $totalCumulativePermitted +=
                                                                        $data['total_permitted'] ?? 0;
                                                                }
                                                            @endphp
                                                            {{ $totalCumulativePermitted }}
                                                        </td>
                                                    </tr>
                                                    <tr class="table-info">
                                                        <th>Sick Hours</th>
                                                        <td>
                                                            @php
                                                                $totalCumulativeSick = 0;
                                                                foreach ($cumulativeData as $data) {
                                                                    $totalCumulativeSick += $data['total_sick'] ?? 0;
                                                                }
                                                            @endphp
                                                            {{ $totalCumulativeSick }}
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Attendance Modal -->
    <div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-labelledby="editAttendanceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAttendanceModalLabel">Edit Student Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editAttendanceForm" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Course:</strong></p>
                                    <p id="modal-course"></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Date:</strong></p>
                                    <p id="modal-date"></p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Student ID:</strong></p>
                                    <p id="modal-student-id"></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Student Name:</strong></p>
                                    <p id="modal-student-name"></p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="modal-status" class="form-label">Attendance Status</label>
                            <select class="form-select" id="modal-status" name="status" required>
                                <option value="present">Present</option>
                                <option value="late">Late</option>
                                <option value="absent">Absent</option>
                                <option value="excused">Excused</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Hourly Attendance Breakdown</label>
                            <p class="text-muted small">Total hours must equal {{ $totalHours ?? 4 }}</p>

                            <input type="hidden" name="total_hours" id="total-hours" value="{{ $totalHours ?? 4 }}">

                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label small text-success">Present</label>
                                    <input type="number" class="form-control hours-input" id="modal-hours-present"
                                        name="hours_present" min="0" max="{{ $totalHours ?? 4 }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-danger">Absent</label>
                                    <input type="number" class="form-control hours-input" id="modal-hours-absent"
                                        name="hours_absent" min="0" max="{{ $totalHours ?? 4 }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-warning">Permit</label>
                                    <input type="number" class="form-control hours-input" id="modal-hours-permitted"
                                        name="hours_permitted" min="0" max="{{ $totalHours ?? 4 }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-info">Sick</label>
                                    <input type="number" class="form-control hours-input" id="modal-hours-sick"
                                        name="hours_sick" min="0" max="{{ $totalHours ?? 4 }}" required>
                                </div>
                            </div>

                            <div class="mt-2">
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" id="progress-present" role="progressbar"
                                        style="width: 25%"></div>
                                    <div class="progress-bar bg-danger" id="progress-absent" role="progressbar"
                                        style="width: 25%"></div>
                                    <div class="progress-bar bg-warning" id="progress-permitted" role="progressbar"
                                        style="width: 25%"></div>
                                    <div class="progress-bar bg-info" id="progress-sick" role="progressbar"
                                        style="width: 25%"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <span class="text-muted small" id="hours-total">Total: 0 / {{ $totalHours ?? 4 }}
                                        hours</span>
                                    <span class="text-danger small d-none" id="hours-error">Total hours must equal
                                        {{ $totalHours ?? 4 }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="modal-remarks" class="form-label">Remarks</label>
                            <textarea class="form-control" id="modal-remarks" name="remarks" rows="2"></textarea>
                            <div class="form-text">Optional notes about the student's attendance</div>
                        </div>

                        <div class="mb-3">
                            <label for="modal-edit-notes" class="form-label">Edit Notes</label>
                            <textarea class="form-control" id="modal-edit-notes" name="edit_notes" rows="2"></textarea>
                            <div class="form-text">Reason for editing attendance data</div>
                        </div>

                        @if (isset($attendance) && $attendance->edit_notes)
                            <div class="mb-3">
                                <label class="form-label">Edit History</label>
                                <div class="edit-notes p-2 bg-light rounded">
                                    <small>{{ $attendance->edit_notes }}</small>
                                    <div class="text-muted mt-1 small">
                                        Last edited:
                                        {{ $attendance->last_edited_at ? Carbon\Carbon::parse($attendance->last_edited_at)->format('d M Y H:i') : 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm btn-primary" id="updateAttendanceBtn">Update
                        Attendance</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })

            // Auto-submit date filter on change
            $('#date').change(function() {
                $('#dateFilterForm').submit();
            });

            // Handle hours input validation
            function updateHoursTotal() {
                const totalHours = parseInt($('#total-hours').val() || 4);
                const present = parseInt($('#modal-hours-present').val() || 0);
                const absent = parseInt($('#modal-hours-absent').val() || 0);
                const permitted = parseInt($('#modal-hours-permitted').val() || 0);
                const sick = parseInt($('#modal-hours-sick').val() || 0);

                const sum = present + absent + permitted + sick;

                // Update total display
                $('#hours-total').text(`Total: ${sum} / ${totalHours} hours`);

                // Show error if total doesn't match
                if (sum !== totalHours) {
                    $('#hours-error').removeClass('d-none');
                    $('#updateAttendanceBtn').prop('disabled', true);
                } else {
                    $('#hours-error').addClass('d-none');
                    $('#updateAttendanceBtn').prop('disabled', false);
                }

                // Update progress bars
                if (totalHours > 0) {
                    $('#progress-present').css('width', `${(present / totalHours) * 100}%`);
                    $('#progress-absent').css('width', `${(absent / totalHours) * 100}%`);
                    $('#progress-permitted').css('width', `${(permitted / totalHours) * 100}%`);
                    $('#progress-sick').css('width', `${(sick / totalHours) * 100}%`);
                }
            }

            // Attach event listeners to hour inputs
            $('.hours-input').on('input', updateHoursTotal);

            // Edit attendance button click
            $('.edit-attendance').on('click', function() {
                const attendanceId = $(this).data('attendance-id');
                const studentName = $(this).data('student-name');
                const studentId = $(this).data('student-id');
                const course = $(this).data('course');
                const date = $(this).data('date');
                const status = $(this).data('status');
                const remarks = $(this).data('remarks');
                const hoursPresent = $(this).data('hours-present');
                const hoursAbsent = $(this).data('hours-absent');
                const hoursPermitted = $(this).data('hours-permitted');
                const hoursSick = $(this).data('hours-sick');
                const editNotes = $(this).data('edit-notes');

                // Set form action URL
                $('#editAttendanceForm').attr('action', "{{ route('lecturer.attendance.update', '') }}/" +
                    attendanceId);

                // Populate modal fields
                $('#modal-student-name').text(studentName);
                $('#modal-student-id').text(studentId);
                $('#modal-course').text(course);
                $('#modal-date').text(date);
                $('#modal-status').val(status);
                $('#modal-remarks').val(remarks);
                $('#modal-hours-present').val(hoursPresent);
                $('#modal-hours-absent').val(hoursAbsent);
                $('#modal-hours-permitted').val(hoursPermitted);
                $('#modal-hours-sick').val(hoursSick);
                $('#modal-edit-notes').val('');

                // If there's previous edit notes, show them
                if (editNotes) {
                    $('#modal-edit-history').removeClass('d-none').find('.edit-notes-content').text(
                        editNotes);
                } else {
                    $('#modal-edit-history').addClass('d-none');
                }

                // Update hours total display
                updateHoursTotal();
            });

            // Update attendance button click
            $('#updateAttendanceBtn').on('click', function() {
                $('#editAttendanceForm').submit();
            });
        });
    </script>
@endpush
