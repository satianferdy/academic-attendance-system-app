@extends('layouts.app')

@section('title', isset($isUpdate) ? 'Perbarui Wajah' : 'Daftarkan Wajah')

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

        .icon-inner {
            width: 20px;
            height: 20px;
            color: white;
        }

        /* Video and capture button styles */
        #video-container {
            position: relative;
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        #video {
            width: 100%;
            border-radius: 10px;
        }

        #capture-btn {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            width: 50px;
            height: 50px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #capture-btn .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.7rem;
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

        /* Thumbnail grid styles */
        .thumbnail-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-top: 1.5rem;
        }

        /* Thumbnail styles */
        .thumbnail-item {
            position: relative;
            border: 2px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 0.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .thumbnail-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .thumbnail-item.good-quality {
            border-color: #198754;
        }

        .thumbnail-item.bad-quality {
            border-color: #dc3545;
        }

        .thumbnail-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .thumbnail-actions {
            position: absolute;
            bottom: 0;
            width: 100%;
            background: rgba(0, 0, 0, 0.6);
            padding: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .quality-indicator {
            color: white;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .quality-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #198754;
            color: white;
            padding: 0.25rem 0.25rem;
            border-radius: 15px;
            font-size: 0.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        /* Loading overlay styles */
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

        /* Step indicator */
        .registration-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .step-indicator {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 1rem;
        }

        .step-number {
            width: 30px;
            height: 30px;
            background-color: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .step-number.active {
            background-color: #0d6efd;
            color: white;
        }

        .step-text {
            font-size: 0.8rem;
            text-align: center;
            color: #6c757d;
        }

        .step-text.active {
            color: #0d6efd;
            font-weight: 500;
        }

        #submit-btn:disabled {
            cursor: not-allowed;
            opacity: 0.5;
        }

        /* Info card styles */
        .info-card {
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 5px;
        }

        .info-card p {
            margin-bottom: 0;
            font-size: 0.9rem;
        }

        /* Previous face image styles */
        .previous-face {
            max-width: 100%;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Update notification */
        .update-notification {
            background-color: rgba(13, 110, 253, 0.1);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #0d6efd;
        }
    </style>
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">General</a></li>
            <li class="breadcrumb-item active" aria-current="page">Pendaftaran Wajah</li>
        </ol>
    </nav>

    <div class="dashboard-container">
        <!-- Content Area -->
        <div class="dashboard-content">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="custom-icon user-icon me-2">
                            <i data-feather="user" class="icon-inner"></i>
                        </div>
                        <h5 class="mb-0">{{ isset($isUpdate) ? 'UPDATE YOUR FACE' : 'REGISTER YOUR FACE' }}</h5>
                    </div>
                    <a href="{{ route('student.face.index') }}" class="btn btn-icon-text btn-sm btn-outline-secondary">
                        <i class="btn-icon-prepend" data-feather="chevron-left"></i>Kembali
                    </a>
                </div>

                <div class="card-body pt-2">
                    <div class="registration-steps mb-4">
                        <div class="step-indicator">
                            <div class="step-number active">1</div>
                            <div class="step-text active">Posisikan Wajah</div>
                        </div>
                        <div class="step-indicator">
                            <div class="step-number">2</div>
                            <div class="step-text">Ambil Foto</div>
                        </div>
                        <div class="step-indicator">
                            <div class="step-number">3</div>
                            <div class="step-text">Verifikasi Kualitas</div>
                        </div>
                        <div class="step-indicator">
                            <div class="step-number">4</div>
                            <div class="step-text">Selesai</div>
                        </div>
                    </div>

                    @if (isset($isUpdate) && $isUpdate)
                        <div class="update-notification d-flex align-items-center mb-4">
                            <i data-feather="refresh-cw" class="me-3 text-primary"></i>
                            <div>
                                <h6 class="mb-1">Mode Pembaruan Wajah</h6>
                                <p class="mb-0">Anda sedang memperbarui pendaftaran wajah berdasarkan permintaan yang
                                    telah disetujui. Ambil
                                    5 foto baru untuk memperbarui data wajah Anda.</p>
                            </div>
                        </div>
                    @endif

                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <div>
                            Posisikan wajah Anda dengan jelas di dalam bingkai kamera. Pastikan pencahayaan baik dan
                            lepaskan kacamata atau penutup wajah untuk akurasi yang lebih baik.
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mx-auto">
                            <div class="info-card mb-3">
                                <p><strong>Petunjuk:</strong> Ambil 5 foto wajah Anda dengan jelas dari sudut berbeda
                                    untuk hasil terbaik. Sistem akan secara otomatis menganalisis kualitas setiap gambar.
                                </p>
                            </div>

                            <!-- Error message container -->
                            <div id="error-message" class="alert alert-danger mt-3" style="display: none;" role="alert">
                                <span id="error-text"></span>
                            </div>

                            <div class="camera-container">
                                <div id="video-container">
                                    <video id="video" autoplay playsinline></video>
                                    <div class="camera-overlay"></div>
                                    <button id="capture-btn" class="btn btn-outline-primary rounded-circle"
                                        title="Take Photo">
                                        <i data-feather="camera"></i>
                                        <span class="badge bg-danger">{{ $remainingShots }}/5</span>
                                    </button>
                                </div>

                                <div class="thumbnail-grid" id="thumbnail-grid"></div>

                                <div class="preview-container text-center mt-4" id="preview-container">
                                    <button id="submit-btn" class="btn btn-icon-text btn-sm btn-success" disabled>
                                        <i data-feather="send"
                                            class="btn-icon-prepend"></i>{{ isset($isUpdate) ? 'Perbarui Wajah' : 'Daftarkan Wajah' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loading-overlay">
        <div class="spinner-border text-light spinner mb-3" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h5 class="text-light">Processing...</h5>
    </div>

    <!-- Hidden Form -->
    <form id="face-form" style="display: none;">
        @csrf
        <input type="hidden" name="redirect_url" value="{{ $redirectUrl }}">
        @if (isset($isUpdate) && $isUpdate)
            <input type="hidden" name="is_update" value="1">
            <input type="hidden" name="update_request_id" value="{{ $updateRequest->id }}">
        @endif
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const MAX_SHOTS = 5;
            let capturedShots = [];
            let remainingShots = MAX_SHOTS;

            const video = document.getElementById('video');
            const captureBtn = document.getElementById('capture-btn');
            const submitBtn = document.getElementById('submit-btn');
            const thumbnailGrid = document.getElementById('thumbnail-grid');
            const errorMessage = document.getElementById('error-message');
            const errorText = document.getElementById('error-text');
            const loadingOverlay = document.getElementById('loading-overlay');
            const faceForm = document.getElementById('face-form');
            const stepNumbers = document.querySelectorAll('.step-number');
            const stepTexts = document.querySelectorAll('.step-text');

            let stream;

            // Update step indicators
            function updateStepIndicators(step) {
                stepNumbers.forEach((el, index) => {
                    if (index < step) {
                        el.classList.add('active');
                    } else {
                        el.classList.remove('active');
                    }
                });

                stepTexts.forEach((el, index) => {
                    if (index < step) {
                        el.classList.add('active');
                    } else {
                        el.classList.remove('active');
                    }
                });
            }

            // Start the camera
            async function startCamera() {
                try {
                    // Check if mediaDevices is supported
                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        throw new Error('Your browser does not support camera access');
                    }

                    let videoDevices = [];
                    let cameraSelect = null;

                    // Try to enumerate devices only if the method exists
                    if (navigator.mediaDevices.enumerateDevices) {
                        try {
                            // Request permissions first on iOS Safari
                            await navigator.mediaDevices.getUserMedia({
                                video: true
                            });

                            // Now try to enumerate devices
                            const devices = await navigator.mediaDevices.enumerateDevices();
                            videoDevices = devices.filter(device => device.kind === 'videoinput');

                            // Only create camera selector if we have multiple cameras
                            if (videoDevices.length > 1) {
                                cameraSelect = document.createElement('select');
                                cameraSelect.id = 'camera-select';
                                cameraSelect.className = 'form-select form-select-sm mb-3';

                                videoDevices.forEach((device, index) => {
                                    const option = document.createElement('option');
                                    option.value = device.deviceId;
                                    option.text = device.label || `Camera ${index + 1}`;
                                    cameraSelect.appendChild(option);
                                });

                                // Add camera selector to DOM
                                const cameraContainer = document.querySelector('.camera-container');
                                cameraContainer.insertBefore(cameraSelect, document.getElementById(
                                    'video-container'));

                                // Add change event listener
                                cameraSelect.addEventListener('change', function() {
                                    startVideoStream(this.value);
                                });
                            }
                        } catch (enumError) {
                            console.warn('Could not enumerate devices:', enumError);
                            // Continue with default camera if enumeration fails
                        }
                    }

                    // Function to start video with specific device ID
                    async function startVideoStream(deviceId = null) {
                        if (stream) {
                            stream.getTracks().forEach(track => track.stop());
                        }

                        // For iOS, prefer the environment camera (rear) first if available
                        const constraints = {
                            video: {
                                facingMode: 'user' // Default to front camera
                            }
                        };

                        // If we have a specific device ID, use it
                        if (deviceId) {
                            constraints.video.deviceId = {
                                exact: deviceId
                            };
                        }

                        stream = await navigator.mediaDevices.getUserMedia(constraints);
                        video.srcObject = stream;
                        captureBtn.disabled = false;
                    }

                    // Start with first camera or default
                    await startVideoStream(videoDevices.length > 0 ? videoDevices[0].deviceId : null);
                    updateStepIndicators(1);

                } catch (err) {
                    errorText.textContent = 'Error accessing camera: ' + err.message;
                    errorMessage.style.display = 'flex';
                    captureBtn.disabled = true;
                }
            }

            // Capture the image
            captureBtn.addEventListener('click', async function() {
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;

                const context = canvas.getContext('2d');
                context.drawImage(video, 0, 0, canvas.width, canvas.height);

                const tempImage = {
                    id: Date.now(),
                    dataURL: canvas.toDataURL('image/jpeg', 0.9),
                    quality: null,
                    status: 'pending',
                    isGoodQuality: false
                };

                capturedShots.push(tempImage);
                remainingShots = MAX_SHOTS - capturedShots.length;
                updateUI();

                if (capturedShots.length > 0) {
                    updateStepIndicators(2);
                }

                try {
                    const response = await validateImageQuality(tempImage.dataURL);

                    // Fixing: Access the correct data from response
                    if (response.status === 'success') {
                        tempImage.quality = response.data.quality_metrics.blur_score;
                        tempImage.isGoodQuality = response.data.quality_metrics.blur_score >= 50;
                        tempImage.status = 'processed';

                        if (capturedShots.filter(shot => shot.isGoodQuality).length > 0) {
                            updateStepIndicators(3);
                        }
                    } else {
                        tempImage.status = 'error';
                        tempImage.message = response.message;
                        tempImage.isGoodQuality = false;

                        // Remove the failed shot from the array
                        capturedShots = capturedShots.filter(shot => shot.id !== tempImage.id);
                        remainingShots = MAX_SHOTS - capturedShots.length;
                    }
                } catch (error) {
                    tempImage.status = 'error';
                    tempImage.message = error.message;
                    tempImage.isGoodQuality = false;

                    // Remove the failed shot from the array
                    capturedShots = capturedShots.filter(shot => shot.id !== tempImage.id);
                    remainingShots = MAX_SHOTS - capturedShots.length;
                }

                updateUI();
            });

            // Improve the error display function
            function showError(message, timeout = 5000) {
                errorText.textContent = message;
                errorMessage.style.display = 'flex';

                // Hide after timeout
                if (timeout > 0) {
                    setTimeout(() => {
                        errorMessage.style.display = 'none';
                    }, timeout);
                }
            }

            // Validate image quality via API
            async function validateImageQuality(dataURL) {
                const blob = dataURLtoBlob(dataURL);
                const formData = new FormData();
                formData.append('image', blob, 'face.jpg');

                try {
                    const response = await fetch('{{ route('student.face.validate-quality') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-API-Key': '{{ config('services.face_recognition.key') }}'
                        },
                        body: formData
                    });

                    const result = await response.json();

                    if (result.status === 'error') {
                        showError(result.message || 'Failed to validate image');
                        throw new Error(result.message || 'Failed to validate image');
                    }

                    return result;
                } catch (error) {
                    console.error('Validation error:', error);
                    throw error;
                }
            }

            // Update UI based on captured shots
            function updateUI() {
                thumbnailGrid.innerHTML = capturedShots.map(shot => `
                <div class="thumbnail-item ${shot.isGoodQuality ? 'good-quality' : 'bad-quality'}">
                    <img src="${shot.dataURL}" alt="Captured face">
                    <div class="thumbnail-actions">
                        <span class="quality-indicator">
                            ${shot.quality ? `Quality: ${Math.round(shot.quality)}%` : 'Checking...'}
                        </span>
                        ${!shot.isGoodQuality ? `<button class="btn btn-danger btn-icon btn-sm" onclick="deleteShot(${shot.id})"><i data-feather="trash-2"></i></button>` : ''}
                    </div>
                    ${shot.isGoodQuality ? `<div class="quality-badge"><i data-feather="check-circle"></i></div>` : ''}
                </div>
            `).join('');

                captureBtn.querySelector('.badge').textContent = `${remainingShots}/5`;
                captureBtn.disabled = remainingShots === 0;

                const allValid = capturedShots.every(shot => shot.isGoodQuality);
                submitBtn.disabled = !(capturedShots.length === MAX_SHOTS && allValid);

                if (capturedShots.length === MAX_SHOTS && allValid) {
                    updateStepIndicators(4);
                }

                // Re-initialize feather icons for newly added DOM elements
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
            }

            // Delete a captured shot
            window.deleteShot = function(id) {
                capturedShots = capturedShots.filter(shot => shot.id !== id);
                remainingShots = MAX_SHOTS - capturedShots.length;

                if (capturedShots.length === 0) {
                    updateStepIndicators(1);
                } else if (!capturedShots.some(shot => shot.isGoodQuality)) {
                    updateStepIndicators(2);
                }

                updateUI();
            };

            // Convert dataURL to Blob
            function dataURLtoBlob(dataURL) {
                const byteString = atob(dataURL.split(',')[1]);
                const mimeString = dataURL.split(',')[0].split(':')[1].split(';')[0];
                const ab = new ArrayBuffer(byteString.length);
                const ia = new Uint8Array(ab);

                for (let i = 0; i < byteString.length; i++) {
                    ia[i] = byteString.charCodeAt(i);
                }

                return new Blob([ab], {
                    type: mimeString
                });
            }

            // Submit registration
            submitBtn.addEventListener('click', async function() {
                loadingOverlay.style.display = 'flex';

                try {
                    const formData = new FormData(faceForm);

                    // Add each good quality image to the form data
                    capturedShots.filter(shot => shot.isGoodQuality).forEach((shot, index) => {
                        const blob = dataURLtoBlob(shot.dataURL);
                        formData.append(`images[${index}]`, blob, `face_${index}.jpg`);
                    });

                    const response = await fetch('{{ route('student.face.store') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content,
                        },
                        body: formData
                    });

                    const result = await response.json();

                    if (result.status === 'success') {
                        window.location.href = result.redirect_url ||
                            '{{ route('student.face.index') }}';
                    } else {
                        throw new Error(result.message || 'Failed to register face');
                    }
                } catch (error) {
                    loadingOverlay.style.display = 'none';
                    showError(error.message, 0); // 0 means don't auto-hide
                }
            });

            // Start the camera when the page loads
            startCamera();
        });
    </script>
@endpush
