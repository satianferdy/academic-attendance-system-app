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
                                        <td>{{ $schedule->semester }} / {{ $schedule->academic_year }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-icon-text btn-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#createAttendanceModal{{ $schedule->id }}">
                                                <i data-feather="plus-circle" class="btn-icon-prepend"></i>Create Session
                                            </button>
                                            <a href="{{ route('lecturer.attendance.show', $schedule->id) }}"
                                                class="btn btn-sm btn-icon-text btn-info">
                                                <i data-feather="info" class="btn-icon-prepend"></i>View Sessions
                                            </a>

                                            <!-- Create Attendance Modal -->
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
                                                                        class="form-label">Session
                                                                        Date</label>
                                                                    <input type="date" class="form-control"
                                                                        id="date{{ $schedule->id }}" name="date"
                                                                        value="{{ date('Y-m-d') }}"
                                                                        min="{{ date('Y-m-d') }}" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-sm btn-secondary"
                                                                    data-bs-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-sm btn-primary">Create
                                                                    Session</button>
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
        // Additional JavaScript to ensure past dates are disabled
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

                    // Show a message using SweetAlert
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Date',
                        text: 'You cannot select a date in the past.',
                        confirmButtonColor: '#3085d6'
                    });
                }
            });
        });
    </script>
@endpush
