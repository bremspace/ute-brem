@extends('layouts.app')
@section('title', 'Dokumen Stok')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1">{{ $type === 'correction' ? 'Koreksi Stok' : 'Pemakaian Barang' }}</h4><div class="text-muted">Catat pergerakan stok dari back office.</div></div>
        <button wire:click="openFormModal" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Tambah</button>
    </div>
    @if(session('success'))<div class="alert alert-success alert-dismissible" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    <div class="card"><div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead><tr><th>Kode</th><th>Tanggal</th><th>Produk</th><th>Lokasi</th><th>Jenis</th><th class="text-end">Qty</th></tr></thead>
                <tbody>
                    @forelse($this->documents as $doc)
                        <tr>
                            <td class="small fw-semibold">{{ $doc->document_code }}</td>
                            <td class="small">{{ $doc->document_date->format('d/m/Y') }}</td>
                            <td>{{ $doc->product?->name ?? '-' }}</td>
                            <td>{{ $doc->location?->name ?? '-' }}</td>
                            <td><span class="badge bg-info">{{ str_replace('_', ' ', $doc->movement_type) }}</span></td>
                            <td class="text-end fw-semibold">{{ number_format($doc->quantity, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada dokumen.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div></div>
</div>
<div class="modal fade" x-show="showFormModal" x-transition.opacity role="dialog" tabindex="-1"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Tambah Dokumen Stok</h5><button type="button" class="btn-close" @click="showFormModal = false"></button></div><div class="modal-body">
    <form wire:submit.prevent="store">
        <div class="mb-3"><label class="form-label">Tanggal <span class="text-danger">*</span></label><input type="date" wire:model="documentDate" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Produk <span class="text-danger">*</span></label><select wire:model="productId" class="form-select" required><option value="">Pilih</option>@foreach($this->products as $p)<option value="{{ $p->id }}">{{ $p->product_code }} - {{ $p->name }}</option>@endforeach</select></div>
        <div class="mb-3"><label class="form-label">Lokasi <span class="text-danger">*</span></label><select wire:model="locationId" class="form-select" required><option value="">Pilih</option>@foreach($this->locations as $loc)<option value="{{ $loc->id }}">{{ $loc->name }}</option>@endforeach</select></div>
        <div class="mb-3"><label class="form-label">Tipe Pergerakan <span class="text-danger">*</span></label><select wire:model="movementType" class="form-select" required><option value="adjustment_plus">Adjustment Plus (+)</option><option value="adjustment_minus">Adjustment Minus (-)</option><option value="out">Stok Keluar</option></select></div>
        <div class="mb-3"><label class="form-label">Jumlah <span class="text-danger">*</span></label><input type="number" wire:model="quantity" class="form-control" min="0.01" step="0.01" required></div>
        <div class="mb-3"><label class="form-label">Keterangan</label><input type="text" wire:model="description" class="form-control"></div>
        <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan</button>
    </form>
</div></div></div></div>
@livewireScripts
@endsection