@extends('layouts.app')

@section('title', 'Detail Jadwal Perkuliahan')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Data</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.schedules.index') }}">Jadwal Perkuliahan</a></li>
            <li class="breadcrumb-item active" aria-current="page">Details</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="card-title mb-0">Detail Jadwal Perkuliahan</h6>
                        <div>
                            <a href="{{ route('admin.schedules.edit', $schedule->id) }}"
                                class="btn btn-primary btn-sm btn-icon-text">
                                <i class="btn-icon-prepend" data-feather="edit"></i>
                                Edit
                            </a>
                            <form action="{{ route('admin.schedules.destroy', $schedule->id) }}" method="post"
                                class="d-inline">
                                @csrf
                                @method('delete')
                                <button type="submit" class="btn btn-danger btn-sm btn-icon-text"
                                    onclick="return confirm('Are you sure you want to delete this schedule?')">
                                    <i class="btn-icon-prepend" data-feather="trash-2"></i>
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Informasi Mata Kuliah</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="30%">Kode Mata Kuliah</th>
                                            <td>{{ $schedule->course->code }}</td>
                                        </tr>
                                        <tr>
                                            <th>Nama Mata Kuliah</th>
                                            <td>{{ $schedule->course->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Semester</th>
                                            <td>{{ $schedule->semester }}</td>
                                        </tr>
                                        <tr>
                                            <th>Tahun Ajaran</th>
                                            <td>{{ $schedule->semesters->name }}</td>
                                        </tr>
                                        {{-- classroom --}}
                                        <tr>
                                            <th>Kelas</th>
                                            <td>{{ $schedule->classroom->name }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Informasi Jadwal</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="30%">Dosen</th>
                                            <td>{{ $schedule->lecturer->user->name ?? 'Unknown' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Ruang</th>
                                            <td>{{ $schedule->room }}</td>
                                        </tr>
                                        <tr>
                                            <th>Hari</th>
                                            <td>{{ $schedule->day }}</td>
                                        </tr>
                                        <tr>
                                            <th>Total Minggu</th>
                                            <td>{{ $schedule->total_weeks ?? 16 }}</td>
                                        </tr>
                                        <tr>
                                            <th>Pertemuan per Minggu</th>
                                            <td>{{ $schedule->meetings_per_week ?? 1 }}</td>
                                        </tr>
                                        {{-- <tr>
                                            <th>Created At</th>
                                            <td>{{ $schedule->created_at->format('d M Y, H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Updated At</th>
                                            <td>{{ $schedule->updated_at->format('d M Y, H:i') }}</td>
                                        </tr> --}}
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Waktu ({{ $schedule->timeSlots->count() }})</h6>
                                </div>
                                <div class="card-body">
                                    @if ($schedule->timeSlots->count() > 0)
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach ($schedule->timeSlots as $timeSlot)
                                                <div class="badge bg-primary p-2">
                                                    {{ $timeSlot->start_time->format('H:i') }} -
                                                    {{ $timeSlot->end_time->format('H:i') }}
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="mt-4">
                                            <div class="card mb-3">
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>No</th>
                                                                    <th>Waktu Mulai</th>
                                                                    <th>Waktu Selesai</th>
                                                                    <th>Durasi</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($schedule->timeSlots as $index => $timeSlot)
                                                                    <tr>
                                                                        <td>{{ $index + 1 }}</td>
                                                                        <td>{{ $timeSlot->start_time->format('H:i') }}</td>
                                                                        <td>{{ $timeSlot->end_time->format('H:i') }}</td>
                                                                        <td>
                                                                            @php
                                                                                $start = \Carbon\Carbon::parse(
                                                                                    $timeSlot->start_time,
                                                                                );
                                                                                $end = \Carbon\Carbon::parse(
                                                                                    $timeSlot->end_time,
                                                                                );
                                                                                $durationInMinutes = $end->diffInMinutes(
                                                                                    $start,
                                                                                );
                                                                                echo floor($durationInMinutes / 60) .
                                                                                    ' hr ' .
                                                                                    $durationInMinutes % 60 .
                                                                                    ' min';
                                                                            @endphp
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="alert alert-warning">
                                            Tidak ada waktu yang ditentukan untuk jadwal ini.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <a href="{{ route('admin.schedules.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
