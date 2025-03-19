@extends('layouts.app')

@section('title', 'Attendance List')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendors/select2/select2.min.css') }}">
    {{-- Di dalam section styles --}}
    <style>
        /* Perbaikan untuk Select2 */
        .select2-container--default .select2-selection--single {
            height: 40px !important;
            padding: 6px 16px !important;
            font-size: 0.875rem !important;
            line-height: 1.5 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 26px !important;
            padding-left: 0 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
        }

        /* Perbaikan dropdown */
        .select2-results__option {
            padding: 8px 16px !important;
            font-size: 0.875rem !important;
        }

        /* Pastikan dropdown memiliki lebar yang sesuai */
        .select2-container {
            min-width: 200px !important;
        }

        .form-label {
            margin-bottom: 0.5rem;
        }

        /* Styling untuk status select */
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
            <li class="breadcrumb-item active" aria-current="page">Attendance List</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Attendance Records</h6>
                    <!-- Combined Filters -->
                    <form id="filter-form" method="GET" action="{{ route('admin.attendance.index') }}">
                        <div class="row mb-4">
                            <div class="col-md-4 mb-2">
                                <label class="form-label" for="course_filter">Course</label>
                                <select class="js-example-basic-single" id="course_filter" data-width="100%"
                                    name="course_id">
                                    <option value="">All Courses</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}"
                                            {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->name }} ({{ $course->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label" for="date_filter">Date</label>
                                <input type="date" class="form-control" id="date_filter" name="date"
                                    value="{{ request('date') }}">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label" for="student_filter">Student</label>
                                <select class="js-example-basic-single" id="student_filter" data-width="100%"
                                    name="student_id">
                                    <option value="">All Students</option>
                                    @foreach ($students as $student)
                                        <option value="{{ $student->id }}"
                                            {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                            {{ $student->nim }} - {{ $student->user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label" for="status_filter">Status</label>
                                <select class="form-select" id="status_filter" name="status">
                                    <option value="">All Status</option>
                                    <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>
                                        Present</option>
                                    <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent
                                    </option>
                                    <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late
                                    </option>
                                    <option value="excused" {{ request('status') == 'excused' ? 'selected' : '' }}>
                                        Excused</option>
                                </select>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table id="dataTableExample" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Student</th>
                                    <th>Course</th>
                                    <th>Lecturer</th>
                                    <th>Status</th>
                                    <th>Check In Time</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $attendance)
                                    <tr data-id="{{ $attendance->id }}">
                                        <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d M Y') }}</td>
                                        <td>
                                            <strong>{{ $attendance->student->nim }}</strong><br>
                                            <small>{{ $attendance->student->user->name }}</small>
                                        </td>
                                        <td>{{ $attendance->classSchedule->course->name }}</td>
                                        <td>{{ $attendance->classSchedule->lecturer->user->name }}</td>
                                        <td>
                                            <select
                                                class="form-control form-control-sm status-select status-{{ $attendance->status }}"
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
                                        <td>{{ $attendance->attendance_time ? \Carbon\Carbon::parse($attendance->attendance_time)->format('H:i') : '-' }}
                                        </td>
                                        <td>{{ $attendance->remarks ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No attendance records found</td>
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
    <script src="{{ asset('assets/vendors/select2/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2.js') }}"></script>
    <script>
        $(function() {
            "use strict";

            // Handle auto-submit on filter change
            $('select[name="course_id"], input[name="date"], select[name="student_id"], select[name="status"]').on(
                'change',
                function() {
                    $('#filter-form').submit();
                });

            // Apply status color styling on page load
            $('.status-select').each(function() {
                var status = $(this).val();
                $(this).removeClass('status-present status-absent status-late status-excused');
                $(this).addClass('status-' + status);
            });

            // Handle status change
            $('.status-select').on('change', function() {
                var attendanceId = $(this).data('id');
                var status = $(this).val();
                var selectElement = $(this);

                // Update select color based on selected status
                selectElement.removeClass('status-present status-absent status-late status-excused');
                selectElement.addClass('status-' + status);

                // Show loading indicator
                Swal.fire({
                    title: 'Updating...',
                    didOpen: () => {
                        Swal.showLoading();
                    },
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false
                });

                $.ajax({
                    url: "{{ route('admin.attendance.update-status') }}",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    data: {
                        attendance_id: attendanceId,
                        status: status
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Error updating status:', xhr);

                        // Show error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Update Failed',
                            text: 'Could not update attendance status. Please try again.',
                        });

                        // Revert selection to previous value
                        var originalStatus = selectElement.data('original-status');
                        selectElement.val(originalStatus);

                        // Revert color class
                        selectElement.removeClass(
                            'status-present status-absent status-late status-excused');
                        selectElement.addClass('status-' + originalStatus);
                    }
                });
            });

            // Store original status when focusing on select
            $('.status-select').on('focus', function() {
                $(this).data('original-status', $(this).val());
            });
        });
    </script>
@endpush
