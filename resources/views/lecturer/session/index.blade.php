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
            <!-- Main Card -->
            <div class="card">
                <div class="card-body">
                    <!-- Primary Filter Section -->
                    <div class="mb-4">
                        <form action="{{ route('lecturer.recent.sessions') }}" method="GET" id="filterForm">
                            <div class="row g-3 align-items-end">
                                <!-- Primary Filters -->
                                <div class="col-md-3">
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
                                <div class="col-md-2">
                                    <label for="week" class="form-label text-muted small">Week</label>
                                    <select class="form-select" id="week" name="week">
                                        <option value="">All Weeks</option>
                                        @for ($i = 1; $i <= $maxWeeks; $i++)
                                            <option value="{{ $i }}" {{ $selectedWeek == $i ? 'selected' : '' }}>
                                                Week {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="date" class="form-label text-muted small">Date</label>
                                    <input type="date" class="form-control" id="date" name="date"
                                        value="{{ $selectedDate }}">
                                </div>
                                <div class="col-md-auto">
                                    <div class="d-flex">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i data-feather="search" class="icon-sm"></i> Filter
                                        </button>
                                        <button type="button" id="resetFilters" class="btn btn-outline-secondary me-2">
                                            <i data-feather="refresh-cw" class="icon-sm"></i> Reset
                                        </button>
                                        <button type="button" class="btn btn-sm btn-link text-primary"
                                            data-bs-toggle="collapse" data-bs-target="#advancedFilterSection"
                                            aria-expanded="false">
                                            Advanced Filters <i data-feather="chevron-down" class="icon-sm"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Advanced Filter Section (Hidden by Default) -->
                            <div class="collapse mt-3" id="advancedFilterSection">
                                <div class="card card-body border-0 bg-light py-3">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="study_program_id" class="form-label text-muted small">Study
                                                Program</label>
                                            <select class="form-select select2" id="study_program_id"
                                                name="study_program_id">
                                                <option value="">All Study Programs</option>
                                                @foreach ($studyPrograms as $program)
                                                    <option value="{{ $program->id }}"
                                                        {{ $selectedProgram == $program->id ? 'selected' : '' }}>
                                                        {{ $program->code }} - {{ $program->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="classroom_id" class="form-label text-muted small">Classroom</label>
                                            <select class="form-select select2" id="classroom_id" name="classroom_id">
                                                <option value="">All Classrooms</option>
                                                @foreach ($classrooms as $classroom)
                                                    <option value="{{ $classroom->id }}"
                                                        {{ $selectedClassroom == $classroom->id ? 'selected' : '' }}
                                                        data-program-id="{{ $classroom->study_program_id }}">
                                                        {{ $classroom->studyProgram->code }} - {{ $classroom->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="semester_id" class="form-label text-muted small">Semester</label>
                                            <select class="form-select" id="semester_id" name="semester_id">
                                                <option value="">All Semesters</option>
                                                @foreach ($semesters as $semester)
                                                    <option value="{{ $semester->id }}"
                                                        {{ $selectedSemester == $semester->id ? 'selected' : '' }}>
                                                        {{ $semester->name }} {{ $semester->is_active ? '(Active)' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Quick Filters -->
                    <div class="mb-3">
                        <a href="{{ route('lecturer.recent.sessions', ['date' => now()->format('Y-m-d')]) }}"
                            class="badge bg-light text-primary me-2 p-2">Today</a>
                        <a href="{{ route('lecturer.recent.sessions') }}" class="badge bg-light text-primary p-2">All
                            Sessions</a>
                    </div>

                    <!-- Results Table -->
                    <div class="table-responsive">
                        <table id="dataTableExample" class="table table-hover table-bordered">
                            <thead>
                                <tr class="bg-light">
                                    <th style="width: 40%;">Course & Class</th>
                                    <th style="width: 15%;">Date</th>
                                    <th style="width: 15%;">Week/Meeting</th>
                                    <th style="width: 20%;">Attendance</th>
                                    <th style="width: 10%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sessionSummaries as $key => $summary)
                                    <tr>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span
                                                    class="fw-bold">{{ $summary['class_schedule']->course->code }}</span>
                                                <span
                                                    class="text-muted">{{ $summary['class_schedule']->course->name }}</span>
                                                <span
                                                    class="badge bg-light text-primary mt-1">{{ $summary['class_schedule']->classroom->name }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $summary['date']->format('d M Y') }}</td>
                                        <td>
                                            @if ($summary['week'] && $summary['meeting'])
                                                <span class="badge bg-info">Week {{ $summary['week'] }}</span>
                                                <span class="badge bg-secondary">Meeting {{ $summary['meeting'] }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="me-2 fw-bold {{ $summary['rate'] >= 75 ? 'text-success' : ($summary['rate'] >= 50 ? 'text-warning' : 'text-danger') }}">
                                                    {{ $summary['rate'] }}%
                                                </div>
                                                <div class="progress flex-grow-1" style="height: 8px;">
                                                    <div class="progress-bar {{ $summary['rate'] >= 75 ? 'bg-success' : ($summary['rate'] >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                        role="progressbar" style="width: {{ $summary['rate'] }}%;">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex mt-1">
                                                <small class="me-2 text-success">Present:
                                                    {{ $summary['present'] }}</small>
                                                <small class="text-danger">Absent: {{ $summary['absent'] }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('lecturer.attendance.show', [
                                                'classSchedule' => $summary['class_schedule']->id,
                                                'date' => $summary['date']->format('Y-m-d'),
                                                'week' => $summary['week'],
                                                'meeting' => $summary['meeting'],
                                            ]) }}"
                                                class="btn btn-icon btn-sm btn-primary" data-bs-toggle="tooltip"
                                                title="View Details">
                                                <i data-feather="eye" class="icon-sm"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i data-feather="calendar-x" class="icon-lg text-muted mb-2"></i>
                                                <span class="text-muted">No attendance sessions found</span>
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
            height: 36px;
            border-color: #e9ecef;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 34px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 34px;
        }

        /* Style for card hover effect */
        #dataTableExample tbody tr {
            transition: all 0.2s ease;
        }

        #dataTableExample tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }

        /* Badge styles */
        .badge {
            font-weight: normal;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .badge:hover {
            filter: brightness(95%);
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Reset filters button handler - direct approach
        document.getElementById('resetFilters').addEventListener('click', function() {
            // Direct browser redirect to the base URL
            window.location.href = "{{ route('lecturer.recent.sessions') }}";
        });
    </script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 for searchable dropdowns
            $('.select2').select2({
                placeholder: 'Select',
                allowClear: true,
                width: '100%'
            });

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Initialize feather icons
            feather.replace();

            // Filter interaction between study program and classroom
            $('#study_program_id').on('change', function() {
                var programId = $(this).val();
                var classroomSelect = $('#classroom_id');

                // If no program selected, show all classrooms
                if (!programId) {
                    classroomSelect.find('option').prop('disabled', false);
                    return;
                }

                // Hide classrooms not in this program
                classroomSelect.find('option').each(function() {
                    var classroomProgramId = $(this).data('program-id');
                    if (classroomProgramId && classroomProgramId != programId) {
                        $(this).prop('disabled', true);
                    } else {
                        $(this).prop('disabled', false);
                    }
                });

                // If current selection is now disabled, select first available
                if (classroomSelect.find('option:selected').prop('disabled')) {
                    classroomSelect.find('option:not(:disabled)').first().prop('selected', true);
                }

                // Update Select2
                classroomSelect.trigger('change');
            });
        });
    </script>
@endpush
