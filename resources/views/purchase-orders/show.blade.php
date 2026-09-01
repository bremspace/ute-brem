@extends('layouts.sneat')

@section('title', 'Ringkasan Purchase Order')

@php
    $qty = rtrim(rtrim(number_format((float) $purchaseOrder->quantity, 2, ',', '.'), '0'), ',');
    $unit = $purchaseOrder->product?->sale_unit ?: 'PCS';
    $unitPrice = $purchaseOrder->unit_price !== null ? 'Rp ' . number_format((float) $purchaseOrder->unit_price, 0, ',', '.') : '-';
    $totalPrice = $purchaseOrder->total_price !== null ? 'Rp ' . number_format((float) $purchaseOrder->total_price, 0, ',', '.') : '-';
@endphp

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">Ringkasan Purchase Order</h4>
            <div class="text-muted">{{ $purchaseOrder->po_number }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Riwayat PO
            </a>
            @if($purchaseOrder->product_id)
                <a href="{{ route('products.stocks.index', $purchaseOrder->product_id) }}" class="btn btn-primary">
                    <i class="bx bx-box me-1"></i> Stok Lokasi
                </a>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Informasi PO</h5>
                    <span class="badge bg-label-{{ $purchaseOrder->status === 'received' ? 'success' : 'secondary' }}">
                        {{ $purchaseOrder->status === 'received' ? 'Diterima' : ucfirst((string) $purchaseOrder->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Nomor PO</div>
                            <div class="fw-semibold">{{ $purchaseOrder->po_number }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Tanggal PO</div>
                            <div class="fw-semibold">{{ optional($purchaseOrder->ordered_at)->format('d M Y H:i') ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Qty Beli</div>
                            <div class="fw-semibold">{{ $qty }} {{ $unit }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Lokasi Masuk</div>
                            <div class="fw-semibold">{{ $purchaseOrder->location?->name ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Harga Beli / Unit</div>
                            <div class="fw-semibold">{{ $unitPrice }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Total</div>
                            <div class="fw-semibold">{{ $totalPrice }}</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">Catatan</div>
                            <div class="fw-semibold">{{ $purchaseOrder->notes ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Status Pembayaran & Hutang Supplier</h5>
                    @if($purchaseOrder->payment_method === 'tempo')
                        @php
                            $badgeColor = match($purchaseOrder->credit_status) {
                                'paid' => 'success',
                                'partial' => 'warning',
                                default => 'danger'
                            };
                        @endphp
                        <span class="badge bg-label-{{ $badgeColor }}">{{ strtoupper((string) $purchaseOrder->credit_status) }}</span>
                    @else
                        <span class="badge bg-label-success">TUNAI</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6 col-lg-3">
                            <div class="text-muted small">Metode Pembayaran</div>
                            <div class="fw-semibold">{{ $purchaseOrder->payment_method === 'tempo' ? 'Tempo / Kredit' : 'Tunai / Cash' }}</div>
                        </div>
                        @if($purchaseOrder->payment_method === 'tempo')
                            <div class="col-md-6 col-lg-3">
                                <div class="text-muted small">Total Pembelian</div>
                                <div class="fw-semibold">Rp {{ number_format((float) $purchaseOrder->total_price, 0, ',', '.') }}</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="text-muted small">Total Terbayar</div>
                                <div class="fw-semibold text-success">Rp {{ number_format((float) $purchaseOrder->paid_amount, 0, ',', '.') }}</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="text-muted small">Sisa Hutang</div>
                                <div class="fw-bold text-danger">Rp {{ number_format(max(0, (float) $purchaseOrder->total_price - (float) $purchaseOrder->paid_amount), 0, ',', '.') }}</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="text-muted small">Jatuh Tempo</div>
                                <div class="fw-semibold {{ $purchaseOrder->credit_due_at && $purchaseOrder->credit_due_at->isPast() && $purchaseOrder->credit_status !== 'paid' ? 'text-danger' : '' }}">
                                    {{ $purchaseOrder->credit_due_at ? $purchaseOrder->credit_due_at->format('d M Y') : '-' }}
                                    @if($purchaseOrder->credit_due_at && $purchaseOrder->credit_status !== 'paid')
                                        @php $days = now()->startOfDay()->diffInDays($purchaseOrder->credit_due_at->startOfDay(), false); @endphp
                                        <span class="small">({{ $days >= 0 ? "$days hari lagi" : "terlambat " . abs($days) . " hari" }})</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($purchaseOrder->payment_method === 'tempo')
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-semibold mb-0">Riwayat Pembayaran Cicilan / Pelunasan</h6>
                            @if((float) $purchaseOrder->paid_amount < (float) $purchaseOrder->total_price)
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#paySupplierModal">
                                    <i class="bx bx-plus me-1"></i> Catat Pelunasan
                                </button>
                            @endif
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Nominal</th>
                                        <th>Metode</th>
                                        <th>Ref</th>
                                        <th>Catatan</th>
                                        <th>Oleh</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($purchaseOrder->payments as $pmt)
                                        <tr>
                                            <td>{{ $pmt->payment_at->format('d M Y H:i') }}</td>
                                            <td class="fw-semibold">Rp {{ number_format((float) $pmt->amount, 0, ',', '.') }}</td>
                                            <td><span class="badge bg-label-secondary">{{ strtoupper($pmt->payment_method) }}</span></td>
                                            <td>{{ $pmt->reference ?: '-' }}</td>
                                            <td>{{ $pmt->notes ?: '-' }}</td>
                                            <td>{{ $pmt->creator?->name ?: '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-3">Belum ada cicilan/pelunasan yang dicatat.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Produk</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Nama Produk</div>
                            <div class="fw-semibold">{{ $purchaseOrder->product?->name ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Kode Produk</div>
                            <div class="fw-semibold">{{ $purchaseOrder->product?->product_code ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Stok Global Saat Ini</div>
                            <div class="fw-semibold">{{ $purchaseOrder->product ? rtrim(rtrim(number_format((float) $purchaseOrder->product->stock_global, 2, ',', '.'), '0'), ',') . ' ' . $unit : '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Minimum Global</div>
                            <div class="fw-semibold">{{ $purchaseOrder->product?->stock_min !== null ? rtrim(rtrim(number_format((float) $purchaseOrder->product->stock_min, 2, ',', '.'), '0'), ',') : '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Supplier</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="text-muted small">Nama</div>
                        <div class="fw-semibold">{{ $purchaseOrder->supplier?->name ?: '-' }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small">Kode</div>
                        <div class="fw-semibold">{{ $purchaseOrder->supplier?->code ?: '-' }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small">PIC</div>
                        <div class="fw-semibold">{{ $purchaseOrder->supplier?->contact_person ?: '-' }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small">Kontak</div>
                        <div class="fw-semibold">{{ $purchaseOrder->supplier?->phone ?: $purchaseOrder->supplier?->email ?: '-' }}</div>
                    </div>
                    <div>
                        <div class="text-muted small">Alamat</div>
                        <div class="fw-semibold">{{ $purchaseOrder->supplier?->address ?: '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Mutasi Stok</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="text-muted small">Referensi</div>
                        <div class="fw-semibold">{{ $purchaseOrder->stockMovement?->reference_number ?: '-' }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small">Tanggal Masuk</div>
                        <div class="fw-semibold">{{ optional($purchaseOrder->received_at)->format('d M Y H:i') ?: '-' }}</div>
                    </div>
                    <div>
                        <div class="text-muted small">Dibuat Oleh</div>
                        <div class="fw-semibold">{{ $purchaseOrder->creator?->name ?: '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Attachment</h5>
                    <span class="badge bg-label-primary">{{ $purchaseOrder->files->count() }} file</span>
                </div>
                <div class="card-body">
                    @if($purchaseOrder->files->isNotEmpty())
                        <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#attachmentsModal">
                            <i class="bx bx-paperclip me-1"></i> Lihat Attachment
                        </button>
                    @else
                        <div class="text-muted">Belum ada attachment.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($purchaseOrder->files->isNotEmpty())
    <div class="modal fade" id="attachmentsModal" tabindex="-1" aria-labelledby="attachmentsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="attachmentsModalLabel">Attachment Purchase Order</h5>
                        <div class="text-muted small">{{ $purchaseOrder->po_number }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group">
                        @foreach($purchaseOrder->files as $file)
                            <a href="{{ $file->file_url }}" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-3">
                                <span class="d-flex align-items-center gap-2 text-truncate">
                                    <i class="bx bx-paperclip fs-4"></i>
                                    <span class="text-truncate">{{ $file->original_name }}</span>
                                </span>
                                <span class="badge bg-label-secondary flex-shrink-0">Buka</span>
                            </a>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endif

@if($purchaseOrder->payment_method === 'tempo' && (float) $purchaseOrder->paid_amount < (float) $purchaseOrder->total_price)
    <div class="modal fade" id="paySupplierModal" tabindex="-1" aria-labelledby="paySupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('purchase-orders.payment', $purchaseOrder->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="paySupplierModalLabel">Catat Pelunasan Hutang Supplier</h5>
                            <div class="text-muted small">Sisa hutang: Rp {{ number_format(max(0, (float) $purchaseOrder->total_price - (float) $purchaseOrder->paid_amount), 0, ',', '.') }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tanggal Pelunasan <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="payment_at" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nominal Pelunasan (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" min="1" max="{{ max(0, (float) $purchaseOrder->total_price - (float) $purchaseOrder->paid_amount) }}" value="{{ max(0, (float) $purchaseOrder->total_price - (float) $purchaseOrder->paid_amount) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash">Cash / Tunai</option>
                                <option value="transfer">Transfer Bank</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Referensi (No. Rekening/No. Transaksi)</label>
                            <input type="text" name="reference" class="form-control" placeholder="Contoh: Ref-1234">
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Catatan</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Catatan tambahan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Simpan Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection
