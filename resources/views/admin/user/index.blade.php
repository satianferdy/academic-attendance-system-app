@extends('layouts.app')

@section('title', 'User Management')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendors/datatables.net-bs5/dataTables.bootstrap5.css') }}">
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Data</a></li>
            <li class="breadcrumb-item active" aria-current="page">User</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Kelola Users</h6>

                    <div class="d-flex justify-content-end mb-3">
                        <div>
                            <a href="{{ route('admin.users.create') }}"
                                class="btn btn-primary btn-sm btn-icon-text mb-2 mb-md-0">
                                <i class="btn-icon-prepend" data-feather="plus-square"></i>
                                Add User
                            </a>
                        </div>
                    </div>

                    <ul class="nav nav-tabs" id="userTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="all-tab" data-bs-toggle="tab" href="#all" role="tab"
                                aria-controls="all" aria-selected="true">Semua</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="admin-tab" data-bs-toggle="tab" href="#admin" role="tab"
                                aria-controls="admin" aria-selected="false">Admin</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="lecturer-tab" data-bs-toggle="tab" href="#lecturer" role="tab"
                                aria-controls="lecturer" aria-selected="false">Dosen</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="student-tab" data-bs-toggle="tab" href="#student" role="tab"
                                aria-controls="student" aria-selected="false">Mahasiswa</a>
                        </li>
                    </ul>
                    <div class="tab-content border border-top-0 p-3" id="userTabContent">
                        <!-- All Users Tab -->
                        <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                            <div class="table-responsive">
                                <table id="allUsersTable" class="table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>NIM/NIP</th>
                                            <th>Nama</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($users as $key => $item)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>
                                                    {{ $item->role == 'student' ? $item->student->nim : ($item->role == 'lecturer' ? $item->lecturer->nip : '-') }}
                                                </td>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->email }}</td>
                                                <td>
                                                    <span
                                                        class="badge bg-{{ $item->role == 'admin' ? 'danger' : ($item->role == 'lecturer' ? 'warning' : 'info') }}">
                                                        {{ ucfirst($item->role) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.users.edit', $item->id) }}"
                                                        class="btn btn-sm btn-primary btn-icon">
                                                        <i class="btn-icon-prepend" data-feather="check-square"></i>
                                                    </a>
                                                    <form action="{{ route('admin.users.destroy', $item->id) }}"
                                                        method="post" class="d-inline">
                                                        @csrf
                                                        @method('delete')
                                                        <button class="btn btn-delete btn-sm btn-danger btn-icon">
                                                            <i class="btn-icon-prepend" data-feather="trash-2"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">No users found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Admin Tab -->
                        <div class="tab-pane fade" id="admin" role="tabpanel" aria-labelledby="admin-tab">
                            <div class="table-responsive">
                                <table id="adminTable" class="table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($admins as $key => $item)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->email }}</td>
                                                <td>
                                                    <a href="{{ route('admin.users.edit', $item->id) }}"
                                                        class="btn btn-sm btn-primary btn-icon">
                                                        <i class="btn-icon-prepend" data-feather="check-square"></i>
                                                    </a>
                                                    <form action="{{ route('admin.users.destroy', $item->id) }}"
                                                        method="post" class="d-inline">
                                                        @csrf
                                                        @method('delete')
                                                        <button class="btn btn-delete btn-sm btn-danger btn-icon">
                                                            <i class="btn-icon-prepend" data-feather="trash-2"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">No admin users found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Lecturer Tab -->
                        <div class="tab-pane fade" id="lecturer" role="tabpanel" aria-labelledby="lecturer-tab">
                            <div class="table-responsive">
                                <table id="lecturerTable" class="table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>NIP</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($lecturers as $key => $item)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $item->lecturer->nip }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->email }}</td>
                                                <td>
                                                    <a href="{{ route('admin.users.edit', $item->id) }}"
                                                        class="btn btn-sm btn-primary btn-icon">
                                                        <i class="btn-icon-prepend" data-feather="check-square"></i>
                                                    </a>
                                                    <form action="{{ route('admin.users.destroy', $item->id) }}"
                                                        method="post" class="d-inline">
                                                        @csrf
                                                        @method('delete')
                                                        <button class="btn btn-delete btn-sm btn-danger btn-icon">
                                                            <i class="btn-icon-prepend" data-feather="trash-2"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No lecturer users found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Student Tab -->
                        <div class="tab-pane fade" id="student" role="tabpanel" aria-labelledby="student-tab">
                            <div class="table-responsive">
                                <table id="studentTable" class="table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>NIM</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Department</th>
                                            <th>Face Register</th>
                                            <th>Class</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($students as $key => $item)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $item->student->nim }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->email }}</td>
                                                <td>{{ $item->student->studyProgram->name ?? '-' }}</td>
                                                <td>
                                                    @if ($item->student->face_registered)
                                                        <span class="badge bg-success">Registered</span>
                                                    @else
                                                        <span class="badge bg-danger">Not Registered</span>
                                                    @endif
                                                </td>
                                                <td>{{ $item->student->classroom->name ?? '-' }}</td>
                                                <td>
                                                    <a href="{{ route('admin.users.edit', $item->id) }}"
                                                        class="btn btn-sm btn-primary btn-icon">
                                                        <i class="btn-icon-prepend" data-feather="check-square"></i>
                                                    </a>
                                                    <form action="{{ route('admin.users.destroy', $item->id) }}"
                                                        method="post" class="d-inline">
                                                        @csrf
                                                        @method('delete')
                                                        <button class="btn btn-delete btn-sm btn-danger btn-icon">
                                                            <i class="btn-icon-prepend" data-feather="trash-2"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">No student users found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendors/datatables.net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('assets/vendors/datatables.net-bs5/dataTables.bootstrap5.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#allUsersTable').DataTable();
            $('#adminTable').DataTable();
            $('#lecturerTable').DataTable();
            $('#studentTable').DataTable();
        });
    </script>
@endpush
