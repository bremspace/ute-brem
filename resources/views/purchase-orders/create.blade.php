@extends('layouts.sneat')

@section('title', 'Buat Purchase Order')

@php
    $selectedSupplierId = old('supplier_id', $primarySupplier?->supplier_id);
    $selectedLocationId = old('location_id', $selectedLocationId);
    $selectedUnitPrice = old('unit_price', $primarySupplier?->last_purchase_price ?? $product->purchase_price);
@endphp

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <div class="fw-semibold mb-1">Periksa input purchase order.</div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h4 class="mb-1">Buat Purchase Order</h4>
                <div class="text-muted">{{ $product->name }} - {{ $product->product_code }}</div>
            </div>
            <a href="{{ route('products.stocks.index', $product) }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali ke Stok
            </a>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Ringkasan Stok</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="text-muted small">Stok sekarang</div>
                            <div class="fs-4 fw-bold">{{ rtrim(rtrim(number_format((float) $product->stock_global, 2, ',', '.'), '0'), ',') }} {{ $product->sale_unit ?: 'PCS' }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small">Minimum global</div>
                            <div class="fw-semibold">{{ $product->stock_min !== null ? rtrim(rtrim(number_format((float) $product->stock_min, 2, ',', '.'), '0'), ',') : '-' }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small">Supplier utama</div>
                            <div class="fw-semibold">{{ $primarySupplier?->supplier?->name ?: '-' }}</div>
                        </div>
                        <div class="alert alert-label-warning mb-0">
                            Setelah PO disimpan, stok langsung masuk sebagai mutasi `Stok Masuk`.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Detail Pembelian</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('purchase-orders.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="return_to" value="stock">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Supplier <span class="text-danger">*</span></label>
                                    <select name="supplier_id" class="form-select" required>
                                        <option value="">Pilih supplier</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" {{ (string) $selectedSupplierId === (string) $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Lokasi Masuk <span class="text-danger">*</span></label>
                                    <select name="location_id" class="form-select" required>
                                        <option value="">Pilih lokasi</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}" {{ (string) $selectedLocationId === (string) $location->id ? 'selected' : '' }}>
                                                {{ $location->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Qty Beli <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0.01" name="quantity" class="form-control" value="{{ old('quantity', $suggestedQty) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Harga Beli / Unit</label>
                                    <input type="number" step="0.01" min="0" name="unit_price" class="form-control" value="{{ $selectedUnitPrice }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal</label>
                                    <input type="datetime-local" name="ordered_at" class="form-control" value="{{ old('ordered_at', now()->format('Y-m-d\TH:i')) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                                    <select name="payment_method" id="paymentMethodSelect" class="form-select" required>
                                        <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Tunai / Cash</option>
                                        <option value="tempo" {{ old('payment_method') === 'tempo' ? 'selected' : '' }}>Tempo / Kredit</option>
                                    </select>
                                </div>
                                <div class="col-md-4 tempo-field d-none">
                                    <label class="form-label">Jatuh Tempo (Hari)</label>
                                    <input type="number" name="credit_term_days" class="form-control" min="1" value="{{ old('credit_term_days', 30) }}">
                                </div>
                                <div class="col-md-4 tempo-field d-none">
                                    <label class="form-label">Uang Muka / DP (Rp)</label>
                                    <input type="number" name="paid_amount" class="form-control" min="0" value="{{ old('paid_amount', 0) }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Catatan</label>
                                    <textarea name="notes" rows="3" class="form-control" placeholder="Contoh: restock karena stok minimum">{{ old('notes') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Attachment</label>
                                    <input type="file" name="attachments[]" class="form-control" multiple accept=".jpg,.jpeg,.png,.webp,.pdf,.xls,.xlsx,.doc,.docx">
                                    <div class="form-text">Bisa upload lebih dari 1 file. Format: gambar, PDF, Excel, atau Word. Maksimal 5 MB per file.</div>
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-purchase-tag me-1"></i> Simpan PO & Tambah Stok
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
    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('paymentMethodSelect');
        const fields = document.querySelectorAll('.tempo-field');

        function toggleFields() {
            if (select.value === 'tempo') {
                fields.forEach(el => el.classList.remove('d-none'));
            } else {
                fields.forEach(el => el.classList.add('d-none'));
            }
        }

        select.addEventListener('change', toggleFields);
        toggleFields(); // Initial run
    });
</script>
@endpush
