@extends('layouts.app')

@section('title', 'Stock Opname')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Stock Opname / Stock Count</h4>
            <div class="text-muted">Penghitungan fisik stok per lokasi & penyesuaian otomatis ke persediaan.</div>
        </div>
        <a href="{{ route('stock-opname.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Buat Opname
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <div class="text-muted small">Total</div>
                            <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="bx bx-box fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <div class="text-muted small">Menunggu</div>
                            <h3 class="mb-0 fs-4">{{ $stats['open'] ?? 0 }}</h3>
                        </div>
                        <div class="text-warning">
                            <i class="bx bx-time fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <div class="text-muted small">Selesai</div>
                            <h3 class="mb-0 fs-4">{{ $stats['completed'] ?? 0 }}</h3>
                        </div>
                        <div class="text-success">
                            <i class="bx bx-check-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form wire:submit.prevent="$refresh">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" wire:model="statusFilter">
                            <option value="all">Semua</option>
                            <option value="open">Menunggu Diproses</option>
                            <option value="completed">Selesai</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Lokasi</label>
                        <select class="form-select" wire:model="locationFilter">
                            <option value="">Semua</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cari</label>
                        <div class="input-group">
                            <input type="text" class="form-control" wire:model="search" placeholder="Kode / Nama Lokasi">
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="bx bx-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tampilkan</label>
                        <select class="form-select" wire:model="perPage">
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Data Stock Opname</h5>
            @if($opnames->total() > 0)
                <div>
                    <span class="text-muted">Total: {{ $opnames->total() }}</span>
                </div>
            @endif
        </div>
        <div class="card-body">
            @if($opnames->total() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>No. Opname</th>
                                <th>Lokasi</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($opnames as $opname)
                                @php
                                    $statusClass = $opname->status === 'completed' ? 'bg-label-success' : 'bg-label-warning';
                                    $statusLabel = $opname->status === 'completed' ? 'Selesai' : 'Menunggu Diproses';
                                @endphp
                                <tr>
                                    <td class="fw-semibold">
                                        <button wire:click="openDetailModal({{ $opname->id }})" class="btn btn-link p-0 text-decoration-none">
                                            {{ $opname->opname_code }}
                                        </button>
                                    </td>
                                    <td>{{ $opname->location?->name ?? '-' }}</td>
                                    <td>{{ $opname->opname_date->format('d M Y') }}</td>
                                    <td>
                                        <span class="badge {{ $statusClass }} fs-6">{{ $statusLabel }}</span>
                                    </td>
                                    <td class="text-muted small">{{ $opname->notes ?? '-' }}</td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <button wire:click="openDetailModal({{ $opname->id }})" class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-detail"></i>
                                            </button>
                                            @if($opname->status === 'open')
                                                <button wire:click="confirmComplete({{ $opname->id }})" 
                                                        class="btn btn-sm btn-primary"
                                                        onclick="event.preventDefault(); return confirm('Proses selisih opname ini? Stok & pembukuan akan diperbarui.');">
                                                    Proses
                                                </button>
                                                <button wire:click="confirmDelete({{ $opname->id }})"
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="event.preventDefault(); return confirm('Hapus draft opname ini?');">
                                                    Hapus
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                {{ $opnames->links() }}
            @else
                <div class="text-center py-5">
                    <div class="text-muted mb-3">
                        <i class="bx bx-inbox fs-1"></i>
                    </div>
                    <p>Belum ada stock opname.</p>
                    <a href="{{ route('stock-opname.create') }}" class="btn btn-primary btn-sm">
                        <i class="bx bx-plus me-1"></i> Buat Opname Baru
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" x-show="showDetailModal" x-transition.opacity role="dialog" tabindex="-1">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Stock Opname</h5>
                <button type="button" class="btn-close" @click="closeDetailModal()"></button>
            </div>
            <div class="modal-body">
                @if($selectedOpnameId)
                    @livewire('stock-opname.stock-opname-form', ['opnameId' => $selectedOpnameId], key('opname-form-' . $selectedOpnameId))
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Complete Modal -->
<div class="modal fade" x-show="showCompleteModal" x-transition.opacity role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Proses Opname</h5>
                <button type="button" class="btn-close" @click="cancelComplete()"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bx bx-error me-1"></i>
                    <strong>Perhatian:</strong> Selisih stok akan disesuaikan otomatis dan dicatat ke pembukuan.
                </div>
                <p>Apakah Anda yakin ingin memproses opname ini?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="cancelComplete()">Batal</button>
                <button type="button" class="btn btn-primary" @click="executeComplete()">Proses Opname</button>
            </div>
        </div>
    </div>
</div>

@livewireScripts
@endsection