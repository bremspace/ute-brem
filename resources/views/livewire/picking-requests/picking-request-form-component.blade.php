@extends('layouts.app')

@section('title', '{{ $request ? $request->request_code . " - Edit" : "Buat Picking Request" }}')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">{{ $request ? 'Edit Picking Request' : 'Buat Picking Request' }}</h4>
            <div class="text-muted">
                @if($request)
                    Detail request {{ $request->request_code }} • {{ $request->location->name ?? '-' }}
                @else
                    Pilih produk dan tentukan jumlah untuk pengambilan sparepart servis.
                @endif
            </div>
        </div>
        <a href="{{ route('picking-requests.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Form -->
    <div class="card">
        <div class="card-body">
            <form wire:submit.prevent="{{ $request ? 'update' : 'store' }}">
                <!-- Header -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Lokasi <span class="text-danger">*</span></label>
                        <select name="location_id" wire:model="locationId" class="form-select" required {{ $request ? 'disabled' : '' }}>
                            <option value="">Pilih Lokasi</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc['id'] }}" {{ $locationId == $loc['id'] ? 'selected' : '' }}>
                                    {{ $loc['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Teknisi</label>
                        <select name="technician_id" wire:model="technicianId" class="form-select">
                            <option value="">Pilih Teknisi</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Keterangan</label>
                        <input type="text" name="notes" wire:model="notes" class="form-control" placeholder="Opsional">
                    </div>
                </div>

                <!-- Add Product -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Cari Produk</label>
                        <input type="text" wire:model="productSearch" class="form-control" placeholder="Kode / Nama produk...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" wire:click="addProduct(productSearch)" class="btn btn-primary w-100"
                                @if(empty(productSearch)) disabled @endif>
                            <i class="bx bx-plus me-1"></i> Tambah ke Tabel
                        </button>
                    </div>
                </div>

                <!-- Items Table -->
                @if(!empty($items))
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Produk</th>
                                    <th style="width:120px;">Qty</th>
                                    <th>Stok Tersedia</th>
                                    <th>Catatan</th>
                                    <th style="width:50px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $productId => $item)
                                    <tr>
                                        <td class="fw-semibold">{{ $item['product_code'] }}</td>
                                        <td>{{ $item['product_name'] }}</td>
                                        <td>
                                            <input type="number" min="1" wire:model="items.{{ $productId }}.qty"
                                                   class="form-control form-control-sm" style="width:100px;">
                                        </td>
                                        <td class="text-muted">{{ $stockMap[$productId] ?? '-' }}</td>
                                        <td>
                                            <input type="text" wire:model="items.{{ $productId }}.note"
                                                   class="form-control form-control-sm" placeholder="Catatan">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" wire:click="removeItem({{ $productId }})" class="btn btn-outline-danger btn-sm">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i> {{ $request ? 'Simpan Perubahan' : 'Simpan Request' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($request && $request->status === 'fulfilled')
        <div class="alert alert-success mt-4">
            <i class="bx bx-check-circle me-1"></i>
            Picking ini sudah diproses. Stok gudang telah dipotong dan sparepart berstatus Reserved for Repair.
        </div>
    @endif
</div>

@livewireScripts
@endsection