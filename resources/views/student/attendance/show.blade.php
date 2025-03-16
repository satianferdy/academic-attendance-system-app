@extends('layouts.app')

@section('title', 'Attendance Verification')

@push('styles')
    <style>
        #video-container {
            position: relative;
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
        }

        #video {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        #canvas {
            display: none;
        }

        #capture-btn {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
        }

        .camera-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 10px;
            border: 2px solid #ced4da;
            box-sizing: border-box;
            pointer-events: none;
        }

        .preview-container {
            display: none;
            margin-top: 20px;
        }

        #preview-image {
            max-width: 100%;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .spinner {
            width: 4rem;
            height: 4rem;
        }

        .success-animation {
            display: none;
            text-align: center;
        }

        .checkmark {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: block;
            stroke-width: 2;
            stroke: #4bb71b;
            stroke-miterlimit: 10;
            box-shadow: inset 0px 0px 0px #4bb71b;
            animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
            margin: 0 auto 20px;
        }

        .checkmark__circle {
            stroke-dasharray: 166;
            stroke-dashoffset: 166;
            stroke-width: 2;
            stroke-miterlimit: 10;
            stroke: #4bb71b;
            fill: none;
            animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }

        .checkmark__check {
            transform-origin: 50% 50%;
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
        }

        @keyframes stroke {
            100% {
                stroke-dashoffset: 0;
            }
        }

        @keyframes scale {

            0%,
            100% {
                transform: none;
            }

            50% {
                transform: scale3d(1.1, 1.1, 1);
            }
        }

        @keyframes fill {
            100% {
                box-shadow: inset 0px 0px 0px 30px #4bb71b;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h6>Verify Attendance</h6>
                        <a href="{{ route('student.attendance.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Attendance
                        </a>
                    </div>
                    <div class="card-body px-4 pt-4 pb-2">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title text-uppercase text-secondary mb-3">Course Information</h6>
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="icon-shape icon-shape-primary rounded me-3">
                                                <i class="fas fa-book"></i>
                                            </div>
                                            <div>
                                                <p class="mb-0 font-weight-bold">{{ $classSchedule->course->name }}</p>
                                                <p class="text-sm text-secondary mb-0">{{ $classSchedule->course->code }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="icon-shape icon-shape-info rounded me-3">
                                                <i class="fas fa-user-tie"></i>
                                            </div>
                                            <div>
                                                <p class="mb-0 font-weight-bold">{{ $classSchedule->lecturer->user->name }}
                                                </p>
                                                <p class="text-sm text-secondary mb-0">Lecturer</p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape icon-shape-warning rounded me-3">
                                                <i class="fas fa-calendar-alt"></i>
                                            </div>
                                            <div>
                                                <p class="mb-0 font-weight-bold">
                                                    {{ \Carbon\Carbon::parse($date)->format('l, d F Y') }}</p>
                                                <p class="text-sm text-secondary mb-0">Session Date</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title text-uppercase text-secondary mb-3">Instructions</h6>
                                        <ol class="ps-3">
                                            <li class="mb-2">Ensure you are in a well-lit area with your face clearly
                                                visible</li>
                                            <li class="mb-2">Position your face within the camera frame</li>
                                            <li class="mb-2">Click the camera button to take your photo</li>
                                            <li class="mb-2">Review your photo and submit for verification</li>
                                            <li>Wait for the system to verify your identity</li>
                                        </ol>
                                        <div class="alert alert-warning mt-3 mb-0">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            Your photo will be compared with your registered face to verify your attendance
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="error-message" class="alert alert-danger" style="display: none;"></div>

                        <div class="camera-container">
                            <div id="video-container">
                                <video id="video" autoplay playsinline></video>
                                <div class="camera-overlay"></div>
                                <button id="capture-btn" class="btn btn-primary btn-lg rounded-circle" title="Take Photo">
                                    <i class="fas fa-camera"></i>
                                </button>
                            </div>
                            <canvas id="canvas"></canvas>

                            <div class="preview-container text-center" id="preview-container">
                                <h5 class="mb-3">Preview</h5>
                                <img id="preview-image" class="mb-3">
                                <div class="mt-3">
                                    <button id="retake-btn" class="btn btn-outline-secondary me-2">
                                        <i class="fas fa-redo me-1"></i> Retake
                                    </button>
                                    <button id="submit-btn" class="btn btn-success">
                                        <i class="fas fa-check me-1"></i> Verify Attendance
                                    </button>
                                </div>
                            </div>

                            <div class="success-animation mt-5" id="success-container">
                                <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                                    <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none" />
                                    <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
                                </svg>
                                <h3 class="text-success">Attendance Verified Successfully!</h3>
                                <p class="mb-4">Your attendance has been recorded for this session.</p>
                                <a href="{{ route('student.attendance.index') }}" class="btn btn-primary">
                                    <i class="fas fa-list me-1"></i> View My Attendance
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="loading-overlay" id="loading-overlay">
        <div class="spinner-border text-light spinner mb-3" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h5 class="text-light">Verifying...</h5>
    </div>

    <form id="verify-form" style="display: none;">
        @csrf
        <input type="hidden" name="image" id="image-data">
        <input type="hidden" name="token" value="{{ $token }}">
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const captureBtn = document.getElementById('capture-btn');
            const retakeBtn = document.getElementById('retake-btn');
            const submitBtn = document.getElementById('submit-btn');
            const previewImage = document.getElementById('preview-image');
            const previewContainer = document.getElementById('preview-container');
            const videoContainer = document.getElementById('video-container');
            const successContainer = document.getElementById('success-container');
            const imageData = document.getElementById('image-data');
            const errorMessage = document.getElementById('error-message');
            const loadingOverlay = document.getElementById('loading-overlay');

            let stream;

            // Start the camera
            async function startCamera() {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'user',
                            width: {
                                ideal: 1280
                            },
                            height: {
                                ideal: 720
                            }
                        }
                    });
                    video.srcObject = stream;
                    captureBtn.disabled = false;
                } catch (err) {
                    errorMessage.textContent = 'Error accessing camera: ' + err.message;
                    errorMessage.style.display = 'block';
                    captureBtn.disabled = true;
                }
            }

            // Capture the image
            captureBtn.addEventListener('click', function() {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;

                const context = canvas.getContext('2d');
                context.drawImage(video, 0, 0, canvas.width, canvas.height);

                previewImage.src = canvas.toDataURL('image/jpeg', 0.9);
                imageData.value = canvas.toDataURL('image/jpeg', 0.9);

                videoContainer.style.display = 'none';
                previewContainer.style.display = 'block';
            });

            // Retake the photo
            retakeBtn.addEventListener('click', function() {
                videoContainer.style.display = 'block';
                previewContainer.style.display = 'none';
                errorMessage.style.display = 'none';
            });

            // Submit the photo for attendance verification
            submitBtn.addEventListener('click', function() {
                loadingOverlay.style.display = 'flex';

                // Convert base64 to blob
                const base64 = imageData.value.split(',')[1];
                const byteCharacters = atob(base64);
                const byteArrays = [];

                for (let i = 0; i < byteCharacters.length; i += 512) {
                    const slice = byteCharacters.slice(i, i + 512);
                    const byteNumbers = new Array(slice.length);

                    for (let j = 0; j < slice.length; j++) {
                        byteNumbers[j] = slice.charCodeAt(j);
                    }

                    const byteArray = new Uint8Array(byteNumbers);
                    byteArrays.push(byteArray);
                }

                const blob = new Blob(byteArrays, {
                    type: 'image/jpeg'
                });

                // Create form data
                const formData = new FormData();
                formData.append('image', blob, 'capture.jpg');
                formData.append('token', document.querySelector('input[name="token"]').value);
                formData.append('_token', document.querySelector('input[name="_token"]').value);

                // Send the image to the server
                fetch('{{ route('student.attendance.verify') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        loadingOverlay.style.display = 'none';

                        if (data.success) {
                            previewContainer.style.display = 'none';
                            successContainer.style.display = 'block';
                        } else {
                            errorMessage.textContent = data.message ||
                                'Verification failed. Please try again.';
                            errorMessage.style.display = 'block';
                            videoContainer.style.display = 'block';
                            previewContainer.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        loadingOverlay.style.display = 'none';
                        errorMessage.textContent =
                            'An error occurred during verification. Please try again.';
                        errorMessage.style.display = 'block';
                        console.error('Error:', error);
                    });
            });

            // Initialize camera when page loads
            startCamera();

            // Clean up resources when leaving the page
            window.addEventListener('beforeunload', function() {
                if (stream) {
                    stream.getTracks().forEach(track => {
                        track.stop();
                    });
                }
            });
        });
    </script>
@endpush
