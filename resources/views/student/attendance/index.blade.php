@extends('layouts.app')

@section('title', 'Presensi')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendors/datatables.net-bs5/dataTables.bootstrap5.css') }}">
    <style>
        .hour-badge {
            display: inline-block;
            width: 24px;
            height: 24px;
            line-height: 24px;
            text-align: center;
            border-radius: 4px;
            color: white;
            font-weight: 500;
            font-size: 12px;
        }

        .attendance-date {
            font-weight: 500;
        }

        .week-meeting-badge {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            color: #6c757d;
            border-radius: 4px;
            padding: 2px 8px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
    </style>
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">General</a></li>
            <li class="breadcrumb-item active" aria-current="page">Presensi</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Data Presensi</h6>
                    <div class="table-responsive">
                        <table id="dataTableExample" class="table table-bordered w-100">
                            <thead>
                                <tr>
                                    <th colspan="6"></th>
                                    <th colspan="4" class="text-center">Hours Breakdown</th>
                                    <th colspan="1"></th>
                                </tr>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Minggu/Pertemuan</th>
                                    <th>Mata Kuliah</th>
                                    <th>Dosen</th>
                                    <th>Status</th>
                                    <th>Check In</th>
                                    <th class="text-center bg-success-subtle">H</th>
                                    <th class="text-center bg-danger-subtle">A</th>
                                    <th class="text-center bg-warning-subtle">I</th>
                                    <th class="text-center bg-info-subtle">S</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $attendance)
                                    <tr>
                                        <td class="attendance-date">
                                            {{ \Carbon\Carbon::parse($attendance->date)->format('d M Y') }}</td>
                                        <td>
                                            @if (isset($attendance->week) && isset($attendance->meeting))
                                                <span class="week-meeting-badge">Week {{ $attendance->week }}</span>
                                                <span class="week-meeting-badge">Meeting {{ $attendance->meeting }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
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
                                        <td>{{ $attendance->attendance_time ? \Carbon\Carbon::parse($attendance->attendance_time)->format('H.i') . ' WIB' : '-' }}
                                        </td>
                                        <td>
                                            <div class="hour-badge bg-success" title="Present">
                                                {{ $attendance->hours_present }}</div>
                                        </td>
                                        <td>
                                            <div class="hour-badge bg-danger" title="Absent">
                                                {{ $attendance->hours_absent }}</div>
                                        </td>
                                        <td>
                                            <div class="hour-badge bg-warning" title="Permitted">
                                                {{ $attendance->hours_permitted }}</div>
                                        </td>
                                        <td>
                                            <div class="hour-badge bg-info" title="Sick">
                                                {{ $attendance->hours_sick }}</div>
                                        </td>
                                        <td>{{ $attendance->remarks ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center">No attendance records found</td>
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
            // Initialize tooltips
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'))
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });
            }
        });
    </script>
@endpush
