@extends('website.layout')

@section('title', 'Daftar Member')

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
            gap: 1rem;
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

        .auth-input::placeholder { color: var(--c-surface-400); }
        .auth-input:focus { border-color: var(--c-primary); box-shadow: 0 0 0 3px rgba(var(--c-primary-rgb), 0.12); }
        .auth-input.is-invalid { border-color: var(--c-danger); box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1); }

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
            margin-top: 0.5rem;
        }

        .auth-submit:hover { background: var(--c-primary-hover); box-shadow: 0 4px 12px rgba(var(--c-primary-rgb), 0.3); }
        .auth-submit:active { transform: scale(0.98); }

        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.8125rem;
            color: var(--c-surface-500);
        }

        .auth-footer a { color: var(--c-primary); font-weight: 600; text-decoration: none; }
        .auth-footer a:hover { text-decoration: underline; }
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
                <h1 class="auth-title">Daftar Member</h1>
                <p class="auth-subtitle">Buat akun untuk menikmati harga spesial</p>
            </div>

            @if($errors->any())
                <div class="auth-error" style="background: rgba(239, 68, 68, 0.08); padding: 0.75rem 1rem; border-radius: var(--c-radius-md); margin-bottom: 0.5rem;">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('website.member.register.submit') }}" class="auth-form">
                @csrf

                <div class="auth-field">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" id="name" name="name"
                        class="auth-input @error('name') is-invalid @enderror"
                        value="{{ old('name') }}"
                        placeholder="Masukkan nama lengkap"
                        autocomplete="name"
                        autofocus required>
                    @error('name')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
                        class="auth-input @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        placeholder="Masukkan email"
                        autocomplete="email"
                        required>
                    @error('email')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="phone">Nomor HP</label>
                    <input type="tel" id="phone" name="phone"
                        class="auth-input @error('phone') is-invalid @enderror"
                        value="{{ old('phone') }}"
                        placeholder="Masukkan nomor HP"
                        autocomplete="tel"
                        required>
                    @error('phone')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                        class="auth-input @error('password') is-invalid @enderror"
                        placeholder="Minimal 8 karakter"
                        autocomplete="new-password"
                        required>
                    @error('password')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="password_confirmation">Konfirmasi Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="auth-input"
                        placeholder="Ulangi password"
                        autocomplete="new-password"
                        required>
                </div>

                <button type="submit" class="auth-submit">Daftar Sekarang</button>
            </form>

            <div class="auth-footer">
                Sudah punya akun? <a href="{{ route('website.member.login') }}">Masuk sekarang</a>
            </div>
        </div>
    </div>
@endsection
