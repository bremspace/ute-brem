<!-- Main Navbar Component -->
@php
    $pageTitle = $pageTitle ?? 'Dashboard';
    $currentRoute = request()->route()->getName();
@endphp

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container-fluid">
        <!-- Logo -->
        <a class="navbar-brand fw-bold" href="{{ route('home') }}">
            <i class="bi bi-shop text-primary me-2"></i>
            UTE Parts POS
        </a>

        <!-- Toggle Sidebar -->
<button @click="sidebarExpanded = !sidebarExpanded"
        class="navbar-toggler"
        type="button">
    <span class="navbar-toggler-icon"></span>
</button>

        <!-- Right Side Navbar -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto">
                <!-- Page Title (Desktop) -->
                <li class="nav-item d-none d-lg-block">
                    <a class="nav-link fw-medium text-secondary" href="#">
                        {{ $pageTitle }}
                    </a>
                </li>

                <!-- Notifications -->
                <li class="nav-item dropdown">
                    <a href="#"
                       class="nav-link position-relative"
                       data-bs-toggle="dropdown">
                        <i class="bi bi-bell fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            3
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <div class="dropdown-header">
                            Notifications
                        </div>
                        <a class="dropdown-item" href="#">
                            Low stock alert: Injeksi XL 10L
                        </a>
                        <a class="dropdown-item" href="#">
                            Pending approval: PO-124
                        </a>
                        <a class="dropdown-item" href="#">
                            New customer registration
                        </a>
                    </div>
                </li>

                <!-- Date/Time -->
                <li class="nav-item">
                    <a class="nav-link small" href="#">
                        {{ date('l, d F Y H:i') }}
                    </a>
                </li>

                <!-- User Menu -->
                <li class="nav-item dropdown">
                    <a id="navbarDropdown"
                       class="nav-link dropdown-toggle d-flex align-items-center"
                       href="#"
                       role="button"
                       data-bs-toggle="dropdown"
                       aria-haspopup="true"
                       aria-expanded="false">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                             style="width: 36px; height: 36px; min-width: 36px;">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="d-none d-lg-block text-start">
                            <div class="small fw-medium">{{ Auth::user()->name }}</div>
                            <div class="small text-muted">{{ Auth::user()->role->name ?? 'User' }}</div>
                        </div>
                    </a>

                    <div class="dropdown-menu dropdown-menu-end">
                        <div class="dropdown-header">
                            Signed in as
                        </div>
                        <div class="dropdown-item-text small">
                            {{ Auth::user()->email }}
                        </div>
                        <hr class="dropdown-divider">
                        <a class="dropdown-item" href="{{ route('profile.show') }}">
                            <i class="bi bi-person-circle me-2"></i> Profile
                        </a>
                        <a class="dropdown-item" href="{{ route('profile.password.edit') }}">
                            <i class="bi bi-key me-2"></i> Change Password
                        </a>
                        <hr class="dropdown-divider">
                        <a class="dropdown-item text-danger"
                           href="#"
                           wire:click.prevent="logout"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>