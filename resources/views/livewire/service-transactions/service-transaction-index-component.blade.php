@php
    $statusColors = ['process' => 'warning', 'done' => 'info', 'taken' => 'success', 'cancelled' => 'secondary'];
    $statusLabels = ['process' => 'Proses', 'done' => 'Selesai', 'taken' => 'Diambil', 'cancelled' => 'Batal'];
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    @if (session('success'))<div class="alert alert-success alert-dismissible" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if (session('error'))<div class="alert alert-danger alert-dismissible" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    @if (!$tableReady)
        <div class="alert alert-warning">Tabel transaksi service belum tersedia. Jalankan migration terlebih dahulu.</div>
    @endif

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-label-primary rounded">
                                <i class="bx bx-wrench fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Total Transaksi</h6>
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
                            <h6 class="mb-0">Total Omset</h6>
                            <h3 class="mb-0">Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}</h3>
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
                                <i class="bx bx-time fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Dalam Proses</h6>
                            <h3 class="mb-0">{{ number_format($summary['pending_count']) }}</h3>
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
                            <h6 class="mb-0">Selesai / Diambil</h6>
                            <h3 class="mb-0">{{ number_format($summary['completed_count']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <h5 class="mb-1">Transaksi Service</h5>
                <div class="text-muted small">Riwayat service HP dan sparepart yang dipakai.</div>
            </div>
            @if (auth()->user()->hasPermission('transactions.create'))
                <a href="{{ route('service-transactions.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i>Service Baru</a>
            @endif
        </div>
        <div class="card-body">
            {{-- Filters --}}
            <div class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Cari kode, pelanggan, merek HP..." />
                </div>
                <div class="col-md-3">
                    <select wire:model.live="selectedStatus" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="process">Proses</option>
                        <option value="done">Selesai</option>
                        <option value="taken">Sudah Diambil</option>
                        <option value="cancelled">Batal</option>
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
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Barang</th>
                            <th>Teknisi</th>
                            <th>Total</th>
                            <th>Kasir</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $row)
                            <tr>
                                <td>
                                    <a href="{{ route('service-transactions.show', $row->id) }}" class="fw-semibold text-primary text-decoration-none">
                                        {{ $row->service_code }}
                                    </a>
                                </td>
                                <td>{{ optional($row->service_at)->format('d M Y H:i') ?: '-' }}</td>
                                <td>{{ $row->customer_name ?: 'Pelanggan umum' }}</td>
                                <td>{{ trim(($row->device_brand ?? '-') . ' ' . ($row->device_type ?? '')) }}</td>
                                <td>{{ $row->technician?->name ?? '-' }}</td>
                                <td class="fw-semibold">Rp {{ number_format((float) $row->grand_total, 0, ',', '.') }}</td>
                                <td>{{ $row->cashier?->name ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-label-{{ $statusColors[$row->status] ?? 'secondary' }}">
                                        {{ $statusLabels[$row->status] ?? $row->status }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('service-transactions.show', $row->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bx bx-show"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Belum ada transaksi service.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</div>
