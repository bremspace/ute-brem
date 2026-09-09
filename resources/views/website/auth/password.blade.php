@extends('website.layout')

@section('title', 'Ganti Password')

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

        .auth-input::placeholder { color: var(--c-surface-400); }
        .auth-input:focus { border-color: var(--c-primary); box-shadow: 0 0 0 3px rgba(var(--c-primary-rgb), 0.12); }
        .auth-input.is-invalid { border-color: var(--c-danger); box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1); }

        .auth-error {
            font-size: 0.8125rem;
            color: var(--c-danger);
            margin-top: 0.25rem;
        }

        .auth-hint {
            font-size: 0.75rem;
            color: var(--c-surface-400);
            margin-top: 0.25rem;
        }

        .auth-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 0.5rem;
        }

        .auth-submit {
            flex: 1;
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
        }

        .auth-submit:hover { background: var(--c-primary-hover); box-shadow: 0 4px 12px rgba(var(--c-primary-rgb), 0.3); }
        .auth-submit:active { transform: scale(0.98); }

        .auth-cancel {
            flex: 1;
            height: 2.75rem;
            border: 1px solid var(--c-surface-200);
            border-radius: var(--c-radius-md);
            background: var(--c-surface-0);
            color: var(--c-surface-700);
            font-family: inherit;
            font-size: 0.9375rem;
            font-weight: 500;
            cursor: pointer;
            transition: all var(--c-transition);
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-cancel:hover { background: var(--c-surface-100); border-color: var(--c-surface-300); }
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
                <h1 class="auth-title">Ganti Password</h1>
                <p class="auth-subtitle">{{ $customer['name'] ?? 'Member' }} &middot; Untuk keamanan akun</p>
            </div>

            @if(session('success'))
                <div style="background: rgba(34, 197, 94, 0.08); padding: 0.75rem 1rem; border-radius: var(--c-radius-md); margin-bottom: 0.5rem; color: var(--c-success); font-size: 0.875rem;">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="auth-error" style="background: rgba(239, 68, 68, 0.08); padding: 0.75rem 1rem; border-radius: var(--c-radius-md); margin-bottom: 0.5rem;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('website.member.password.update') }}" class="auth-form">
                @csrf
                <input type="hidden" name="return" value="{{ old('return', $returnUrl) }}">

                <div class="auth-field">
                    <label for="current_password">Password Lama</label>
                    <input type="password" id="current_password" name="current_password"
                        class="auth-input @error('current_password') is-invalid @enderror"
                        placeholder="Masukkan password lama"
                        autocomplete="current-password"
                        required>
                    @error('current_password')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="password">Password Baru</label>
                    <input type="password" id="password" name="password"
                        class="auth-input @error('password') is-invalid @enderror"
                        placeholder="Minimal 8 karakter"
                        autocomplete="new-password"
                        required>
                    @error('password')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                    <div class="auth-hint">Minimal 8 karakter</div>
                </div>

                <div class="auth-field">
                    <label for="password_confirmation">Konfirmasi Password Baru</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="auth-input"
                        placeholder="Ulangi password baru"
                        autocomplete="new-password"
                        required>
                </div>

                <div class="auth-actions">
                    <a href="{{ $returnUrl }}" class="auth-cancel">Batal</a>
                    <button type="submit" class="auth-submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
