<nav class="sidebar">
    <div class="sidebar-header">
        <a href="#" class="sidebar-brand">
            POLTEK<span>BATU</span>
        </a>
        <div class="sidebar-toggler not-active">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
    <div class="sidebar-body">
        <ul class="nav">
            @auth
                {{-- Common Dashboard for all users --}}
                <li class="nav-item nav-category">Main</li>
                <li class="nav-item {{ Request::is(auth()->user()->role . '/dashboard') ? 'active' : '' }}">
                    <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="nav-link">
                        <i class="link-icon" data-feather="box"></i>
                        <span class="link-title">Dashboard</span>
                    </a>
                </li>

                {{-- Admin Only Menus --}}
                @if (auth()->user()->role === 'admin')
                    <li class="nav-item nav-category">General</li>
                    <li class="nav-item {{ Request::is('admin/users*') ? 'active' : '' }}">
                        <a href="{{ route('admin.users.index') }}" class="nav-link">
                            <i class="link-icon" data-feather="users"></i>
                            <span class="link-title">User Management</span>
                        </a>
                    </li>

                    <li class="nav-item {{ Request::is('admin/schedules*') ? 'active' : '' }}">
                        <a href="{{ route('admin.schedules.index') }}" class="nav-link">
                            <i class="link-icon" data-feather="calendar"></i>
                            <span class="link-title">Schedule Management</span>
                        </a>
                    </li>

                    <li class="nav-item {{ Request::is('admin/attendance*') ? 'active' : '' }}">
                        <a href="{{ route('admin.attendance.index') }}" class="nav-link">
                            <i class="link-icon" data-feather="calendar"></i>
                            <span class="link-title">Attendance Management</span>
                        </a>
                    </li>

                    <li class="nav-item {{ Request::is('admin/face-requests*') ? 'active' : '' }}">
                        <a href="{{ route('admin.face-requests.index') }}" class="nav-link">
                            <i class="link-icon" data-feather="camera"></i>
                            <span class="link-title">Face Update Requests</span>
                        </a>
                    </li>
                @endif

                {{-- Student Only Menus --}}
                @if (auth()->user()->role === 'student')
                    <li class="nav-item nav-category">General</li>
                    <li class="nav-item {{ Request::is('student/schedule*') ? 'active' : '' }}">
                        <a href="{{ route('student.schedule.index') }}" class="nav-link">
                            <i class="link-icon" data-feather="calendar"></i>
                            <span class="link-title">Schedule</span>
                        </a>
                    </li>
                    <li class="nav-item {{ Request::is('student/attendance*') ? 'active' : '' }}">
                        <a href="{{ route('student.attendance.index') }}" class="nav-link">
                            <i class="link-icon" data-feather="calendar"></i>
                            <span class="link-title">Attendance Records</span>
                        </a>
                    </li>
                    <li class="nav-item {{ Request::is('student/face*') ? 'active' : '' }}">
                        <a href="{{ route('student.face.index') }}" class="nav-link">
                            <i class="link-icon" data-feather="camera"></i>
                            <span class="link-title">Face Registration</span>
                        </a>
                    </li>
                @endif

                {{-- Lecturer Only Menus --}}
                @if (auth()->user()->role === 'lecturer')
                    <li class="nav-item nav-category">General</li>
                    <li
                        class="nav-item {{ Request::is('lecturer/attendance*') && !Request::is('lecturer/attendance-data*') ? 'active' : '' }}">
                        <a href="{{ route('lecturer.attendance.index') }}" class="nav-link">
                            <i class="link-icon" data-feather="calendar"></i>
                            <span class="link-title">Session</span>
                        </a>
                    </li>
                    <li class="nav-item {{ Request::is('lecturer/schedule*') ? 'active' : '' }}">
                        <a href="{{ route('lecturer.schedule.index') }}" class="nav-link">
                            <i class="link-icon" data-feather="calendar"></i>
                            <span class="link-title">Schedule</span>
                        </a>
                    </li>
                    <li class="nav-item {{ Request::is('lecturer/attendance-data*') ? 'active' : '' }}">
                        <a href="{{ route('lecturer.attendance-data.index') }}" class="nav-link">
                            <i class="link-icon" data-feather="calendar"></i>
                            <span class="link-title">Attendance Management</span>
                        </a>
                    </li>
                @endif
            @endauth
        </ul>
    </div>
</nav>
