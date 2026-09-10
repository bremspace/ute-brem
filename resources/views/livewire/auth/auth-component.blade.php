{{-- resources/views/livewire/auth/auth-component.blade.php --}}
<style>
    .auth-page {
        min-height: calc(100vh - var(--c-nav-height, 64px));
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }

    .auth-card {
        width: 100%;
        max-width: 420px;
        background: var(--c-surface-0, #fff);
        border-radius: var(--c-radius-xl, 0.75rem);
        border: 1px solid var(--c-surface-200, #e5e8f0);
        box-shadow: var(--c-shadow-lg, 0 4px 24px rgba(0,0,0,0.06));
        padding: 2.5rem 2rem;
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
        background: var(--c-primary, #5c73f8);
        color: #fff;
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .auth-title {
        font-size: 1.375rem;
        font-weight: 700;
        color: var(--c-surface-800, #1a1d2e);
        margin: 0 0 0.375rem;
    }

    .auth-subtitle {
        font-size: 0.875rem;
        color: var(--c-surface-500, #6b7280);
        margin: 0;
    }

    .auth-form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .auth-field {
        display: flex;
        flex-direction: column;
    }

    .auth-field label {
        display: block;
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--c-surface-700, #374151);
        margin-bottom: 0.375rem;
    }

    .auth-input {
        width: 100%;
        height: 2.75rem;
        padding: 0 0.875rem;
        border: 1px solid var(--c-surface-200, #e5e8f0);
        border-radius: var(--c-radius-md, 0.5rem);
        background: var(--c-surface-0, #fff);
        font-family: inherit;
        font-size: 0.9375rem;
        color: var(--c-surface-800, #1a1d2e);
        transition: all 0.2s ease;
        outline: none;
    }

    .auth-input::placeholder {
        color: var(--c-surface-400, #9ca3af);
    }

    .auth-input:focus {
        border-color: var(--c-primary, #5c73f8);
        box-shadow: 0 0 0 3px rgba(92, 115, 248, 0.12);
    }

    .auth-input.is-invalid {
        border-color: var(--c-danger, #ef4444);
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .auth-input-wrap {
        position: relative;
    }

    .auth-input-wrap .auth-input {
        padding-right: 2.75rem;
    }

    .auth-eye-btn {
        position: absolute;
        right: 0.5rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: var(--c-surface-400, #9ca3af);
        padding: 0.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--c-radius-md, 0.5rem);
        transition: color 0.2s ease;
    }

    .auth-eye-btn:hover {
        color: var(--c-surface-600, #4b5563);
    }

    .auth-error {
        font-size: 0.8125rem;
        color: var(--c-danger, #ef4444);
        margin-top: 0.25rem;
    }

    .auth-error-box {
        background: rgba(239, 68, 68, 0.08);
        padding: 0.75rem 1rem;
        border-radius: var(--c-radius-md, 0.5rem);
        margin-bottom: 0.5rem;
    }

    .auth-success-box {
        background: rgba(34, 197, 94, 0.08);
        padding: 0.75rem 1rem;
        border-radius: var(--c-radius-md, 0.5rem);
        margin-bottom: 0.5rem;
        color: var(--c-success, #16a34a);
    }

    .auth-submit {
        width: 100%;
        height: 2.75rem;
        border: none;
        border-radius: var(--c-radius-md, 0.5rem);
        background: var(--c-primary, #5c73f8);
        color: #fff;
        font-family: inherit;
        font-size: 0.9375rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }

    .auth-submit:hover {
        background: var(--c-primary-hover, #4a5fd4);
        box-shadow: 0 4px 12px rgba(92, 115, 248, 0.3);
    }

    .auth-submit:active {
        transform: scale(0.98);
    }

    .auth-footer {
        text-align: center;
        margin-top: 1.5rem;
        font-size: 0.8125rem;
        color: var(--c-surface-500, #6b7280);
    }

    .auth-footer a {
        color: var(--c-primary, #5c73f8);
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
        color: var(--c-surface-400, #9ca3af);
        font-size: 0.75rem;
        margin: 0.5rem 0;
    }

    .auth-divider::before,
    .auth-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--c-surface-200, #e5e8f0);
    }
</style>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" width="28" height="28">
                    <rect width="28" height="28" rx="6" fill="currentColor"/>
                    <text x="14" y="19" text-anchor="middle" fill="#fff" font-family="Inter, system-ui, sans-serif" font-size="15" font-weight="700">U</text>
                </svg>
            </div>
            @if($mode === 'login')
                <h1 class="auth-title">Masuk Member</h1>
                <p class="auth-subtitle">Login untuk melihat harga online produk</p>
            @elseif($mode === 'register')
                <h1 class="auth-title">Daftar Member</h1>
                <p class="auth-subtitle">Buat akun untuk menikmati harga spesial</p>
            @elseif($mode === 'password')
                <h1 class="auth-title">Ganti Password</h1>
                <p class="auth-subtitle">Perbarui password akun Anda</p>
            @endif
        </div>

        @if($errorMessage)
            <div class="auth-error auth-error-box">{{ $errorMessage }}</div>
        @endif
        @if($successMessage)
            <div class="auth-success-box">{{ $successMessage }}</div>
        @endif

        <form wire:submit.prevent="submit" class="auth-form">
            {{-- LOGIN MODE --}}
            @if($mode === 'login')
                <div class="auth-field">
                    <label for="auth-login">Email atau No HP</label>
                    <input type="text" id="auth-login" wire:model.defer="login"
                        class="auth-input @error('login') is-invalid @enderror"
                        placeholder="Masukkan email atau nomor HP"
                        autocomplete="username" required>
                    @error('login')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-field" x-data="{ show: false }">
                    <label for="auth-password">Password</label>
                    <div class="auth-input-wrap">
                        <input :type="show ? 'text' : 'password'" id="auth-password" wire:model.defer="password"
                            class="auth-input @error('password') is-invalid @enderror"
                            placeholder="Masukkan password"
                            autocomplete="current-password" required>
                        <button type="button" class="auth-eye-btn" @click="show = !show" tabindex="-1">
                            <svg x-show="!show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg x-show="show" x-cloak width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                </div>

            {{-- REGISTER MODE --}}
            @elseif($mode === 'register')
                <div class="auth-field">
                    <label for="auth-name">Nama Lengkap</label>
                    <input type="text" id="auth-name" wire:model.defer="name"
                        class="auth-input @error('name') is-invalid @enderror"
                        placeholder="Masukkan nama lengkap"
                        autocomplete="name" required>
                    @error('name')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="auth-email">Email</label>
                    <input type="email" id="auth-email" wire:model.defer="email"
                        class="auth-input @error('email') is-invalid @enderror"
                        placeholder="Masukkan email"
                        autocomplete="email" required>
                    @error('email')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="auth-phone">Nomor HP</label>
                    <input type="tel" id="auth-phone" wire:model.defer="phone"
                        class="auth-input @error('phone') is-invalid @enderror"
                        placeholder="Masukkan nomor HP"
                        autocomplete="tel" required>
                    @error('phone')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-field" x-data="{ show: false }">
                    <label for="auth-password">Password</label>
                    <div class="auth-input-wrap">
                        <input :type="show ? 'text' : 'password'" id="auth-password" wire:model.defer="password"
                            class="auth-input @error('password') is-invalid @enderror"
                            placeholder="Minimal 8 karakter"
                            autocomplete="new-password" required>
                        <button type="button" class="auth-eye-btn" @click="show = !show" tabindex="-1">
                            <svg x-show="!show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg x-show="show" x-cloak width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-field" x-data="{ show: false }">
                    <label for="auth-password-confirmation">Konfirmasi Password</label>
                    <div class="auth-input-wrap">
                        <input :type="show ? 'text' : 'password'" id="auth-password-confirmation" wire:model.defer="password_confirmation"
                            class="auth-input @error('password_confirmation') is-invalid @enderror"
                            placeholder="Ulangi password"
                            autocomplete="new-password" required>
                        <button type="button" class="auth-eye-btn" @click="show = !show" tabindex="-1">
                            <svg x-show="!show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg x-show="show" x-cloak width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                    @error('password_confirmation')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                </div>

            {{-- PASSWORD MODE --}}
            @elseif($mode === 'password')
                <div class="auth-field" x-data="{ show: false }">
                    <label for="auth-current-password">Password Saat Ini</label>
                    <div class="auth-input-wrap">
                        <input :type="show ? 'text' : 'password'" id="auth-current-password" wire:model.defer="current_password"
                            class="auth-input @error('current_password') is-invalid @enderror"
                            placeholder="Masukkan password saat ini"
                            autocomplete="current-password" required>
                        <button type="button" class="auth-eye-btn" @click="show = !show" tabindex="-1">
                            <svg x-show="!show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg x-show="show" x-cloak width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                    @error('current_password')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-field" x-data="{ show: false }">
                    <label for="auth-new-password">Password Baru</label>
                    <div class="auth-input-wrap">
                        <input :type="show ? 'text' : 'password'" id="auth-new-password" wire:model.defer="password"
                            class="auth-input @error('password') is-invalid @enderror"
                            placeholder="Minimal 8 karakter"
                            autocomplete="new-password" required>
                        <button type="button" class="auth-eye-btn" @click="show = !show" tabindex="-1">
                            <svg x-show="!show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg x-show="show" x-cloak width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-field" x-data="{ show: false }">
                    <label for="auth-password-confirmation">Konfirmasi Password Baru</label>
                    <div class="auth-input-wrap">
                        <input :type="show ? 'text' : 'password'" id="auth-password-confirmation" wire:model.defer="password_confirmation"
                            class="auth-input @error('password_confirmation') is-invalid @enderror"
                            placeholder="Ulangi password baru"
                            autocomplete="new-password" required>
                        <button type="button" class="auth-eye-btn" @click="show = !show" tabindex="-1">
                            <svg x-show="!show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg x-show="show" x-cloak width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                    @error('password_confirmation')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                </div>
            @endif

            <button type="submit" class="auth-submit">
                @if($mode === 'login')
                    Masuk
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                    </svg>
                @elseif($mode === 'register')
                    Daftar Sekarang
                @elseif($mode === 'password')
                    Perbarui Password
                @endif
            </button>
        </form>

        <div class="auth-footer">
            @if($mode === 'login')
                Belum punya akun? <a href="{{ route('website.member.register') }}">Daftar sekarang</a>
            @elseif($mode === 'register')
                Sudah punya akun? <a href="{{ route('website.member.login') }}">Masuk sekarang</a>
            @elseif($mode === 'password')
                <a href="{{ route('website.member.login') }}">Kembali ke Login</a>
            @endif
        </div>

        @if($mode === 'login')
            <div class="auth-divider">atau</div>
            <div class="auth-footer">
                <a href="{{ route('website.products.index') }}">Kembali ke katalog</a>
            </div>
        @endif
    </div>
</div>
