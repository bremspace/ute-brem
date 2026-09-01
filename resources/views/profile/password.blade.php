@extends('layouts.sneat')

@section('title', 'Ganti Password')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <div class="fw-semibold mb-1">Periksa input password.</div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Ganti Password</h5>
                        <small class="text-muted">Gunakan password minimal 8 karakter, kombinasi huruf besar/kecil dan angka.</small>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('profile.password.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label" for="current_password">Password Lama <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" id="current_password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required autocomplete="current-password">
                                    <button class="btn btn-outline-secondary password-toggle" type="button" data-target="current_password" aria-label="Tampilkan password">
                                        <i class="bx bx-show"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="password">Password Baru <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                                    <button class="btn btn-outline-secondary password-toggle" type="button" data-target="password" aria-label="Tampilkan password">
                                        <i class="bx bx-show"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label" for="password_confirmation">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required autocomplete="new-password">
                                    <button class="btn btn-outline-secondary password-toggle" type="button" data-target="password_confirmation" aria-label="Tampilkan password">
                                        <i class="bx bx-show"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-key me-1"></i> Simpan Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
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
@endpush
