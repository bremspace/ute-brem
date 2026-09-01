@extends('layouts.sneat')

@section('title', 'Tambah Cabang')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Tambah Cabang Toko</h5>
            <a href="{{ route('branches.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back me-1"></i>Kembali</a>
        </div>
        <div class="card-body">
            <form action="{{ route('branches.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label" for="name">Nama Cabang <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="code">Kode Cabang <span class="text-danger">*</span></label>
                    <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror"
                        value="{{ old('code') }}" required placeholder="Contoh: HO, SBY, JKT">
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="phone">No. Telepon</label>
                    <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror"
                        value="{{ old('phone') }}">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="address">Alamat Cabang</label>
                    <textarea id="address" name="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address') }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="is_main" name="is_main" value="1"
                        {{ old('is_main') ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_main">Jadikan Cabang Utama / Pusat</label>
                    <div class="form-text text-muted">Hanya boleh ada 1 Cabang Utama. Jika diaktifkan, Cabang Utama sebelumnya otomatis menjadi Cabang biasa.</div>
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                        {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Cabang Aktif</label>
                </div>

                <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Simpan</button>
                <a href="{{ route('branches.index') }}" class="btn btn-secondary"><i class="bx bx-x me-1"></i>Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection
