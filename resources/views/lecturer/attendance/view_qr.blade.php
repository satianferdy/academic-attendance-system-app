@extends('layouts.app')

@section('title', 'View QR Code')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('lecturer.dashboard') }}">Lecturer</a></li>
            <li class="breadcrumb-item"><a href="{{ route('lecturer.attendance.index') }}">Attendance</a></li>
            <li class="breadcrumb-item"><a
                    href="{{ route('lecturer.attendance.show', ['id' => $classSchedule->id]) }}">Attendance
                    List</a></li>
            <li class="breadcrumb-item active" aria-current="page">View QR Code</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="card-title">QR Code - {{ $classSchedule->course->code }}</h6>
                        <div>
                            <a href="{{ route('lecturer.attendance.show', ['id' => $classSchedule->id]) }}"
                                class="btn btn-secondary btn-sm btn-icon-text" type="button">
                                <i class="btn-icon-prepend" data-feather="list"></i> Back to Attendance List
                            </a>
                        </div>
                    </div>

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

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title">Session Details</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th>Course</th>
                                                <td>{{ $classSchedule->course->code }} - {{ $classSchedule->course->name }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Session Date</th>
                                                <td>{{ \Carbon\Carbon::parse(request('date'))->format('l, d F Y') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Room</th>
                                                <td>{{ $classSchedule->room }}</td>
                                            </tr>
                                            <tr>
                                                <th>Time</th>
                                                <td>
                                                    @if ($classSchedule->timeSlots && $classSchedule->timeSlots->count() > 0)
                                                        @foreach ($classSchedule->timeSlots as $timeSlot)
                                                            <div>{{ $timeSlot->start_time->format('H:i') }} -
                                                                {{ $timeSlot->end_time->format('H:i') }}</div>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted">No time slots</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Session End Time</th>
                                                <td>{{ $sessionEndTime }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="d-grid gap-2 mt-3">
                                        <button type="button" class="btn btn-primary btn-icon-text" data-bs-toggle="modal"
                                            data-bs-target="#extendSessionModal">
                                            <i class="btn-icon-prepend" data-feather="clock"></i>Extend Session
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Attendance QR Code</h6>
                                    <p class="text-muted mb-3">Students can scan this QR code to mark their attendance</p>

                                    <div class="qr-container mb-3">
                                        {!! $qrCode !!}
                                    </div>

                                    <div class="alert alert-info mt-3">
                                        <i data-feather="clock" class="icon-sm me-2"></i>
                                        Session ends at <strong>{{ $sessionEndTime }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Extend Session Modal -->
    <div class="modal fade" id="extendSessionModal" tabindex="-1" aria-labelledby="extendSessionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="extendSessionModalLabel">Extend Session Time</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Current session end time: <strong>{{ $sessionEndTime }}</strong></p>
                    <p>Choose how much time to add to the session:</p>

                    <form id="extendTimeForm"
                        action="{{ route('lecturer.attendance.extend_time', ['classSchedule' => $classSchedule->id, 'date' => $date]) }}"
                        method="POST">
                        @csrf

                        <div class="d-flex justify-content-center pt-2">
                            <div class="btn-group w-100" role="group" aria-label="Extension time options">
                                <input type="radio" class="btn-check" name="minutes" id="minutes10" value="10"
                                    autocomplete="off" checked>
                                <label class="btn btn-outline-primary" for="minutes10">10 minutes</label>

                                <input type="radio" class="btn-check" name="minutes" id="minutes20" value="20"
                                    autocomplete="off">
                                <label class="btn btn-outline-primary" for="minutes20">20 minutes</label>

                                <input type="radio" class="btn-check" name="minutes" id="minutes30" value="30"
                                    autocomplete="off">
                                <label class="btn btn-outline-primary" for="minutes30">30 minutes</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary"
                        onclick="document.getElementById('extendTimeForm').submit();">Extend Session</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Get session date and end time
            const sessionDate = '{{ $date }}';
            const sessionEndTime = '{{ $sessionEndTime }}';

            // Combine date and time for comparison
            const sessionDateTime = new Date(`${sessionDate}T${sessionEndTime}`);
            const now = new Date();

            // Disable extension if current datetime is after session datetime
            if (now > sessionDateTime) {
                $('#extendSessionModal button[type="button"]').prop('disabled', true);
                $('#extendSessionModal').on('show.bs.modal', function(e) {
                    e.preventDefault();

                    Swal.fire({
                        icon: 'error',
                        title: 'Session Ended',
                        text: 'Attendance session has already ended!',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });

                    return false;
                });
            }
        });
    </script>
@endpush
