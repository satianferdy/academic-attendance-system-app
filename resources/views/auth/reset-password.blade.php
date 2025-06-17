@extends('layouts.auth')

@section('title', 'Reset Password')

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

        /* Password toggle styling */
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            cursor: pointer;
            z-index: 10;
            display: block !important;
            pointer-events: auto;
        }

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

        .back-to-login {
            text-align: center;
            margin-top: 20px;
        }

        .back-to-login a {
            color: #999;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }

        .back-to-login a span {
            color: #22538a;
        }

        .alert {
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 14px;
        }

        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }
    </style>
@endpush

@section('content')
    <div class="login-container">
        <div class="logo">
            <img src="{{ asset('assets/images/logo.jpg') }}" alt="Logo">
        </div>

        <h2>Reset Password</h2>
        <p class="subtitle">
            Create a new password for your account
        </p>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST">
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="input-group">
                <input type="email" class="form-control" id="email" name="email" placeholder="Email address"
                    value="{{ old('email', request()->email) }}" required>
            </div>

            <div class="input-group">
                <input type="password" class="form-control" id="password" name="password" placeholder="New password"
                    required>
                <span class="password-toggle" id="password-toggle" onclick="togglePassword('password', 'visibilityIcon1')">
                    <i data-feather="eye" id="visibilityIcon1"></i>
                </span>
            </div>

            <div class="input-group">
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                    placeholder="Confirm new password" required>
                <span class="password-toggle" id="password-toggle2"
                    onclick="togglePassword('password_confirmation', 'visibilityIcon2')">
                    <i data-feather="eye" id="visibilityIcon2"></i>
                </span>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </div>
        </form>

        <div class="back-to-login">
            <p>
                <a href="{{ route('login') }}">
                    <span>Back to Login</span>
                </a>
            </p>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Initialize all feather icons when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Feather icons
            feather.replace();

            // Ensure password toggles are visible
            setupPasswordToggle('password', 'password-toggle', 'visibilityIcon1');
            setupPasswordToggle('password_confirmation', 'password-toggle2', 'visibilityIcon2');
        });

        function setupPasswordToggle(fieldId, toggleId, iconId) {
            var passwordToggle = document.getElementById(toggleId);
            var passwordField = document.getElementById(fieldId);

            // Prevent the toggle from being hidden when focusing
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
        }

        function togglePassword(fieldId, iconId) {
            var password = document.getElementById(fieldId);
            var icon = document.getElementById(iconId);

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
                    [iconId]: true
                }
            });

            // Ensure the toggle remains visible after icon change
            document.getElementById(fieldId === 'password' ? 'password-toggle' : 'password-toggle2').style.display =
                'block';
            document.getElementById(fieldId === 'password' ? 'password-toggle' : 'password-toggle2').style.visibility =
                'visible';
            document.getElementById(fieldId === 'password' ? 'password-toggle' : 'password-toggle2').style.opacity = '1';
        }
    </script>
@endpush
