@extends('layouts.app')

@section('title', 'Lecturer Dashboard')

@section('page-title', 'Lecturer Dashboard')

@push('styles')
    <style>
        .stat-card {
            transition: all 0.2s ease;
            border-radius: 8px;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .attendance-chart-container {
            height: 250px;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .activity-timestamp {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .quick-action-card {
            transition: all 0.2s ease;
            border-radius: 8px;
        }

        .quick-action-card:hover {
            transform: translateY(-3px);
            background-color: rgba(0, 0, 0, 0.02);
        }

        .card {
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            border: none;
        }

        .card-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1rem 1.25rem;
            background-color: transparent;
        }

        .stat-icon {
            height: 40px;
            width: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Welcome Card -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body d-flex align-items-center">
                        <div>
                            <h5 class="fw-semibold mb-1">Welcome, {{ Auth::user()->name }}!</h5>
                            <p class="text-muted mb-0">Here's your facial recognition attendance system overview for today.
                            </p>
                        </div>
                        <div class="ms-auto">
                            <a href="{{ route('lecturer.attendance.index') }}" class="btn btn-icon-text btn-sm btn-primary">
                                <i class="btn-icon-prepend" data-feather="camera" class="me-1"></i>
                                Start Attendance Session
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-primary-subtle me-2">
                                <i data-feather="users" class="text-primary"></i>
                            </div>
                            <h6 class="card-subtitle text-muted mb-0">Total Students</h6>
                        </div>
                        <h3 class="fw-semibold mb-0">{{ $totalStudents }}</h3>
                        <div class="d-flex align-items-center mt-2">
                            <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                <div class="progress-bar bg-primary" role="progressbar"
                                    style="width: {{ $faceRegistrationPercentage }}%"></div>
                            </div>
                            <span class="text-muted small">{{ $studentsWithFace }} registered faces</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-success-subtle me-2">
                                <i data-feather="book" class="text-success"></i>
                            </div>
                            <h6 class="card-subtitle text-muted mb-0">Today's Classes</h6>
                        </div>
                        <h3 class="fw-semibold mb-0">{{ $todaySchedules->count() }}</h3>
                        <small class="text-muted">{{ Carbon\Carbon::now()->format('l, d M Y') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-info-subtle me-2">
                                <i data-feather="check-circle" class="text-info"></i>
                            </div>
                            <h6 class="card-subtitle text-muted mb-0">Attendance Rate</h6>
                        </div>
                        <h3 class="fw-semibold mb-0">{{ $avgAttendanceRate }}%</h3>
                        <small class="text-muted">Average from recent sessions</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <!-- Today's Schedule -->
            <div class="col-md-5">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="card-title fw-semibold mb-0">Today's Schedule</h6>
                        <a href="{{ route('lecturer.schedule.index') }}" class="btn btn-sm btn-outline-primary">Full
                            Schedule</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($todaySchedules as $schedule)
                                <div class="list-group-item p-3">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 fw-medium">{{ $schedule->course->name }}</h6>
                                            <div class="text-muted d-flex align-items-center">
                                                <i data-feather="clock" class="me-1"
                                                    style="width: 14px; height: 14px;"></i>
                                                <span>{{ $schedule->timeSlots->first()->start_time->format('H:i') }} -
                                                    {{ $schedule->timeSlots->last()->end_time->format('H:i') }}</span>
                                            </div>
                                            <div class="text-muted d-flex align-items-center mt-1">
                                                <i data-feather="map-pin" class="me-1"
                                                    style="width: 14px; height: 14px;"></i>
                                                <span>{{ $schedule->classroom->name ?? $schedule->room }}</span>
                                            </div>
                                        </div>
                                        <div class="d-flex">
                                            <button class="btn btn-icon btn-sm btn-primary">
                                                <i data-feather="camera"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="list-group-item p-3 text-center text-muted">
                                    <i data-feather="calendar-x" class="mb-2"></i>
                                    <p class="mb-0">No classes scheduled for today</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Weekly Attendance Chart -->
            <div class="col-md-7">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="card-title fw-semibold mb-0">Weekly Attendance Overview</h6>
                    </div>
                    <div class="card-body">
                        <div class="attendance-chart-container">
                            <canvas id="weeklyAttendanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Attendance Sessions -->
            <div class="col-md-7">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="card-title fw-semibold mb-0">Recent Attendance Sessions</h6>
                        <a href="{{ route('lecturer.attendance-data.index') }}" class="btn btn-sm btn-outline-primary">View
                            All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Course</th>
                                        <th>Date</th>
                                        <th>Present</th>
                                        <th>Absent</th>
                                        <th>Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentSessionsStats as $stat)
                                        <tr>
                                            <td>{{ $stat['course'] }}</td>
                                            <td>{{ $stat['date'] }}</td>
                                            <td>{{ $stat['present'] + $stat['late'] }}</td>
                                            <td>{{ $stat['absent'] }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress me-2" style="width: 60px; height: 6px;">
                                                        <div class="progress-bar bg-{{ $stat['presentPercentage'] >= 80 ? 'success' : ($stat['presentPercentage'] >= 60 ? 'warning' : 'danger') }}"
                                                            role="progressbar"
                                                            style="width: {{ $stat['presentPercentage'] }}%"></div>
                                                    </div>
                                                    <span class="small">{{ $stat['presentPercentage'] }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i data-feather="database" class="mb-2"></i>
                                                <p class="mb-0">No attendance records found</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Face Recognition Status -->
            <div class="col-md-5">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="card-title fw-semibold mb-0">Face Recognition Status</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="progress-label">
                                <span class="text-muted">Student Face Registration</span>
                                <span class="fw-medium">{{ $faceRegistrationPercentage }}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" role="progressbar"
                                    style="width: {{ $faceRegistrationPercentage }}%; background-color: #6571ff;">
                                </div>
                            </div>
                            <small class="text-muted mt-2 d-block">{{ $studentsWithFace }} out of
                                {{ $totalStudents }} students have registered their face</small>
                        </div>
                        <div class="mt-3">
                            <h6 class="fw-medium mb-3">Upcoming Classes</h6>
                            <div class="list-group list-group-flush">
                                @php $hasUpcoming = false; @endphp

                                @foreach ($upcomingDays as $day => $date)
                                    @if (isset($upcomingSchedules[$day]))
                                        @php
                                            $hasUpcoming = true;
                                            $counter = 0;
                                        @endphp

                                        @foreach ($upcomingSchedules[$day] as $schedule)
                                            @if ($counter < 3)
                                                @php
                                                    $timeSlot = $schedule->timeSlots->first();
                                                    $timeSlots = $schedule->timeSlots->last();
                                                    $counter++;
                                                @endphp
                                                <div class="list-group-item px-0 py-2 border-0">
                                                    <div class="d-flex">
                                                        <div class="me-3 d-flex flex-column align-items-center justify-content-center"
                                                            style="min-width: 50px;">
                                                            <span
                                                                class="badge bg-light text-dark rounded-pill px-3 py-2">{{ ucfirst(substr($day, 0, 3)) }}</span>
                                                            <small class="text-muted mt-1">{{ $date }}</small>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1 fw-medium">{{ $schedule->course->name }}</h6>
                                                            <small class="text-muted d-flex align-items-center">
                                                                <i data-feather="clock" class="me-1"
                                                                    style="width: 12px; height: 12px;"></i>
                                                                {{ $timeSlot->start_time->format('H:i') }} -
                                                                {{ $timeSlots->end_time->format('H:i') }}
                                                            </small>
                                                            <small class="text-muted d-flex align-items-center mt-1">
                                                                <i data-feather="map-pin" class="me-1"
                                                                    style="width: 12px; height: 12px;"></i>
                                                                {{ $schedule->classroom->name ?? $schedule->room }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
                                @endforeach

                                @if (!$hasUpcoming)
                                    <div class="text-center text-muted py-3">
                                        <i data-feather="calendar" class="mb-2"></i>
                                        <p class="mb-0">No upcoming classes in the next week</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendors/chartjs/Chart.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Weekly attendance chart
            const ctx = document.getElementById('weeklyAttendanceChart').getContext('2d');

            // More muted color palette
            const chartColors = {
                present: 'rgba(114, 124, 245, 0.7)',
                absent: 'rgba(241, 85, 108, 0.7)',
                late: 'rgba(255, 190, 11, 0.7)',
                excused: 'rgba(42, 181, 125, 0.7)'
            };

            const chartData = {
                labels: @json($weekDays),
                datasets: [{
                        label: 'Hadir',
                        data: @json($weeklyAttendanceData['present']),
                        backgroundColor: chartColors.present,
                        borderColor: chartColors.present,
                        borderWidth: 0
                    },
                    {
                        label: 'Absen',
                        data: @json($weeklyAttendanceData['absent']),
                        backgroundColor: chartColors.absent,
                        borderColor: chartColors.absent,
                        borderWidth: 0
                    },
                    {
                        label: 'Terlambat',
                        data: @json($weeklyAttendanceData['late']),
                        backgroundColor: chartColors.late,
                        borderColor: chartColors.late,
                        borderWidth: 0
                    },
                    {
                        label: 'Izin',
                        data: @json($weeklyAttendanceData['excused']),
                        backgroundColor: chartColors.excused,
                        borderColor: chartColors.excused,
                        borderWidth: 0
                    }
                ]
            };

            const chartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        stacked: true,
                        ticks: {
                            precision: 0,
                            color: '#6c757d'
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        }
                    },
                    x: {
                        stacked: true,
                        ticks: {
                            maxRotation: 0,
                            color: '#6c757d'
                        },
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            boxWidth: 12,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 20,
                            color: '#6c757d'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#000',
                        bodyColor: '#6c757d',
                        borderColor: 'rgba(0, 0, 0, 0.1)',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 4,
                        callbacks: {
                            title: function(tooltipItems) {
                                return tooltipItems[0].label;
                            },
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw;
                            }
                        }
                    }
                }
            };

            new Chart(ctx, {
                type: 'bar',
                data: chartData,
                options: chartOptions
            });

            // Initialize feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
    </script>
@endpush
