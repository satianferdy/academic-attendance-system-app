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
                <li class="nav-item nav-category">Admin</li>
                @if (auth()->user()->role === 'admin')
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
                            <span class="link-title">Class Schedule</span>
                        </a>
                    </li>
                @endif


                {{-- Student Only Menus --}}
                @if (auth()->user()->role === 'student')
                    <li class="nav-item nav-category">General</li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#general" role="button" aria-expanded="false"
                            aria-controls="general">
                            <i class="link-icon" data-feather="inbox"></i>
                            <span class="link-title">General</span>
                            <i class="link-arrow" data-feather="chevron-down"></i>
                        </a>
                        <div class="collapse" id="general">
                            <ul class="nav sub-menu">
                                <li class="nav-item">
                                    <a href="#" class="nav-link">Biodata Mahasiswa</a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link">Unggah Foto KTM</a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link">Register Wajah</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#akademik" role="button" aria-expanded="false"
                            aria-controls="akademik">
                            <i class="link-icon" data-feather="book"></i>
                            <span class="link-title">Akademik</span>
                            <i class="link-arrow" data-feather="chevron-down"></i>
                        </a>
                        <div class="collapse" id="akademik">
                            <ul class="nav sub-menu">
                                <li class="nav-item">
                                    <a href="#" class="nav-link">Kartu Rencana Studi (KRS)</a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link">Jadwal Perkuliahan</a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link">Nilai Mahasiswa</a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link">LMS</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#surat" role="button" aria-expanded="false"
                            aria-controls="surat">
                            <i class="link-icon" data-feather="mail"></i>
                            <span class="link-title">Surat & Kuisioner</span>
                            <i class="link-arrow" data-feather="chevron-down"></i>
                        </a>
                        <div class="collapse" id="surat">
                            <ul class="nav sub-menu">
                                <li class="nav-item">
                                    <a href="#" class="nav-link">Permintaan Surat</a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link">Riwayat Surat</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- Lecturer Only Menus --}}
                @if (auth()->user()->role === 'lecturer')
                    <li class="nav-item nav-category">General</li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#akademik" role="button"
                            aria-expanded="false" aria-controls="akademik">
                            <i class="link-icon" data-feather="book"></i>
                            <span class="link-title">Akademik</span>
                            <i class="link-arrow" data-feather="chevron-down"></i>
                        </a>
                        <div class="collapse" id="akademik">
                            <ul class="nav sub-menu">
                                <li class="nav-item">
                                    <a href="#" class="nav-link">Jadwal Mengajar</a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link">Memulai Kelas</a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link">Entri Nilai</a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link">LMS</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif
            @endauth
        </ul>
    </div>
</nav>
