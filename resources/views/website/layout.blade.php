<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $appCompanyName ?? 'UTE Parts' }} | Data Barang</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('brand-u.svg') }}">
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/css/core.css') }}">
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/fonts/iconify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('sneat/assets/css/demo.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f7f8fb 0%, #edf5f2 48%, #fff7ea 100%);
            min-height: 100vh;
        }

        .website-navbar {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(56, 69, 81, 0.08);
        }

        .brand-wordmark {
            letter-spacing: 0.1em;
        }

        .product-card {
            border: 0;
            box-shadow: 0 14px 34px rgba(32, 36, 44, 0.08);
            transition: transform 0.16s ease, box-shadow 0.16s ease;
        }

        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 42px rgba(32, 36, 44, 0.12);
        }

        .product-thumb {
            width: 100%;
            height: 132px;
            object-fit: cover;
            background: #f2f3f5;
        }

        .filter-card {
            border: 1px solid rgba(56, 69, 81, 0.08);
            box-shadow: 0 10px 28px rgba(32, 36, 44, 0.07);
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-container .select2-selection--single {
            min-height: calc(2rem + 2px);
            border: 1px solid #d9dee3;
            border-radius: 0.375rem;
            padding: 0.2rem 0.75rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 1.5rem;
            padding-left: 0;
            font-size: 13px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(2rem + 2px);
            right: 0.5rem;
        }

        .select2-container .select2-selection--multiple {
            min-height: calc(2rem + 2px);
            border: 1px solid #d9dee3;
            border-radius: 0.375rem;
            padding: 0.2rem 0.45rem;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: #eef2f7;
            border: 0;
            border-radius: 999px;
            padding: 0.15rem 1.25rem 0.15rem 0.5rem;
            margin-top: 0.15rem;
            position: relative;
            font-size: 12px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            position: absolute;
            right: 0.38rem;
            left: auto;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            margin: 0;
            padding: 0;
            color: #6b7280;
            background: transparent;
        }

        .select2-dropdown {
            border-color: #d9dee3;
        }
    </style>
    @stack('styles')
</head>

<body>
    <nav class="navbar website-navbar sticky-top">
        <div class="container-xxl py-2">
            <a href="{{ route('website.products.index') }}" class="navbar-brand fw-bold text-primary brand-wordmark">
                {{ $appCompanyName ?? 'UTE Parts' }}
            </a>
            <div class="d-flex align-items-center gap-2">
                @if (!empty($customer))
                    <span class="badge bg-label-success d-none d-md-inline-flex">
                        {{ $customer['name'] }} - {{ number_format((int) ($customer['points_balance'] ?? 0), 0, ',', '.') }} poin
                    </span>
                    <a href="{{ route('website.member.password.edit', ['return' => request()->fullUrl()]) }}"
                        class="btn btn-sm btn-outline-primary">
                        Ganti Password
                    </a>
                    <form action="{{ route('website.member.logout') }}" method="POST" class="mb-0">
                        @csrf
                        <button class="btn btn-sm btn-outline-secondary">Logout</button>
                    </form>
                @else
                    <a href="{{ route('website.member.login', ['return' => request()->fullUrl()]) }}"
                        class="btn btn-sm btn-primary">
                        Login Member
                    </a>
                @endif
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <script src="{{ asset('sneat/assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @stack('scripts')
</body>

</html>
