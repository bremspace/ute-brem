{{-- resources/views/livewire/auth/auth-component.blade.php --}}
<div class="auth-page" x-data="{showPassword:false}">
    <div class="auth-card">
        <h2 class="auth-header">
            @if($mode === 'login')
                Masuk Member
            @elseif($mode === 'register')
                Daftar Member
            @elseif($mode === 'password')
                Ganti Password
            @endif
        </h2>
        @if($errorMessage)
            <div class="auth-error" style="padding:0.75rem;background:rgba(239,68,68,0.1);border-radius:var(--c-radius-md);text-align:center;margin-bottom:1rem;">
                {{ $errorMessage }}
            </div>
        @endif
        @if($successMessage)
            <div class="auth-success" style="padding:0.75rem;background:rgba(34,197,94,0.1);border-radius:var(--c-radius-md);text-align:center;margin-bottom:1rem;">
                {{ $successMessage }}
            </div>
        @endif
        <form wire:submit.prevent="submit" class="auth-form">
            @if($mode === 'login')
                <div class="auth-field">
                    <input type="text" wire:model.defer="login" placeholder="Email atau HP" class="auth-input" required>
                    <div class="auth-input-wrap" style="position:relative;">
                        <input :type="showPassword ? 'text' : 'password'" wire:model.defer="password" placeholder="Password" class="auth-input @error('password') is-invalid @enderror" required>
                        <button type="button" @click="showPassword = !showPassword" style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--c-surface-400);">
                            {{-- eye icon --}}
                        </button>
                    </div>
                    @error('login')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                    @error('password')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                </div>
            @elseif($mode === 'register')
                <div class="auth-field">
                    <input type="text" wire:model.defer="name" placeholder="Nama" class="auth-input" required>
                    <input type="email" wire:model.defer="email" placeholder="Email" class="auth-input" required>
                    <input type="text" wire:model.defer="phone" placeholder="No HP" class="auth-input" required>
                    <div class="auth-input-wrap" style="position:relative;">
                        <input :type="showPassword ? 'text' : 'password'" wire:model.defer="password" placeholder="Password" class="auth-input @error('password') is-invalid @enderror" required>
                        <button type="button" @click="showPassword = !showPassword" style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--c-surface-400);">
                            {{-- eye icon --}}
                        </button>
                    </div>
                    <div class="auth-input-wrap" style="position:relative;">
                        <input :type="showPassword ? 'text' : 'password'" wire:model.defer="password_confirmation" placeholder="Ulangi Password" class="auth-input @error('password_confirmation') is-invalid @enderror" required>
                        <button type="button" @click="showPassword = !showPassword" style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--c-surface-400);">
                            {{-- eye icon --}}
                        </button>
                    </div>
                    @error('name')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                    @error('email')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                    @error('phone')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                    @error('password')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                    @error('password_confirmation')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                </div>
            @elseif($mode === 'password')
                <div class="auth-field">
                    <input type="password" wire:model.defer="current_password" placeholder="Password Saat Ini" class="auth-input" required>
                    <div class="auth-input-wrap" style="position:relative;">
                        <input :type="showPassword ? 'text' : 'password'" wire:model.defer="password" placeholder="Password Baru" class="auth-input @error('password') is-invalid @enderror" required>
                        <button type="button" @click="showPassword = !showPassword" style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--c-surface-400);">
                            {{-- eye icon --}}
                        </button>
                    </div>
                    <div class="auth-input-wrap" style="position:relative;">
                        <input :type="showPassword ? 'text' : 'password'" wire:model.defer="password_confirmation" placeholder="Ulangi Password Baru" class="auth-input @error('password_confirmation') is-invalid @enderror" required>
                        <button type="button" @click="showPassword = !showPassword" style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--c-surface-400);">
                            {{-- eye icon --}}
                        </button>
                    </div>
                    @error('current_password')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                    @error('password')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                </div>
            @endif
            <button type="submit" class="auth-btn">
                @if($mode === 'login')
                    Masuk
                @elseif($mode === 'register')
                    Daftar
                @elseif($mode === 'password')
                    Perbarui Password
                @endif
            </button>
        </form>
        <div class="auth-footer">
            @if($mode === 'login')
                Belum punya akun? <a href="{{ route('website.member.register') }}">Daftar</a>
            @elseif($mode === 'register')
                Sudah punya akun? <a href="{{ route('website.member.login') }}">Masuk</a>
            @elseif($mode === 'password')
                <a href="{{ route('website.member.login') }}">Kembali ke Login</a>
            @endif
        </div>
    </div>
</div>
