@extends('layouts.app')

@section('title', 'Create User')

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="card-title">Create New User</h6>
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
                                        <label for="student_nim" class="form-label">Student ID (NIM)</label>
                                        <input type="text"
                                            class="form-control @error('student_nim') is-invalid @enderror" id="student_nim"
                                            name="student_nim" value="{{ old('student_nim') }}" placeholder="Enter NIM"
                                            data-required>
                                        @error('student_nim')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="student_department" class="form-label">Department</label>
                                        <select class="form-control @error('student_department') is-invalid @enderror"
                                            id="student_department" name="student_department" data-required>
                                            <option value="">Select Department</option>
                                            <option value="Teknik Informatika"
                                                {{ old('student_department') == 'Teknik Informatika' ? 'selected' : '' }}>
                                                Teknik Informatika
                                            </option>
                                            <option value="Sistem Informasi Bisnis"
                                                {{ old('student_department') == 'Sistem Informasi Bisnis' ? 'selected' : '' }}>
                                                Sistem Informasi Bisnis
                                            </option>
                                        </select>
                                        @error('student_department')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="student_faculty" class="form-label">Faculty</label>
                                        <input type="text"
                                            class="form-control @error('student_faculty') is-invalid @enderror"
                                            id="student_faculty" name="student_faculty" value="Teknologi Informasi"
                                            readonly>
                                        @error('student_faculty')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <!-- Classroom selection -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="classroom_id" class="form-label">Classroom</label>
                                        <select class="form-select @error('classroom_id') is-invalid @enderror"
                                            id="classroom_id" name="classroom_id" data-required>
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
                                        <label for="lecturer_nip" class="form-label">Lecturer ID (NIP)</label>
                                        <input type="text"
                                            class="form-control @error('lecturer_nip') is-invalid @enderror"
                                            id="lecturer_nip" name="lecturer_nip" value="{{ old('lecturer_nip') }}"
                                            placeholder="Enter NIP" data-required>
                                        @error('lecturer_nip')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="lecturer_department" class="form-label">Department</label>
                                        <select class="form-control @error('lecturer_department') is-invalid @enderror"
                                            id="lecturer_department" name="lecturer_department" data-required>
                                            <option value="">Select Department</option>
                                            <option value="Teknik Informatika"
                                                {{ old('lecturer_department') == 'Teknik Informatika' ? 'selected' : '' }}>
                                                Teknik Informatika
                                            </option>
                                            <option value="Sistem Informasi Bisnis"
                                                {{ old('lecturer_department') == 'Sistem Informasi Bisnis' ? 'selected' : '' }}>
                                                Sistem Informasi Bisnis
                                            </option>
                                        </select>
                                        @error('lecturer_department')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="lecturer_faculty" class="form-label">Faculty</label>
                                        <input type="text"
                                            class="form-control @error('lecturer_faculty') is-invalid @enderror"
                                            id="lecturer_faculty" name="lecturer_faculty" value="Teknologi Informasi"
                                            readonly>
                                        @error('lecturer_faculty')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-sm btn-primary me-2">Submit</button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-secondary">Cancel</a>
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
            function toggleFields() {
                const role = $('#role').val();

                // Disable dan sembunyikan semua field role
                $('#student-fields, #lecturer-fields').addClass('d-none')
                    .find('input, select, textarea').prop('disabled', true);

                // Hapus atribut required
                $('[data-required]').removeAttr('required');

                if (role === 'student') {
                    const $studentFields = $('#student-fields');
                    $studentFields.removeClass('d-none')
                        .find('input, select, textarea').prop('disabled', false);
                    $studentFields.find('[data-required]').attr('required', true);
                } else if (role === 'lecturer') {
                    const $lecturerFields = $('#lecturer-fields');
                    $lecturerFields.removeClass('d-none')
                        .find('input, select, textarea').prop('disabled', false);
                    $lecturerFields.find('[data-required]').attr('required', true);
                }
            }

            // Jalankan saat pertama load
            toggleFields();

            // Event handler untuk perubahan role
            $('#role').on('change', toggleFields);
        });
    </script>
@endpush
