@extends('layouts.sneat')

@section('title', 'Konfigurasi SID Retail')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Konfigurasi Database SID Retail</h4>
            <div class="text-muted">Atur parameter koneksi database sumber SID Retail.</div>
        </div>
        <a href="{{ route('sid-retail.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Kembali ke Migrasi
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Parameter Koneksi MySQL SID Retail</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('sid-retail.config.save') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Host Database</label>
                            <input type="text" name="host" class="form-control" value="{{ $config['host'] ?? '127.0.0.1' }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Port</label>
                            <input type="number" name="port" class="form-control" value="{{ $config['port'] ?? '3306' }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Database</label>
                            <input type="text" name="database" class="form-control" value="{{ $config['database'] ?? 'toko_1_3' }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username Database</label>
                            <input type="text" name="username" class="form-control" value="{{ $config['username'] ?? 'root' }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" value="{{ $config['password'] ?? '' }}">
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('sid-retail.index') }}" class="btn btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan Konfigurasi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection