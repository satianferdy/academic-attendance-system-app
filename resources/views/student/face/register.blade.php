@extends('layouts.app')

@section('title', 'Register Face')

@push('styles')
    <style>
        /* Style untuk video dan tombol capture */
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

        /* Style untuk grid thumbnail */
        .thumbnail-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-top: 1rem;
        }

        /* Style untuk thumbnail */
        .thumbnail-item {
            position: relative;
            border: 2px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .thumbnail-item.good-quality {
            border-color: #28a745;
            /* Hijau untuk kualitas baik */
        }

        .thumbnail-item.bad-quality {
            border-color: #dc3545;
            /* Merah untuk kualitas jelek */
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
        }

        .delete-btn {
            color: white;
            cursor: pointer;
        }

        .quality-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #28a745;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
        }

        /* Style untuk loading overlay */
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
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h6>Register Your Face</h6>
                        <a href="{{ route('student.face.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                    </div>
                    <div class="card-body px-4 pt-4 pb-2">
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            Please position your face clearly in the camera frame. Ensure good lighting and remove glasses
                            or face coverings.
                        </div>

                        <div id="error-message" class="alert alert-danger" style="display: none;"></div>

                        <div class="row">
                            <div class="col-md-8 mx-auto">
                                <div class="camera-container">
                                    <div id="video-container">
                                        <video id="video" autoplay playsinline></video>
                                        <div class="camera-overlay"></div>
                                        <button id="capture-btn" class="btn btn-primary btn-lg rounded-circle"
                                            title="Take Photo">
                                            <i class="fas fa-camera"></i>
                                            <span class="badge bg-danger">{{ $remainingShots }}/5</span>
                                        </button>
                                    </div>

                                    <div class="thumbnail-grid" id="thumbnail-grid"></div>

                                    <div class="preview-container text-center mt-4" id="preview-container">
                                        <button id="submit-btn" class="btn btn-success" disabled>
                                            <i class="fas fa-check me-1"></i> Register Face
                                        </button>
                                    </div>
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
            const loadingOverlay = document.getElementById('loading-overlay');
            const faceForm = document.getElementById('face-form');

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

                try {
                    const response = await validateImageQuality(tempImage.dataURL);

                    // Perbaikan: Akses data yang benar dari response
                    if (response.status === 'success') {
                        tempImage.quality = response.data.quality_metrics.blur_score;
                        tempImage.isGoodQuality = response.data.quality_metrics.blur_score >= 70;
                        tempImage.status = 'processed';
                    } else {
                        tempImage.status = 'error';
                        tempImage.message = response.message;
                        tempImage.isGoodQuality = false;
                    }
                } catch (error) {
                    tempImage.status = 'error';
                    tempImage.message = error.message;
                    tempImage.isGoodQuality = false;
                }

                updateUI();
            });

            // Validate image quality via API
            async function validateImageQuality(dataURL) {
                const blob = dataURLtoBlob(dataURL);
                const formData = new FormData();
                formData.append('image', blob, 'face.jpg');

                // Gunakan route name yang benar
                const response = await fetch('{{ route('student.face.validate-quality') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-API-Key': '{{ config('services.face_recognition.key') }}' // Tambahkan header API key
                    },
                    body: formData
                });

                if (!response.ok) {
                    throw new Error('Failed to validate image quality');
                }

                return response.json();
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
                            ${!shot.isGoodQuality ? `
                                                <button class="btn btn-danger btn-sm delete-btn" onclick="deleteShot(${shot.id})">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            ` : ''}
                        </div>
                        ${shot.isGoodQuality ? `
                                            <div class="quality-badge">
                                                <i class="fas fa-check-circle"></i> Good Quality
                                            </div>
                                        ` : ''}
                    </div>
                `).join('');

                captureBtn.querySelector('.badge').textContent = `${remainingShots}/5`;
                captureBtn.disabled = remainingShots === 0;

                const allValid = capturedShots.every(shot => shot.isGoodQuality);
                submitBtn.disabled = !(capturedShots.length === MAX_SHOTS && allValid);
            }

            // Delete a captured shot
            window.deleteShot = function(id) {
                capturedShots = capturedShots.filter(shot => shot.id !== id);
                remainingShots = MAX_SHOTS - capturedShots.length;
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

            // Start the camera when the page loads
            startCamera();
        });
    </script>
@endpush
