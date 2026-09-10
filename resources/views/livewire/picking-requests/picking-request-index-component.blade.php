@extends('layouts.app')

@section('title', 'Picking Request')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Picking Request</h4>
            <div class="text-muted">Permintaan pengambilan sparepart untuk servis.</div>
        </div>
        <a href="{{ route('picking-requests.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Buat Request
        </a>
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
                        <div class="text-primary"><i class="bx bx-package fs-1"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <div class="text-muted small">Open</div>
                            <h3 class="mb-0 fs-4">{{ $stats['open'] ?? 0 }}</h3>
                        </div>
                        <div class="text-warning"><i class="bx bx-time fs-1"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <div class="text-muted small">Fulfilled</div>
                            <h3 class="mb-0 fs-4">{{ $stats['fulfilled'] ?? 0 }}</h3>
                        </div>
                        <div class="text-success"><i class="bx bx-check-circle fs-1"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <div class="text-muted small">Cancelled</div>
                            <h3 class="mb-0 fs-4">{{ $stats['cancelled'] ?? 0 }}</h3>
                        </div>
                        <div class="text-danger"><i class="bx bx-x-circle fs-1"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form wire:submit.prevent="$refresh">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select class="form-select" wire:model="statusFilter">
                            <option value="all">Semua</option>
                            <option value="open">Open</option>
                            <option value="fulfilled">Fulfilled</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cari</label>
                        <div class="input-group">
                            <input type="text" class="form-control" wire:model="search" placeholder="Kode / Lokasi">
                            <button type="submit" class="btn btn-outline-secondary"><i class="bx bx-search"></i></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Data Picking Request</h5>
            @if($requests->total() > 0)
                <span class="text-muted">Total: {{ $requests->total() }}</span>
            @endif
        </div>
        <div class="card-body">
            @if($requests->total() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Lokasi</th>
                                <th>Teknisi</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $req)
                                @php
                                    $statusClass = match($req->status) {
                                        'open' => 'bg-label-warning',
                                        'fulfilled' => 'bg-label-success',
                                        'cancelled' => 'bg-label-danger',
                                    };
                                @endphp
                                <tr>
                                    <td class="fw-semibold">
                                        <button wire:click="openDetailModal({{ $req->id }})" class="btn btn-link p-0 text-decoration-none">
                                            {{ $req->request_code }}
                                        </button>
                                    </td>
                                    <td>{{ $req->location?->name ?? '-' }}</td>
                                    <td>{{ $req->technician?->name ?? '-' }}</td>
                                    <td><span class="badge {{ $statusClass }} fs-6">{{ ucfirst($req->status) }}</span></td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <button wire:click="openDetailModal({{ $req->id }})" class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-detail"></i>
                                            </button>
                                            @if($req->status === 'open')
                                                <button wire:click="confirmFulfill({{ $req->id }})" class="btn btn-sm btn-success" onclick="event.preventDefault(); return confirm('Proses picking ini? Stok gudang akan dipotong.');">
                                                    <i class="bx bx-check"></i>
                                                </button>
                                                <button wire:click="confirmCancel({{ $req->id }})" class="btn btn-sm btn-outline-danger" onclick="event.preventDefault(); return confirm('Batalkan request ini?');">
                                                    <i class="bx bx-x"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $requests->links() }}
            @else
                <div class="text-center py-5">
                    <div class="text-muted mb-3"><i class="bx bx-inbox fs-1"></i></div>
                    <p>Belum ada picking request.</p>
                    <a href="{{ route('picking-requests.create') }}" class="btn btn-primary btn-sm">
                        <i class="bx bx-plus me-1"></i> Buat Request
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
                <h5 class="modal-title">Detail Picking Request</h5>
                <button type="button" class="btn-close" @click="closeDetailModal()"></button>
            </div>
            <div class="modal-body">
                @if($selectedRequestId)
                    @livewire('picking-requests.picking-request-form', ['requestId' => $selectedRequestId], key('pr-form-' . $selectedRequestId))
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Fulfill Confirm Modal -->
<div class="modal fade" x-show="showFulfillConfirm" x-transition.opacity role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Proses</h5>
                <button type="button" class="btn-close" @click="cancelFulfill()"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bx bx-error me-1"></i>
                    <strong>Perhatian:</strong> Stok gudang akan dipotong dan sparepart akan berstatus Reserved for Repair.
                </div>
                <p>Apakah Anda yakin ingin memproses picking ini?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" @click="cancelFulfill()">Batal</button>
                <button class="btn btn-success" @click="executeFulfill()">Proses</button>
            </div>
        </div>
    </div>
</div>

@livewireScripts
@endsection