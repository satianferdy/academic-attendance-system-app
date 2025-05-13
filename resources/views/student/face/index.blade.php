@extends('layouts.app')

@section('title', 'Pendaftaran Wajah')

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
        }

        .info-icon {
            background-color: #4DD0E1;
        }

        .camera-icon {
            background-color: #8C9EFF;
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

        .bg-info-subtle {
            background-color: rgba(13, 202, 240, 0.1);
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

        .request-form {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            border-left: 4px solid #0d6efd;
        }

        .update-status {
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
        }

        .update-status.pending {
            background-color: rgba(255, 193, 7, 0.1);
            border-left-color: #ffc107;
        }

        .update-status.approved {
            background-color: rgba(25, 135, 84, 0.1);
            border-left-color: #198754;
        }

        .update-status.rejected {
            background-color: rgba(220, 53, 69, 0.1);
            border-left-color: #dc3545;
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
        {{-- <div class="dashboard-header bg-primary mb-4">
            <h4 class="text-white mb-0">Pendaftaran Wajah</h4>
        </div> --}}

        <!-- Content Area -->
        <div class="dashboard-content">
            <div class="row g-4">
                <!-- Registration Status Card -->
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0 d-flex align-items-center">
                            <div class="custom-icon user-icon me-2">
                                <i data-feather="user" class="icon-inner"></i>
                            </div>
                            <h5 class="mb-0">STATUS PENDAFTARAN WAJAH</h5>
                        </div>
                        <div class="card-body">
                            @if (!$student->face_registered)
                                <!-- Not Registered State -->
                                <div class="status-box p-4 rounded-3 mb-4 bg-warning-subtle">
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="badge bg-warning me-2">Tidak Terdaftar</span>
                                        <span class="text-warning fw-bold">Silakan Daftar Wajah Anda</span>
                                    </div>
                                    <p class="mb-3">Anda belum mendaftar wajah untuk kehadiran. Silakan klik tombol di
                                        bawah untuk mendaftar.</p>

                                    <div class="text-center">
                                        <a href="{{ route('student.face.register') }}"
                                            class="btn btn-sm btn-icon-text btn-primary" type="button">
                                            <i class="btn-icon-prepend" data-feather="user-plus"></i> Daftar Sekarang
                                        </a>
                                    </div>
                                </div>
                            @else
                                <!-- Face Registered State -->
                                <div class="status-box p-4 rounded-3 mb-4 bg-success-subtle">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-success me-2">Terdaftar</span>
                                        <span class="text-success fw-bold">Ready untuk Presensi</span>
                                    </div>
                                    <p class="mb-3">Wajah Anda telah terdaftar untuk kehadiran</p>
                                </div>

                                <!-- Check for Update Request Status -->
                                @if (isset($pendingRequest))
                                    <!-- Pending Update Request -->
                                    <div class="update-status pending">
                                        <div class="d-flex align-items-center mb-3">
                                            <i data-feather="clock" class="me-2 text-warning"></i>
                                            <h6 class="mb-0 text-warning">Request Pending!</h6>
                                        </div>
                                        <p class="mb-2">Request Anda untuk memperbarui data wajah sedang
                                            diproses.</p>
                                        <p class="mb-2"><strong>Submitted:</strong>
                                            {{ $pendingRequest->created_at->format('M d, Y') }}</p>
                                        <p class="mb-0"><strong>Reason:</strong> {{ $pendingRequest->reason }}</p>
                                    </div>
                                @elseif(isset($approvedRequest))
                                    <!-- Approved Update Request -->
                                    <div class="update-status approved">
                                        <div class="d-flex align-items-center mb-3">
                                            <i data-feather="check-circle" class="me-2 text-success"></i>
                                            <h6 class="mb-0 text-success">Request Disetujui!</h6>
                                        </div>
                                        <p class="mb-3">Request Anda untuk memperbarui data wajah telah
                                            disetujui.</p>
                                        @if ($approvedRequest->admin_notes)
                                            <p class="mb-3"><strong>Admin Notes:</strong>
                                                {{ $approvedRequest->admin_notes }}</p>
                                        @endif
                                        <div class="text-center">
                                            <a href="{{ route('student.face.update', $approvedRequest->id) }}"
                                                class="btn btn-sm btn-icon-text btn-success" type="button">
                                                <i class="btn-icon-prepend" data-feather="camera"></i> Perbarui Sekarang
                                            </a>
                                        </div>
                                    </div>
                                @elseif(isset($rejectedRequest))
                                    <!-- Rejected Update Request -->
                                    <div class="update-status rejected">
                                        <div class="d-flex align-items-center mb-3">
                                            <i data-feather="x-circle" class="me-2 text-danger"></i>
                                            <h6 class="mb-0 text-danger">Request Ditolak!</h6>
                                        </div>
                                        <p class="mb-2">Request Anda untuk memperbarui data wajah telah
                                            ditolak.</p>
                                        <p class="mb-3"><strong>Reason:</strong> {{ $rejectedRequest->admin_notes }}</p>

                                        <!-- Allow requesting again after rejection -->
                                        <div id="request-update-section">
                                            <button type="button" class="btn btn-sm btn-icon-text btn-outline-primary"
                                                id="show-request-form">
                                                <i class="btn-icon-prepend" data-feather="refresh-cw"></i> Request Now
                                            </button>
                                        </div>

                                        <!-- Hidden request form (will be shown on button click) -->
                                        <div class="request-form mt-3" id="update-request-form" style="display: none;">
                                            <h6 class="mb-3">Request Pembaruan Wajah</h6>
                                            <form action="{{ route('student.face.store-request') }}" method="POST">
                                                @csrf
                                                <div class="mb-3">
                                                    <label for="reason" class="form-label">Alasan Pembaruan <span
                                                            class="text-danger">*</span></label>
                                                    <textarea class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" rows="3"
                                                        required></textarea>
                                                    @error('reason')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="d-flex justify-content-end">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm me-2"
                                                        id="cancel-request">
                                                        Batal
                                                    </button>
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        Kirim
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @elseif(isset($completedRequest))
                                    <!-- Completed Update Request -->
                                    <div class="update-status approved">
                                        <div class="d-flex align-items-center mb-3">
                                            <i data-feather="check-circle" class="me-2 text-success"></i>
                                            <h6 class="mb-0 text-success">Pembaruan Wajah Selesai!</h6>
                                        </div>
                                        <p class="mb-2">Data wajah Anda telah diperbarui.
                                            {{ $completedRequest->updated_at->format('M d, Y') }}.</p>
                                        @if ($completedRequest->admin_notes)
                                            <p class="mb-3"><strong>Admin Notes:</strong>
                                                {{ $completedRequest->admin_notes }}</p>
                                        @endif

                                        <!-- Allow requesting again -->
                                        <div id="request-update-section" class="text-center mt-4">
                                            <p class="mb-3">Butuh pembaruan wajah lagi?</p>
                                            <button type="button" class="btn btn-sm btn-icon-text btn-outline-primary"
                                                id="show-request-form">
                                                <i data-feather="refresh-cw" class="btn-icon-prepend"></i> Request Now
                                            </button>
                                        </div>

                                        <div class="request-form mt-3" id="update-request-form" style="display: none;">
                                            <h6 class="mb-3">Request Pembaruan Wajah</h6>
                                            <form action="{{ route('student.face.store-request') }}" method="POST">
                                                @csrf
                                                <div class="mb-3">
                                                    <label for="reason" class="form-label">Alasan Pembaruan <span
                                                            class="text-danger">*</span></label>
                                                    <textarea class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" rows="3"
                                                        placeholder="Please explain why you need to update your face data" required></textarea>
                                                    @error('reason')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <div class="form-text">
                                                        Sertakan alasan yang jelas mengapa Anda perlu memperbarui data wajah
                                                        Anda. Ini akan ditinjau oleh administrator.
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-end">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm me-2"
                                                        id="cancel-request">
                                                        Batal
                                                    </button>
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        Kirim
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @else
                                    <!-- No Update Request Yet -->
                                    <div id="request-update-section" class="text-center mt-4">
                                        <p class="mb-3">Butuh pembaruan wajah?</p>
                                        <button type="button" class="btn btn-icon-text btn-sm btn-outline-primary"
                                            id="show-request-form">
                                            <i data-feather="refresh-cw" class="btn-icon-prepend"></i> Request Now
                                        </button>
                                    </div>

                                    <!-- Hidden request form (will be shown on button click) -->
                                    <div class="request-form mt-3" id="update-request-form" style="display: none;">
                                        <h6 class="mb-3">Request Pembaruan Wajah</h6>
                                        <form action="{{ route('student.face.store-request') }}" method="POST">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="reason" class="form-label">Alasan Pembaruan <span
                                                        class="text-danger">*</span></label>
                                                <textarea class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" rows="3"
                                                    placeholder="Please explain why you need to update your face data" required></textarea>
                                                @error('reason')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text">
                                                    Sertakan alasan yang jelas mengapa Anda perlu memperbarui data wajah
                                                    Anda. Ini akan ditinjau oleh administrator.
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-end">
                                                <button type="button" class="btn btn-outline-secondary btn-sm me-2"
                                                    id="cancel-request">
                                                    Batal
                                                </button>
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    Kirim
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            @endif
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
                            <h5 class="mb-0">CARA KERJA</h5>
                        </div>
                        <div class="card-body">
                            <div class="steps-container">
                                <div class="step d-flex mb-2">
                                    <div class="step-number">1</div>
                                    <div class="step-content">
                                        <p class="mb-0"><strong>Daftarkan Wajah Anda</strong><br>Gunakan foto
                                            depan yang jelas dengan pencahayaan yang baik</p>
                                    </div>
                                </div>

                                <div class="step d-flex mb-2">
                                    <div class="step-number">2</div>
                                    <div class="step-content">
                                        <p class="mb-0"><strong>Pindai Kode QR</strong><br>Pindai kode QR yang
                                            disediakan oleh dosen Anda</p>
                                    </div>
                                </div>

                                <div class="step d-flex mb-2">
                                    <div class="step-number">3</div>
                                    <div class="step-content">
                                        <p class="mb-0"><strong>Verifikasi Identitas</strong><br>Ambil selfie yang jelas
                                            untuk
                                            verifikasi identitas</p>
                                    </div>
                                </div>

                                <div class="step d-flex">
                                    <div class="step-number">4</div>
                                    <div class="step-content">
                                        <p class="mb-0"><strong>Kehadiran Tercatat</strong><br>Kehadiran Anda
                                            secara otomatis tercatat saat berhasil</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-light p-3 rounded-3 mt-3">
                                <div class="d-flex align-items-start">
                                    <i data-feather="zap" class="text-warning me-2 mt-2"></i>
                                    <p class="mb-0"><strong>Tip:</strong> Pastikan pencahayaan yang baik dan tampilan
                                        wajah yang jelas saat melakukan registrasi atau verifikasi untuk meningkatkan
                                        akurasi pengenalan.</p>
                                </div>
                            </div>

                            @if ($student->face_registered)
                                <div class="bg-info-subtle p-3 rounded-3 mt-4">
                                    <div class="d-flex align-items-start">
                                        <i data-feather="info" class="text-info me-2 mt-1"></i>
                                        <div>
                                            <h6 class="mb-1">Tentang Permintaan Pembaruan Wajah</h6>
                                            <p class="mb-0">Jika Anda perlu memperbarui data wajah (misalnya, perubahan
                                                penampilan, kacamata, gaya rambut baru), Anda dapat mengajukan permintaan
                                                pembaruan. Persetujuan admin diperlukan untuk pembaruan wajah guna
                                                memastikan keamanan sistem.</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
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
                            <h5 class="mb-0">TIPS REGISTRASI WAJAH</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-4 mb-md-0">
                                    <img src="{{ asset('assets/images/reg-face.jpg') }}" alt="Face Registration Tips"
                                        class="tips-image">
                                </div>
                                <div class="col-md-8">
                                    <div class="tips-item">
                                        <i data-feather="sun" class="tips-icon text-warning"></i>
                                        <div>
                                            <h6>Pencahayaan yang Baik</h6>
                                            <p>Pastikan wajah Anda terkena cahaya yang baik dari depan. Hindari bayangan
                                                yang keras, pencahayaan dari belakang, atau cahaya yang tidak merata.
                                                Cahaya alami adalah yang terbaik bila memungkinkan.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="tips-item">
                                        <i data-feather="align-center" class="tips-icon text-primary"></i>
                                        <div>
                                            <h6>Posisi yang Tepat</h6>
                                            <p>Posisikan wajah Anda di tengah frame. Jaga kepala tetap lurus dan
                                                lihat langsung ke kamera. Seluruh wajah Anda harus terlihat jelas.</p>
                                        </div>
                                    </div>

                                    <div class="tips-item">
                                        <i data-feather="eye" class="tips-icon text-info"></i>
                                        <div>
                                            <h6>Tampilan Jelas</h6>
                                            <p>Lepaskan kacamata, masker, atau aksesori yang menutupi bagian wajah Anda.
                                                Pastikan wajah Anda tidak terhalang oleh rambut, bayangan, atau benda
                                                lainnya.</p>
                                        </div>
                                    </div>

                                    <div class="tips-item">
                                        <i data-feather="target" class="tips-icon text-success"></i>
                                        <div>
                                            <h6>Jarak yang Tepat</h6>
                                            <p>Jaga jarak yang nyaman dari kamera. Tidak terlalu dekat (yang dapat
                                                mendistorsi
                                                fitur wajah) dan tidak terlalu jauh (yang mengurangi detail).</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tips-do-dont mt-3">
                                <div class="tip-card">
                                    <div class="tip-header do-header d-flex align-items-center">
                                        <i data-feather="check" class="me-2"></i>
                                        <span>LAKUKAN</span>
                                    </div>
                                    <div class="tip-body">
                                        <ul class="mb-0 ps-3">
                                            <li>Hadap kamera secara langsung</li>
                                            <li>Pertahankan ekspresi wajah netral</li>
                                            <li>Pastikan pencahayaan merata pada wajah</li>
                                            <li>Posisikan setinggi mata dengan kamera</li>
                                            <li>Gunakan latar belakang polos</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="tip-card">
                                    <div class="tip-header dont-header d-flex align-items-center">
                                        <i data-feather="x" class="me-2"></i>
                                        <span>JANGAN</span>
                                    </div>
                                    <div class="tip-body">
                                        <ul class="mb-0 ps-3">
                                            <li>Miringkan kepala Anda pada sudut ekstrem</li>
                                            <li>Menggunakan kacamata hitam atau riasan tebal</li>
                                            <li>Mendaftar dalam kondisi pencahayaan buruk</li>
                                            <li>Menggunakan filter atau editan berlebihan</li>
                                            <li>Memiliki latar belakang yang mengganggu</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-light p-3 rounded-3 mt-4">
                                <div class="d-flex align-items-start">
                                    <i data-feather="alert-triangle" class="text-warning me-2 mt-1"></i>
                                    <p class="mb-0"><strong>Catatan Penting:</strong> Untuk alasan privasi dan keamanan,
                                        data wajah Anda dienkripsi dan disimpan dengan aman. Data ini hanya digunakan untuk
                                        keperluan verifikasi kehadiran dan tidak dibagikan kepada pihak ketiga.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }

            // Show/hide update request form
            const showFormButton = document.getElementById('show-request-form');
            const updateForm = document.getElementById('update-request-form');
            const cancelButton = document.getElementById('cancel-request');
            const requestSection = document.getElementById('request-update-section');

            if (showFormButton && updateForm) {
                showFormButton.addEventListener('click', function() {
                    updateForm.style.display = 'block';
                    requestSection.style.display = 'none';
                });
            }

            if (cancelButton && updateForm && requestSection) {
                cancelButton.addEventListener('click', function() {
                    updateForm.style.display = 'none';
                    requestSection.style.display = 'block';
                });
            }
        });
    </script>
@endpush
