@extends('layouts.app')

@section('title', 'Create User')

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="card-title">Buat User Baru</h6>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-icon-text btn-secondary">
                            <i class="btn-icon-prepend" data-feather="arrow-left"></i> Kembali
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
                                    <label for="name" class="form-label">Nama</label>
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
                                    <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
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
                                        <option value="lecturer" {{ old('role') == 'lecturer' ? 'selected' : '' }}>Dosen
                                        </option>
                                        <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Mahasiswa
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
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="student_nim" class="form-label">Mahasiswa ID (NIM)</label>
                                        <input type="text"
                                            class="form-control @error('student_nim') is-invalid @enderror" id="student_nim"
                                            name="student_nim" value="{{ old('student_nim') }}" placeholder="Enter NIM"
                                            data-required>
                                        @error('student_nim')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="study_program_id" class="form-label">Program Studi</label>
                                        <select class="form-select @error('study_program_id') is-invalid @enderror"
                                            id="study_program_id" name="study_program_id" data-required>
                                            <option value="">Select Study Program</option>
                                            @foreach ($studyPrograms as $program)
                                                <option value="{{ $program->id }}"
                                                    {{ old('study_program_id') == $program->id ? 'selected' : '' }}
                                                    data-program-id="{{ $program->id }}">
                                                    {{ $program->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('study_program_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <!-- Classroom selection -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="classroom_id" class="form-label">elas</label>
                                        <select class="form-select @error('classroom_id') is-invalid @enderror"
                                            id="classroom_id" name="classroom_id" data-required disabled>
                                            <option value="">Select Study Program First</option>
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
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="lecturer_nip" class="form-label">Dosen ID (NIP)</label>
                                        <input type="text"
                                            class="form-control @error('lecturer_nip') is-invalid @enderror"
                                            id="lecturer_nip" name="lecturer_nip" value="{{ old('lecturer_nip') }}"
                                            placeholder="Enter NIP" data-required>
                                        @error('lecturer_nip')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-sm btn-primary me-2">Simpan</button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-secondary">Batal</a>
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
            // Store classroom data keyed by study program ID
            const classroomsByProgram = {
                @foreach ($studyPrograms as $program)
                    {{ $program->id }}: [
                        @foreach ($classrooms->where('study_program_id', $program->id) as $classroom)
                            {
                                id: {{ $classroom->id }},
                                name: "{{ $classroom->name }}"
                            },
                        @endforeach
                    ],
                @endforeach
            };

            // Function to toggle fields based on selected role
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

                    // Keep classroom dropdown disabled until study program is selected
                    if (!$('#study_program_id').val()) {
                        $('#classroom_id').prop('disabled', true);
                    }
                } else if (role === 'lecturer') {
                    const $lecturerFields = $('#lecturer-fields');
                    $lecturerFields.removeClass('d-none')
                        .find('input, select, textarea').prop('disabled', false);
                    $lecturerFields.find('[data-required]').attr('required', true);
                }
            }

            // Function to update classroom options based on selected study program
            function updateClassroomOptions() {
                const selectedProgramId = $('#study_program_id').val();
                const $classroomSelect = $('#classroom_id');

                // Reset classroom dropdown
                $classroomSelect.empty().prop('disabled', true);
                $classroomSelect.append('<option value="">Select Classroom</option>');

                if (selectedProgramId) {
                    const classrooms = classroomsByProgram[selectedProgramId] || [];

                    if (classrooms.length > 0) {
                        // Add classroom options based on selected study program
                        classrooms.forEach(function(classroom) {
                            const option = new Option(classroom.name, classroom.id);
                            $classroomSelect.append(option);
                        });

                        // Enable classroom dropdown
                        $classroomSelect.prop('disabled', false);

                        // Set previously selected value if exists
                        const oldValue = "{{ old('classroom_id') }}";
                        if (oldValue) {
                            $classroomSelect.val(oldValue);
                        }
                    } else {
                        $classroomSelect.append('<option value="">No classrooms available</option>');
                    }
                }
            }

            // Run when page loads
            toggleFields();

            // Event handler for role change
            $('#role').on('change', toggleFields);

            // Event handler for study program change
            $('#study_program_id').on('change', updateClassroomOptions);

            // Handle initial study program selection if there's a value
            if ($('#study_program_id').val()) {
                updateClassroomOptions();
            }
        });
    </script>
@endpush
