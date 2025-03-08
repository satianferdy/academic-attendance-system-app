@extends('layouts.auth')

@section('title', 'Login')

@push('styles')
    <style>
        body {
            background-color: #f0f5ff;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Roboto', sans-serif;
        }

        .login-container {
            max-width: 500px;
            width: 100%;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            background-color: white;
            text-align: center;
        }

        .logo {
            margin-bottom: 25px;
        }

        .logo img {
            width: 70px;
            height: auto;
        }

        h2 {
            color: #22538a;
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .subtitle {
            color: #999;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .form-control {
            padding: 12px 15px 12px 15px;
            border: 1px solid #e1e5eb;
            border-radius: 8px;
            width: 100%;
            position: relative;
        }

        /* Important fix for the password icon */
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            cursor: pointer;
            z-index: 10;
            /* Ensure the toggle is above other elements */
            display: block !important;
            /* Force the toggle to always display */
            pointer-events: auto;
            /* Ensure clicks are registered */
        }

        /* Ensure the SVG inside remains visible */
        .password-toggle svg {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        .btn-primary {
            background-color: #22538a;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 500;
            width: 100%;
            margin-top: 10px;
        }

        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }

        .forgot-password a {
            color: #999;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }

        .forgot-password a span {
            color: #22538a;
        }

        .login-info {
            text-align: left;
            margin-bottom: 15px;
            font-size: 13px;
            color: #666;
        }
    </style>
@endpush

@section('content')
    <div class="login-container">
        <div class="logo">
            <img src="{{ asset('assets/images/logo.png') }}" alt="Logo">
        </div>

        <h2>Welcome Back</h2>
        <p class="subtitle">
            Enter your credentials to access your account.
        </p>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login.submit') }}" method="POST">
            @csrf
            <div class="input-group">
                <input type="text" class="form-control" id="username" name="username"
                    placeholder="Enter your NIM/NIP/Email" value="{{ old('username') }}" required>
            </div>
            <div class="login-info">
                <small>* Students use NIM, Lecturers use NIP, Admins use Email</small>
            </div>

            <div class="input-group">
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password"
                    required>
                <span class="password-toggle" id="password-toggle" onclick="togglePassword()">
                    <i data-feather="eye" id="visibilityIcon"></i>
                </span>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Sign In</button>
            </div>
        </form>

        <div class="forgot-password">
            <a href="{{ route('register') }}">Dont have an account? <span>Sign up now</span></a>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Initialize all feather icons when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Feather icons
            feather.replace();

            // Ensure the password toggle is always visible
            var passwordToggle = document.getElementById('password-toggle');
            var passwordField = document.getElementById('password');

            // Prevent the toggle from being hidden when focusing on password
            passwordField.addEventListener('focus', function() {
                setTimeout(function() {
                    if (passwordToggle) {
                        passwordToggle.style.display = 'block';
                        passwordToggle.style.visibility = 'visible';
                        passwordToggle.style.opacity = '1';
                    }
                }, 10);
            });

            // Prevent the toggle from being hidden when typing
            passwordField.addEventListener('input', function() {
                if (passwordToggle) {
                    passwordToggle.style.display = 'block';
                    passwordToggle.style.visibility = 'visible';
                    passwordToggle.style.opacity = '1';
                }
            });
        });

        function togglePassword() {
            var password = document.getElementById('password');
            var icon = document.getElementById('visibilityIcon');

            if (password.type === 'password') {
                password.type = 'text';
                icon.setAttribute('data-feather', 'eye-off');
            } else {
                password.type = 'password';
                icon.setAttribute('data-feather', 'eye');
            }

            // Reinitialize just this icon to avoid flickering
            feather.replace({
                icons: {
                    'visibilityIcon': true
                }
            });

            // Ensure the toggle remains visible after icon change
            document.getElementById('password-toggle').style.display = 'block';
            document.getElementById('password-toggle').style.visibility = 'visible';
            document.getElementById('password-toggle').style.opacity = '1';
        }
    </script>
@endpush
