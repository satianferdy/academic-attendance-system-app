@extends('layouts.app')

@section('title', 'Create User')

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="card-title">Create New User</h6>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i data-feather="arrow-left"></i> Back
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

                    <form action="{{ route('admin.users.store') }}" method="POST" class="forms-sample">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name') }}" placeholder="Enter name"
                                        required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        id="email" name="email" value="{{ old('email') }}" placeholder="Enter email"
                                        required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        id="password" name="password" placeholder="Enter password" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation" placeholder="Confirm password" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role" class="form-label">Role</label>
                                    <select class="form-select @error('role') is-invalid @enderror" id="role"
                                        name="role" required>
                                        <option value="">Select Role</option>
                                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                        <option value="lecturer" {{ old('role') == 'lecturer' ? 'selected' : '' }}>Lecturer
                                        </option>
                                        <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student
                                        </option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Student specific fields -->
                        <div id="student-fields" class="d-none">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="nim" class="form-label">Student ID (NIM)</label>
                                        <input type="text" class="form-control @error('nim') is-invalid @enderror"
                                            id="nim" name="nim" value="{{ old('nim') }}"
                                            placeholder="Enter NIM">
                                        @error('nim')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="department" class="form-label">Department</label>
                                        <input type="text" class="form-control @error('department') is-invalid @enderror"
                                            id="department" name="department" value="{{ old('department') }}"
                                            placeholder="Enter Department">
                                        @error('department')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="faculty" class="form-label">Faculty</label>
                                        <input type="text" class="form-control @error('faculty') is-invalid @enderror"
                                            id="faculty" name="faculty" value="{{ old('faculty') }}"
                                            placeholder="Enter Faculty">
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
                                            id="classroom_id" name="classroom_id">
                                            <option value="">Select Classroom</option>
                                            @foreach ($classrooms as $classroom)
                                                <option value="{{ $classroom->id }}"
                                                    {{ old('classroom_id') == $classroom->id ? 'selected' : '' }}>
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

                        <!-- Lecturer specific fields -->
                        <div id="lecturer-fields" class="d-none">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="nip" class="form-label">Lecturer ID (NIP)</label>
                                        <input type="text" class="form-control @error('nip') is-invalid @enderror"
                                            id="nip" name="nip" value="{{ old('nip') }}"
                                            placeholder="Enter NIP">
                                        @error('nip')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="lecturer_department" class="form-label">Department</label>
                                        <input type="text"
                                            class="form-control @error('department') is-invalid @enderror"
                                            id="lecturer_department" name="department" value="{{ old('department') }}"
                                            placeholder="Enter Department">
                                        @error('department')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="lecturer_faculty" class="form-label">Faculty</label>
                                        <input type="text" class="form-control @error('faculty') is-invalid @enderror"
                                            id="lecturer_faculty" name="faculty" value="{{ old('faculty') }}"
                                            placeholder="Enter Faculty">
                                        @error('faculty')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary me-2">Submit</button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Set initial state
            toggleRoleFields();

            // Toggle role-specific fields based on selected role
            $('#role').on('change', function() {
                toggleRoleFields();
            });

            // Add this to your script section
            $('form').on('submit', function() {
                const role = $('#role').val();

                if (role === 'student') {
                    // Disable lecturer fields to prevent them from being submitted
                    $('#lecturer-fields input').prop('disabled', true);
                } else if (role === 'lecturer') {
                    // Disable student fields to prevent them from being submitted
                    $('#student-fields input, #student-fields select').prop('disabled', true);
                }
            });

            function toggleRoleFields() {
                const role = $('#role').val();

                if (role === 'student') {
                    $('#student-fields').removeClass('d-none');
                    $('#lecturer-fields').addClass('d-none');
                } else if (role === 'lecturer') {
                    $('#student-fields').addClass('d-none');
                    $('#lecturer-fields').removeClass('d-none');
                } else {
                    $('#student-fields').addClass('d-none');
                    $('#lecturer-fields').addClass('d-none');
                }
            }
        });
    </script>
@endpush
