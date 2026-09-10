@extends('layouts.app')

@section('title', 'Daftar Transfer Stok Cabang')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Daftar Transfer Stok</h4>
            <div class="text-muted">Kelola transfer stok antar cabang/toko.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('branch-transfers.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Transfer Baru
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <div class="text-muted small">Total</div>
                            <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="bx bx-package fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
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
        <div class="col-md-3">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <div class="text-muted small">Dalam Perjalanan</div>
                            <h3 class="mb-0 fs-4">{{ $stats['in_transit'] ?? 0 }}</h3>
                        </div>
                        <div class="text-info">
                            <i class="bx bx-truck fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <div class="text-muted small">Dibatalkan</div>
                            <h3 class="mb-0 fs-4">{{ $stats['cancelled'] ?? 0 }}</h3>
                        </div>
                        <div class="text-danger">
                            <i class="bx bx-x-circle fs-1"></i>
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
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select class="form-select" x-model="selectedStatus">
                            <option value="all">Semua</option>
                            <option value="draft">Draft</option>
                            <option value="in_transit">Dalam Perjalanan</option>
                            <option value="completed">Selesai</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cabang Asal</label>
                        <select class="form-select" x-model="sourceBranchId">
                            <option value="">Semua</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $sourceBranchId == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cabang Tujuan</label>
                        <select class="form-select" x-model="targetBranchId">
                            <option value="">Semua</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $targetBranchId == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Periode</label>
                        <div class="input-group">
                            <input type="date" class="form-control" x-model="dateFrom" placeholder="Dari">
                            <input type="date" class="form-control" x-model="dateTo" placeholder="Sampai">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Cari</label>
                        <div class="input-group">
                            <input type="text" class="form-control" x-model="search" placeholder="Kode / Nama">
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="bx bx-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Data Transfer</h5>
            @if($transfers->total() > 0)
                <div>
                    <span class="text-muted">Total: {{ $transfers->total() }}</span>
                </div>
            @endif
        </div>
        <div class="card-body">
            @if($transfers->total() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Dari</th>
                                <th>Ke</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Terima</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transfers as $transfer)
                                @php
                                    $statusClass = match($transfer->status) {
                                        'draft' => 'bg-label-secondary',
                                        'in_transit' => 'bg-label-info',
                                        'completed' => 'bg-label-success',
                                        'cancelled' => 'bg-label-danger',
                                    };
                                    $statusLabel = match($transfer->status) {
                                        'draft' => 'Draft',
                                        'in_transit' => 'Dalam Perjalanan',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan',
                                    };
                                @endphp
                                <tr>
                                    <td class="fw-semibold">
                                        <button wire:click="openDetailModal({{ $transfer->id }})" class="btn btn-link p-0 text-decoration-none">
                                            {{ $transfer->transfer_code }}
                                        </button>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $transfer->sourceBranch?->name }}</div>
                                        <small class="text-muted">{{ $transfer->sourceLocation?->name }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $transfer->targetBranch?->name }}</div>
                                        <small class="text-muted">{{ $transfer->targetLocation?->name }}</small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $statusClass }} fs-6">{{ $statusLabel }}</span>
                                    </td>
                                    <td>
                                        <div>{{ $transfer->created_at->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ $transfer->created_at->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        @if($transfer->received_at)
                                            <div class="text-success fw-bold">
                                                {{ $transfer->received_at->format('d/m/Y H:i') }}
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <button wire:click="openDetailModal({{ $transfer->id }})" class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-detail"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                {{ $transfers->links() }}
            @else
                <div class="text-center py-5">
                    <div class="text-muted mb-3">
                        <i class="bx bx-inbox fs-1"></i>
                    </div>
                    <p>Tidak ada data transfer stok.</p>
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
                <h5 class="modal-title">Detail Transfer Stok</h5>
                <button type="button" class="btn-close" @click="closeDetailModal()"></button>
            </div>
            <div class="modal-body">
                @if($selectedTransferId)
                    @livewire('branches.transfer-detail', ['transferId' => $selectedTransferId])
                @endif
            </div>
        </div>
    </div>
</div>

@livewireScripts
@endsection