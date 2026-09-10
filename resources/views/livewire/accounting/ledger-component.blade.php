@extends('layouts.app')
@section('title', 'Buku Besar')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1">Buku Besar (General Ledger)</h4><div class="text-muted">Lihat catatan akun per periode.</div></div>
    </div>
    <div class="card mb-4"><div class="card-body">
        <form wire:submit.prevent="$refresh" class="row g-3 align-items-end">
            <div class="col-md-4"><label class="form-label">Akun</label><select wire:model="accountId" class="form-select"><option value="">Pilih Akun</option>@foreach($this->accounts as $acc)<option value="{{ $acc->id }}" {{ $accountId == $acc->id ? 'selected' : '' }}>[{{ $acc->code }}] {{ $acc->name }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label">Dari</label><input type="date" wire:model="from" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Sampai</label><input type="date" wire:model="to" class="form-control"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter me-1"></i> Filter</button></div>
        </form>
    </div></div>
    @if($selected)
    <div class="card mb-3"><div class="card-body"><span class="text-muted">Saldo Awal:</span> <strong class="ms-2">Rp {{ number_format($opening, 0, ',', '.') }}</strong></div></div>
    @endif
    <div class="card"><div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead><tr><th>Kode Jurnal</th><th>Tanggal</th><th>Keterangan</th><th>Ref</th><th class="text-end">Debit</th><th class="text-end">Kredit</th><th class="text-end">Saldo</th></tr></thead>
                <tbody>
                    @forelse($this->rows as $row)
                        <tr>
                            <td class="small fw-semibold">{{ $row->code }}</td>
                            <td class="small">{{ $row->date }}</td>
                            <td class="small">{{ $row->description }}</td>
                            <td class="small text-muted">{{ $row->reference ?? '-' }}</td>
                            <td class="text-end">{{ $row->debit > 0 ? number_format($row->debit, 0, ',', '.') : '-' }}</td>
                            <td class="text-end">{{ $row->credit > 0 ? number_format($row->credit, 0, ',', '.') : '-' }}</td>
                            <td class="text-end fw-bold">Rp {{ number_format($row->balance, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div></div>
</div>
@livewireScripts
@endsection