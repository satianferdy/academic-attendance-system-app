@extends('layouts.app')

@section('title', 'My Attendance')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Student</a></li>
            <li class="breadcrumb-item active" aria-current="page">Attendance</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">My Attendance Records</h6>
                    <div class="table-responsive">
                        <table id="dataTableExample" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Course</th>
                                    <th>Lecturer</th>
                                    <th>Status</th>
                                    <th>Check In Time</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $attendance)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d M Y') }}</td>
                                        <td>{{ $attendance->classSchedule->course->name }}</td>
                                        <td>{{ $attendance->classSchedule->lecturer->user->name }}</td>
                                        <td>
                                            @if ($attendance->status == 'present')
                                                <span class="badge bg-success">Present</span>
                                            @elseif($attendance->status == 'absent')
                                                <span class="badge bg-danger">Absent</span>
                                            @elseif($attendance->status == 'late')
                                                <span class="badge bg-warning">Late</span>
                                            @elseif($attendance->status == 'excused')
                                                <span class="badge bg-info">Excused</span>
                                            @else
                                                <span class="badge bg-secondary">Not Marked</span>
                                            @endif
                                        </td>
                                        <td>{{ $attendance->attendance_time ? \Carbon\Carbon::parse($attendance->attendance_time)->format('H:i') : '-' }}
                                        </td>
                                        <td>{{ $attendance->remarks ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No attendance records found</td>
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
