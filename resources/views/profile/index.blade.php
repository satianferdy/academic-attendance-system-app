@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Profil Saya</h5>
                        <a href="{{ route('profile.change-password') }}" class="btn btn-sm btn-icon-text btn-outline-primary">
                            <i data-feather="key" class="btn-icon-prepend"></i> Ganti Password
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-4 text-center">
                                <div class="avatar-placeholder mb-3">
                                    <i data-feather="user" style="width: 64px; height: 64px; color: #6c757d;"></i>
                                </div>
                                <h5>{{ $user->name }}</h5>
                                <span class="badge bg-primary">{{ ucfirst($user->role) }}</span>
                            </div>
                            <div class="col-md-8">
                                <h6 class="border-bottom pb-2">Infomasi User</h6>
                                <div class="row mb-3">
                                    <div class="col-sm-4 fw-medium">Email:</div>
                                    <div class="col-sm-8">{{ $user->email }}</div>
                                </div>
                                @if ($user->role == 'student' && $user->student)
                                    <h6 class="border-bottom pb-2 mt-4">Informasi Mahasiswa</h6>
                                    <div class="row mb-3">
                                        <div class="col-sm-4 fw-medium">NIM:</div>
                                        <div class="col-sm-8">{{ $user->student->nim }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4 fw-medium">Program:</div>
                                        <div class="col-sm-8">{{ $user->student->studyProgram->name ?? 'Not assigned' }}
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4 fw-medium">Kelas:</div>
                                        <div class="col-sm-8">{{ $user->student->classroom->name ?? 'Not assigned' }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4 fw-medium">Registrasi Wajah:</div>
                                        <div class="col-sm-8">
                                            @if ($user->student->face_registered)
                                                <span class="badge bg-success">Terdaftar</span>
                                            @else
                                                <span class="badge bg-warning">Belum Terdaftar</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if ($user->role == 'lecturer' && $user->lecturer)
                                    <h6 class="border-bottom pb-2 mt-4">Informasi Dosen</h6>
                                    <div class="row mb-3">
                                        <div class="col-sm-4 fw-medium">NIP:</div>
                                        <div class="col-sm-8">{{ $user->lecturer->nip }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4 fw-medium">Kelas:</div>
                                        <div class="col-sm-8">{{ $classCount }} classes</div>
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
