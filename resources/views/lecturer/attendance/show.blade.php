@extends('layouts.app')

@section('title', 'Attendance List')

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

                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="mb-3">
                        <form id="dateFilterForm" method="GET"
                            action="{{ route('lecturer.attendance.show', ['classSchedule' => $classSchedule->id]) }}) }}">
                            <div class="d-flex align-items-center">
                                <label for="date" class="me-2">Session Date:</label>
                                <input type="date" class="form-control form-control-sm" id="date" name="date"
                                    value="{{ $date }}" style="width: 200px;">
                                <button type="submit" class="btn btn-sm btn-outline-primary ms-2">Filter</button>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table id="dataTableExample" class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Student NIM</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Check-in Time</th>
                                    <th>Remarks</th>
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
                                                {{ \Carbon\Carbon::parse($attendance->attendance_time)->format('H:i:s') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if ($attendance->remarks)
                                                <span data-bs-toggle="tooltip" title="{{ $attendance->remarks }}">
                                                    {{ Str::limit($attendance->remarks, 30, '...') }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
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
                                                data-remarks="{{ $attendance->remarks }}">
                                                <i class="btn-icon-prepend" data-feather="edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No attendance records found for this date
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
                                        <table class="table">
                                            <tr>
                                                <th>Total Students</th>
                                                <td>{{ $attendances->count() }}</td>
                                            </tr>
                                            <tr>
                                                <th>Present</th>
                                                <td>{{ $attendances->where('status', 'present')->count() }}</td>
                                            </tr>
                                            <tr>
                                                <th>Late</th>
                                                <td>{{ $attendances->where('status', 'late')->count() }}</td>
                                            </tr>
                                            <tr>
                                                <th>Excused</th>
                                                <td>{{ $attendances->where('status', 'excused')->count() }}</td>
                                            </tr>
                                            <tr>
                                                <th>Absent</th>
                                                <td>{{ $attendances->where('status', 'absent')->count() }}</td>
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
                            <label for="modal-remarks" class="form-label">Remarks</label>
                            <textarea class="form-control" id="modal-remarks" name="remarks" rows="3"></textarea>
                            <div class="form-text">Optional notes or comments about the student's attendance</div>
                        </div>
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

            // Edit attendance button click
            $('.edit-attendance').on('click', function() {
                const attendanceId = $(this).data('attendance-id');
                const studentName = $(this).data('student-name');
                const studentId = $(this).data('student-id');
                const course = $(this).data('course');
                const date = $(this).data('date');
                const status = $(this).data('status');
                const remarks = $(this).data('remarks');

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
            });

            // Update attendance button click
            $('#updateAttendanceBtn').on('click', function() {
                $('#editAttendanceForm').submit();
            });
        });
    </script>
@endpush
