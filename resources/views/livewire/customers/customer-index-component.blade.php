@extends('layouts.app')

@section('title', 'Customer')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Customer</h4>
            <div class="text-muted">Kelola data customer dan member.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('customers.import') }}" class="btn btn-outline-primary">
                <i class="bx bx-upload me-1"></i>Import
            </a>
            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i>Tambah Customer
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <div class="text-muted small">Total</div>
                            <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
                        </div>
                        <div class="text-primary"><i class="bx bx-users fs-1"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <div class="text-muted small">Member</div>
                            <h3 class="mb-0 fs-4">{{ $stats['members'] ?? 0 }}</h3>
                        </div>
                        <div class="text-success"><i class="bx bx-group fs-1"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <div class="text-muted small">Regular</div>
                            <h3 class="mb-0 fs-4">{{ $stats['regular'] ?? 0 }}</h3>
                        </div>
                        <div class="text-info"><i class="bx bx-user fs-1"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <div class="text-muted small">Aktif</div>
                            <h3 class="mb-0 fs-4">{{ $stats['active'] ?? 0 }}</h3>
                        </div>
                        <div class="text-warning"><i class="bx bx-check-circle fs-1"></i></div>
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
                    <div class="col-md-3">
                        <label class="form-label">Tipe</label>
                        <select class="form-select" wire:model="typeFilter">
                            <option value="">Semua</option>
                            <option value="member">Member</option>
                            <option value="regular">Regular</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" wire:model="statusFilter">
                            <option value="">Semua</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cari</label>
                        <div class="input-group">
                            <input type="text" class="form-control" wire:model="search" placeholder="Nama / Kode / HP">
                            <button type="submit" class="btn btn-outline-secondary"><i class="bx bx-search"></i></button>
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

    <!-- Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Customer</h5>
            @if($customers->total() > 0)
                <span class="text-muted">Total: {{ $customers->total() }}</span>
            @endif
        </div>
        <div class="card-body">
            @if($customers->total() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Kode Member</th>
                                <th>Nama</th>
                                <th>No HP</th>
                                <th>Email</th>
                                <th>Tipe</th>
                                <th>Poin</th>
                                <th>Group</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $customer)
                                @php
                                    $statusClass = $customer->is_active ? 'bg-label-success' : 'bg-label-secondary';
                                @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $customer->member_code ?? '-' }}</td>
                                    <td>{{ $customer->name }}</td>
                                    <td>{{ $customer->phone ?: '-' }}</td>
                                    <td class="text-muted small">{{ $customer->email ?: '-' }}</td>
                                    <td>
                                        <span class="badge {{ $customer->type === 'member' ? 'bg-label-success' : 'bg-label-info' }} fs-6">
                                            {{ ucfirst($customer->type) }}
                                        </span>
                                    </td>
                                    <td>{{ number_format((int) $customer->points_balance, 0, ',', '.') }}</td>
                                    <td>{{ $customer->group?->name ?? '-' }}</td>
                                    <td><span class="badge {{ $statusClass }} fs-6">{{ $customer->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <button wire:click="openDetailModal({{ $customer->id }})" class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-detail"></i>
                                            </button>
                                            <button wire:click="confirmRedeem({{ $customer->id }})" class="btn btn-sm btn-outline-warning"
                                                    @if($customer->type !== 'member' || ! $customer->is_active) disabled @endif>
                                                <i class="bx bx-gift"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $customers->links() }}
            @else
                <div class="text-center py-5">
                    <div class="text-muted mb-3"><i class="bx bx-inbox fs-1"></i></div>
                    <p>Belum ada data customer.</p>
                    <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">
                        <i class="bx bx-plus me-1"></i> Tambah Customer
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" x-show="showDetailModal" x-transition.opacity role="dialog" tabindex="-1">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Customer</h5>
                <button type="button" class="btn-close" @click="closeDetailModal()"></button>
            </div>
            <div class="modal-body">
                @if($selectedCustomerId)
                    @livewire('customers.customer-form', ['customerId' => $selectedCustomerId], key('customer-form-' . $selectedCustomerId))
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Redeem Confirm Modal -->
<div class="modal fade" x-show="showRedeemModal" x-transition.opacity role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tukar Poin</h5>
                <button type="button" class="btn-close" @click="cancelRedeem()"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Jumlah Poin</label>
                    <input type="number" wire:model="redeemPoints" class="form-control" min="1" placeholder="Masukkan poin">
                </div>
                <div class="mb-3">
                    <label class="form-label">Alasan</label>
                    <input type="text" wire:model="redeemReason" class="form-control" placeholder="Contoh: Tukar hadiah">
                </div>
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-1"></i>
                    Poin akan dikurangi dari saldo customer.
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" @click="cancelRedeem()">Batal</button>
                <button class="btn btn-warning" @click="executeRedeem()">Tukar Poin</button>
            </div>
        </div>
    </div>
</div>

@livewireScripts
@endsection