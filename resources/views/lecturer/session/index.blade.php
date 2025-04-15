@extends('layouts.app')

@section('title', 'Recent Session Attendance')


@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Recent Session Attendance</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Recent Session Attendance</h6>

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Filters -->
                    <div class="mb-4">
                        <form action="{{ route('lecturer.recent.sessions') }}" method="GET" id="filterForm">
                            <div class="row g-2 align-items-end" style="max-width: 45%;">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label for="course_id" class="form-label">Course</label>
                                        <select class="form-select" id="course_id" name="course_id">
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
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label for="date" class="form-label">Date</label>
                                        <input type="date" class="form-control" id="date" name="date"
                                            value="{{ $selectedDate }}">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table id="dataTableExample" class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Course</th>
                                    <th>Date</th>
                                    <th>Present</th>
                                    <th>Absent</th>
                                    <th>Rate</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sessionSummaries as $key => $summary)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <strong>{{ $summary['class_schedule']->course->code }}</strong><br>
                                            <small>{{ $summary['class_schedule']->course->name }}</small><br>
                                            <span
                                                class="badge bg-info text-white">{{ $summary['class_schedule']->classroom->name }}</span>
                                        </td>
                                        <td>{{ $summary['date']->format('d M Y') }}</td>
                                        <td>{{ $summary['present'] }}</td>
                                        <td>{{ $summary['absent'] }}</td>
                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar {{ $summary['rate'] >= 70 ? 'bg-success' : ($summary['rate'] >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                    role="progressbar" style="width: {{ $summary['rate'] }}%;"
                                                    aria-valuenow="{{ $summary['rate'] }}" aria-valuemin="0"
                                                    aria-valuemax="100">
                                                    {{ $summary['rate'] }}%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('lecturer.attendance.show', [
                                                'classSchedule' => $summary['class_schedule']->id,
                                                'date' => $summary['date']->format('Y-m-d'),
                                            ]) }}"
                                                class="btn btn-sm btn-icon btn-primary">
                                                <i class="btn-icon-prepend" data-feather="info"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No attendance sessions found</td>
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
        $(document).ready(function() {
            // Initialize Select2 for searchable dropdown
            $('.select2').select2({
                placeholder: 'Select a course',
                allowClear: true,
                width: '100%'
            });

            // Auto-submit form when any filter changes
            $('#date').on('change', function() {
                $('#filterForm').submit();
            });

            // For select2, we need a special event handler
            $('#course_id').on('select2:select select2:unselect', function() {
                $('#filterForm').submit();
            });
        });
    </script>
@endpush
