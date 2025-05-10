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

                    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="forms-sample">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nama</label>
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
                                    <label for="password" class="form-label">Password <small>(Kosongkan jika tidak ingin
                                            mengubah)</small></label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        id="password" name="password" placeholder="Enter new password">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
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
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nim" class="form-label">Mahasiswa ID (NIM)</label>
                                            <input type="text" class="form-control @error('nim') is-invalid @enderror"
                                                id="nim" name="nim"
                                                value="{{ old('nim', $user->student->nim ?? '') }}" placeholder="Enter NIM"
                                                required>
                                            @error('nim')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="study_program_id" class="form-label">Program Studi</label>
                                            <select class="form-select @error('study_program_id') is-invalid @enderror"
                                                id="study_program_id" name="study_program_id" required>
                                                <option value="">Select Study Program</option>
                                                @foreach ($studyPrograms as $program)
                                                    <option value="{{ $program->id }}"
                                                        {{ old('study_program_id', $user->student->study_program_id ?? '') == $program->id ? 'selected' : '' }}
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
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="classroom_id" class="form-label">>Kelas</label>
                                            <select class="form-select @error('classroom_id') is-invalid @enderror"
                                                id="classroom_id" name="classroom_id" required>
                                                <option value="">Loading classrooms...</option>
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
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nip" class="form-label">Dosen ID (NIP)</label>
                                            <input type="text" class="form-control @error('nip') is-invalid @enderror"
                                                id="nip" name="nip"
                                                value="{{ old('nip', $user->lecturer->nip ?? '') }}"
                                                placeholder="Enter NIP" required>
                                            @error('nip')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

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
    @if ($user->role === 'student')
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

                // Function to update classroom options based on selected study program
                function updateClassroomOptions() {
                    const selectedProgramId = $('#study_program_id').val();
                    const $classroomSelect = $('#classroom_id');
                    const currentClassroom = "{{ old('classroom_id', $user->student->classroom_id ?? '') }}";

                    // Reset classroom dropdown
                    $classroomSelect.empty();
                    $classroomSelect.append('<option value="">Select Classroom</option>');

                    if (selectedProgramId) {
                        const classrooms = classroomsByProgram[selectedProgramId] || [];

                        if (classrooms.length > 0) {
                            // Add classroom options based on selected study program
                            classrooms.forEach(function(classroom) {
                                const selected = (classroom.id == currentClassroom) ? 'selected' : '';
                                $classroomSelect.append(
                                    `<option value="${classroom.id}" ${selected}>${classroom.name}</option>`
                                );
                            });
                        } else {
                            $classroomSelect.append('<option value="">No classrooms available</option>');
                        }
                    }
                }

                // Event handler for study program change
                $('#study_program_id').on('change', updateClassroomOptions);

                // Initialize classroom options on page load
                updateClassroomOptions();
            });
        </script>
    @endif
@endpush
