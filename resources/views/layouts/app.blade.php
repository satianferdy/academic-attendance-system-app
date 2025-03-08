<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Learning Management System')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #212529;
            color: white;
            padding-top: 20px;
        }

        .sidebar-link {
            color: #adb5bd;
            text-decoration: none;
            display: block;
            padding: 8px 16px;
            margin: 4px 0;
            border-radius: 4px;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .content {
            padding: 20px;
        }

        .navbar {
            border-bottom: 1px solid #dee2e6;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <h4 class="text-center mb-4">LMS</h4>
                <div class="px-3">
                    <h6 class="text-uppercase text-muted mb-2 px-2">Menu</h6>
                    @yield('sidebar')
                </div>
            </div>

            <!-- Main content -->
            <div class="col-md-9 col-lg-10 ms-sm-auto px-0">
                <!-- Navbar -->
                <nav class="navbar navbar-expand-lg navbar-light bg-light px-3">
                    <span class="navbar-brand">@yield('page-title', 'Dashboard')</span>
                    <div class="ms-auto">
                        <span class="me-3">{{ Auth::user()->name }}</span>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary">Logout</button>
                        </form>
                    </div>
                </nav>

                <!-- Content -->
                <div class="content">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
