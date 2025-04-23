@extends('layouts.app')

@section('title', 'Recent Session Attendance')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Dosen</a></li>
            <li class="breadcrumb-item active" aria-current="page">Sesi Presensi</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">

            <!-- Filters Card -->
            <div class="card">
                <div class="card-body">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body py-3">
                            <form action="{{ route('lecturer.recent.sessions') }}" method="GET" id="filterForm">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="form-group mb-0">
                                            <label for="course_id" class="form-label text-muted small">Course</label>
                                            <select class="form-select select2" id="course_id" name="course_id">
                                                <option value="">All Courses</option>
                                                @foreach ($courses as $course)
                                                    <option value="{{ $course->id }}"
                                                        {{ $selectedCourse == $course->id ? 'selected' : '' }}>
                                                        {{ $course->code }} - {{ $course->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-0">
                                            <label for="week" class="form-label text-muted small">Week</label>
                                            <select class="form-select" id="week" name="week">
                                                <option value="">All Weeks</option>
                                                @for ($i = 1; $i <= $maxWeeks; $i++)
                                                    <option value="{{ $i }}"
                                                        {{ $selectedWeek == $i ? 'selected' : '' }}>
                                                        Week {{ $i }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-0">
                                            <label for="date" class="form-label text-muted small">Date</label>
                                            <input type="date" class="form-control" id="date" name="date"
                                                value="{{ $selectedDate }}">
                                        </div>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i data-feather="filter" class="icon-sm me-1"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="dataTableExample" class="table table-hover">
                            <thead>
                                <tr class="bg-light">
                                    <th style="width: 50px;">No</th>
                                    <th>Course</th>
                                    <th style="width: 120px;">Date</th>
                                    <th style="width: 100px;">Week/Meeting</th>
                                    <th style="width: 100px;">Present</th>
                                    <th style="width: 100px;">Absent</th>
                                    <th style="width: 160px;">Attendance Rate</th>
                                    <th style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sessionSummaries as $key => $summary)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold">{{ $summary['class_schedule']->course->code }}</span>
                                                <span
                                                    class="text-muted small">{{ $summary['class_schedule']->course->name }}</span>
                                                <span
                                                    class="badge bg-light text-primary mt-1">{{ $summary['class_schedule']->classroom->name }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $summary['date']->format('d M Y') }}</td>
                                        <td>
                                            @if ($summary['week'] && $summary['meeting'])
                                                <span class="badge bg-info">Week {{ $summary['week'] }}</span>
                                                <span class="badge bg-secondary">Meeting
                                                    {{ $summary['meeting'] }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-success fw-medium">{{ $summary['present'] }}</td>
                                        <td class="text-danger fw-medium">{{ $summary['absent'] }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="me-2">{{ $summary['rate'] }}%</span>
                                                <div class="progress flex-grow-1" style="height: 5px;">
                                                    <div class="progress-bar {{ $summary['rate'] >= 75 ? 'bg-success' : ($summary['rate'] >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                        role="progressbar" style="width: {{ $summary['rate'] }}%;"
                                                        aria-valuenow="{{ $summary['rate'] }}" aria-valuemin="0"
                                                        aria-valuemax="100">
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('lecturer.attendance.show', [
                                                'classSchedule' => $summary['class_schedule']->id,
                                                'date' => $summary['date']->format('Y-m-d'),
                                                'week' => $summary['week'],
                                                'meeting' => $summary['meeting'],
                                            ]) }}"
                                                class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip"
                                                title="View Details">
                                                <i data-feather="eye" class="icon-sm"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i data-feather="calendar-x" class="icon-lg text-muted mb-2"></i>
                                                <span class="text-muted">No attendance sessions found</span>
                                                <small class="text-muted">Try adjusting your filter criteria</small>
                                            </div>
                                        </td>
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

@push('styles')
    <style>
        .select2-container .select2-selection--single {
            height: 38px;
            border-color: #e9ecef;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize Select2 for searchable dropdown
            $('.select2').select2({
                placeholder: 'Select a course',
                allowClear: true,
                width: '100%'
            });

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

            // Initialize feather icons
            feather.replace();

            // Auto-submit form when filter changes (disabled for better UX with the submit button)

            // $('#date, #week, #course_id').on('change', function() {
            //     $('#filterForm').submit();
            // });

        });
    </script>
@endpush
