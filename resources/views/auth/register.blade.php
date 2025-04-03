@extends('layouts.auth')

@section('title', 'Register')

@push('styles')
    <style>
        body {
            background-color: #f0f5ff;
            font-family: 'Roboto', sans-serif;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .register-container {
            max-width: 900px;
            width: 100%;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            background-color: white;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-container img {
            width: 80px;
            height: auto;
        }

        h2 {
            color: #22538a;
            font-size: 28px;
            text-align: center;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .subtitle {
            color: #777;
            font-size: 16px;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-layout {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }

        .main-form {
            flex: 1;
            min-width: 300px;
        }

        .role-details {
            flex: 1;
            min-width: 300px;
            padding-left: 30px;
            border-left: 1px solid #eee;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: #22538a;
            outline: none;
            box-shadow: 0 0 0 3px rgba(34, 83, 138, 0.1);
        }

        .form-text {
            display: block;
            margin-top: 5px;
            font-size: 13px;
            color: #888;
        }

        .role-section {
            margin-bottom: 25px;
        }

        .role-options {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }

        .role-option {
            display: flex;
            align-items: center;
        }

        .role-option input {
            margin-right: 8px;
        }

        .role-fields h3 {
            color: #22538a;
            font-size: 20px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .btn-register {
            display: block;
            width: 100%;
            padding: 14px;
            background-color: #22538a;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 30px;
        }

        .btn-register:hover {
            background-color: #1a4270;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 15px;
        }

        .login-link a {
            color: #22538a;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .alert {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert ul {
            margin: 0;
            padding-left: 20px;
        }

        @media (max-width: 768px) {
            .form-layout {
                flex-direction: column;
            }

            .role-details {
                padding-left: 0;
                border-left: none;
                border-top: 1px solid #eee;
                padding-top: 20px;
                margin-top: 10px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container">
        <div class="register-container">
            <div class="logo-container">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Logo">
            </div>

            <h2>Create Account</h2>
            <p class="subtitle">Fill in the details below to register.</p>

            @if ($errors->any())
                <div class="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register.submit') }}" method="POST">
                @csrf
                <div class="form-layout">
                    <!-- Left Side - Main Registration Form -->
                    <div class="main-form">
                        <div class="form-group">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name') }}" placeholder="Enter your full name" required>
                        </div>

                        <div class="form-group">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="{{ old('email') }}" placeholder="Enter your email" required>
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Enter your password" required>
                            <span class="form-text">Password must be at least 8 characters.</span>
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation"
                                name="password_confirmation" placeholder="Confirm your password" required>
                        </div>

                        <div class="role-section">
                            <label class="form-label">Role</label>
                            <div class="role-options">
                                <div class="role-option">
                                    <input type="radio" id="roleStudent" name="role" value="student"
                                        {{ old('role') == 'student' ? 'checked' : '' }} required>
                                    <label for="roleStudent">Student</label>
                                </div>
                                <div class="role-option">
                                    <input type="radio" id="roleLecturer" name="role" value="lecturer"
                                        {{ old('role') == 'lecturer' ? 'checked' : '' }}>
                                    <label for="roleLecturer">Lecturer</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side - Role Information -->
                    <div class="role-details">
                        <!-- Student Fields -->
                        <div id="studentFields" class="role-fields">
                            <h3>Student Information</h3>
                            <div class="form-group">
                                <label for="nim" class="form-label">NIM (Student ID)</label>
                                <input type="text" class="form-control" id="nim" name="nim"
                                    placeholder="Enter your student ID" value="{{ old('nim') }}">
                            </div>
                            <div class="form-group">
                                <label for="student_department" class="form-label">Department</label>
                                <input type="text" class="form-control" id="student_department" name="student_department"
                                    placeholder="Enter your department" value="{{ old('student_department') }}">
                            </div>
                            <div class="form-group">
                                <label for="student_faculty" class="form-label">Faculty</label>
                                <input type="text" class="form-control" id="student_faculty" name="student_faculty"
                                    placeholder="Enter your faculty" value="{{ old('student_faculty') }}">
                            </div>
                        </div>

                        <!-- Lecturer Fields -->
                        <div id="lecturerFields" class="role-fields" style="display: none;">
                            <h3>Lecturer Information</h3>
                            <div class="form-group">
                                <label for="nip" class="form-label">NIP (Lecturer ID)</label>
                                <input type="text" class="form-control" id="nip" name="nip"
                                    placeholder="Enter your lecturer ID" value="{{ old('nip') }}">
                            </div>
                            <div class="form-group">
                                <label for="lecturer_department" class="form-label">Department</label>
                                <input type="text" class="form-control" id="lecturer_department"
                                    placeholder="Enter your department" name="lecturer_department"
                                    value="{{ old('lecturer_department') }}">
                            </div>
                            <div class="form-group">
                                <label for="lecturer_faculty" class="form-label">Faculty</label>
                                <input type="text" class="form-control" id="lecturer_faculty" name="lecturer_faculty"
                                    placeholder="Enter your faculty" value="{{ old('lecturer_faculty') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-register">Register</button>

                <div class="login-link">
                    Already have an account? <a href="{{ route('login') }}">Sign in</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
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
@endpush
