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
                <li class="nav-item">
                    <a href="{{ route(auth()->user()->role . '.dashboard') }}"
                        class="nav-link {{ request()->routeIs(auth()->user()->role . '.dashboard') ? 'active' : '' }}">
                        <i class="link-icon" data-feather="box"></i>
                        <span class="link-title">Dashboard</span>
                    </a>
                </li>

                {{-- Admin Only Menus --}}
                @if (auth()->user()->role === 'admin')
                    <li class="nav-item nav-category">General</li>
                    <li class="nav-item">
                        <a href="{{ route('admin.users.index') }}"
                            class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="link-icon" data-feather="users"></i>
                            <span class="link-title">User Management</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.schedules.index') }}"
                            class="nav-link {{ request()->routeIs('admin.schedules.*') ? 'active' : '' }}">
                            <i class="link-icon" data-feather="calendar"></i>
                            <span class="link-title">Schedule Management</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.attendance.index') }}"
                            class="nav-link {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
                            <i class="link-icon" data-feather="calendar"></i>
                            <span
                                class="link-title
                                {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">Attendance
                                Management</span>
                        </a>
                    </li>
                @endif


                {{-- Student Only Menus --}}
                @if (auth()->user()->role === 'student')
                    <li class="nav-item nav-category">General</li>
                    {{-- schedule --}}
                    <li class="nav-item">
                        <a href="{{ route('student.schedule.index') }}"
                            class="nav-link {{ request()->routeIs('student.schedule.*') ? 'active' : '' }}">
                            <i class="link-icon" data-feather="calendar"></i>
                            <span
                                class="link-title
                                {{ request()->routeIs('student.schedule.*') ? 'active' : '' }}">Schedule</span>
                        </a>
                    </li>
                    {{-- attendance --}}
                    <li class="nav-item">
                        <a href="{{ route('student.attendance.index') }}"
                            class="nav-link {{ request()->routeIs('student.attendance.*') ? 'active' : '' }}">
                            <i class="link-icon" data-feather="calendar"></i>
                            <span
                                class="link-title
                                {{ request()->routeIs('student.attendance.*') ? 'active' : '' }}">Attendance
                                Records</span>
                        </a>
                    </li>
                    {{-- face --}}
                    <li class="nav-item">
                        <a href="{{ route('student.face.index') }}"
                            class="nav-link {{ request()->routeIs('student.face.*') ? 'active' : '' }}">
                            <i class="link-icon" data-feather="camera"></i>
                            <span
                                class="link-title
                                {{ request()->routeIs('student.face.*') ? 'active' : '' }}">Face
                                Registration</span>
                        </a>
                    </li>
                @endif

                {{-- Lecturer Only Menus --}}
                @if (auth()->user()->role === 'lecturer')
                    <li class="nav-item nav-category">General</li>
                    <li class="nav-item">
                        <a href="{{ route('lecturer.attendance.index') }}"
                            class="nav-link {{ request()->routeIs('lecturer.attendance.*') ? 'active' : '' }}">
                            <i class="link-icon" data-feather="calendar"></i>
                            <span class="link-title">Session</span>
                        </a>
                    </li>
                    {{-- schedule --}}
                    <li class="nav-item">
                        <a href="{{ route('lecturer.schedule.index') }}"
                            class="nav-link {{ request()->routeIs('lecturer.schedule.*') ? 'active' : '' }}">
                            <i class="link-icon" data-feather="calendar"></i>
                            <span
                                class="link-title
                                {{ request()->routeIs('lecturer.schedule.*') ? 'active' : '' }}">Schedule</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('lecturer.attendance-data.index') }}"
                            class="nav-link {{ request()->routeIs('lecturer.attendance-data.*') ? 'active' : '' }}">
                            <i class="link-icon" data-feather="calendar"></i>
                            <span
                                class="link-title
                                {{ request()->routeIs('lecturer.attendance-data.*') ? 'active' : '' }}">Attendance
                                Management</span>
                        </a>
                    </li>
                @endif
            @endauth
        </ul>
    </div>
</nav>
