@php
    $formatRupiah = fn($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
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

    {{-- Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Riwayat Transaksi</h4>
            <p class="text-muted mb-0">Daftar transaksi penjualan dan ringkasan harian.</p>
        </div>
        <div class="d-flex gap-2">
            @can('transactions.create')
                <a href="{{ route('transactions.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="bx bx-plus"></i> Transaksi Baru (POS)
                </a>
            @endcan
        </div>
    </div>

    {{-- Daily Summary Metrics --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <div class="text-muted small mb-1">Total Transaksi</div>
                    <div class="h4 mb-0 fw-bold text-primary">{{ $summary['transactions_count'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <div class="text-muted small mb-1">Total Item Terjual</div>
                    <div class="h4 mb-0 fw-bold text-info">{{ $summary['items_count'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <div class="text-muted small mb-1">Total Omset</div>
                    <div class="h4 mb-0 fw-bold text-success">{{ $formatRupiah($summary['grand_total']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <div class="text-muted small mb-1">Total Diskon</div>
                    <div class="h4 mb-0 fw-bold text-danger">{{ $formatRupiah($summary['discount_total']) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Controls: Channel Tabs & Filter --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="btn-group w-100" role="group">
                        <button type="button" wire:click="setChannel('toko')" class="btn btn-sm {{ $saleChannel === 'toko' ? 'btn-primary' : 'btn-outline-primary' }}">
                            Toko
                        </button>
                        <button type="button" wire:click="setChannel('cabang')" class="btn btn-sm {{ $saleChannel === 'cabang' ? 'btn-primary' : 'btn-outline-primary' }}">
                            Cabang
                        </button>
                        <button type="button" wire:click="setChannel('partai')" class="btn btn-sm {{ $saleChannel === 'partai' ? 'btn-primary' : 'btn-outline-primary' }}">
                            Partai
                        </button>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <input type="date" wire:model.live="selectedDate" class="form-control form-control-sm" />
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kode/pelanggan..." class="form-control form-control-sm" />
                </div>
            </div>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode Trx</th>
                        <th>Waktu</th>
                        <th>Pelanggan</th>
                        <th>Lokasi</th>
                        <th>Kasir</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($sales as $sale)
                        <tr>
                            <td>
                                <a href="{{ route('transactions.show', $sale->id) }}" class="fw-bold text-primary text-decoration-none">
                                    {{ $sale->sale_code }}
                                </a>
                            </td>
                            <td>{{ $sale->sale_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td>{{ $sale->customer?->name ?? 'Umum' }}</td>
                            <td>{{ $sale->location?->name ?? '-' }}</td>
                            <td>{{ $sale->cashier?->name ?? '-' }}</td>
                            <td class="fw-bold text-success">{{ $formatRupiah($sale->grand_total) }}</td>
                            <td>
                                @if($sale->status === 'paid')
                                    <span class="badge bg-label-success">Paid</span>
                                @else
                                    <span class="badge bg-label-secondary">Void</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('transactions.receipt', $sale->id) }}" class="btn btn-xs btn-outline-secondary" target="_blank" title="Cetak Struk">
                                        <i class="bx bx-printer"></i>
                                    </a>
                                    <a href="{{ route('transactions.show', $sale->id) }}" class="btn btn-xs btn-outline-primary" title="Detail">
                                        <i class="bx bx-show"></i>
                                    </a>
                                    @if($sale->status === 'paid')
                                        @can('transactions.view')
                                            <button type="button" wire:click="confirmVoid({{ $sale->id }})" class="btn btn-xs btn-outline-danger" title="Void Transaksi">
                                                <i class="bx bx-x-circle"></i>
                                            </button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bx bx-receipt fs-1 mb-2"></i>
                                <p class="mb-0">Tidak ada transaksi ditemukan untuk filter ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sales->hasPages())
            <div class="card-footer py-3 border-top">
                {{ $sales->links() }}
            </div>
        @endif
    </div>

    {{-- Void Modal --}}
    <div x-data="{ open: @entangle('showVoidModal') }" x-show="open" class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" x-cloak>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Void Transaksi</h5>
                    <button type="button" class="btn-close" @click="open = false"></button>
                </div>
                <div class="modal-body">
                    <p class="text-danger mb-3">
                        <i class="bx bx-error-circle me-1"></i> Transaksi yang di-void tidak dapat dikembalikan statusnya.
                    </p>
                    <div class="mb-3">
                        <label class="form-label">Alasan Pembatalan / Void:</label>
                        <input type="text" wire:model="voidReason" class="form-control" placeholder="Contoh: Salah input kasir / pelanggan batal" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="open = false">Batal</button>
                    <button type="button" class="btn btn-danger" wire:click="executeVoid">Void Transaksi</button>
                </div>
            </div>
        </div>
    </div>
</div>
