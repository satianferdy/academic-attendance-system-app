@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('page-title', 'Admin Dashboard - Sistem Presensi Pengenalan Wajah')

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
        <!-- Previous welcome card remains unchanged -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body d-flex align-items-center">
                        <div>
                            <h5 class="fw-semibold mb-1">Selamat Datang, Admin!</h5>
                            <p class="text-muted mb-0">Inilah yang terjadi dengan sistem presensi Anda hari ini.</p>
                        </div>
                        <div class="ms-auto">
                            <button class="btn btn-sm btn-outline-primary">Buat Laporan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Overview with updated icons -->
        <div class="row g-3 mb-4">
            <div class="col-md-4 col-lg-2">
                <!-- First card unchanged as it already uses data-feather -->
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-primary-subtle me-2">
                                <i data-feather="users" class="text-primary"></i>
                            </div>
                            <h6 class="card-subtitle text-muted mb-0">Mahasiswa</h6>
                        </div>
                        <h3 class="fw-semibold mb-0">{{ $statistics['totalStudents'] }}</h3>
                        <small class="text-muted">Total registrasi</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-info-subtle me-2">
                                <i data-feather="user-check" class="text-info"></i>
                            </div>
                            <h6 class="card-subtitle text-muted mb-0">Dosen</h6>
                        </div>
                        <h3 class="fw-semibold mb-0">{{ $statistics['totalLecturers'] }}</h3>
                        <small class="text-muted">Total registrasi</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-success-subtle me-2">
                                <i data-feather="book" class="text-success"></i>
                            </div>
                            <h6 class="card-subtitle text-muted mb-0">Mata Kuliah</h6>
                        </div>
                        <h3 class="fw-semibold mb-0">{{ $statistics['totalCourses'] }}</h3>
                        <small class="text-muted">Total tersedia</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-warning-subtle me-2">
                                <i data-feather="home" class="text-warning"></i>
                            </div>
                            <h6 class="card-subtitle text-muted mb-0">Kelas</h6>
                        </div>
                        <h3 class="fw-semibold mb-0">{{ $statistics['totalClassrooms'] }}</h3>
                        <small class="text-muted">Total tersedia</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-danger-subtle me-2">
                                <i data-feather="calendar" class="text-danger"></i>
                            </div>
                            <h6 class="card-subtitle text-muted mb-0">Jadwal</h6>
                        </div>
                        <h3 class="fw-semibold mb-0">{{ $statistics['totalSchedules'] }}</h3>
                        <small class="text-muted">Total jadwal</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-secondary-subtle me-2">
                                <i data-feather="check-circle" class="text-secondary"></i>
                            </div>
                            <h6 class="card-subtitle text-muted mb-0">Hari ini</h6>
                        </div>
                        <h3 class="fw-semibold mb-0">{{ $statistics['todayAttendanceCount'] }}</h3>
                        <small class="text-muted">Presensi tercatat</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <!-- Face Registration Progress -->
            <div class="col-md-5">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="card-title fw-semibold mb-0">Status Pengenalan Wajah</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="progress-label">
                                <span class="text-muted">Kemajuan Registrasi Wajah</span>
                                <span class="fw-medium">{{ $faceRegistration['faceRegistrationPercentage'] }}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" role="progressbar"
                                    style="width: {{ $faceRegistration['faceRegistrationPercentage'] }}%; background-color: #6571ff;">
                                </div>
                            </div>
                            <small class="text-muted mt-2 d-block">{{ $faceRegistration['studentsWithFace'] }} dari
                                {{ $faceRegistration['totalStudents'] }} mahasiswa telah
                                mendaftarkan wajah mereka</small>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <a href="" class="btn btn-sm btn-icon-text btn-outline-primary">
                                <i data-feather="user-x" class="btn-icon-prepend"></i> Mahasiswa Belum Terdaftar
                            </a>
                            <a href="" class="btn btn-sm btn-icon-text btn-outline-secondary">
                                <i data-feather="database" class="btn-icon-prepend"></i> Kelola Data Wajah
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Weekly Attendance Chart section remains unchanged -->
            <div class="col-md-7">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="card-title fw-semibold mb-0">Ringkasan Kehadiran Mingguan</h6>
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
            <!-- Recent Activities -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="card-title fw-semibold mb-0">Aktivitas Terbaru</h6>
                        <a href="{{ route('admin.attendance.index') }}" class="btn btn-sm btn-outline-primary">Lihat
                            Semua</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($recentAttendances as $attendance)
                                <div class="list-group-item px-3 py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <span class="fw-medium">{{ $attendance->student->user->name }}</span>
                                                    <br />
                                                    <small class="text-muted">
                                                        <span
                                                            class="badge rounded-pill {{ match ($attendance->status) {
                                                                'present' => 'bg-success-subtle text-success',
                                                                'late' => 'bg-warning-subtle text-warning',
                                                                'excused' => 'bg-info-subtle text-info',
                                                                default => 'bg-danger-subtle text-danger',
                                                            } }}">{{ $attendance->status }}</span>
                                                        {{ $attendance->classSchedule->course->name }}
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <small
                                                        class="text-muted d-block">{{ $attendance->attendance_time ? $attendance->attendance_time->format('H:i') : '-' }}</small>
                                                    <small
                                                        class="text-muted">{{ $attendance->date ? $attendance->date->format('d M') : '-' }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="list-group-item px-3 py-2 text-center text-muted">
                                    <i data-feather="calendar-x" class="me-1"></i> Tidak ada aktivitas terbaru
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="card-title fw-semibold mb-0">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <a href="{{ route('admin.users.create') }}"
                                    class="card quick-action-card p-3 text-decoration-none">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-primary-subtle me-3">
                                            <i data-feather="user-plus" class="text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-medium">Tambah Mahasiswa Baru</h6>
                                            <small class="text-muted">
                                                Registrasi mahasiswa baru
                                            </small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('admin.users.create') }}"
                                    class="card quick-action-card p-3 text-decoration-none">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-info-subtle me-3">
                                            <i data-feather="user-check" class="text-info"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-medium">
                                                Tambah Dosen Baru
                                            </h6>
                                            <small class="text-muted">
                                                Registrasi dosen baru
                                            </small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('admin.users.create') }}"
                                    class="card quick-action-card p-3 text-decoration-none">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-success-subtle me-3">
                                            <i data-feather="book" class="text-success"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-medium">Buat Mata Kuliah Baru</h6>
                                            <small class="text-muted">
                                                Tambah mata kuliah baru
                                            </small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('admin.schedules.create') }}"
                                    class="card quick-action-card p-3 text-decoration-none">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-warning-subtle me-3">
                                            <i data-feather="calendar" class="text-warning"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-medium">
                                                Buat Jadwal Baru
                                            </h6>
                                            <small class="text-muted">
                                                Tambah jadwal baru
                                            </small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="" class="card quick-action-card p-3 text-decoration-none">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-danger-subtle me-3">
                                            <i data-feather="file-text" class="text-danger"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-medium">
                                                Ekspor Data Kehadiran
                                            </h6>
                                            <small class="text-muted">
                                                Unduh laporan kehadiran
                                            </small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="" class="card quick-action-card p-3 text-decoration-none">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-secondary-subtle me-3">
                                            <i data-feather="settings" class="text-secondary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-medium">
                                                Pengaturan Sistem
                                            </h6>
                                            <small class="text-muted">
                                                Sesuaikan pengaturan sistem
                                            </small>
                                        </div>
                                    </div>
                                </a>
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
    {{-- <script src="{{ asset('assets/vendors/feather-icons/feather.min.js') }}"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script>
        // Initialize Feather Icons
        feather.replace();
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
                labels: @json($attendanceData['weekDays']),
                datasets: [{
                        label: 'Hadir',
                        data: @json($attendanceData['series'][0]['data']),
                        backgroundColor: chartColors.present,
                        borderColor: chartColors.present,
                        borderWidth: 0
                    },
                    {
                        label: 'Tidak Hadir',
                        data: @json($attendanceData['series'][1]['data']),
                        backgroundColor: chartColors.absent,
                        borderColor: chartColors.absent,
                        borderWidth: 0
                    },
                    {
                        label: 'Terlambat',
                        data: @json($attendanceData['series'][2]['data']),
                        backgroundColor: chartColors.late,
                        borderColor: chartColors.late,
                        borderWidth: 0
                    },
                    {
                        label: 'Izin',
                        data: @json($attendanceData['series'][3]['data']),
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
        });
    </script>
@endpush
