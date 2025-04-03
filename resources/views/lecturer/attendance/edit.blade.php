@extends('layouts.app')

@section('title', 'Edit Attendance')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('lecturer.dashboard') }}">Lecturer</a></li>
            <li class="breadcrumb-item"><a href="{{ route('lecturer.attendance.index') }}">Attendance</a></li>
            <li class="breadcrumb-item">
                <a
                    href="{{ route('lecturer.attendance.show', ['id' => $attendance->class_schedule_id, 'date' => $attendance->date]) }}">
                    Attendance List
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-md-8 grid-margin stretch-card mx-auto">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Edit Student Attendance</h6>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-4">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Course:</strong></p>
                                <p>{{ $attendance->classSchedule->course->code }} -
                                    {{ $attendance->classSchedule->course->name }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Date:</strong></p>
                                <p>{{ \Carbon\Carbon::parse($attendance->date)->format('l, d F Y') }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Student ID:</strong></p>
                                <p>{{ $attendance->student->student_id }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Student Name:</strong></p>
                                <p>{{ $attendance->student->user->name }}</p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('lecturer.attendance.update', $attendance->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="status" class="form-label">Attendance Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="present" {{ $attendance->status == 'present' ? 'selected' : '' }}>Present
                                </option>
                                <option value="late" {{ $attendance->status == 'late' ? 'selected' : '' }}>Late</option>
                                <option value="absent" {{ $attendance->status == 'absent' ? 'selected' : '' }}>Absent
                                </option>
                                <option value="excused" {{ $attendance->status == 'excused' ? 'selected' : '' }}>Excused
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="3">{{ $attendance->remarks }}</textarea>
                            <div class="form-text">Optional notes or comments about the student's attendance</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('lecturer.attendance.show', ['id' => $attendance->class_schedule_id, 'date' => $attendance->date]) }}"
                                class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Attendance</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize feather icons
            feather.replace();
        });
    </script>
@endsection
