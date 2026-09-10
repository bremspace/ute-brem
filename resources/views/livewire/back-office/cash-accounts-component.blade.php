@extends('layouts.app')
@section('title', 'Kas & Bank')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1">Kas & Bank</h4><div class="text-muted">Kelola akun kas, bank, dan e-wallet.</div></div>
        <button wire:click="openFormModal" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Tambah Akun</button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead><tr><th>Kode</th><th>Nama</th><th>Tipe</th><th class="text-end">Saldo</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                        @foreach($this->cashAccounts as $account)
                            <tr>
                                <td class="fw-semibold">{{ $account->code }}</td>
                                <td>{{ $account->name }}</td>
                                <td><span class="badge bg-label-info">{{ strtoupper($account->type) }}</span></td>
                                <td class="text-end fw-semibold">Rp {{ number_format($account->current_balance, 0, ',', '.') }}</td>
                                <td><span class="badge {{ $account->is_active ? 'bg-label-success' : 'bg-label-secondary' }}">{{ $account->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td class="text-end"><button wire:click="toggleStatus({{ $account->id }})" class="btn btn-sm btn-outline-warning">{{ $account->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" x-show="showFormModal" x-transition.opacity role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Tambah Akun Kas</h5><button type="button" class="btn-close" @click="showFormModal = false"></button></div>
            <div class="modal-body">
                <form wire:submit.prevent="store">
                    <div class="mb-3"><label class="form-label">Kode <span class="text-danger">*</span></label><input type="text" wire:model="code" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Nama <span class="text-danger">*</span></label><input type="text" wire:model="name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Tipe <span class="text-danger">*</span></label><select wire:model="type" class="form-select"><option value="cash">Kas</option><option value="bank">Bank</option><option value="ewallet">E-Wallet</option></select></div>
                    <div class="mb-3"><label class="form-label">Saldo Awal</label><input type="number" wire:model="openingBalance" class="form-control" min="0"></div>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@livewireScripts
@endsection