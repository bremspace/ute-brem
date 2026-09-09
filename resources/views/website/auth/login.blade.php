@extends('website.layout')

@section('title', 'Masuk Member')

@push('styles')
    <style>
        .auth-page {
            min-height: calc(100vh - var(--c-nav-height));
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .auth-card {
            width: 100%;
            max-width: 420px;
            background: var(--c-surface-0);
            border-radius: var(--c-radius-xl);
            border: 1px solid var(--c-surface-200);
            box-shadow: var(--c-shadow-lg);
            padding: 2.5rem 2rem;
            animation: authSlideUp 0.4s ease-out;
        }

        @keyframes authSlideUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: var(--c-primary);
            color: #fff;
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .auth-title {
            font-size: 1.375rem;
            font-weight: 700;
            color: var(--c-surface-800);
            margin: 0 0 0.375rem;
        }

        .auth-subtitle {
            font-size: 0.875rem;
            color: var(--c-surface-500);
            margin: 0;
        }

        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .auth-field label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--c-surface-700);
            margin-bottom: 0.375rem;
        }

        .auth-input {
            width: 100%;
            height: 2.75rem;
            padding: 0 0.875rem;
            border: 1px solid var(--c-surface-200);
            border-radius: var(--c-radius-md);
            background: var(--c-surface-0);
            font-family: inherit;
            font-size: 0.9375rem;
            color: var(--c-surface-800);
            transition: all var(--c-transition);
            outline: none;
        }

        .auth-input::placeholder {
            color: var(--c-surface-400);
        }

        .auth-input:focus {
            border-color: var(--c-primary);
            box-shadow: 0 0 0 3px rgba(var(--c-primary-rgb), 0.12);
        }

        .auth-input.is-invalid {
            border-color: var(--c-danger);
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        .auth-error {
            font-size: 0.8125rem;
            color: var(--c-danger);
            margin-top: 0.25rem;
        }

        .auth-submit {
            width: 100%;
            height: 2.75rem;
            border: none;
            border-radius: var(--c-radius-md);
            background: var(--c-primary);
            color: #fff;
            font-family: inherit;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--c-transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .auth-submit:hover {
            background: var(--c-primary-hover);
            box-shadow: 0 4px 12px rgba(var(--c-primary-rgb), 0.3);
        }

        .auth-submit:active {
            transform: scale(0.98);
        }

        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.8125rem;
            color: var(--c-surface-500);
        }

        .auth-footer a {
            color: var(--c-primary);
            font-weight: 600;
            text-decoration: none;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .auth-divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--c-surface-400);
            font-size: 0.75rem;
            margin: 0.5rem 0;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--c-surface-200);
        }
    </style>
@endpush

@section('content')
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" width="28" height="28">
                        <rect width="28" height="28" rx="6" fill="currentColor"/>
                        <text x="14" y="19" text-anchor="middle" fill="#fff" font-family="Inter, system-ui, sans-serif" font-size="15" font-weight="700">U</text>
                    </svg>
                </div>
                <h1 class="auth-title">Masuk Member</h1>
                <p class="auth-subtitle">Login untuk melihat harga online produk</p>
            </div>

            @if($errors->any())
                <div class="auth-error" style="background: rgba(239, 68, 68, 0.08); padding: 0.75rem 1rem; border-radius: var(--c-radius-md); margin-bottom: 0.5rem;">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('success'))
                <div class="auth-error" style="background: rgba(34, 197, 94, 0.08); padding: 0.75rem 1rem; border-radius: var(--c-radius-md); margin-bottom: 0.5rem; color: var(--c-success);">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('website.member.login.submit') }}" class="auth-form">
                @csrf
                <input type="hidden" name="return" value="{{ old('return', $returnUrl) }}">

                <div class="auth-field">
                    <label for="login">Email atau No HP</label>
                    <input type="text" id="login" name="login"
                        class="auth-input @error('login') is-invalid @enderror"
                        value="{{ old('login') }}"
                        placeholder="Masukkan email atau nomor HP"
                        autocomplete="username"
                        autofocus required>
                    @error('login')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                        class="auth-input @error('password') is-invalid @enderror"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                        required>
                    @error('password')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="auth-submit">
                    Masuk
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                    </svg>
                </button>
            </form>

            <div class="auth-footer">
                Belum punya akun? <a href="{{ route('website.member.register') }}">Daftar sekarang</a>
            </div>

            <div class="auth-divider">atau</div>

            <div class="auth-footer">
                <a href="{{ route('website.products.index') }}">Kembali ke katalog</a>
            </div>
        </div>
    </div>
@endsection
