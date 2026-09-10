@extends('layouts.app')

@section('title', 'Input Serial')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Input Serial / IMEI</h4>
            <div class="text-muted">Catat serial barang yang diterima — status otomatis <b>available</b> dan siap dipicking.</div>
        </div>
        <a href="{{ route('item-serials.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>

    @if($products->isEmpty())
        <div class="alert alert-warning">Belum ada produk dengan <b>Has Serial Number</b> aktif.</div>
    @else
        <form wire:submit.prevent="store">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Produk (Serialized)</label>
                            <select wire:model="productId" class="form-select" required>
                                <option value="">Pilih Produk</option>
                                @foreach($products as $product)
                                    <option value="{{ $product['id'] }}">{{ $product['product_code'] }} · {{ $product['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bin / Rak (opsional)</label>
                            <select wire:model="locationRackId" class="form-select">
                                <option value="">-- Tanpa rak --</option>
                                @foreach($racks as $rack)
                                    <option value="{{ $rack['id'] }}">{{ $rack['location']['name'] ?? '' }} / {{ $rack['code'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Daftar Serial / IMEI</label>
                            <textarea wire:model="serials" rows="6" class="form-control font-monospace" required
                                      placeholder="Satu serial per baris (bisa juga dipisah koma):&#10;IMEI-123456789012345&#10;SN-ABC123"></textarea>
                            <div class="form-text">Unique per produk — duplikat otomatis dilewati.</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">Simpan Serial</button>
                </div>
            </div>
        </form>
    @endif
</div>

@livewireScripts
@endsection