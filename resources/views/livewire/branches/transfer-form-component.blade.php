@extends('layouts.app')

@section('title', '{{ $transfer ? $transfer->transfer_code . " - Edit" : "Buat Transfer Stok" }}')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">{{ $transfer ? 'Edit Transfer Stok' : 'Buat Transfer Stok' }}</h4>
            <div class="text-muted">
                {{ $transfer ? 'Edit detail transfer stok.' : 'Buat draf pengiriman stok antar cabang/toko.' }}
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('branch-transfers.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Form Card -->
    <div class="card">
        <div class="card-body">
            <form wire:submit.prevent="store">
                <!-- Header Info -->
                <div class="row g-4 mb-4">
                    <!-- Source Branch & Location -->
                    <div class="col-md-6">
                        <label class="form-label">Cabang Asal <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select name="source_branch_id" wire:model="sourceBranchId" class="form-select" required>

                            @if($userBranchId)
                                <option value="" disabled>Pilih Cabang</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $userBranchId == $branch->id ? 'disabled selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                                <input type="hidden" name="source_branch_id" value="{{ $userBranchId }}">
                            @else
                                <option value="">Pilih Cabang Asal</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $sourceBranchId == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach

                            </select>
                        </div>
                    </div>

                    <!-- Target Branch & Location -->
                    <div class="col-md-6">
                        <label class="form-label">Cabang Tujuan <span class="text-danger">*</span></label>
                        <select name="target_branch_id" wire:model="targetBranchId" class="form-select" required>
                            <option value="">Pilih Cabang Tujuan</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $targetBranchId == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Locations -->
                    <div class="col-md-6">
                        <label class="form-label">Lokasi Asal <span class="text-danger">*</span></label>
                        <select name="source_location_id" wire:model="sourceLocationId" class="form-select" required>
                            <option value="">Pilih Lokasi Asal</option>
                            @foreach($sourceLocations as $loc)
                                <option value="{{ $loc->id }}" {{ $sourceLocationId == $loc->id ? 'selected' : '' }}>
                                    {{ $loc->name }} ({{ $loc->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Lokasi Tujuan <span class="text-danger">*</span></label>
                        <select name="target_location_id" wire:model="targetLocationId" class="form-select" required>
                            <option value="">Pilih Lokasi Tujuan</option>
                            @foreach($targetLocations as $loc)
                                <option value="{{ $loc->id }}" {{ $targetLocationId == $loc->id ? 'selected' : '' }}>
                                    {{ $loc->name }} ({{ $loc->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Notes -->
                    <div class="col-12">
                        <label class="form-label">Catatan Pengiriman</label>
                        <textarea name="notes" wire:model="notes" rows="3" class="form-control"
                                  placeholder="Contoh: Stok mingguan cabang, kirim via kurir"></textarea>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Daftar Produk</h5>
                    </div>

                    <!-- Add Product Panel -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Cari Produk</label>
                            <select id="product_lookup" wire:model="selectedProductId" class="form-select select2-basic">
                                <option value="">Pilih berdasarkan nama atau kode...</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-code="{{ $product->product_code }}"
                                            data-name="{{ $product->name }}">
                                        [{{ $product->product_code }}] {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" @click="addProductToItems()" class="btn btn-primary w-100">
                                <i class="bx bx-plus me-1"></i> Tambah ke Tabel
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 35%;">Produk</th>
                                    <th style="width: 20%;">2 Rak Asal</th>
                                    <th style="width: 20%;">Rak Tujuan</th>
                                    <th style="width: 15%;">Jumlah Kirim</th>
                                    <th style="width: 15%;">Catatan</th>
                                    <th style="width: 5%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(empty($items))
                                    <tr id="empty-row">
                                        <td colspan="6" class="text-center text-muted py-4">
                                            Belum ada produk yang ditambahkan. Silakan pilih produk di atas.
                                        </td>
                                    </tr>
                                @endif

                                @foreach($items as $productId => $item)
                                    <tr class="item-row" data-product-id="{{ $productId }}">
                                        <td>
                                            <input type="hidden" name="items[{{ $productId }}][product_id]"
                                                   value="{{ $item['product_id'] }}">
                                            <span class="fw-semibold">[{{ $item['product_code'] }}] {{ $item['product_name'] }}</span>
                                        </td>
                                        <td>
                                            <select name="items[{{ $productId }}][source_location_rack_id]"
                                                    class="form-select form-select-sm source-rack-select">
                                                <option value="">Pilih Rak</option>
                                                @foreach($racks as $rack)
                                                    <option value="{{ $rack->id }}" {{ ($item['source_location_rack_id'] ?? '') == $rack->id ? 'selected' : '' }}>
                                                        {{ $rack->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="items[{{ $productId }}][target_location_rack_id]"
                                                    class="form-select form-select-sm target-rack-select">
                                                <option value="">Pilih Rak</option>
                                                @foreach($racks as $rack)
                                                    <option value="{{ $rack->id }}" {{ ($item['target_location_rack_id'] ?? '') == $rack->id ? 'selected' : '' }}>
                                                        {{ $rack->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0.01" name="items[{{ $productId }}][quantity_sent]"
                                                   class="form-control form-control-sm"
                                                   value="{{ $item['quantity_sent'] }}" required
                                                   placeholder="Qty">
                                        </td>
                                        <td>
                                            <input type="text" name="items[{{ $productId }}][notes]"
                                                   class="form-control form-control-sm"
                                                   value="{{ $item['notes'] ?? '' }}" placeholder="Catatan opsional">
                                        </td>
                                        <td class="text-center">
                                            <button type="button"
                                                    @click="removeItem('{{ $productId }}')"
                                                    class="btn btn-outline-danger btn-sm btn-remove-row">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Simpan Draf
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
{{-- Alpine.js card styles for the rack selects --}}
<style>
    .btn-remove-row {
        padding: 0.25rem 0.5rem;
    }
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px !important;
        padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('.select2-basic').select2({
            width: '100%'
        });

        // Dynamic rack population for newly added items
        document.querySelectorAll('.source-rack-select').forEach(select => {
            select.addEventListener('change', function() {
                // Rack change handled via Alpine.js updateItem
            });
        });
    });
</script>
@endpush

@livewireScripts
@endsection