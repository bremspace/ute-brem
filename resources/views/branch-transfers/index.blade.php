@extends('layouts.sneat')

@section('title', 'Kirim Stok Antar Cabang')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Filter Pencarian</h5>
            @if(auth()->user()->hasPermission('master.product_stocks.edit'))
                <a href="{{ route('branch-transfers.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Buat Transfer Stok
                </a>
            @endif
        </div>
        <div class="card-body">
            <form action="{{ route('branch-transfers.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label" for="status">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>Dalam Perjalanan</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="source_branch_id">Cabang Asal</label>
                    <select name="source_branch_id" id="source_branch_id" class="form-select">
                        <option value="">Semua Cabang</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('source_branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="target_branch_id">Cabang Tujuan</label>
                    <select name="target_branch_id" id="target_branch_id" class="form-select">
                        <option value="">Semua Cabang</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('target_branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <div class="w-100">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="w-100">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                </div>
                <div class="col-12 text-end">
                    <a href="{{ route('branch-transfers.index') }}" class="btn btn-outline-secondary me-2">Reset</a>
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Kode Dokumen</th>
                        <th>Cabang Asal</th>
                        <th>Cabang Tujuan</th>
                        <th>Tanggal Kirim</th>
                        <th>Tanggal Diterima</th>
                        <th>Pembuat</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($transfers as $transfer)
                        <tr>
                            <td>
                                <a href="{{ route('branch-transfers.show', $transfer) }}" class="fw-semibold text-primary">
                                    {{ $transfer->transfer_code }}
                                </a>
                            </td>
                            <td>
                                <div>{{ $transfer->sourceBranch?->name ?? '-' }}</div>
                                <small class="text-muted">{{ $transfer->sourceLocation?->name }}</small>
                            </td>
                            <td>
                                <div>{{ $transfer->targetBranch?->name ?? '-' }}</div>
                                <small class="text-muted">{{ $transfer->targetLocation?->name }}</small>
                            </td>
                            <td>{{ $transfer->sent_at ? $transfer->sent_at->format('d/m/Y H:i') : '-' }}</td>
                            <td>{{ $transfer->received_at ? $transfer->received_at->format('d/m/Y H:i') : '-' }}</td>
                            <td>{{ $transfer->creator?->name }}</td>
                            <td>
                                @if($transfer->status === 'draft')
                                    <span class="badge bg-label-secondary">Draft</span>
                                @elseif($transfer->status === 'in_transit')
                                    <span class="badge bg-label-info">Dalam Perjalanan</span>
                                @elseif($transfer->status === 'completed')
                                    <span class="badge bg-label-success">Selesai</span>
                                @elseif($transfer->status === 'cancelled')
                                    <span class="badge bg-label-danger">Dibatalkan</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-menu-item dropdown-item" href="{{ route('branch-transfers.show', $transfer) }}">
                                            <i class="bx bx-show-alt me-1"></i> Detail
                                        </a>
                                        @if($transfer->status === 'in_transit' && auth()->user()->hasPermission('master.product_stocks.edit'))
                                            <a class="dropdown-menu-item dropdown-item" href="{{ route('branch-transfers.receive.form', $transfer) }}">
                                                <i class="bx bx-package me-1"></i> Terima Barang
                                            </a>
                                        @endif
                                        <a class="dropdown-menu-item dropdown-item" href="{{ route('branch-transfers.print', $transfer) }}" target="_blank">
                                            <i class="bx bx-printer me-1"></i> Cetak Surat Jalan
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                Belum ada riwayat transfer stok cabang.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transfers->hasPages())
            <div class="card-footer d-flex justify-content-end">
                {{ $transfers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
