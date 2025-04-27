@extends('layouts.app')

@section('title', 'Edit Attendance Session')

@push('styles')
    <style>
        .card {
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
        }

        .session-info {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .session-info-item {
            display: flex;
            margin-bottom: 8px;
        }

        .session-info-label {
            width: 120px;
            font-weight: 500;
            color: #6c757d;
        }

        .hours-input {
            width: 60px;
            text-align: center;
        }

        .status-select {
            min-width: 100px;
        }

        .status-present {
            background-color: #d1e7dd !important;
            color: #0a3622 !important;
        }

        .status-absent {
            background-color: #f8d7da !important;
            color: #58151c !important;
        }

        .status-late {
            background-color: #fff3cd !important;
            color: #664d03 !important;
        }

        .status-excused {
            background-color: #cff4fc !important;
            color: #055160 !important;
        }
    </style>
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.attendance.index') }}">Attendance Management</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Session</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="card-title">Edit Attendance Session</h6>
                        <a href="{{ route('admin.attendance.index', [
                            'study_program_id' => $session->classSchedule->study_program_id,
                            'class_schedule_id' => $session->class_schedule_id,
                        ]) }}"
                            class="btn btn-sm btn-icon-text btn-outline-secondary">
                            <i data-feather="arrow-left" class="btn-icon-prepend"></i>Back
                        </a>
                    </div>

                    <!-- Session Information -->
                    <div class="session-info">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="session-info-item">
                                    <div class="session-info-label">Course:</div>
                                    <div>{{ $session->classSchedule->course->name }}</div>
                                </div>
                                <div class="session-info-item">
                                    <div class="session-info-label">Class:</div>
                                    <div>{{ $session->classSchedule->classroom->name }}</div>
                                </div>
                                <div class="session-info-item">
                                    <div class="session-info-label">Date:</div>
                                    <div>{{ $session->session_date->format('l, d F Y') }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="session-info-item">
                                    <div class="session-info-label">Week/Meeting:</div>
                                    <div>Week {{ $session->week }}, Meeting {{ $session->meetings }}</div>
                                </div>
                                <div class="session-info-item">
                                    <div class="session-info-label">Total Hours:</div>
                                    <div>{{ $session->total_hours }}</div>
                                </div>
                                <div class="session-info-item">
                                    <div class="session-info-label">Time:</div>
                                    <div>{{ $session->start_time->format('H:i') }} - {{ $session->end_time->format('H:i') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i data-feather="info" class="icon-sm me-2"></i>
                                <strong>Note:</strong> Total hours for each student must equal {{ $session->total_hours }}.
                                When
                                you increase one value, the system will automatically decrease from another value.
                            </div>

                            <!-- Status Buttons -->
                            <div class="mb-3">
                                <button id="allPresentBtn" class="btn btn-outline-success btn-sm me-2">All Present</button>
                                <button id="allAbsentBtn" class="btn btn-outline-danger btn-sm me-2">All Absent</button>
                                <button id="allLateBtn" class="btn btn-outline-warning btn-sm me-2">All Late (1h
                                    Absent)</button>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance List -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="attendanceTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student ID</th>
                                    <th>Student Name</th>
                                    <th>Status</th>
                                    <th style="width: 100px">Present (H)</th>
                                    <th style="width: 100px">Absent (A)</th>
                                    <th style="width: 100px">Permit (I)</th>
                                    <th style="width: 100px">Sick (S)</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($attendances as $attendance)
                                    <tr data-id="{{ $attendance->id }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $attendance->student->nim }}</td>
                                        <td>{{ $attendance->student->user->name }}</td>
                                        <td>
                                            <select
                                                class="form-select form-select-sm status-select status-{{ $attendance->status }}"
                                                data-id="{{ $attendance->id }}">
                                                <option value="present" class="status-present"
                                                    {{ $attendance->status == 'present' ? 'selected' : '' }}>Present
                                                </option>
                                                <option value="absent" class="status-absent"
                                                    {{ $attendance->status == 'absent' ? 'selected' : '' }}>Absent</option>
                                                <option value="late" class="status-late"
                                                    {{ $attendance->status == 'late' ? 'selected' : '' }}>Late</option>
                                                <option value="excused" class="status-excused"
                                                    {{ $attendance->status == 'excused' ? 'selected' : '' }}>Excused
                                                </option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number"
                                                class="form-control form-control-sm hours-input hours-present"
                                                value="{{ $attendance->hours_present }}" min="0"
                                                max="{{ $session->total_hours }}" data-id="{{ $attendance->id }}">
                                        </td>
                                        <td>
                                            <input type="number"
                                                class="form-control form-control-sm hours-input hours-absent"
                                                value="{{ $attendance->hours_absent }}" min="0"
                                                max="{{ $session->total_hours }}" data-id="{{ $attendance->id }}">
                                        </td>
                                        <td>
                                            <input type="number"
                                                class="form-control form-control-sm hours-input hours-permitted"
                                                value="{{ $attendance->hours_permitted }}" min="0"
                                                max="{{ $session->total_hours }}" data-id="{{ $attendance->id }}">
                                        </td>
                                        <td>
                                            <input type="number"
                                                class="form-control form-control-sm hours-input hours-sick"
                                                value="{{ $attendance->hours_sick }}" min="0"
                                                max="{{ $session->total_hours }}" data-id="{{ $attendance->id }}">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm remarks"
                                                value="{{ $attendance->remarks }}" placeholder="Optional notes"
                                                data-id="{{ $attendance->id }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 text-end">
                        <button id="saveAllBtn" class="btn btn-icon-text btn-sm btn-primary">
                            <i data-feather="save" class="btn-icon-prepend"></i>Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            "use strict";

            // Initialize feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }

            // Constants
            const TOTAL_HOURS = {{ $session->total_hours }};

            // Apply status color styling
            function updateStatusStyles() {
                $('.status-select').each(function() {
                    var status = $(this).val();
                    $(this).removeClass('status-present status-absent status-late status-excused');
                    $(this).addClass('status-' + status);
                });
            }

            // Initial styling
            updateStatusStyles();

            // Status change updates styling and suggests hour allocations
            $('.status-select').on('change', function() {
                const row = $(this).closest('tr');
                const status = $(this).val();

                // Update styling
                $(this).removeClass('status-present status-absent status-late status-excused');
                $(this).addClass('status-' + status);

                // Auto-suggest hours based on status
                if (confirm("Do you want to set hours based on this status?")) {
                    switch (status) {
                        case 'present':
                            row.find('.hours-present').val(TOTAL_HOURS);
                            row.find('.hours-absent').val(0);
                            row.find('.hours-permitted').val(0);
                            row.find('.hours-sick').val(0);
                            break;
                        case 'absent':
                            row.find('.hours-present').val(0);
                            row.find('.hours-absent').val(TOTAL_HOURS);
                            row.find('.hours-permitted').val(0);
                            row.find('.hours-sick').val(0);
                            break;
                        case 'late':
                            row.find('.hours-present').val(TOTAL_HOURS - 1);
                            row.find('.hours-absent').val(1);
                            row.find('.hours-permitted').val(0);
                            row.find('.hours-sick').val(0);
                            break;
                        case 'excused':
                            row.find('.hours-present').val(0);
                            row.find('.hours-absent').val(0);
                            row.find('.hours-permitted').val(TOTAL_HOURS);
                            row.find('.hours-sick').val(0);
                            break;
                    }

                    // Highlight the row
                    row.addClass('table-success');
                    setTimeout(() => {
                        row.removeClass('table-success');
                    }, 500);
                }
            });

            // Remove the automatic hour adjustment logic and replace with simple validation
            $('.hours-input').on('input', function() {
                const row = $(this).closest('tr');

                // Get current values of all hour inputs
                const present = parseInt(row.find('.hours-present').val()) || 0;
                const absent = parseInt(row.find('.hours-absent').val()) || 0;
                const permitted = parseInt(row.find('.hours-permitted').val()) || 0;
                const sick = parseInt(row.find('.hours-sick').val()) || 0;

                // Calculate current sum
                const currentSum = present + absent + permitted + sick;

                // Add visual indicator if sum exceeds TOTAL_HOURS
                if (currentSum > TOTAL_HOURS) {
                    row.addClass('table-danger');

                    // Optional: show a small warning next to the inputs
                    if (!row.find('.hours-warning').length) {
                        row.find('.hours-input').last().after(
                            `<small class="text-danger hours-warning">Total: ${currentSum}/${TOTAL_HOURS}</small>`
                        );
                    } else {
                        row.find('.hours-warning').text(`Total: ${currentSum}/${TOTAL_HOURS}`);
                    }
                } else {
                    row.removeClass('table-danger');
                    row.find('.hours-warning').remove();
                }

                // Still update status based on hour distribution
                updateStatusBasedOnHours(row);
            });

            // Also fix the function to update status based on hours
            function updateStatusBasedOnHours(row) {
                const present = parseInt(row.find('.hours-present').val()) || 0;
                const absent = parseInt(row.find('.hours-absent').val()) || 0;
                const permitted = parseInt(row.find('.hours-permitted').val()) || 0;
                const sick = parseInt(row.find('.hours-sick').val()) || 0;

                // Determine status based on hour distribution
                if (present === TOTAL_HOURS) {
                    row.find('.status-select').val('present');
                } else if (absent === TOTAL_HOURS) {
                    row.find('.status-select').val('absent');
                } else if (permitted === TOTAL_HOURS) {
                    row.find('.status-select').val('excused');
                } else if (present > 0 && absent > 0) {
                    row.find('.status-select').val('late');
                } else if (sick === TOTAL_HOURS) {
                    // Optional: Add handling for when all hours are marked as sick
                    row.find('.status-select').val('excused');
                }

                updateStatusStyles();
            }

            // Validate hours total
            function validateHours(row) {
                const present = parseInt(row.find('.hours-present').val()) || 0;
                const absent = parseInt(row.find('.hours-absent').val()) || 0;
                const permitted = parseInt(row.find('.hours-permitted').val()) || 0;
                const sick = parseInt(row.find('.hours-sick').val()) || 0;

                const sum = present + absent + permitted + sick;

                if (sum !== TOTAL_HOURS) {
                    return {
                        valid: false,
                        message: `Total hours must equal ${TOTAL_HOURS}`
                    };
                }

                return {
                    valid: true
                };
            }

            // Bulk action buttons
            $('#allPresentBtn').click(function() {
                if (confirm('Set all students as Present?')) {
                    $('#attendanceTable tbody tr').each(function() {
                        $(this).find('.hours-present').val(TOTAL_HOURS);
                        $(this).find('.hours-absent').val(0);
                        $(this).find('.hours-permitted').val(0);
                        $(this).find('.hours-sick').val(0);
                        $(this).find('.status-select').val('present');
                    });
                    updateStatusStyles();
                }
            });

            $('#allAbsentBtn').click(function() {
                if (confirm('Set all students as Absent?')) {
                    $('#attendanceTable tbody tr').each(function() {
                        $(this).find('.hours-present').val(0);
                        $(this).find('.hours-absent').val(TOTAL_HOURS);
                        $(this).find('.hours-permitted').val(0);
                        $(this).find('.hours-sick').val(0);
                        $(this).find('.status-select').val('absent');
                    });
                    updateStatusStyles();
                }
            });

            $('#allLateBtn').click(function() {
                if (confirm('Set all students as Late (1 hour absent)?')) {
                    $('#attendanceTable tbody tr').each(function() {
                        $(this).find('.hours-present').val(TOTAL_HOURS - 1);
                        $(this).find('.hours-absent').val(1);
                        $(this).find('.hours-permitted').val(0);
                        $(this).find('.hours-sick').val(0);
                        $(this).find('.status-select').val('late');
                    });
                    updateStatusStyles();
                }
            });

            // Save All button
            $('#saveAllBtn').click(function() {
                // Validate all rows first
                let invalidRows = [];

                $('#attendanceTable tbody tr').each(function() {
                    const row = $(this);
                    const present = parseInt(row.find('.hours-present').val()) || 0;
                    const absent = parseInt(row.find('.hours-absent').val()) || 0;
                    const permitted = parseInt(row.find('.hours-permitted').val()) || 0;
                    const sick = parseInt(row.find('.hours-sick').val()) || 0;

                    const sum = present + absent + permitted + sick;

                    if (sum !== TOTAL_HOURS) {
                        invalidRows.push({
                            row: row,
                            studentName: row.find('td:nth-child(2)').text(),
                            sum: sum
                        });
                    }
                });

                if (invalidRows.length > 0) {
                    let errorMessage = 'Some students have invalid hour totals:<br>';
                    invalidRows.forEach(item => {
                        errorMessage +=
                            `- ${item.studentName}: ${item.sum} hours (should be ${TOTAL_HOURS})<br>`;
                        item.row.addClass('table-danger');
                    });

                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Hours',
                        html: errorMessage,
                        confirmButtonText: 'Fix Issues'
                    });

                    setTimeout(() => {
                        invalidRows.forEach(item => {
                            item.row.removeClass('table-danger');
                        });
                    }, 5000);

                    return;
                }

                // All rows are valid, prepare data for saving
                Swal.fire({
                    title: 'Saving All Records',
                    html: 'Please wait...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const allData = [];

                $('#attendanceTable tbody tr').each(function() {
                    const row = $(this);
                    allData.push({
                        attendance_id: row.data('id'),
                        status: row.find('.status-select').val(),
                        hours_present: row.find('.hours-present').val(),
                        hours_absent: row.find('.hours-absent').val(),
                        hours_permitted: row.find('.hours-permitted').val(),
                        hours_sick: row.find('.hours-sick').val(),
                        remarks: row.find('.remarks').val()
                    });
                });

                // Send all data to server
                $.ajax({
                    url: "{{ route('admin.attendance.update-status') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        attendances: allData
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'All attendance records have been updated.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        console.error('Error updating attendance:', xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Update Failed',
                            text: 'Could not update attendance records. Please try again.'
                        });
                    }
                });
            });
        });
    </script>
@endpush
