@extends('layouts.sneat')

@section('title', 'Input Serial')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h4 class="fw-semibold mb-0">Input Serial / IMEI</h4>
        <small class="text-muted">Catat serial barang yang diterima — status otomatis <b>available</b> dan siap dipicking.</small>
    </div>
    <a href="{{ route('item-serials.index') }}" class="btn btn-light btn-sm">Kembali</a>
</div>

@if ($products->isEmpty())
    <div class="alert alert-warning">Belum ada produk dengan <b>Has Serial Number</b> aktif.</div>
@else
<form method="POST" action="{{ route('item-serials.store') }}">
    @csrf
    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small mb-1">Produk (Serialized)</label>
                    <select name="product_id" class="form-select" required>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}">{{ $p->product_code }} · {{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small mb-1">Bin / Rak (opsional)</label>
                    <select name="location_rack_id" class="form-select">
                        <option value="">-- Tanpa rak --</option>
                        @foreach ($racks as $r)
                            <option value="{{ $r->id }}">{{ $r->location?->name }} / {{ $r->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label small mb-1">Daftar Serial / IMEI</label>
                    <textarea name="serials" rows="6" class="form-control font-monospace" required
                        placeholder="Satu serial per baris (bisa juga dipisah koma):&#10;IMEI-123456789012345&#10;SN-ABC123"></textarea>
                    <div class="form-text">Unique per produk — duplikat otomatis dilewati.</div>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button class="btn btn-primary">Simpan Serial</button>
        </div>
    </div>
</form>
@endif
@endsection
