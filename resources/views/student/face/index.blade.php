@extends('layouts.app')

@section('title', 'Face Recognition')

@push('styles')
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

        .custom-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-icon {
            background-color: #FFC107;
            /* Yellow/amber color */
        }

        .info-icon {
            background-color: #4DD0E1;
            /* Teal color */
        }

        .camera-icon {
            background-color: #8C9EFF;
            /* Light purple color */
        }

        .icon-inner {
            width: 20px;
            height: 20px;
            color: white;
        }

        .step-number {
            width: 30px;
            height: 30px;
            background-color: #f0f0f0;
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

        .tips-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .tips-icon {
            margin-right: 15px;
            min-width: 24px;
        }

        .tips-image {
            width: 100%;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .tips-do-dont {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 15px;
        }

        .tip-card {
            flex: 1;
            min-width: 200px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e9ecef;
        }

        .tip-header {
            padding: 8px 12px;
            color: white;
            font-weight: 500;
        }

        .do-header {
            background-color: #198754;
        }

        .dont-header {
            background-color: #dc3545;
        }

        .tip-body {
            padding: 12px;
            background-color: white;
        }
    </style>
@endpush

@section('content')
    <div class="dashboard-container">
        <!-- Alerts -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert">
                <div class="d-flex align-items-center">
                    <i data-feather="check-circle" class="me-2"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert">
                <div class="d-flex align-items-center">
                    <i data-feather="alert-circle" class="me-2"></i>
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
                            <div class="custom-icon user-icon me-2">
                                <i data-feather="user" class="icon-inner"></i>
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
                                    <a href="{{ route('student.face.register') }}"
                                        class="btn btn-icon-text btn-outline-primary">
                                        <i class="btn-icon-prepend" data-feather="refresh-cw" class="me-2"></i>Update
                                        Registration
                                    </a>
                                @else
                                    <a href="{{ route('student.face.register') }}" class="btn btn-icon-text btn-primary">
                                        <i class="btn-icon-prepend" data-feather="user-plus" class="me-2"></i>Register Now
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
                            <div class="custom-icon info-icon me-2">
                                <i data-feather="info" class="icon-inner"></i>
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
                                    <i data-feather="zap" class="text-warning me-2 mt-2"></i>
                                    <p class="mb-0"><strong>Tip:</strong> Ensure good lighting and a clear view of
                                        your face when registering or verifying to improve recognition accuracy.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Face Registration Tips Card -->
                <div class="col-12 mt-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0 d-flex align-items-center">
                            <div class="custom-icon camera-icon me-2">
                                <i data-feather="camera" class="icon-inner"></i>
                            </div>
                            <h5 class="mb-0">FACE REGISTRATION TIPS</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-4 mb-md-0">
                                    <img src="{{ asset('assets/images/register-face.jpg') }}" alt="Face Registration Tips"
                                        class="tips-image">
                                </div>
                                <div class="col-md-8">
                                    <div class="tips-item">
                                        <i data-feather="sun" class="tips-icon text-warning"></i>
                                        <div>
                                            <h6>Good Lighting</h6>
                                            <p>Ensure your face is well-lit from the front. Avoid harsh shadows,
                                                backlighting, or uneven lighting. Natural light is best whenever possible.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="tips-item">
                                        <i data-feather="align-center" class="tips-icon text-primary"></i>
                                        <div>
                                            <h6>Proper Positioning</h6>
                                            <p>Position your face in the center of the frame. Keep your head straight and
                                                look directly at the camera. Your entire face should be visible.</p>
                                        </div>
                                    </div>

                                    <div class="tips-item">
                                        <i data-feather="eye" class="tips-icon text-info"></i>
                                        <div>
                                            <h6>Clear View</h6>
                                            <p>Remove glasses, masks, or any accessories that cover parts of your face.
                                                Ensure your face is not obscured by hair, shadows, or objects.</p>
                                        </div>
                                    </div>

                                    <div class="tips-item">
                                        <i data-feather="target" class="tips-icon text-success"></i>
                                        <div>
                                            <h6>Appropriate Distance</h6>
                                            <p>Keep a comfortable distance from the camera. Not too close (which can distort
                                                features) and not too far (which reduces detail).</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tips-do-dont mt-3">
                                <div class="tip-card">
                                    <div class="tip-header do-header d-flex align-items-center">
                                        <i data-feather="check" class="me-2"></i>
                                        <span>DO</span>
                                    </div>
                                    <div class="tip-body">
                                        <ul class="mb-0 ps-3">
                                            <li>Face the camera directly</li>
                                            <li>Maintain a neutral expression</li>
                                            <li>Ensure even lighting on your face</li>
                                            <li>Position at eye level with camera</li>
                                            <li>Use a plain background</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="tip-card">
                                    <div class="tip-header dont-header d-flex align-items-center">
                                        <i data-feather="x" class="me-2"></i>
                                        <span>DON'T</span>
                                    </div>
                                    <div class="tip-body">
                                        <ul class="mb-0 ps-3">
                                            <li>Tilt your head at extreme angles</li>
                                            <li>Wear sunglasses or heavy makeup</li>
                                            <li>Register in poor lighting conditions</li>
                                            <li>Use excessive filters or edits</li>
                                            <li>Have distracting backgrounds</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-light p-3 rounded-3 mt-4">
                                <div class="d-flex align-items-start">
                                    <i data-feather="alert-triangle" class="text-warning me-2 mt-1"></i>
                                    <p class="mb-0"><strong>Important Note:</strong> For privacy and security reasons,
                                        your facial data is encrypted and stored securely. It's only used for attendance
                                        verification purposes and is not shared with third parties.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
