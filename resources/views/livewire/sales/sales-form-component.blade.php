@php
    $formatRupiah = fn($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
@endphp

<div class="container-fluid py-3">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Top Bar / Controls --}}
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-md-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge {{ $hasActiveCashSession ? 'bg-success' : 'bg-warning text-dark' }} px-3 py-2">
                            <i class="bx {{ $hasActiveCashSession ? 'bx-check-circle' : 'bx-time' }} me-1"></i>
                            {{ $hasActiveCashSession ? 'Sesi Kas: Aktif' : 'Kas Belum Dibuka' }}
                        </span>
                        @if (! $hasActiveCashSession)
                            <button type="button" @click="$wire.set('showCashSessionModal', true)" class="btn btn-sm btn-primary">
                                Buka Kas
                            </button>
                        @endif
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted mb-0">Lokasi / Gudang:</label>
                    <select wire:model.live="location_id" class="form-select form-select-sm">
                        @foreach ($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted mb-0">Channel Penjualan:</label>
                    <select wire:model.live="saleChannel" class="form-select form-select-sm">
                        <option value="toko">Toko / Retail</option>
                        <option value="cabang">Cabang</option>
                        <option value="partai">Partai / Grosir</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 text-end">
                    <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bx bx-list-ul me-1"></i> Riwayat Transaksi
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- POS Work Area --}}
    <div class="row g-3">
        {{-- Left: Scanner, Search & Cart --}}
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 mb-3">
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bx bx-barcode-reader"></i>
                                </span>
                                <input type="text" wire:model="barcodeInput" wire:keydown.enter="scanBarcode"
                                    class="form-control" placeholder="Scan barcode / ketik kode produk lalu Enter..." autofocus />
                            </div>
                        </div>
                        <div class="col-12 col-md-6 position-relative">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bx bx-search"></i>
                                </span>
                                <input type="text" wire:model.live.debounce.300ms="productSearch"
                                    class="form-control" placeholder="Cari nama produk..." />
                            </div>
                            @if (! empty($searchResults))
                                <div class="list-group position-absolute w-100 shadow mt-1 z-3" style="max-height: 240px; overflow-y: auto;">
                                    @foreach ($searchResults as $prod)
                                        <button type="button" wire:click="addProductToCart({{ $prod->id }})"
                                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2">
                                            <div>
                                                <div class="fw-bold">{{ $prod->name }}</div>
                                                <small class="text-muted">{{ $prod->product_code }} · Stok: {{ $prod->stock_global }}</small>
                                            </div>
                                            <span class="badge bg-primary rounded-pill">{{ $formatRupiah($prod->selling_price) }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cart Table --}}
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0 fw-bold">Keranjang Belanja ({{ count($cart) }} item)</h6>
                    @if (! empty($cart))
                        <button type="button" wire:click="clearCart" class="btn btn-xs btn-outline-danger">
                            <i class="bx bx-trash me-1"></i> Kosongkan
                        </button>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Produk</th>
                                <th style="width: 130px;">Harga</th>
                                <th style="width: 140px;">Jumlah</th>
                                <th style="width: 140px;">Subtotal</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cart as $index => $item)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $item['product_name'] }}</div>
                                        <small class="text-muted">{{ $item['product_code'] }}</small>
                                    </td>
                                    <td>{{ $formatRupiah($item['unit_price']) }}</td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <button type="button" class="btn btn-outline-secondary"
                                                wire:click="updateQuantity({{ $index }}, {{ max(0, $item['quantity'] - 1) }})">-</button>
                                            <input type="number" step="any" min="0" class="form-control text-center px-1"
                                                value="{{ $item['quantity'] }}"
                                                wire:change="updateQuantity({{ $index }}, $event.target.value)" />
                                            <button type="button" class="btn btn-outline-secondary"
                                                wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})">+</button>
                                        </div>
                                    </td>
                                    <td class="fw-bold text-primary">{{ $formatRupiah($item['subtotal']) }}</td>
                                    <td>
                                        <button type="button" wire:click="removeItem({{ $index }})" class="btn btn-xs btn-outline-danger">
                                            <i class="bx bx-x"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bx bx-cart fs-1 mb-2"></i>
                                        <p class="mb-0">Belum ada item dalam keranjang. Silakan scan barcode atau cari produk.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right: Checkout & Payment --}}
        <div class="col-12 col-lg-4">
            {{-- Big Total Banner --}}
            <div class="card border-0 shadow-sm rounded-3 mb-3 text-center p-4" style="background: #eef2ff; border: 2px solid #c7d2fe !important;">
                <div class="text-muted small fw-bold mb-1">TOTAL BAYAR</div>
                <div class="display-5 fw-bold" style="color: #4338ca;">
                    {{ $formatRupiah($this->grandTotal) }}
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3 p-3">
                <form wire:submit.prevent="submitSale">
                    {{-- Customer Selector --}}
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pelanggan:</label>
                        <select wire:model.live="customer_id" class="form-select form-select-sm">
                            <option value="">-- Umum / Non-Member --</option>
                            @foreach ($customers as $cust)
                                <option value="{{ $cust->id }}">
                                    {{ $cust->name }} {{ $cust->phone ? "({$cust->phone})" : '' }} [Poin: {{ $cust->points_balance ?? 0 }}]
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Payment Method --}}
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Metode Pembayaran:</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" wire:model.live="payment_method" value="cash" class="btn-check" id="pay_cash" autocomplete="off" checked>
                            <label class="btn btn-outline-primary btn-sm" for="pay_cash">Tunai</label>

                            <input type="radio" wire:model.live="payment_method" value="transfer" class="btn-check" id="pay_transfer" autocomplete="off">
                            <label class="btn btn-outline-primary btn-sm" for="pay_transfer">Transfer</label>

                            <input type="radio" wire:model.live="payment_method" value="qris" class="btn-check" id="pay_qris" autocomplete="off">
                            <label class="btn btn-outline-primary btn-sm" for="pay_qris">QRIS</label>

                            <input type="radio" wire:model.live="payment_method" value="tempo" class="btn-check" id="pay_tempo" autocomplete="off">
                            <label class="btn btn-outline-primary btn-sm" for="pay_tempo">Tempo</label>
                        </div>
                    </div>

                    {{-- Cash Input & Change --}}
                    @if ($payment_method === 'cash')
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Jumlah Dibayar:</label>
                            <input type="number" step="any" min="0" wire:model.live="paid_amount"
                                class="form-control form-control-lg fw-bold text-end" placeholder="0" />
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded">
                            <span class="small text-muted fw-bold">Kembalian:</span>
                            <span class="h5 mb-0 fw-bold {{ $this->changeAmount > 0 ? 'text-success' : 'text-muted' }}">
                                {{ $formatRupiah($this->changeAmount) }}
                            </span>
                        </div>
                    @else
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nomor Referensi / Bukti:</label>
                            <input type="text" wire:model="payment_reference" class="form-control form-control-sm" placeholder="Nomor resi / ref transfer" />
                        </div>
                    @endif

                    {{-- Notes --}}
                    <div class="mb-3">
                        <label class="form-label small text-muted">Catatan (opsional):</label>
                        <textarea wire:model="notes" class="form-control form-control-sm" rows="2" placeholder="Catatan transaksi..."></textarea>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-bold shadow"
                        style="background: #5c73f8; border-color: #5c73f8;"
                        @if (empty($cart) || ! $hasActiveCashSession) disabled @endif>
                        <i class="bx bx-check-circle me-1"></i> Selesaikan Transaksi
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Open Cash Session Modal --}}
    <div x-data="{ open: @entangle('showCashSessionModal') }" x-show="open" class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" x-cloak>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Buka Sesi Kas Kasir</h5>
                    <button type="button" class="btn-close" @click="open = false"></button>
                </div>
                <form wire:submit.prevent="openCashSession">
                    <div class="modal-body">
                        <p class="text-muted small mb-3">Masukkan saldo kas awal di laci kasir untuk tanggal hari ini sebelum memulai transaksi.</p>
                        <div class="mb-3">
                            <label class="form-label">Saldo Kas Awal (Rp):</label>
                            <input type="number" step="any" min="0" wire:model="opening_cash" class="form-control form-control-lg fw-bold" placeholder="0" required autofocus />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="open = false">Batal</button>
                        <button type="submit" class="btn btn-primary" style="background: #5c73f8;">Buka Sesi Kas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
