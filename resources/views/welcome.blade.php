<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Learning Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .welcome-container {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .welcome-card {
            max-width: 500px;
            width: 100%;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background-color: white;
            text-align: center;
        }

        .btn-group {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="welcome-container">
        <div class="welcome-card">
            <h1 class="mb-4">Learning Management System Taek</h1>
            <p class="lead mb-4">Selamat datang di sistem manajemen pembelajaran. Silakan login atau daftar untuk
                melanjutkan.</p>

            <div class="btn-group">
                <a href="{{ route('login') }}" class="btn btn-primary me-2">Login</a>
                <a href="{{ route('register') }}" class="btn btn-outline-primary">Register</a>
            </div>
        </div>
    </div>
</body>

</html>
