@php
    $formatRupiah = fn($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
@endphp

<div class="container-fluid flex-grow-1 container-p-y service-page">
    @if (session('success'))<div class="alert alert-success alert-dismissible" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if (session('error'))<div class="alert alert-danger alert-dismissible" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('service-transactions.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back"></i></a>
            <h5 class="mb-0">Transaksi Service</h5>
        </div>
        <button type="button" wire:click="submitTransaction" class="btn btn-primary"><i class="bx bx-save me-1"></i>Simpan</button>
    </div>

    <div class="row g-3 align-items-start">
        <div class="col-xl-5 col-lg-6">
            <div class="service-panel p-3 mb-3">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label">Kode</label>
                        <input type="text" wire:model="draftServiceCode" class="form-control" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal</label>
                        <input type="date" wire:model="service_at" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tgl Kembali</label>
                        <input type="date" wire:model="return_date" class="form-control">
                    </div>
                </div>
            </div>

            <div class="service-panel p-3 mb-3">
                <h6 class="mb-2">Identitas Pelanggan</h6>
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label">Kode Pelanggan</label>
                        <input type="text" wire:model="customer_code" class="form-control">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Nama Pelanggan <span class="text-danger">*</span></label>
                        <input type="text" wire:model="customer_name" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">ID Customer</label>
                        <input type="number" wire:model="customer_id" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Alamat</label>
                        <input type="text" wire:model="customer_address" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Telepon</label>
                        <input type="text" wire:model="customer_phone" class="form-control">
                    </div>
                </div>
            </div>

            <div class="service-panel p-3 mb-3">
                <h6 class="mb-2">Identitas Barang Servis</h6>
                <div class="row g-2">
                    <div class="col-md-4"><label class="form-label">Merek</label><input type="text" wire:model="device_brand" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Type</label><input type="text" wire:model="device_type" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Nomor Seri</label><input type="text" wire:model="serial_number" class="form-control"></div>
                    <div class="col-md-6">
                        <label class="form-label">Kunci HP</label>
                        <select wire:model="device_lock_type" class="form-select">
                            <option value="">Tidak ada</option>
                            <option value="pin">PIN</option>
                            <option value="pattern">Pola</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">PIN / Pola HP</label>
                        <input type="text" wire:model="device_lock_value" class="form-control" placeholder="Contoh PIN: 123456">
                    </div>
                </div>
            </div>

            <div class="service-panel p-3">
                <h6 class="mb-2">Kerusakan dan Kelengkapan</h6>
                <div class="row g-2">
                    <div class="col-12">
                        <label class="form-label">Keterangan Cek</label>
                        <textarea wire:model="check_notes" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Keluhan Service</label>
                        <textarea wire:model="complaint" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kelengkapan</label>
                        <textarea wire:model="accessories" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-7 col-lg-6">
            <div class="service-right-sticky">
                <div class="row g-3 mb-3">
                    <div class="col-xl-8">
                        <div class="service-panel p-3 h-100">
                            <h6 class="mb-2">Biaya Jasa dan Sparepart</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label">Cari Jasa</label>
                                    <input type="text" wire:model.live.debounce.300ms="serviceSearch" class="form-control" placeholder="Ketik nama jasa...">
                                    @if(!empty($serviceSearch) && strlen($serviceSearch) >= 2)
                                        <div class="list-group mt-1" style="max-height: 200px; overflow-y: auto;">
                                            @php $services = $this->searchServices(); @endphp
                                            @foreach($services as $svc)
                                                <button type="button" class="list-group-item list-group-item-action" wire:click="addItem('service', {{ $svc['id'] }}, '{{ addslashes($svc['name']) }}', '{{ addslashes($svc['code']) }}', {{ $svc['unit_price'] }}, {{ $svc['is_open_price'] ? 'true' : 'false' }}, {{ $svc['allow_discount_override'] ? 'true' : 'false' }})">
                                                    <div class="fw-semibold">{{ $svc['name'] }}</div>
                                                    <small class="text-muted">{{ $svc['code'] }} — Rp {{ number_format($svc['unit_price'], 0, ',', '.') }}</small>
                                                </button>
                                            @endforeach
                                            @if(empty($services))
                                                <div class="list-group-item text-muted">Jasa tidak ditemukan.</div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Cari Sparepart</label>
                                    <input type="text" wire:model.live.debounce.300ms="productSearch" class="form-control" placeholder="Ketik nama sparepart...">
                                    @if(!empty($productSearch) && strlen($productSearch) >= 2)
                                        <div class="list-group mt-1" style="max-height: 200px; overflow-y: auto;">
                                            @php $products = $this->searchProducts(); @endphp
                                            @foreach($products as $prod)
                                                <button type="button" class="list-group-item list-group-item-action" wire:click="addItem('product', {{ $prod['id'] }}, '{{ addslashes($prod['name']) }}', '{{ addslashes($prod['code']) }}', {{ $prod['unit_price'] }}, {{ $prod['is_open_price'] ? 'true' : 'false' }}, false)">
                                                    <div class="fw-semibold">{{ $prod['name'] }}</div>
                                                    <small class="text-muted">{{ $prod['code'] }} — Rp {{ number_format($prod['unit_price'], 0, ',', '.') }}</small>
                                                </button>
                                            @endforeach
                                            @if(empty($products))
                                                <div class="list-group-item text-muted">Sparepart tidak ditemukan.</div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Lokasi Stok</label>
                                    <select wire:model="location_id" class="form-select">
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Pembayaran</label>
                                    <select wire:model="payment_method" class="form-select">
                                        <option value="cash">Cash</option>
                                        <option value="transfer">Transfer</option>
                                        <option value="qris">QRIS</option>
                                        <option value="tempo">Tempo</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">No. Referensi</label>
                                    <input type="text" wire:model="payment_reference" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="service-panel p-3 h-100 d-flex align-items-center justify-content-center">
                            <div class="service-total">{{ number_format($this->grandTotal, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>

                <div class="card service-items mb-3">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th style="width:95px;">Qty</th>
                                    <th style="width:135px;">Harga</th>
                                    <th style="width:120px;">Disc</th>
                                    <th style="width:135px;">Subtotal</th>
                                    <th style="width:52px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $index => $item)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $item['name'] }}</div>
                                            <div class="text-muted small">{{ $item['code'] }}</div>
                                        </td>
                                        <td>
                                            <input type="number" min="0" step="1" class="form-control form-control-sm"
                                                value="{{ $item['quantity'] }}"
                                                wire:change="updateItemQuantity({{ $index }}, $event.target.value)">
                                        </td>
                                        <td>
                                            <input type="number" min="0" step="1" class="form-control form-control-sm"
                                                value="{{ $item['unit_price'] }}"
                                                {{ !$item['is_open_price'] ? 'disabled' : '' }}
                                                wire:change="updateItemPrice({{ $index }}, $event.target.value)">
                                        </td>
                                        <td>
                                            <input type="number" min="0" step="1" class="form-control form-control-sm"
                                                value="{{ $item['discount_value'] ?? 0 }}"
                                                {{ !$item['allow_discount_override'] ? 'disabled' : '' }}
                                                wire:change="updateItemDiscount({{ $index }}, $event.target.value)">
                                        </td>
                                        <td class="text-end fw-bold">{{ $formatRupiah($item['subtotal']) }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeItem({{ $index }})">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">Belum ada jasa atau sparepart.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="service-panel p-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select wire:model="status" class="form-select">
                                <option value="process">Proses</option>
                                <option value="done">Selesai</option>
                                <option value="taken">Sudah Diambil</option>
                                <option value="cancelled">Dibatalkan</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bayar</label>
                            <input type="number" min="0" step="1" wire:model="paid_amount" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Teknisi</label>
                            <select wire:model="technician_id" class="form-select">
                                <option value="">-</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Format Faktur</label>
                            <input type="text" wire:model="invoice_format" class="form-control">
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-6 col-md-3 d-flex justify-content-between">
                            <span>SUBTOTAL</span>
                            <strong>{{ $formatRupiah($this->subtotal) }}</strong>
                        </div>
                        <div class="col-6 col-md-3 d-flex justify-content-between">
                            <span>DISC</span>
                            <strong>{{ $formatRupiah($this->discountTotal) }}</strong>
                        </div>
                        <div class="col-6 col-md-3 d-flex justify-content-between">
                            <span>GRAND</span>
                            <strong>{{ $formatRupiah($this->grandTotal) }}</strong>
                        </div>
                        <div class="col-6 col-md-3 d-flex justify-content-between">
                            <span>{{ $this->payment_method === 'tempo' ? 'SISA TEMPO' : 'KEMBALI' }}</span>
                            <strong>{{ $formatRupiah($this->changeAmount) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cash Session Modal --}}
    <div x-data="{ open: @entangle('showCashSessionModal') }" x-show="open" class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" x-cloak>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Buka Sesi Kas Kasir</h5>
                    <button type="button" class="btn-close" @click="open = false"></button>
                </div>
                <form wire:submit.prevent="openCashSession">
                    <div class="modal-body">
                        <p class="text-muted small mb-3">Masukkan saldo kas awal di laci kasir untuk hari ini sebelum memulai transaksi.</p>
                        <div class="mb-3">
                            <label class="form-label">Saldo Kas Awal (Rp):</label>
                            <input type="number" step="any" min="0" wire:model="opening_cash" class="form-control form-control-lg fw-bold" placeholder="0" required autofocus />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="open = false">Batal</button>
                        <button type="submit" class="btn btn-primary">Buka Sesi Kas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
