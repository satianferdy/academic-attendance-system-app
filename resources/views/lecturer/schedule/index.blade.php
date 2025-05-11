@extends('layouts.app')

@section('title', 'Class Schedule Management')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendors/datatables.net-bs5/dataTables.bootstrap5.css') }}">
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Dosen</a></li>
            <li class="breadcrumb-item active" aria-current="page">Class Schedule</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title mb-0">Class Schedule Management</h6>
                        <div>
                            <button type="button" class="btn btn-icon-text btn-xs btn-outline-primary me-1"
                                id="todayScheduleBtn">
                                <i data-feather="calendar" class="icon-xs"></i> Today
                            </button>
                            <button type="button" class="btn btn-icon-text btn-xs btn-outline-secondary"
                                id="allScheduleBtn">
                                <i data-feather="list" class="icon-xs"></i> All
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="scheduleTable" class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Course</th>
                                    <th>Class</th>
                                    <th>Room</th>
                                    <th>Day</th>
                                    <th>Time</th>
                                    <th>Semester/Year</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($schedules as $key => $schedule)
                                    <tr class="schedule-row" data-day="{{ $schedule->day }}">
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            <strong>{{ $schedule->course->code }}</strong><br>
                                            <small>{{ $schedule->course->name }}</small>
                                        </td>
                                        <td>{{ $schedule->classroom->name }}</td>
                                        <td>{{ $schedule->room }}</td>
                                        <td>{{ $schedule->day }}</td>
                                        <td>
                                            @if ($schedule->timeSlots->count() > 0)
                                                @foreach ($schedule->timeSlots as $timeSlot)
                                                    <div>{{ $timeSlot->start_time->format('H:i') }} -
                                                        {{ $timeSlot->end_time->format('H:i') }}</div>
                                                @endforeach
                                            @else
                                                <span class="text-muted">No time slots</span>
                                            @endif
                                        </td>
                                        <td>{{ $schedule->semester }} / {{ $schedule->semesters->name }}</td>
                                    </tr>
                                @empty
                                    <tr class="no-data-row">
                                        <td colspan="7" class="text-center">No schedules found</td>
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
    <script src="{{ asset('assets/vendors/datatables.net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('assets/vendors/datatables.net-bs5/dataTables.bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/js/data-table.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var dataTable = $('#scheduleTable').DataTable();

            // Get current day name in English (Monday, Tuesday, etc.)
            function getCurrentDayName() {
                return '{{ now()->format('l') }}'; // Get from server side for accuracy
            }

            // Handle Today's Schedule button click
            $('#todayScheduleBtn').on('click', function() {
                const today = getCurrentDayName();

                // Reset all filters first
                dataTable.search('').draw();

                // If there are no schedules for today, show message
                const todaySchedules = $('.schedule-row[data-day="' + today + '"]');

                if (todaySchedules.length === 0) {
                    // Hide all regular rows
                    $('.schedule-row').hide();

                    // Remove existing "no today schedules" message if exists
                    $('.no-today-message').remove();

                    // Add a message row if not already present
                    if ($('.no-today-message').length === 0) {
                        $('tbody').append(
                            '<tr class="no-today-message"><td colspan="7" class="text-center">No schedules for today (' +
                            today + ')</td></tr>');
                    }
                } else {
                    // Remove any "no schedules" message
                    $('.no-today-message').remove();
                    $('.no-data-row').hide();

                    // Filter rows by current day
                    $('.schedule-row').each(function() {
                        const rowDay = $(this).data('day');
                        if (rowDay === today) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                }

                // Highlight the active button
                $(this).removeClass('btn-outline-primary').addClass('btn-primary');
                $('#allScheduleBtn').removeClass('btn-secondary').addClass('btn-outline-secondary');

                // Update icons
                feather.replace();
            });

            // Handle All Schedules button click
            $('#allScheduleBtn').on('click', function() {
                // Remove any custom message rows
                $('.no-today-message').remove();

                // Show all regular rows and original no-data message if applicable
                $('.schedule-row').show();
                $('.no-data-row').show();

                // Highlight the active button
                $(this).removeClass('btn-outline-secondary').addClass('btn-secondary');
                $('#todayScheduleBtn').removeClass('btn-primary').addClass('btn-outline-primary');

                // Update icons
                feather.replace();
            });

            // Initialize feather icons for buttons
            feather.replace();
        });
    </script>
@endpush
