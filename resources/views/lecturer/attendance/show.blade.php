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
                            <form action="{{ route('lecturer.attendance.create') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="class_id" value="{{ $classSchedule->id }}">
                                <input type="hidden" name="date" value="{{ $date }}">
                                <button type="submit" class="btn btn-primary">
                                    <i data-feather="refresh-cw"></i> Manage QR Code
                                </button>
                            </form>
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
                            action="{{ route('lecturer.attendance.show', $classSchedule->id) }}">
                            <div class="d-flex align-items-center">
                                <label for="date" class="me-2">Session Date:</label>
                                <input type="date" class="form-control form-control-sm" id="date" name="date"
                                    value="{{ $date }}" style="width: 200px;">
                                <button type="submit" class="btn btn-sm btn-outline-primary ms-2">Filter</button>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table id="attendanceTable" class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Student ID</th>
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
                                        <td>{{ $attendance->student->student_id }}</td>
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
                                            @if ($attendance->check_in_time)
                                                {{ \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i:s') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $attendance->remarks ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('lecturer.attendance.edit', $attendance->id) }}"
                                                class="btn btn-sm btn-primary">
                                                <i data-feather="edit-2"></i> Edit
                                            </a>
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
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize datatable
            $('#attendanceTable').DataTable({
                "ordering": true,
                "info": true,
                "searching": true,
                "lengthChange": true,
                "pageLength": 25
            });

            // Initialize feather icons
            feather.replace();

            // Auto-submit date filter on change
            $('#date').change(function() {
                $('#dateFilterForm').submit();
            });
        });
    </script>
@endsection
