@extends('layouts.app')

@section('title', 'Face Update Requests')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendors/datatables.net-bs5/dataTables.bootstrap5.css') }}">
    <style>
        .badge-counter {
            position: relative;
            top: -2px;
        }

        .status-badge {
            font-size: 0.8rem;
            min-width: 75px;
            text-align: center;
        }

        .face-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .modal-body {
            text-align: center;
        }

        .modal-footer .btn {
            min-width: 80px;
        }

        .face-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            transition: opacity 0.3s;
        }

        .face-image.loading {
            opacity: 0.6;
        }

        /* Add a fancy background for the image container */
        .image-container {
            background: #f5f5f5;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: inline-block;
        }
    </style>
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item active" aria-current="page">Face Update Requests</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="card-title">Face Update Requests</h6>

                        <div class="d-flex gap-2">
                            <button type="button"
                                onclick="window.location.href='{{ route('admin.face-requests.index', ['status' => 'pending']) }}'"
                                class="btn btn-icon-text btn-xs {{ $status === 'pending' ? 'btn-primary' : 'btn-outline-primary' }} me-1">
                                <i data-feather="clock" class="icon-xs"></i> Pending
                                @if ($pendingCount = \App\Models\FaceUpdateRequest::where('status', 'pending')->count())
                                    <span class="badge badge-counter bg-danger">{{ $pendingCount }}</span>
                                @endif
                            </button>
                            <button type="button"
                                onclick="window.location.href='{{ route('admin.face-requests.index', ['status' => 'approved']) }}'"
                                class="btn btn-icon-text btn-xs {{ $status === 'approved' ? 'btn-success' : 'btn-outline-success' }} me-1">
                                <i data-feather="check-circle" class="icon-xs"></i> Approved
                            </button>
                            <button type="button"
                                onclick="window.location.href='{{ route('admin.face-requests.index', ['status' => 'rejected']) }}'"
                                class="btn btn-icon-text btn-xs {{ $status === 'rejected' ? 'btn-danger' : 'btn-outline-danger' }} me-1">
                                <i data-feather="x-circle" class="icon-xs"></i> Rejected
                            </button>
                            <button type="button"
                                onclick="window.location.href='{{ route('admin.face-requests.index', ['status' => 'all']) }}'"
                                class="btn btn-icon-text btn-xs {{ $status === 'all' ? 'btn-secondary' : 'btn-outline-secondary' }} me-1">
                                <i data-feather="list" class="icon-xs"></i> All
                            </button>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="dataTableExample" class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Student</th>
                                    <th>Submitted</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $request)
                                    <tr>
                                        <td>{{ $request->id }}</td>
                                        <td>
                                            <div>{{ $request->student->user->name }}</div>
                                            <small class="text-muted">{{ $request->student->nim }}</small>
                                        </td>
                                        <td>{{ $request->created_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            <span
                                                class="badge status-badge {{ match ($request->status) {
                                                    'pending' => 'bg-warning',
                                                    'approved' => 'bg-success',
                                                    'rejected' => 'bg-danger',
                                                    default => 'bg-secondary',
                                                } }}">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a class="btn btn-sm btn-info btn-icon" data-bs-toggle="modal"
                                                data-bs-target="#viewFaceModal" data-id="{{ $request->id }}"
                                                data-name="{{ $request->student->user->name }}"
                                                data-nim="{{ $request->student->nim }}"
                                                data-created="{{ $request->created_at->format('M d, Y H:i') }}"
                                                data-reason="{{ $request->reason }}" data-status="{{ $request->status }}"
                                                data-face-img="{{ route('face-images.show', $request->student_id) }}">
                                                <i class="btn-icon-prepend" data-feather="eye"></i>
                                            </a>

                                            @if ($request->status === 'pending')
                                                <a href="#" class="btn btn-sm btn-success btn-icon"
                                                    data-bs-toggle="modal" data-bs-target="#approveModal"
                                                    data-id="{{ $request->id }}"
                                                    data-name="{{ $request->student->user->name }}"
                                                    data-bs-toggle="tooltip" title="Approve">
                                                    <i data-feather="check"></i>
                                                </a>
                                                <a href="#" class="btn btn-sm btn-danger btn-icon"
                                                    data-bs-toggle="modal" data-bs-target="#rejectModal"
                                                    data-id="{{ $request->id }}"
                                                    data-name="{{ $request->student->user->name }}"
                                                    data-bs-toggle="tooltip" title="Reject">
                                                    <i data-feather="x"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <p class="text-muted mb-0">No face update requests found.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $requests->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Face Modal -->
    <div class="modal fade" id="viewFaceModal" tabindex="-1" aria-labelledby="viewFaceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewFaceModalLabel">Current Face Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3" id="studentInfo">
                        <h6 class="student-name mb-1"></h6>
                        <p class="text-muted student-nim mb-1"></p>
                        <p class="text-muted request-date mb-0"></p>
                    </div>

                    <div class="mb-3">
                        <h6 class="mb-2">Reason for Update:</h6>
                        <p class="reason-text mb-0"></p>
                    </div>

                    <h6 class="mb-3">Current Face Image:</h6>
                    <div class="image-container">
                        <img src="" alt="Face Image" class="face-image mb-3" id="faceImage">
                    </div>

                    <div class="request-status">
                        <span class="badge status-badge"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

                    <div id="pendingActions" style="display: none;">
                        <button type="button" class="btn btn-success btn-approve">Approve</button>
                        <button type="button" class="btn btn-danger btn-reject">Reject</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveModalLabel">Approve Face Update Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="approveForm" action="" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to approve the face update request for <strong
                                id="approveStudentName"></strong>?</p>
                        <p>The student will be able to update their face data after approval.</p>

                        <div class="mb-3">
                            <label for="approveNotes" class="form-label">Admin Notes (Optional):</label>
                            <textarea class="form-control" id="approveNotes" name="admin_notes" rows="3"
                                placeholder="Add any notes or special instructions..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Reject Face Update Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="rejectForm" action="" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to reject the face update request for <strong
                                id="rejectStudentName"></strong>?</p>

                        <div class="mb-3">
                            <label for="rejectReason" class="form-label">Reason for Rejection <span
                                    class="text-danger">*</span>:</label>
                            <textarea class="form-control" id="rejectReason" name="admin_notes" rows="3"
                                placeholder="Explain why the request is being rejected..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendors/datatables.net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('assets/vendors/datatables.net-bs5/dataTables.bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/js/data-table.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Setup view face modal
            const viewFaceModal = document.getElementById('viewFaceModal');
            viewFaceModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const requestId = button.getAttribute('data-id');
                const studentName = button.getAttribute('data-name');
                const studentNim = button.getAttribute('data-nim');
                const createdDate = button.getAttribute('data-created');
                const reason = button.getAttribute('data-reason');
                const status = button.getAttribute('data-status');
                const faceImg = button.getAttribute('data-face-img');

                // Populate modal
                viewFaceModal.querySelector('.student-name').textContent = studentName;
                viewFaceModal.querySelector('.student-nim').textContent = 'NIM: ' + studentNim;
                viewFaceModal.querySelector('.request-date').textContent = 'Submitted: ' + createdDate;
                viewFaceModal.querySelector('.reason-text').textContent = reason;

                // Set the face image source - this is the key change
                const faceImage = viewFaceModal.querySelector('#faceImage');
                faceImage.src = faceImg;

                // Add loading indicator and error handling
                faceImage.classList.add('loading');
                faceImage.onload = function() {
                    faceImage.classList.remove('loading');
                };
                faceImage.onerror = function() {
                    faceImage.src = '/assets/images/reg-face.jpg'; // Fallback image
                    faceImage.classList.remove('loading');
                };

                // Set status badge (unchanged)
                const statusBadge = viewFaceModal.querySelector('.status-badge');
                statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                statusBadge.className = 'badge status-badge';

                switch (status) {
                    case 'pending':
                        statusBadge.classList.add('bg-warning');
                        viewFaceModal.querySelector('#pendingActions').style.display = 'block';
                        break;
                    case 'approved':
                        statusBadge.classList.add('bg-success');
                        viewFaceModal.querySelector('#pendingActions').style.display = 'none';
                        break;
                    case 'rejected':
                        statusBadge.classList.add('bg-danger');
                        viewFaceModal.querySelector('#pendingActions').style.display = 'none';
                        break;
                }

                // Setup action buttons within the modal
                if (status === 'pending') {
                    const approveBtn = viewFaceModal.querySelector('.btn-approve');
                    const rejectBtn = viewFaceModal.querySelector('.btn-reject');

                    approveBtn.onclick = function() {
                        $('#viewFaceModal').modal('hide');
                        $('#approveModal').modal('show');
                        document.getElementById('approveStudentName').textContent = studentName;
                        document.getElementById('approveForm').action =
                            `/admin/face-requests/${requestId}/approve`;
                    };

                    rejectBtn.onclick = function() {
                        $('#viewFaceModal').modal('hide');
                        $('#rejectModal').modal('show');
                        document.getElementById('rejectStudentName').textContent = studentName;
                        document.getElementById('rejectForm').action =
                            `/admin/face-requests/${requestId}/reject`;
                    };
                }
            });

            // Setup approve modal
            const approveModal = document.getElementById('approveModal');
            approveModal.addEventListener('show.bs.modal', function(event) {
                // Direct trigger from table
                if (event.relatedTarget) {
                    const button = event.relatedTarget;
                    const requestId = button.getAttribute('data-id');
                    const studentName = button.getAttribute('data-name');

                    document.getElementById('approveStudentName').textContent = studentName;
                    document.getElementById('approveForm').action =
                        `/admin/face-requests/${requestId}/approve`;
                }
            });

            // Setup reject modal
            const rejectModal = document.getElementById('rejectModal');
            rejectModal.addEventListener('show.bs.modal', function(event) {
                // Direct trigger from table
                if (event.relatedTarget) {
                    const button = event.relatedTarget;
                    const requestId = button.getAttribute('data-id');
                    const studentName = button.getAttribute('data-name');

                    document.getElementById('rejectStudentName').textContent = studentName;
                    document.getElementById('rejectForm').action =
                        `/admin/face-requests/${requestId}/reject`;
                }
            });
        });
    </script>
@endpush
