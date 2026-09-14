{{-- resources/views/livewire/auth/admin-login.blade.php --}}
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" width="28" height="28">
                    <rect width="28" height="28" rx="6" fill="currentColor"/>
                    <text x="14" y="19" text-anchor="middle" fill="#fff" font-family="Inter, system-ui, sans-serif" font-size="15" font-weight="700">U</text>
                </svg>
            </div>
            <h1 class="auth-title">Masuk Admin</h1>
            <p class="auth-subtitle">Login untuk mengakses panel admin UTE Parts</p>
        </div>
        @if($errorMessage)
            <div class="auth-error auth-error-box">{{ $errorMessage }}</div>
        @endif
        <form wire:submit.prevent="login" class="auth-form">
            <div class="auth-field">
                <label for="admin-login">Email atau Username</label>
                <input type="text" id="admin-login" wire:model.defer="login"
                    class="auth-input @error('login') is-invalid @enderror"
                    placeholder="Masukkan email atau username"
                    autocomplete="username" required
                    value="{{ old('login') }}">
                @error('login')
                    <div class="auth-error">{{ $message }}</div>
                @enderror
            </div>
            <div class="auth-field" x-data="{ show: false }">
                <label for="admin-password">Password</label>
                <div class="auth-input-wrap">
                    <input :type="show ? 'text' : 'password'" id="admin-password" wire:model.defer="password"
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
            <div class="auth-field" style="flex-direction: row; align-items: center; gap: 0.5rem;">
                <input type="checkbox" id="admin-remember" wire:model="remember"
                    class="form-check-input" style="width: auto; margin: 0;">
                <label for="admin-remember" class="auth-subtitle" style="margin: 0;">Remember me</label>
            </div>
            <button type="submit" class="auth-submit">
                Masuk
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                </svg>
            </button>
        </form>
        <div class="auth-footer">
            <a href="{{ route('website.products.index') }}">Kembali ke katalog</a>
        </div>
    </div>
</div>
