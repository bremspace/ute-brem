@extends('website.layout')

@section('content')
    <div class="container-xxl py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-1">Login Member</h4>
                        <p class="text-muted mb-4">Login untuk melihat harga online produk.</p>

                        @if($errors->any())
                            <div class="alert alert-danger">{{ $errors->first() }}</div>
                        @endif

                        <form method="POST" action="{{ route('website.member.login.submit') }}">
                            @csrf
                            <input type="hidden" name="return" value="{{ old('return', $returnUrl) }}">
                            <div class="mb-3">
                                <label class="form-label">Email / No HP</label>
                                <input type="text" name="login" class="form-control" value="{{ old('login') }}" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button class="btn btn-primary w-100">Login</button>
                        </form>
                        <a href="{{ route('website.products.index') }}" class="btn btn-link w-100 mt-2">Kembali ke Data Barang</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
