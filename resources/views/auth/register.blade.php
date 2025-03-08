<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 40px 0;
        }

        .register-form {
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background-color: white;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="register-form">
            <h2 class="text-center mb-4">Register</h2>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register.submit') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}"
                        required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}"
                        required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div class="form-text">Password must be at least 8 characters.</div>
                </div>
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="role" id="roleStudent" value="student"
                            {{ old('role') == 'student' ? 'checked' : '' }} required>
                        <label class="form-check-label" for="roleStudent">
                            Student
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="role" id="roleLecturer" value="lecturer"
                            {{ old('role') == 'lecturer' ? 'checked' : '' }}>
                        <label class="form-check-label" for="roleLecturer">
                            Lecturer
                        </label>
                    </div>
                </div>

                <!-- Student Fields -->
                <div id="studentFields" class="role-fields">
                    <h5 class="mt-4 mb-3">Student Information</h5>
                    <div class="mb-3">
                        <label for="nim" class="form-label">NIM (Student ID)</label>
                        <input type="text" class="form-control" id="nim" name="nim"
                            value="{{ old('nim') }}">
                    </div>
                    <div class="mb-3">
                        <label for="student_department" class="form-label">Department</label>
                        <input type="text" class="form-control" id="student_department" name="student_department"
                            value="{{ old('student_department') }}">
                    </div>
                    <div class="mb-3">
                        <label for="student_faculty" class="form-label">Faculty</label>
                        <input type="text" class="form-control" id="student_faculty" name="student_faculty"
                            value="{{ old('student_faculty') }}">
                    </div>
                </div>

                <!-- Lecturer Fields -->
                <div id="lecturerFields" class="role-fields" style="display: none;">
                    <h5 class="mt-4 mb-3">Lecturer Information</h5>
                    <div class="mb-3">
                        <label for="nip" class="form-label">NIP (Lecturer ID)</label>
                        <input type="text" class="form-control" id="nip" name="nip"
                            value="{{ old('nip') }}">
                    </div>
                    <div class="mb-3">
                        <label for="lecturer_department" class="form-label">Department</label>
                        <input type="text" class="form-control" id="lecturer_department"
                            name="lecturer_department" value="{{ old('lecturer_department') }}">
                    </div>
                    <div class="mb-3">
                        <label for="lecturer_faculty" class="form-label">Faculty</label>
                        <input type="text" class="form-control" id="lecturer_faculty" name="lecturer_faculty"
                            value="{{ old('lecturer_faculty') }}">
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Register</button>
                </div>
            </form>

            <div class="mt-3 text-center">
                <p>Already have an account? <a href="{{ route('login') }}">Login</a></p>
                <p><a href="/">Back to home</a></p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleStudent = document.getElementById('roleStudent');
            const roleLecturer = document.getElementById('roleLecturer');
            const studentFields = document.getElementById('studentFields');
            const lecturerFields = document.getElementById('lecturerFields');

            function toggleFields() {
                if (roleStudent.checked) {
                    studentFields.style.display = 'block';
                    lecturerFields.style.display = 'none';
                } else if (roleLecturer.checked) {
                    studentFields.style.display = 'none';
                    lecturerFields.style.display = 'block';
                }
            }

            roleStudent.addEventListener('change', toggleFields);
            roleLecturer.addEventListener('change', toggleFields);

            // Initial toggle based on selected role
            toggleFields();
        });
    </script>
</body>

</html>
