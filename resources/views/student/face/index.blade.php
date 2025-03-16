@extends('layouts.app')

@section('title', 'Face Recognition')

@section('content')
    <div class="container-fluid p-0">
        <div class="dashboard-container">
            <!-- Alerts -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle me-2"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Header Banner -->
            <div class="dashboard-header bg-primary mb-4">
                <h4 class="text-white mb-0">Face Recognition Dashboard</h4>
            </div>

            <!-- Content Area -->
            <div class="dashboard-content px-3 pb-4">
                <div class="row g-4">
                    <!-- Registration Status Card -->
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-header bg-transparent border-0 d-flex align-items-center">
                                <div class="icon-circle bg-warning text-white me-2">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <h5 class="mb-0">FACE REGISTRATION STATUS</h5>
                            </div>
                            <div class="card-body">
                                <div
                                    class="status-box p-3 rounded-3 mb-4 {{ $student->face_registered ? 'bg-success-subtle' : 'bg-warning-subtle' }}">
                                    @if ($student->face_registered)
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge bg-success me-2">Registered</span>
                                            <span class="text-success fw-bold">Ready for Verification</span>
                                        </div>
                                        <p class="mb-0">Your face is registered in our system and ready for attendance
                                            verification.</p>
                                    @else
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge bg-warning me-2">Not Registered</span>
                                            <span class="text-warning fw-bold">Action Required</span>
                                        </div>
                                        <p class="mb-0">You need to register your face to use the facial recognition
                                            attendance system.</p>
                                    @endif
                                </div>

                                <div class="text-center">
                                    @if ($student->face_registered)
                                        <a href="{{ route('student.face.register') }}" class="btn btn-outline-primary">
                                            <i class="fas fa-sync-alt me-2"></i>Update Registration
                                        </a>
                                    @else
                                        <a href="{{ route('student.face.register') }}" class="btn btn-primary">
                                            <i class="fas fa-user-plus me-2"></i>Register Now
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- How It Works Card -->
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-header bg-transparent border-0 d-flex align-items-center">
                                <div class="icon-circle bg-info text-white me-2">
                                    <i class="fas fa-info"></i>
                                </div>
                                <h5 class="mb-0">HOW IT WORKS</h5>
                            </div>
                            <div class="card-body">
                                <div class="steps-container">
                                    <div class="step d-flex mb-2">
                                        <div class="step-number">1</div>
                                        <div class="step-content">
                                            <p class="mb-0"><strong>Register Your Face</strong><br>Use a clear
                                                front-facing photo in good lighting</p>
                                        </div>
                                    </div>

                                    <div class="step d-flex mb-2">
                                        <div class="step-number">2</div>
                                        <div class="step-content">
                                            <p class="mb-0"><strong>Scan QR Code</strong><br>Scan the QR code provided by
                                                your lecturer</p>
                                        </div>
                                    </div>

                                    <div class="step d-flex mb-2">
                                        <div class="step-number">3</div>
                                        <div class="step-content">
                                            <p class="mb-0"><strong>Verify Identity</strong><br>Take a clear selfie for
                                                identity verification</p>
                                        </div>
                                    </div>

                                    <div class="step d-flex">
                                        <div class="step-number">4</div>
                                        <div class="step-content">
                                            <p class="mb-0"><strong>Attendance Recorded</strong><br>Your attendance is
                                                automatically recorded on success</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-light p-2 rounded-3 mt-3">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-lightbulb text-warning me-2 mt-1"></i>
                                        <p class="mb-0"><strong>Tip:</strong> Ensure good lighting and a clear view of
                                            your face when registering or verifying to improve recognition accuracy.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .dashboard-container {
            width: 100%;
            min-height: calc(100vh - 60px);
            display: flex;
            flex-direction: column;
            background-color: #f8f9fa;
        }

        .dashboard-header {
            padding: 1rem 1.5rem;
            width: 100%;
        }

        .dashboard-content {
            flex: 1;
        }

        .icon-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .step-number {
            width: 28px;
            height: 28px;
            background-color: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 12px;
            flex-shrink: 0;
        }

        .step-content {
            flex-grow: 1;
            font-size: 0.9rem;
        }

        .bg-success-subtle {
            background-color: rgba(25, 135, 84, 0.1);
        }

        .bg-warning-subtle {
            background-color: rgba(255, 193, 7, 0.1);
        }

        .card {
            margin-bottom: 0;
        }

        .card-header {
            padding: 0.75rem 1rem;
        }

        .card-body {
            padding: 1rem;
        }
    </style>
@endsection
