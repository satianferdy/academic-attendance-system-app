@extends('layouts.auth')

@section('title', 'Lupa Kata Sandi')

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

        .btn-primary {
            background-color: #22538a;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 500;
            width: 100%;
            margin-top: 10px;
        }

        .btn-outline-secondary {
            border: 1px solid #999;
            background-color: transparent;
            color: #666;
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
            <img src="{{ asset('assets/images/logo.png') }}" alt="Logo">
        </div>

        <h2>Lupa Kata Sandi</h2>
        <p class="subtitle">
            Masukkan alamat email Anda dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi.
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

        <form action="{{ route('password.email') }}" method="POST">
            @csrf
            <div class="input-group">
                <input type="email" class="form-control" id="email" name="email"
                    placeholder="Enter your email address" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Kirim Tautan Reset</button>
            </div>
        </form>

        <div class="back-to-login">
            <p>
                <a href="{{ route('login') }}">
                    <span>Kembali ke Halaman Login</span>
                </a>
            </p>
        </div>
    </div>
@endsection
