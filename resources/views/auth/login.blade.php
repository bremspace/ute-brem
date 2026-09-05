@extends('layouts.auth')

@section('content')
    <div class="login-page">
        <div class="container-xxl login-center">
            <div class="login-shell row g-0 align-items-stretch">
                <div class="col-lg-5 col-md-6 login-pane d-flex align-items-center">
                    <div class="w-100 p-4 p-md-5">
                        <div class="text-center text-md-start mb-4">
                            <div class="logo-only mb-3">
                                <span class="brand-mark">UTE Parts</span>
                            </div>
                            <h1 class="fw-bold mb-1">UTE Parts</h1>
                            <p class="text-muted mb-0">Welcome back! Please enter your details.</p>
                        </div>

                        <form method="POST" action="{{ route('login') }}" class="d-grid gap-3">
                            @csrf
                            <div>
                                <label class="form-label" for="username">Username or Email</label>
                                <div class="input-icon">
                                    <input
                                        type="text"
                                        class="form-control form-control-lg @error('username') is-invalid @enderror"
                                        id="username"
                                        name="username"
                                        value="{{ old('username') }}"
                                        placeholder="Enter your username"
                                        autofocus
                                        required
                                    />
                                    <i class="bx bx-user"></i>
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="form-label mb-0" for="password">Password</label>
                                    <a href="#" class="small text-primary text-decoration-none">Forgot password?</a>
                                </div>
                                <div class="input-icon">
                                    <input
                                        type="password"
                                        class="form-control form-control-lg @error('password') is-invalid @enderror"
                                        id="password"
                                        name="password"
                                        placeholder="********"
                                        required
                                    />
                                    <button type="button" class="password-toggle" data-target="password" aria-label="Tampilkan password">
                                        <i class="bx bx-show"></i>
                                    </button>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                    {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>

                            <button class="btn btn-primary btn-lg w-100 d-flex align-items-center justify-content-center gap-2" type="submit">
                                <span>Sign In</span>
                            </button>
                        </form>

                        <p class="text-center text-muted mt-4 mb-0">
                            Don't have an account? <a href="#" class="fw-semibold text-primary text-decoration-none">Sign up now</a>
                        </p>
                    </div>
                </div>

                <div class="col-lg-7 col-md-6 d-none d-md-flex updates-pane">
                    <div class="updates-overlay"></div>
                    <div class="updates-content d-flex flex-column justify-content-start p-5">
                        <button class="updates-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#updatesCollapse"
                            aria-expanded="false" aria-controls="updatesCollapse">
                            <span class="d-flex align-items-center gap-2">
                                <i class="bx bx-bell text-primary"></i>
                                <span>Latest Updates</span>
                            </span>
                            <i class="bx bx-chevron-down"></i>
                        </button>

                        <div class="collapse" id="updatesCollapse">
                            <div class="updates-scroll mt-3">
                                <div class="update-card">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge rounded-pill text-bg-primary">v2.4.0</span>
                                        <span class="small text-light text-opacity-75">Today</span>
                                    </div>
                                    <h5 class="text-white mb-1">Dark Mode Enhanced</h5>
                                    <p class="text-light text-opacity-75 mb-0">
                                        We've overhauled the dark mode experience for better contrast and accessibility.
                                    </p>
                                </div>

                                <div class="update-card">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge rounded-pill text-bg-secondary">v2.3.0</span>
                                        <span class="small text-light text-opacity-75">2 weeks ago</span>
                                    </div>
                                    <h5 class="text-white mb-1">Security Update</h5>
                                    <p class="text-light text-opacity-75 mb-0">
                                        Two-factor authentication is now available for admin accounts.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="login-glow login-glow-left"></div>
        <div class="login-glow login-glow-right"></div>
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Spline+Sans:wght@300;400;500;600;700&display=swap');

        .login-page {
            background: #f0e9f2;
            min-height: 100vh;
            font-family: "Spline Sans", "Public Sans", sans-serif;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        html,
        body {
            background: #f0e9f2;
        }

        .container-fluid {
            padding: 0;
        }

        .login-center {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 0;
        }

        .login-shell {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #eadff0;
            box-shadow: 0 24px 60px rgba(32, 15, 35, 0.15);
            overflow: hidden;
            max-width: 1100px;
            width: 100%;
        }

        .logo-only {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 52px;
            padding: 0 18px;
            border-radius: 999px;
            background: #140717;
            color: #fff;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .login-pane {
            background: #ffffff;
        }

        .updates-pane {
            position: relative;
            color: #fff;
            background: #140717;
            min-height: 100%;
        }

        .updates-pane::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuBXxVPG81z1WACjLjTefhKh0wf9p_pcVz-Q8nQqyKPpTtWOdwyBohECDj0HUuVY4hml04NuCagTZJDAeOYRx2M4t6ngaKZHCf_YRBnWzwzTFtfnLvHoEk9R2eexRM-VzGxgzh2bQsv-o6dU2LZgZGmpb4O5c9zGe1kEOTgxTFTBVhaWMDESISncUiaRpuCPT4uGuomrPyYVinSVn1KCXc7k_qZc8dUBgwhiHgQwPqvAVGB2bbPC9b6iiJC_PEIGvE9IUhn8tHHiSA");
            background-size: cover;
            background-position: center;
            opacity: 0.45;
            mix-blend-mode: screen;
        }

        .updates-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(32, 15, 35, 0.95), rgba(45, 27, 50, 0.8), rgba(92, 115, 248, 0.15));
            z-index: 1;
        }

        .updates-content {
            position: relative;
            z-index: 2;
            height: 100%;
        }

        .updates-scroll {
            max-height: none;
            overflow: visible;
            padding-right: 0;
            display: grid;
            gap: 16px;
        }

        .updates-toggle {
            background: rgba(20, 7, 23, 0.65);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #fff;
            border-radius: 10px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 600;
            transition: background 0.2s ease;
        }

        .updates-toggle:hover {
            background: rgba(56, 28, 61, 0.8);
        }

        .updates-toggle .bx-chevron-down {
            transition: transform 0.2s ease;
        }

        .updates-toggle[aria-expanded="true"] .bx-chevron-down {
            transform: rotate(180deg);
        }

        .update-card {
            background: rgba(56, 28, 61, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 18px;
            backdrop-filter: blur(8px);
        }

        .input-icon {
            position: relative;
        }

        .input-icon input {
            padding-right: 44px;
        }

        .input-icon i {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #b8a4bd;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #8f7895;
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
        }

        .password-toggle:hover,
        .password-toggle:focus {
            background: #f0e9f2;
            color: #140717;
        }

        .password-toggle i {
            position: static;
            transform: none;
            color: inherit;
        }

        .btn-primary {
            background-color: #5c73f8;
            border-color: #5c73f8;
            box-shadow: 0 6px 16px rgba(92, 115, 248, 0.35);
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background-color: #4a5fd4;
            border-color: #4a5fd4;
        }

        .login-glow {
            position: absolute;
            width: 360px;
            height: 360px;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.25;
            pointer-events: none;
        }

        .login-glow-left {
            top: -120px;
            left: -120px;
            background: rgba(92, 115, 248, 0.5);
        }

        .login-glow-right {
            bottom: -120px;
            right: -120px;
            background: rgba(90, 105, 255, 0.4);
        }

        @media (max-width: 991.98px) {
            .login-shell {
                border-radius: 10px;
            }
        }

        @media (max-width: 767.98px) {
            .login-shell {
                border-radius: 10px;
            }
        }
    </style>

    <script>
        document.querySelectorAll('.password-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const input = document.getElementById(button.dataset.target);
                const icon = button.querySelector('i');

                if (!input || !icon) return;

                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                icon.className = isHidden ? 'bx bx-hide' : 'bx bx-show';
            });
        });
    </script>
@endsection
