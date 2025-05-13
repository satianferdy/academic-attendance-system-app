@extends('layouts.app')

@section('title', 'Dashboard')

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

        .schedule-item {
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
        }

        .schedule-item:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .schedule-item.active {
            border-left-color: #6571ff;
        }

        .schedule-time {
            min-width: 100px;
        }

        .course-tag {
            border-radius: 12px;
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
        }

        .face-status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        .time-badge {
            font-size: 0.65rem;
            padding: 0.15rem 0.5rem;
            border-radius: 12px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Welcome card -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body d-md-flex align-items-center">
                        <div>
                            <h5 class="fw-semibold mb-1">Selamat, {{ Auth::user()->name }}!</h5>
                            <p class="text-muted mb-0">
                                NIM: {{ Auth::user()->student->nim }} |
                                Kelas: {{ Auth::user()->student->classroom->name }} |
                                Program Studi: {{ Auth::user()->student->studyProgram->name }}
                            </p>
                        </div>

                        <div class="ms-auto mt-3 mt-md-0">
                            @if (!Auth::user()->student->face_registered)
                                <a href="{{ route('student.face.register') }}" class="btn btn-primary">
                                    <i data-feather="camera" class="icon-sm me-1"></i> Register Wajah
                                </a>
                            @else
                                <div class="d-flex align-items-center">
                                    <div class="face-status-indicator bg-success me-2"></div>
                                    <span class="text-success fw-medium">Face Recognition Active</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="row g-3 mb-4">
            <div class="col-md-4 col-lg-3">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-primary-subtle me-2">
                                <i data-feather="check-circle" class="text-primary"></i>
                            </div>
                            <h6 class="card-subtitle text-muted mb-0">Presensi Rate</h6>
                        </div>
                        <h3 class="fw-semibold mb-0">{{ $attendanceStats['attendanceRate'] }}%</h3>
                        <small class="text-muted">Overall Presensi</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-success-subtle me-2">
                                <i data-feather="book-open" class="text-success"></i>
                            </div>
                            <h6 class="card-subtitle text-muted mb-0">Kelas Attended</h6>
                        </div>
                        <h3 class="fw-semibold mb-0">{{ $attendanceStats['presentCount'] + $attendanceStats['lateCount'] }}
                        </h3>
                        <small class="text-muted">Dari {{ $attendanceStats['totalClasses'] }} sesi</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-warning-subtle me-2">
                                <i data-feather="clock" class="text-warning"></i>
                            </div>
                            <h6 class="card-subtitle text-muted mb-0">Kelas Hari Ini</h6>
                        </div>
                        <h3 class="fw-semibold mb-0">{{ $todayClasses->count() }}</h3>
                        <small
                            class="text-muted">{{ Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMM YYYY') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-danger-subtle me-2">
                                <i data-feather="alert-circle" class="text-danger"></i>
                            </div>
                            <h6 class="card-subtitle text-muted mb-0">Total Absent</h6>
                        </div>
                        <h3 class="fw-semibold mb-0">{{ $attendanceStats['absentCount'] }}</h3>
                        <small
                            class="text-muted">{{ $attendanceStats['absentCount'] > 0 ? 'Butuh Perhatian' : 'Kerja Bagus!' }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <!-- Today's Schedule -->
            <div class="col-md-6 col-lg-5">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="card-title fw-semibold mb-0">Jadwal Hari Ini</h6>
                        <span class="text-muted">{{ Carbon\Carbon::now()->locale('id')->isoFormat('dddd') }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($todayClasses as $class)
                                @php
                                    $now = Carbon\Carbon::now();
                                    $isActive = false;

                                    // Find earliest start time and latest end time
                                    $earliestStart = null;
                                    $latestEnd = null;

                                    foreach ($class->timeSlots as $timeSlot) {
                                        $startTime = Carbon\Carbon::createFromFormat(
                                            'H:i',
                                            $timeSlot->start_time->format('H:i'),
                                        );
                                        $endTime = Carbon\Carbon::createFromFormat(
                                            'H:i',
                                            $timeSlot->end_time->format('H:i'),
                                        );

                                        if ($earliestStart === null || $startTime->lt($earliestStart)) {
                                            $earliestStart = $startTime;
                                        }

                                        if ($latestEnd === null || $endTime->gt($latestEnd)) {
                                            $latestEnd = $endTime;
                                        }

                                        if ($now->between($startTime, $endTime)) {
                                            $isActive = true;
                                        }
                                    }
                                @endphp

                                <div class="list-group-item px-3 py-3">
                                    <div class="d-flex">
                                        <div class="schedule-time">
                                            <div class="mb-1">
                                                <span class="fw-medium">
                                                    {{ $earliestStart->format('H:i') }} -
                                                    {{ $latestEnd->format('H:i') }}
                                                </span>
                                                @if ($isActive)
                                                    <span class="text-muted ms-1">(Now)</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="mb-1 fw-medium">{{ $class->course->name }}</h6>
                                            <p class="mb-0 small text-muted">
                                                <i data-feather="user" class="icon-xs me-1"></i>
                                                {{ $class->lecturer->user->name }}
                                            </p>
                                        </div>
                                        <div class="ms-auto">
                                            <span class="badge bg-light text-dark">
                                                <i data-feather="map-pin" class="icon-xs me-1"></i> {{ $class->room }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="list-group-item p-4 text-center">
                                    <p class="text-muted mb-0">No classes scheduled for today</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Chart -->
            <div class="col-md-6 col-lg-7">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="card-title fw-semibold mb-0">Riwayat Presensi</h6>
                    </div>
                    <div class="card-body">
                        <div class="attendance-chart-container">
                            <canvas id="attendanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Face Recognition Status -->
            <div class="col-md-6 col-lg-5 order-2 order-md-1">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="card-title fw-semibold mb-0">Face Recognition</h6>
                        @if ($faceRecognitionStatus['isRegistered'])
                            <span class="badge rounded-pill bg-success">Active</span>
                        @else
                            <span class="badge rounded-pill bg-danger">Not Registered</span>
                        @endif
                    </div>
                    <div class="card-body">
                        @if (!$faceRecognitionStatus['isRegistered'])
                            <div class="text-center p-4">
                                <div class="mb-3">
                                    <i data-feather="camera-off" style="width: 48px; height: 48px;" class="text-muted"></i>
                                </div>
                                <h6>Face Not Registered</h6>
                                <p class="text-muted mb-4">Anda belum melakukan pendaftaran wajah untuk presensi otomatis.
                                    Silakan lakukan pendaftaran wajah untuk memulai.</p>
                                <a href="{{ route('student.face.register') }}" class="btn btn-primary">
                                    <i data-feather="camera" class="icon-sm me-1"></i> Register Now
                                </a>
                            </div>
                        @else
                            <div class="text-center p-4">
                                <div class="mb-3">
                                    <i data-feather="check-circle" style="width: 48px; height: 48px;"
                                        class="text-success"></i>
                                </div>
                                <h6>Face Recognition Active</h6>
                                <p class="text-muted mb-2">data wajah Anda telah terdaftar dan siap digunakan untuk
                                    presensi otomatis.</p>
                                </p>
                                <p class="small text-muted mb-4">Last updated:
                                    {{ $faceRecognitionStatus['lastUpdate']->format('d M Y, H:i') }}</p>

                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('student.face.index') }}" class="btn btn-sm btn-outline-primary">
                                        <i data-feather="refresh-cw" class="icon-sm me-1"></i> Update
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i data-feather="trash-2" class="icon-sm me-1"></i> Remove
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Attendance -->
            <div class="col-md-6 col-lg-7 order-1 order-md-2">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="card-title fw-semibold mb-0">Presensi Terakhir</h6>
                        <a href="{{ route('student.attendance.index') }}" class="btn btn-sm btn-outline-primary">Lihat
                            Semua</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Mata Kuliah</th>
                                        <th>Jam</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentAttendances as $attendance)
                                        <tr>
                                            <td>{{ $attendance->date->format('d M Y') }}</td>
                                            <td>{{ $attendance->classSchedule->course->name }}</td>
                                            <td>{{ $attendance->attendance_time ? $attendance->attendance_time->format('H:i') : '-' }}
                                            </td>
                                            <td>
                                                <span
                                                    class="badge rounded-pill {{ match ($attendance->status) {
                                                        'present' => 'bg-success-subtle text-success',
                                                        'late' => 'bg-warning-subtle text-warning',
                                                        'excused' => 'bg-info-subtle text-info',
                                                        default => 'bg-danger-subtle text-danger',
                                                    } }}">{{ $attendance->status }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                Tidak ada data presensi
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
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendors/chartjs/Chart.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
            // Setup the attendance chart
            const ctx = document.getElementById('attendanceChart').getContext('2d');

            const chartColors = {
                present: 'rgba(114, 124, 245, 0.7)',
                absent: 'rgba(241, 85, 108, 0.7)',
                late: 'rgba(255, 190, 11, 0.7)',
                excused: 'rgba(42, 181, 125, 0.7)'
            };

            const chartData = {
                labels: @json($attendanceStats['monthlyData']['months']),
                datasets: [{
                        label: 'Hadir',
                        data: @json($attendanceStats['monthlyData']['series'][0]['data']),
                        backgroundColor: chartColors.present,
                        borderColor: chartColors.present,
                        borderWidth: 0
                    },
                    {
                        label: 'Terlambat',
                        data: @json($attendanceStats['monthlyData']['series'][1]['data']),
                        backgroundColor: chartColors.late,
                        borderColor: chartColors.late,
                        borderWidth: 0
                    },
                    {
                        label: 'Absent',
                        data: @json($attendanceStats['monthlyData']['series'][2]['data']),
                        backgroundColor: chartColors.absent,
                        borderColor: chartColors.absent,
                        borderWidth: 0
                    },
                    {
                        label: 'Izin',
                        data: @json($attendanceStats['monthlyData']['series'][3]['data']),
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
        });
    </script>
@endpush
