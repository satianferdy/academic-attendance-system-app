@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="card-title">Edit User</h6>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-icon-text btn-secondary">
                            <i class="btn-icon-prepend" data-feather="arrow-left"></i> Back
                        </a>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="forms-sample">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name', $user->name) }}"
                                        placeholder="Enter name" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        id="email" name="email" value="{{ old('email', $user->email) }}"
                                        placeholder="Enter email" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password (leave blank to keep current)</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        id="password" name="password" placeholder="Enter new password">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation" placeholder="Confirm new password">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role" class="form-label">Role</label>
                                    <input type="text" class="form-control" value="{{ ucfirst($user->role) }}" readonly>
                                    <input type="hidden" name="role" value="{{ $user->role }}">
                                </div>
                            </div>
                        </div>

                        <!-- Student specific fields -->
                        @if ($user->role === 'student')
                            <div id="student-fields">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="nim" class="form-label">Student ID (NIM)</label>
                                            <input type="text" class="form-control @error('nim') is-invalid @enderror"
                                                id="nim" name="nim"
                                                value="{{ old('nim', $user->student->nim ?? '') }}" placeholder="Enter NIM"
                                                required>
                                            @error('nim')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="department" class="form-label">Department</label>
                                            <select class="form-control @error('department') is-invalid @enderror"
                                                id="department" name="department" required>
                                                <option value="">Select Department</option>
                                                <option value="Teknik Informatika"
                                                    {{ old('department', $user->student->department ?? '') == 'Teknik Informatika' ? 'selected' : '' }}>
                                                    Teknik Informatika</option>
                                                <option value="Sistem Informasi Bisnis"
                                                    {{ old('department', $user->student->department ?? '') == 'Sistem Informasi Bisnis' ? 'selected' : '' }}>
                                                    Sistem Informasi Bisnis</option>
                                            </select>
                                            @error('department')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="faculty" class="form-label">Faculty</label>
                                            <input type="text"
                                                class="form-control @error('faculty') is-invalid @enderror" id="faculty"
                                                name="faculty" value="Teknologi Informasi" readonly required>
                                            @error('faculty')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="classroom_id" class="form-label">Classroom</label>
                                            <select class="form-select @error('classroom_id') is-invalid @enderror"
                                                id="classroom_id" name="classroom_id" required>
                                                <option value="">Select Classroom</option>
                                                @foreach ($classrooms as $classroom)
                                                    <option value="{{ $classroom->id }}"
                                                        {{ old('classroom_id', $user->student->classroom_id ?? '') == $classroom->id ? 'selected' : '' }}>
                                                        {{ $classroom->name }} ({{ $classroom->department }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('classroom_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Lecturer specific fields -->
                        @if ($user->role === 'lecturer')
                            <div id="lecturer-fields">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="nip" class="form-label">Lecturer ID (NIP)</label>
                                            <input type="text" class="form-control @error('nip') is-invalid @enderror"
                                                id="nip" name="nip"
                                                value="{{ old('nip', $user->lecturer->nip ?? '') }}"
                                                placeholder="Enter NIP" required>
                                            @error('nip')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="department" class="form-label">Department</label>
                                            <select class="form-control @error('department') is-invalid @enderror"
                                                id="department" name="department" required>
                                                <option value="">Select Department</option>
                                                <option value="Teknik Informatika"
                                                    {{ old('department', $user->lecturer->department ?? '') == 'Teknik Informatika' ? 'selected' : '' }}>
                                                    Teknik Informatika</option>
                                                <option value="Sistem Informasi Bisnis"
                                                    {{ old('department', $user->lecturer->department ?? '') == 'Sistem Informasi Bisnis' ? 'selected' : '' }}>
                                                    Sistem Informasi Bisnis</option>
                                            </select>
                                            @error('department')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="faculty" class="form-label">Faculty</label>
                                            <input type="text"
                                                class="form-control @error('faculty') is-invalid @enderror" id="faculty"
                                                name="faculty" value="Teknologi Informasi" readonly required>
                                            @error('faculty')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mt-4">
                            <button type="submit" class="btn btn-sm btn-primary me-2">Update</button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
