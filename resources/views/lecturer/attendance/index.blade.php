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
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#createAttendanceModal{{ $schedule->id }}">
                                                <i data-feather="plus-circle"></i> Create Session
                                            </button>
                                            <a href="{{ route('lecturer.attendance.show', $schedule->id) }}"
                                                class="btn btn-sm btn-info">
                                                <i data-feather="eye"></i> View Sessions
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
                                                                    <label for="date" class="form-label">Session
                                                                        Date</label>
                                                                    <input type="date" class="form-control"
                                                                        id="date" name="date"
                                                                        value="{{ date('Y-m-d') }}" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-primary">Create
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

{{-- @section('scripts')
    <script>
        $(document).ready(function() {
            $('#dataTableExample').DataTable();

            // Initialize feather icons
            feather.replace();
        });
    </script>
@endsection --}}
