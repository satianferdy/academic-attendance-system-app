@extends('layouts.app')

@section('title', 'Daftar Presensi')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendors/datatables.net-bs5/dataTables.bootstrap5.css') }}">
    <style>
        .filter-form .form-select {
            min-width: 200px;
            max-height: 30px;
            font-size: 12px;
        }

        .btn-icon {
            width: 30px !important;
            height: 30px !important;
        }

        .btn-icon-prepend {
            width: 16px !important;
            height: auto !important;
        }
    </style>
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">General</a></li>
            <li class="breadcrumb-item active" aria-current="page">Daftar Presensi</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Daftar Presensi</h6>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <!-- Filter Form -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Filter Options</h6>
                            <form action="{{ route('lecturer.attendance-data.index') }}" method="GET" class="filter-form">
                                <div class="row g-3 align-items-center">
                                    <div class="col-auto">
                                        <label for="study_program_id" class="col-form-label">Program Studi</label>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" id="study_program_id" name="study_program_id"
                                            onchange="this.form.submit()">
                                            <option value="">Select Program Studi</option>
                                            @foreach ($studyPrograms as $program)
                                                <option value="{{ $program->id }}"
                                                    {{ $selectedProgramId == $program->id ? 'selected' : '' }}>
                                                    {{ $program->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-auto">
                                        <label for="class_schedule_id" class="col-form-label">Kelas</label>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" id="class_schedule_id" name="class_schedule_id"
                                            {{ count($classSchedules) ? '' : 'disabled' }} onchange="this.form.submit()">
                                            <option value="">Select Kelas</option>
                                            @foreach ($classSchedules as $schedule)
                                                <option value="{{ $schedule->id }}"
                                                    {{ $selectedScheduleId == $schedule->id ? 'selected' : '' }}>
                                                    {{ $schedule->combined_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if ($selectedScheduleId)
                        <div class="row">
                            <!-- Left Side: Sessions List -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                        <h6 class="card-title mb-0">Sesi Presensi</h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-bordered mb-0 table-sessions">
                                                <thead>
                                                    <tr>
                                                        <th>Minggu ke-</th>
                                                        <th>Pertemuan ke-</th>
                                                        <th>Tanggal</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($sessionsList as $session)
                                                        <tr>
                                                            <td>
                                                                <span class="badge bg-light text-dark">
                                                                    {{ $session->week }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-light text-dark">
                                                                    {{ $session->meetings }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                @if ($session->session_date)
                                                                    {{ $session->session_date->format('d-m-Y') }}
                                                                @else
                                                                    <span class="text-muted">No date</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <a href="{{ route('lecturer.attendance-data.edit-session', ['session' => $session->id]) }}"
                                                                    class="btn btn-icon btn-sm btn-primary"
                                                                    data-bs-toggle="tooltip" title="Edit">
                                                                    <i data-feather="edit" class="btn-icon-prepend"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="3" class="text-center py-3">Tidak ada sesi
                                                                presensi untuk kelas ini</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Right Side: Cumulative Attendance -->
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header bg-white">
                                        <h6 class="card-title mb-0">Presensi Komulatif</h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-bordered mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>NIM</th>
                                                        <th>Nama</th>
                                                        <th class="text-center bg-success-subtle">Hadir</th>
                                                        <th class="text-center bg-danger-subtle">Absent</th>
                                                        <th class="text-center bg-warning-subtle">Izin</th>
                                                        <th class="text-center bg-info-subtle">Sakit</th>
                                                        <th class="text-center">Total (%)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($cumulativeData as $index => $data)
                                                        @php
                                                            $totalHours = $data['total_hours'];
                                                            $presentPercentage =
                                                                $totalHours > 0
                                                                    ? round(
                                                                        ($data['hours_present'] / $totalHours) * 100,
                                                                    )
                                                                    : 0;
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>{{ $data['student']->nim }}</td>
                                                            <td>{{ $data['student']->user->name }}</td>
                                                            <td class="text-center">
                                                                <span>{{ $data['hours_present'] }}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                <span>{{ $data['hours_absent'] }}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                <span>{{ $data['hours_permitted'] }}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                <span>{{ $data['hours_sick'] }}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="progress flex-grow-1 me-2"
                                                                        style="height: 6px;">
                                                                        <div class="progress-bar {{ $presentPercentage >= 75 ? 'bg-success' : ($presentPercentage >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                                            style="width: {{ $presentPercentage }}%"></div>
                                                                    </div>
                                                                    <span class="small">{{ $presentPercentage }}%</span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="8" class="text-center py-3">Tidak ada mahasiswa
                                                                terdaftar di kelas ini</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>
                    @else
                        <div class="alert alert-info">
                            <i data-feather="info" class="icon-md me-2"></i>
                            Silakan pilih program studi dan kelas untuk melihat data presensi.
                        </div>
                    @endif
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
        $(function() {
            // Initialize feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
    </script>
@endpush
