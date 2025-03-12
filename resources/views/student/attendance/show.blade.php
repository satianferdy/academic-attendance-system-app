@extends('layouts.app')

@section('title', 'Mark Attendance')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Mark Attendance for {{ $classSchedule->course->name }}</h3>
                    </div>
                    <div class="card-body">
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

                        @if (session('warning'))
                            <div class="alert alert-warning">
                                {{ session('warning') }}
                            </div>
                        @endif

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Course Details</h5>
                                <p><strong>Course:</strong> {{ $classSchedule->course->name }}</p>
                                <p><strong>Lecturer:</strong> {{ $classSchedule->lecturer->user->name }}</p>
                                <p><strong>Room:</strong> {{ $classSchedule->room }}</p>
                                <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</p>
                            </div>
                            <div class="col-md-6">
                                <h5>Attendance Status</h5>
                                @php
                                    $attendance = App\Models\Attendance::where('student_id', Auth::user()->student->id)
                                        ->where('class_schedule_id', $classSchedule->id)
                                        ->where('date', $date)
                                        ->first();
                                @endphp

                                @if ($attendance && in_array($attendance->status, ['present', 'late']))
                                    <div class="alert alert-success">
                                        <h5><i class="fas fa-check"></i> Attendance Marked!</h5>
                                        <p>Your attendance has been successfully recorded as
                                            <strong>{{ ucfirst($attendance->status) }}</strong>.
                                        </p>
                                        <p>Time:
                                            {{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : 'N/A' }}
                                        </p>
                                    </div>
                                @else
                                    <div id="attendanceForm">
                                        <div class="alert alert-info">
                                            <p>Please complete facial verification to mark your attendance for today's
                                                class.</p>
                                        </div>

                                        <!-- Check enrollment status -->
                                        @php
                                            $isEnrolled = true; // Replace with actual enrollment check
                                            $student = Auth::user()->student;
                                            $isEnrolled = $classSchedule->classroom_id == $student->classroom_id;
                                        @endphp

                                        @if (!$isEnrolled)
                                            <div class="alert alert-danger">
                                                <p>You are not enrolled in this class. If you believe this is an error,
                                                    please contact your academic advisor.</p>
                                            </div>
                                        @else
                                            <form id="verifyAttendanceForm" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="token" value="{{ $token }}">

                                                <div class="text-center mb-3">
                                                    <div id="camera-container" class="mx-auto"
                                                        style="width: 320px; height: 240px; border: 1px solid #ddd; position: relative;">
                                                        <video id="camera" width="320" height="240"
                                                            autoplay></video>
                                                        <canvas id="canvas" width="320" height="240"
                                                            style="display: none;"></canvas>
                                                        <div id="loading"
                                                            style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.7); text-align: center; padding-top: 100px;">
                                                            <div class="spinner-border text-primary" role="status">
                                                                <span class="visually-hidden">Loading...</span>
                                                            </div>
                                                            <p>Verifying...</p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="text-center">
                                                    <button type="button" id="captureBtn" class="btn btn-primary">Capture &
                                                        Submit</button>
                                                    <button type="button" id="retakeBtn" class="btn btn-secondary"
                                                        style="display: none;">Retake</button>
                                                </div>

                                                <div id="result" class="mt-3 text-center"></div>
                                            </form>
                                        @endif
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

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('camera');
            const canvas = document.getElementById('canvas');
            const captureBtn = document.getElementById('captureBtn');
            const retakeBtn = document.getElementById('retakeBtn');
            const result = document.getElementById('result');
            const loading = document.getElementById('loading');
            const attendanceForm = document.getElementById('attendanceForm');
            const ctx = canvas.getContext('2d');
            let stream;

            // Start camera
            async function startCamera() {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: true
                    });
                    video.srcObject = stream;
                } catch (err) {
                    console.error('Error accessing camera:', err);
                    result.innerHTML =
                        '<div class="alert alert-danger">Unable to access camera. Please make sure camera permissions are enabled.</div>';
                }
            }

            // Initialize camera if form exists
            if (captureBtn) {
                startCamera();
            }

            // Capture image and submit
            captureBtn.addEventListener('click', function() {
                // Capture image
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                video.style.display = 'none';
                canvas.style.display = 'block';
                captureBtn.style.display = 'none';
                retakeBtn.style.display = 'inline-block';

                // Convert to blob
                canvas.toBlob(function(blob) {
                    const formData = new FormData();
                    formData.append('image', blob, 'capture.jpg');
                    formData.append('token', document.querySelector('input[name="token"]').value);
                    formData.append('_token', document.querySelector('input[name="_token"]').value);

                    // Show loading
                    loading.style.display = 'block';

                    // Submit attendance
                    fetch('{{ route('student.attendance.verify') }}', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            loading.style.display = 'none';

                            if (data.status === 'success') {
                                result.innerHTML = '<div class="alert alert-success">' + data
                                    .message + '</div>';
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                result.innerHTML = '<div class="alert alert-danger">' + data
                                    .message + '</div>';
                                retakeBtn.click();
                            }
                        })
                        .catch(error => {
                            loading.style.display = 'none';
                            result.innerHTML = '<div class="alert alert-danger">Error: ' + error
                                .message + '</div>';
                            retakeBtn.click();
                        });
                }, 'image/jpeg', 0.9);
            });

            // Retake photo
            retakeBtn.addEventListener('click', function() {
                video.style.display = 'block';
                canvas.style.display = 'none';
                captureBtn.style.display = 'inline-block';
                retakeBtn.style.display = 'none';
                result.innerHTML = '';
            });
        });
    </script>
@endsection
