<form wire:submit.prevent="updatePassword"&#62;
    <div class="mb-3">
        <label class="form-label" for="current_password">Password Lama <span class="text-danger">*</span></label>
        <div class="input-group">
            <input type="password" id="current_password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" wire:model.defer="current_password" required autocomplete="current-password">
            <button type="button" class="btn btn-outline-secondary password-toggle" data-target="current_password" aria-label="Tampilkan password">
                <i class="bx bx-show"></i>
            </button>
        </div>
        <@error('current_password')><div class="invalid-feedback">{{ $message }}</div></@error>
    </div>

    <div class="mb-3">
        <label class="form-label" for="password">Password Baru <span class="text-danger">*</span></label>
        <div class="input-group">
            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" wire:model.defer="password" required autocomplete="new-password">
            <button type="button" class="btn btn-outline-secondary password-toggle" data-target="password" aria-label="Tampilkan password">
                <i class="bx bx-show"></i>
            </button>
        </div>
        <@error('password')><div class="invalid-feedback">{{ $message }}</div></@error>
    </div>

    <div class="mb-4">
        <label class="form-label" for="password_confirmation">Konfirmasi Password Baru <span class="text-danger">*</span></label>
        <div class="input-group">
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" wire:model.defer="password_confirmation" required autocomplete="new-password">
            <button type="button" class="btn btn-outline-secondary password-toggle" data-target="password_confirmation" aria-label="Tampilkan password">
                <i class="bx bx-show"></i>
            </button>
        </div>
        <@error('password_confirmation')><div class="invalid-feedback">{{ $message }}</div></@error>
    </div>

    <div class="text-end">
        <button type="submit" class="btn btn-primary">
            <i class="bx bx-key me-1"></i> Simpan Password
        </button>
    </div>
</form>
<script>
    document.querySelectorAll('.password-toggle').forEach(button => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.target);
            const icon = button.querySelector('i');
            if (!input || !icon) return;
            const hidden = input.type === 'password';
            input.type = hidden ? 'text' : 'password';
            icon.className = hidden ? 'bx bx-hide' : 'bx bx-show';
        });
    });
</script>