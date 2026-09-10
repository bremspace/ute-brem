@extends('layouts.app')
@section('title', 'Kasbon Karyawan')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1">Kasbon Karyawan</h4><div class="text-muted">Kelola kasbon/advance karyawan.</div></div>
        <button wire:click="openFormModal" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Tambah Kasbon</button>
    </div>
    @if(session('success'))<div class="alert alert-success alert-dismissible" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    <div class="card"><div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead><tr><th>Kode</th><th>Tanggal</th><th>Karyawan</th><th>Akun</th><th class="text-end">Jumlah</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($this->advances as $adv)
                        <tr>
                            <td class="small fw-semibold">{{ $adv->advance_code }}</td>
                            <td class="small">{{ $adv->advance_date->format('d/m/Y') }}</td>
                            <td>{{ $adv->employee?->name ?? '-' }}</td>
                            <td>{{ $adv->cashAccount?->name ?? '-' }}</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($adv->amount, 0, ',', '.') }}</td>
                            <td><span class="badge {{ $adv->status === 'open' ? 'bg-warning' : 'bg-success' }}">{{ ucfirst($adv->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada kasbon.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div></div>
</div>
<div class="modal fade" x-show="showFormModal" x-transition.opacity role="dialog" tabindex="-1"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Tambah Kasbon</h5><button type="button" class="btn-close" @click="showFormModal = false"></button></div><div class="modal-body">
    <form wire:submit.prevent="store">
        <div class="mb-3"><label class="form-label">Tanggal <span class="text-danger">*</span></label><input type="date" wire:model="advanceDate" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Karyawan <span class="text-danger">*</span></label><select wire:model="employeeId" class="form-select" required><option value="">Pilih</option>@foreach($this->employees as $emp)<option value="{{ $emp->id }}">{{ $emp->name }}</option>@endforeach</select></div>
        <div class="mb-3"><label class="form-label">Akun Kas <span class="text-danger">*</span></label><select wire:model="cashAccountId" class="form-select" required><option value="">Pilih</option>@foreach($this->activeCashAccounts as $acc)<option value="{{ $acc->id }}">{{ $acc->name }}</option>@endforeach</select></div>
        <div class="mb-3"><label class="form-label">Jumlah <span class="text-danger">*</span></label><input type="number" wire:model="amount" class="form-control" min="1" required></div>
        <div class="mb-3"><label class="form-label">Keterangan</label><input type="text" wire:model="description" class="form-control"></div>
        <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan</button>
    </form>
</div></div></div></div>
@livewireScripts
@endsection