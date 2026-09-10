@extends('layouts.app')
@section('title', 'Transaksi Kas')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">{{ $type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}</h4>
            <div class="text-muted">Kelola transaksi pemasukan/pengeluaran kas.</div>
        </div>
        <button wire:click="openFormModal" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Tambah</button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead><tr><th>Kode</th><th>Tanggal</th><th>Akun</th><th>Kategori</th><th>Keterangan</th><th class="text-end">Jumlah</th></tr></thead>
                    <tbody>
                        @forelse($this->transactions as $trx)
                            <tr>
                                <td class="small fw-semibold">{{ $trx->transaction_code }}</td>
                                <td class="small">{{ $trx->transaction_date->format('d/m/Y') }}</td>
                                <td>{{ $trx->cashAccount?->name ?? '-' }}</td>
                                <td>{{ $trx->costCategory?->name ?? '-' }}</td>
                                <td class="small text-muted">{{ $trx->description ?? '-' }}</td>
                                <td class="text-end fw-semibold {{ $type === 'income' ? 'text-success' : 'text-danger' }}">
                                    {{ $type === 'income' ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada transaksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" x-show="showFormModal" x-transition.opacity role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Tambah {{ $type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}</h5><button type="button" class="btn-close" @click="showFormModal = false"></button></div>
            <div class="modal-body">
                <form wire:submit.prevent="store">
                    <div class="mb-3"><label class="form-label">Tanggal <span class="text-danger">*</span></label><input type="date" wire:model="transactionDate" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Akun Kas <span class="text-danger">*</span></label><select wire:model="cashAccountId" class="form-select" required><option value="">Pilih</option>@foreach($this->activeCashAccounts as $acc)<option value="{{ $acc->id }}">{{ $acc->name }}</option>@endforeach</select></div>
                    <div class="mb-3"><label class="form-label">Kategori Biaya</label><select wire:model="costCategoryId" class="form-select"><option value="">Pilih</option>@foreach($this->categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach</select></div>
                    <div class="mb-3"><label class="form-label">Jumlah <span class="text-danger">*</span></label><input type="number" wire:model="amount" class="form-control" min="1" required></div>
                    <div class="mb-3"><label class="form-label">Keterangan</label><input type="text" wire:model="description" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Referensi</label><input type="text" wire:model="reference" class="form-control"></div>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@livewireScripts
@endsection