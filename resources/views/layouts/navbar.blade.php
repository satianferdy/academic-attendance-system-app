<nav class="navbar">
    <a href="#" class="sidebar-toggler">
        <i data-feather="menu"></i>
    </a>
    <div class="navbar-content">
        <ul class="navbar-nav">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown"
                    role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <p class="tx-14 text-muted mb-0 me-2">{{ session('nim', 'NIM tidak ditemukan') }}</p>
                    <img class="wd-30 ht-30 rounded-circle" style="object-fit: cover; object-position: center top;"
                        src="https://ui-avatars.com/api/?name=" alt="">
                </a>
                <div class="dropdown-menu p-0" aria-labelledby="profileDropdown">
                    <div class="d-flex flex-column align-items-center border-bottom px-5 py-3">
                        <div class="mb-3">

                            <img class="wd-80 ht-80 rounded-circle"
                                style="object-fit: cover; object-position: center top;"
                                src="https://ui-avatars.com/api/?name=">
                        </div>
                        <div class="text-center">
                            <p class="tx-16 fw-bolder"></p>
                            <p class="tx-12 text-muted"></p>
                        </div>
                    </div>
                    <ul class="list-unstyled p-1">
                        <li class="dropdown-item py-2">
                            <a href="" class="text-body ms-0">
                                <i class="me-2 icon-md" data-feather="user"></i>
                                <span>Profile</span>
                            </a>
                        </li>
                        <li class="dropdown-item py-2">
                            <a href="#" class="text-body ms-0"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="me-2 icon-md" data-feather="log-out"></i>
                                <span>Log Out</span>
                            </a>
                        </li>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </ul>
                </div>
            </li>
        </ul>
    </div>
</nav>
