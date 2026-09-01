@extends('website.layout')

@section('content')
    <div class="container-xxl py-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-1">Ganti Password Member</h4>
                        <p class="text-muted mb-4">Untuk keamanan akun member.</p>

                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">{{ $errors->first() }}</div>
                        @endif

                        <form method="POST" action="{{ route('website.member.password.update') }}">
                            @csrf
                            <input type="hidden" name="return" value="{{ old('return', $returnUrl) }}">

                            <div class="mb-3">
                                <label class="form-label">Password Lama</label>
                                <input type="password" name="current_password"
                                    class="form-control @error('current_password') is-invalid @enderror" required>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password Baru</label>
                                <input type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror" required minlength="8">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Minimal 8 karakter.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" name="password_confirmation" class="form-control" required minlength="8">
                            </div>

                            <div class="d-flex gap-2">
                                <a href="{{ $returnUrl }}" class="btn btn-outline-secondary w-50">Batal</a>
                                <button class="btn btn-primary w-50">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

