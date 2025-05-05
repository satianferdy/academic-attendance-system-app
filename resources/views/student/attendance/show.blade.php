@extends('layouts.app')

@section('title', 'Attendance Verification')

@push('styles')
    <style>
        .dashboard-container {
            width: 100%;
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

        .camera-icon {
            background-color: #8C9EFF;
        }

        .course-icon {
            background-color: #4DD0E1;
        }

        .icon-inner {
            width: 20px;
            height: 20px;
            color: white;
        }

        .camera-container,
        #video-container {
            position: relative;
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
            border-radius: 10px;
            overflow: hidden;
        }

        #video {
            width: 100%;
            display: block;
            /* This ensures no extra space */
            border-radius: 10px;
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
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
            border-radius: 50%;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
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
            z-index: 5;
        }

        .preview-container {
            display: none;
            margin-top: 20px;
            text-align: center;
        }

        #preview-image {
            max-width: 100%;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
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
            padding: 2rem;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
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

        .info-box {
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .course-info {
            background-color: rgba(77, 208, 225, 0.1);
            border-left: 4px solid #4DD0E1;
        }

        .instruction-box {
            background-color: rgba(140, 158, 255, 0.1);
            border-left: 4px solid #8C9EFF;
        }

        .tips-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .tips-icon {
            margin-right: 15px;
            min-width: 24px;
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
    <div class="dashboard-container">
        <!-- Alerts -->
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
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="text-white mb-0">Attendance Verification</h4>
            </div>
        </div>

        <!-- Content Area -->
        <div class="dashboard-content px-3">
            <div class="row g-4">
                <!-- Course Information -->
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0 d-flex align-items-center">
                            <div class="custom-icon course-icon me-2">
                                <i data-feather="book" class="icon-inner"></i>
                            </div>
                            <h5 class="mb-0">COURSE INFORMATION</h5>
                        </div>
                        <div class="card-body">
                            <div class="info-box course-info p-3">
                                <div class="d-flex align-items-center mb-3">
                                    <i data-feather="book" class="text-primary me-3"></i>
                                    <div>
                                        <p class="mb-0 font-weight-bold">{{ $classSchedule->course->name }}</p>
                                        <p class="text-sm text-secondary mb-0">{{ $classSchedule->course->code }}</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <i data-feather="user" class="text-info me-3"></i>
                                    <div>
                                        <p class="mb-0 font-weight-bold">{{ $classSchedule->lecturer->user->name }}</p>
                                        <p class="text-sm text-secondary mb-0">Lecturer</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i data-feather="calendar" class="text-warning me-3"></i>
                                    <div>
                                        <p class="mb-0 font-weight-bold">
                                            {{ \Carbon\Carbon::parse($date)->format('l, d F Y') }}</p>
                                        <p class="text-sm text-secondary mb-0">Session Date</p>
                                    </div>
                                </div>
                            </div>

                            <div class="info-box instruction-box p-3">
                                <h6 class="mb-3 d-flex align-items-center">
                                    <i data-feather="info" class="text-primary me-2"></i>
                                    Instructions
                                </h6>
                                <ol class="ps-3 mb-0">
                                    <li class="mb-2">Ensure you are in a well-lit area with your face clearly visible</li>
                                    <li class="mb-2">Position your face within the camera frame</li>
                                    <li class="mb-2">Click the camera button to take your photo</li>
                                    <li class="mb-2">Review your photo and submit for verification</li>
                                    <li>Wait for the system to verify your identity</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Camera Section -->
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0 d-flex align-items-center">
                            <div class="custom-icon camera-icon me-2">
                                <i data-feather="camera" class="icon-inner"></i>
                            </div>
                            <h5 class="mb-0">FACE VERIFICATION</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-3">
                                <div class="d-flex align-items-start">
                                    <i data-feather="alert-circle" class="me-2 mt-1"></i>
                                    <span>Your photo will be compared with your registered face to verify your
                                        attendance</span>
                                </div>
                            </div>

                            <div id="error-message" class="alert alert-danger alert-dismissible fade show mt-2"
                                style="display: none;" role="alert">
                                <div class="d-flex align-items-center">
                                    <i data-feather="alert-circle" class="me-2"></i>
                                    <span id="error-message-text"></span>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>

                            <div class="camera-container mb-3" id="video-container">
                                <video id="video" autoplay playsinline></video>
                                <div class="camera-overlay"></div>
                                <button id="capture-btn" class="btn btn-primary btn-icon rounded-circle" title="Take Photo">
                                    <i data-feather="camera"></i>
                                </button>
                            </div>
                            <canvas id="canvas"></canvas>

                            <div class="preview-container" id="preview-container">
                                <h5 class="mb-3">Preview</h5>
                                <img id="preview-image" class="mb-3">
                                <div class="mt-3">
                                    <button id="retake-btn" class="btn btn-icon-text btn-outline-secondary">
                                        <i class="btn-icon-prepend" data-feather="refresh-cw"></i> Retake
                                    </button>
                                    <button id="submit-btn" class="btn btn-success btn-icon-text">
                                        <i class="btn-icon-prepend" data-feather="check"></i> Verify
                                        Attendance
                                    </button>
                                </div>
                            </div>

                            <div class="success-animation mt-3" id="success-container">
                                <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                                    <circle class="checkmark__circle" cx="26" cy="26" r="25"
                                        fill="none" />
                                    <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
                                </svg>
                                <h3 class="text-success">Attendance Verified Successfully!</h3>
                                <p class="mb-4">Your attendance has been recorded for this session.</p>
                                <a href="{{ route('student.attendance.index') }}" class="btn btn-primary">
                                    <i data-feather="list" class="me-1"></i> View My Attendance
                                </a>
                            </div>

                            <div class="tips mt-3">
                                <div class="tips-item">
                                    <i data-feather="sun" class="tips-icon text-warning"></i>
                                    <div>
                                        <small class="text-muted">For better results, ensure good lighting and look
                                            directly at the camera.</small>
                                    </div>
                                </div>
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
            // Initialize Feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }

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
            const errorMessageText = document.getElementById('error-message-text');
            const loadingOverlay = document.getElementById('loading-overlay');

            let stream;

            // Add this function at the top of your script
            function showError(message, timeout = 5000) {
                errorMessageText.textContent = message;
                errorMessage.style.display = 'block';

                // Optional: scroll to error message to ensure visibility
                if (typeof errorMessage.scrollIntoView === 'function') {
                    errorMessage.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                }
            }

            // Function to get available video devices and select the appropriate one
            async function getVideoDevices() {
                try {
                    // Get all media devices
                    const devices = await navigator.mediaDevices.enumerateDevices();

                    // Filter for video input devices (cameras)
                    const videoDevices = devices.filter(device => device.kind === 'videoinput');

                    // console.log('All available video devices:', videoDevices);

                    if (videoDevices.length === 0) {
                        throw new Error('No video devices found');
                    }

                    // Create a list of device labels and IDs for inspection
                    const deviceList = videoDevices.map((device, index) => {
                        return {
                            index: index,
                            label: device.label || `Camera ${index + 1}`,
                            id: device.deviceId
                        };
                    });

                    // console.log('Device list:', deviceList);

                    // Filter out OBS and other virtual cameras
                    const realCameras = videoDevices.filter(device => {
                        const label = (device.label || '').toLowerCase();
                        return !label.includes('obs') &&
                            !label.includes('virtual') &&
                            !label.includes('screen') &&
                            !label.includes('display');
                    });

                    // console.log('Filtered real cameras:', realCameras);

                    // If we have real cameras, use the last one (likely external)
                    if (realCameras.length > 0) {
                        return realCameras[realCameras.length - 1].deviceId;
                    }

                    // If no real cameras detected, use the first available camera
                    return videoDevices[0].deviceId;
                } catch (err) {
                    console.error('Error enumerating devices:', err);
                    return null;
                }
            }

            // Function to add a camera selection dropdown
            async function addCameraSelector() {
                try {
                    const devices = await navigator.mediaDevices.enumerateDevices();
                    const videoDevices = devices.filter(device => device.kind === 'videoinput');

                    if (videoDevices.length <= 1) {
                        return; // No need for selector if we have only one camera
                    }

                    // Create the selector container
                    const selectorContainer = document.createElement('div');
                    selectorContainer.className = 'mb-3';

                    // Create a label
                    const label = document.createElement('label');
                    label.className = 'form-label';
                    label.textContent = 'Select Camera:';
                    selectorContainer.appendChild(label);

                    // Create the select element
                    const select = document.createElement('select');
                    select.className = 'form-select';
                    select.id = 'camera-selector';

                    // Add options for each camera
                    videoDevices.forEach((device, index) => {
                        const option = document.createElement('option');
                        option.value = device.deviceId;
                        option.textContent = device.label || `Camera ${index + 1}`;
                        select.appendChild(option);
                    });

                    selectorContainer.appendChild(select);

                    // Insert before the camera container
                    videoContainer.parentNode.insertBefore(selectorContainer, videoContainer);

                    // Add event listener to change camera
                    select.addEventListener('change', async function() {
                        // Stop current stream
                        if (stream) {
                            stream.getTracks().forEach(track => track.stop());
                        }

                        // Start new stream with selected device
                        try {
                            stream = await navigator.mediaDevices.getUserMedia({
                                video: {
                                    deviceId: {
                                        exact: this.value
                                    },
                                    width: {
                                        ideal: 1280
                                    },
                                    height: {
                                        ideal: 720
                                    }
                                }
                            });
                            video.srcObject = stream;
                        } catch (err) {
                            // console.error('Error switching camera:', err);
                            errorMessageText.textContent = 'Error switching camera: ' + err.message;
                            errorMessage.style.display = 'block';
                        }
                    });
                } catch (err) {
                    console.error('Error creating camera selector:', err);
                }
            }

            // Start the camera with the selected device
            async function startCamera() {
                try {
                    // Get the preferred camera device ID
                    const deviceId = await getVideoDevices();

                    // Create camera selector dropdown
                    await addCameraSelector();

                    // Configure constraints based on available devices
                    const constraints = {
                        video: {
                            facingMode: 'user', // Use front camera
                            width: {
                                ideal: 1280
                            },
                            height: {
                                ideal: 720
                            }
                        }
                    };

                    // If we have a specific device ID, use it
                    if (deviceId) {
                        constraints.video.deviceId = {
                            exact: deviceId
                        };
                    }

                    // Get media stream with our constraints
                    stream = await navigator.mediaDevices.getUserMedia(constraints);
                    video.srcObject = stream;
                    captureBtn.disabled = false;

                    // Update the camera selector to show the current device
                    const cameraSelector = document.getElementById('camera-selector');
                    if (cameraSelector && deviceId) {
                        cameraSelector.value = deviceId;
                    }

                    // console.log('Camera started with device ID:', deviceId);
                } catch (err) {
                    console.error('Camera access error:', err);

                    // If specific device fails, try again without specifying device
                    if (err.name === 'OverconstrainedError' || err.name === 'ConstraintNotSatisfiedError') {
                        try {
                            // console.log('Falling back to default camera');
                            stream = await navigator.mediaDevices.getUserMedia({
                                video: true
                            });
                            video.srcObject = stream;
                            captureBtn.disabled = false;
                        } catch (fallbackErr) {
                            errorMessageText.textContent = 'Error accessing camera: ' + fallbackErr.message;
                            errorMessage.style.display = 'block';
                            captureBtn.disabled = true;
                        }
                    } else {
                        errorMessageText.textContent = 'Error accessing camera: ' + err.message;
                        errorMessage.style.display = 'block';
                        captureBtn.disabled = true;
                    }
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
                errorMessage.style.display = 'none'; // Hide any previous error

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
                        // console.log('Response data:', data);
                        loadingOverlay.style.display = 'none';

                        if (data.success === true || data.status === 'success') {
                            // Show SweetAlert
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: data.message || 'Attendance verified successfully.',
                                showConfirmButton: false,
                                timer: 2000 // Auto close after 2 seconds
                            }).then(() => {
                                // Redirect to the attendance index page
                                window.location.href =
                                    "{{ route('student.attendance.index') }}";
                            });
                        } else {
                            // Just display the error message from the server
                            const errorMsg = data.message || 'Verification failed. Please try again.';
                            // Show the error message
                            showError(errorMsg);

                            videoContainer.style.display = 'block';
                            previewContainer.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        // console.error('Fetch error:', error);
                        loadingOverlay.style.display = 'none';

                        // Show a generic error message for network or parsing errors
                        showError('A network error occurred during verification. Please try again.');

                        // Return to camera view
                        videoContainer.style.display = 'block';
                        previewContainer.style.display = 'none';
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
