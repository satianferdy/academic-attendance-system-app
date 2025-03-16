@extends('layouts.app')

@section('title', 'My Attendance')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">My Attendance Records</h3>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
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
                                            <td>{{ $attendance->attendance_time ? \Carbon\Carbon::parse($attendance->attendance_time)->format('H:i') : 'N/A' }}
                                            </td>
                                            <td>{{ $attendance->remarks ?: 'N/A' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No attendance records found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- <div class="mt-3">
                            {{ $attendances->links() }}
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
