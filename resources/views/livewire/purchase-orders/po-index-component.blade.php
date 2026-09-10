@php
    $formatRupiah = fn($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    @if (session('success'))<div class="alert alert-success alert-dismissible" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if (session('error'))<div class="alert alert-danger alert-dismissible" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if (session('warning'))<div class="alert alert-warning alert-dismissible" role="alert">{{ session('warning') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-label-primary rounded">
                                <i class="bx bx-package fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Total PO</h6>
                            <h3 class="mb-0">{{ number_format($summary['total_count']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-label-success rounded">
                                <i class="bx bx-dollar fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Total Nilai</h6>
                            <h3 class="mb-0">{{ $formatRupiah($summary['total_value']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-label-warning rounded">
                                <i class="bx bx-file fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Draft</h6>
                            <h3 class="mb-0">{{ number_format($summary['draft_count']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-label-info rounded">
                                <i class="bx bx-check-circle fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Diterima</h6>
                            <h3 class="mb-0">{{ number_format($summary['received_count']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <h5 class="mb-1">Purchase Orders</h5>
                <div class="text-muted small">Daftar pembelian barang dan proyeksi stok.</div>
            </div>
            @if (auth()->user()->hasPermission('master.access'))
                <a href="{{ route('purchase-orders.restock') }}" class="btn btn-warning"><i class="bx bx-refresh me-1"></i>Rekomendasi Restock</a>
            @endif
        </div>
        <div class="card-body">
            {{-- Filters --}}
            <div class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Cari nomor PO, produk, supplier..." />
                </div>
                <div class="col-md-3">
                    <select wire:model.live="selectedStatus" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="draft">Draft</option>
                        <option value="received">Diterima</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="perPage" class="form-select">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>PO Number</th>
                            <th>Tanggal</th>
                            <th>Produk</th>
                            <th>Supplier</th>
                            <th>Lokasi</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseOrders as $po)
                            @php
                                $qty = rtrim(rtrim(number_format((float) $po->quantity, 2, ',', '.'), '0'), ',');
                                $unit = $po->product?->sale_unit ?: 'PCS';
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('purchase-orders.show', $po->id) }}" class="fw-semibold text-primary text-decoration-none">
                                        {{ $po->po_number }}
                                    </a>
                                </td>
                                <td>{{ optional($po->ordered_at)->format('d M Y H:i') ?: '-' }}</td>
                                <td>{{ $po->product?->name ?? '-' }}</td>
                                <td>{{ $po->supplier?->name ?? '-' }}</td>
                                <td>{{ $po->location?->name ?? '-' }}</td>
                                <td>{{ $qty }} {{ $unit }}</td>
                                <td class="fw-semibold">{{ $formatRupiah($po->total_price) }}</td>
                                <td>
                                    <span class="badge bg-label-{{ $po->status === 'received' ? 'success' : 'secondary' }}">
                                        {{ $po->status === 'received' ? 'Diterima' : ucfirst($po->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('purchase-orders.show', $po->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bx bx-show"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Belum ada purchase order.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $purchaseOrders->links() }}
            </div>
        </div>
    </div>
</div>
