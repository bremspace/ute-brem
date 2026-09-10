@php
    $formatRupiah = fn($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    @if (session('success'))<div class="alert alert-success alert-dismissible" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if (session('error'))<div class="alert alert-danger alert-dismissible" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back"></i></a>
            <h5 class="mb-0">Buat Purchase Order Baru</h5>
        </div>
        <button type="button" wire:click="submitPO" class="btn btn-primary"><i class="bx bx-save me-1"></i>Simpan PO</button>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Pilih Produk</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Cari Produk</label>
                            <input type="text" wire:model.live.debounce.300ms="productSearch" class="form-control" placeholder="Ketik nama atau kode produk...">
                            @if(!empty($productSearch) && strlen($productSearch) >= 2)
                                <div class="list-group mt-1" style="max-height: 200px; overflow-y: auto;">
                                    @php $products = $this->searchProducts(); @endphp
                                    @foreach($products as $prod)
                                        <button type="button" class="list-group-item list-group-item-action" wire:click="selectProduct({{ $prod['id'] }})">
                                            <div class="fw-semibold">{{ $prod['name'] }}</div>
                                            <small class="text-muted">{{ $prod['code'] }} | Stok: {{ number_format($prod['stock_global']) }} | Min: {{ number_format($prod['stock_min']) }}</small>
                                        </button>
                                    @endforeach
                                    @if(empty($products))
                                        <div class="list-group-item text-muted">Produk tidak ditemukan.</div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($selectedProduct)
                        <div class="alert alert-info mt-3 mb-0">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Produk:</strong> {{ $selectedProduct->name }} ({{ $selectedProduct->product_code }})
                                </div>
                                <div class="col-md-4">
                                    <strong>Stok Global:</strong> {{ number_format($selectedProduct->stock_global) }} {{ $selectedProduct->sale_unit ?: 'PCS' }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Min Stok:</strong> {{ number_format($selectedProduct->stock_min ?? 0) }} {{ $selectedProduct->sale_unit ?: 'PCS' }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if($product_id)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Detail PO</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Supplier</label>
                                <select wire:model="supplier_id" class="form-select" required>
                                    <option value="">Pilih Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Lokasi Tujuan</label>
                                <select wire:model="location_id" class="form-select" required>
                                    <option value="">Pilih Lokasi</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Jumlah (Qty)</label>
                                <input type="number" wire:model="quantity" class="form-control" min="0.01" step="0.01" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Harga Satuan (Rp)</label>
                                <input type="number" wire:model="unit_price" class="form-control" min="0" step="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tanggal Pesan</label>
                                <input type="date" wire:model="ordered_at" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Metode Pembayaran</label>
                                <select wire:model="payment_method" class="form-select" required>
                                    <option value="cash">Cash</option>
                                    <option value="tempo">Tempo</option>
                                </select>
                            </div>
                            <div class="col-md-6" wire:ignore.self x-data="{ tempo: $wire.payment_method === 'tempo' }" x-effect="tempo = $wire.payment_method === 'tempo'">
                                <label class="form-label">Lama Tempo (hari)</label>
                                <input type="number" wire:model="credit_term_days" class="form-control" min="1" x-bind:disabled="!tempo" x-bind:required="tempo">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Catatan</label>
                                <textarea wire:model="notes" class="form-control" rows="2" placeholder="Catatan tambahan untuk PO ini..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0">Ringkasan PO</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6"><span>Qty:</span></div>
                            <div class="col-6 text-end"><strong>{{ number_format($quantity, 2) }} {{ $selectedProduct?->sale_unit ?: 'PCS' }}</strong></div>
                            <div class="col-6"><span>Harga Satuan:</span></div>
                            <div class="col-6 text-end"><strong>{{ $formatRupiah($unit_price ?? 0) }}</strong></div>
                            <hr class="my-2">
                            <div class="col-6"><span class="fw-bold">TOTAL</span></div>
                            <div class="col-6 text-end"><strong class="text-primary fs-5">{{ $formatRupiah($this->totalPrice) }}</strong></div>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-light border text-center py-5">
                    <i class="bx bx-search fs-1 text-muted mb-2"></i>
                    <h6 class="text-muted">Pilih produk terlebih dahulu untuk membuat PO</h6>
                </div>
            @endif
        </div>

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0">Informasi Tambahan</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Pastikan data sudah benar sebelum menyimpan PO. PO yang sudah disimpan akan otomatis memasukkan stok ke lokasi yang dipilih.</p>
                    
                    <div class="d-grid gap-2">
                        <button type="button" wire:click="submitPO" class="btn btn-primary btn-lg">
                            <i class="bx bx-save me-1"></i>Simpan PO
                        </button>
                        <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-x me-1"></i>Batal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
