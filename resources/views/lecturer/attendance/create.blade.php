@extends('layouts.app')

@section('title', 'Attendance Session')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('lecturer.dashboard') }}">Lecturer</a></li>
            <li class="breadcrumb-item"><a href="{{ route('lecturer.attendance.index') }}">Attendance</a></li>
            <li class="breadcrumb-item active" aria-current="page">Session</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="card-title">Attendance Session - {{ $classSchedule->course->code }}</h6>
                        <div>
                            <a href="{{ route('lecturer.attendance.show', ['id' => $classSchedule->id, 'date' => request('date')]) }}"
                                class="btn btn-secondary">
                                <i data-feather="list"></i> View Attendance List
                            </a>
                        </div>
                    </div>

                    @if (isset($result) && $result['status'] === 'success')
                        <div class="alert alert-success">
                            {{ $result['message'] ?? 'Attendance session created successfully!' }}
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
                                        </table>
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

                                    <div class="d-grid gap-2">
                                        <button type="button" id="refreshQR" class="btn btn-primary"
                                            data-class-id="{{ $classSchedule->id }}" data-date="{{ request('date') }}">
                                            <i data-feather="refresh-cw"></i> Refresh QR Code
                                        </button>
                                    </div>
                                    {{-- <p class="text-muted mt-2">
                                        <small>QR code expires in {{ config('services.qrcode.expiry_time', 30) }}
                                            minutes</small>
                                    </p> --}}
                                    <p class="text-muted mt-2">
                                        <small>QR code expires at
                                            {{ isset($result['session_expires']) ? $result['session_expires'] : now()->addMinutes(30)->format('H:i') }}</small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize feather icons
            feather.replace();

            // QR Code refresh button
            $('#refreshQR').click(function() {
                const classId = $(this).data('class-id');
                const date = $(this).data('date');

                $.ajax({
                    url: "{{ route('lecturer.attendance.qr') }}",
                    type: 'POST',
                    data: {
                        class_id: classId,
                        date: date,
                        _token: "{{ csrf_token() }}"
                    },
                    beforeSend: function() {
                        $('#refreshQR').prop('disabled', true);
                        $('#refreshQR').html('<i data-feather="loader"></i> Generating...');
                        feather.replace();
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            $('.qr-container').html(response.qr_code);
                            // Update expiration time
                            if (response.expires_at) {
                                $('.expiry-time').text(response.expires_at);
                            }
                            toastr.success('QR code has been refreshed!');
                        } else {
                            toastr.error(response.message || 'Failed to refresh QR code.');
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message ||
                            'An error occurred. Please try again.');
                    },
                    complete: function() {
                        $('#refreshQR').prop('disabled', false);
                        $('#refreshQR').html(
                            '<i data-feather="refresh-cw"></i> Refresh QR Code');
                        feather.replace();
                    }
                });
            });
        });
    </script>
@endsection
