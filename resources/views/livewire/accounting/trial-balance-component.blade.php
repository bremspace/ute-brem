@extends('layouts.app')
@section('title', 'Neraca Saldo')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles
    <div class="mb-4"><h4 class="mb-1">Neraca Saldo (Trial Balance)</h4><div class="text-muted">Saldo semua akun postable per periode.</div></div>
    <div class="card mb-4"><div class="card-body">
        <form wire:submit.prevent="$refresh" class="row g-3 align-items-end">
            <div class="col-md-4"><label class="form-label">Dari</label><input type="date" wire:model="from" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Sampai</label><input type="date" wire:model="to" class="form-control"></div>
            <div class="col-md-4"><button type="submit" class="btn btn-primary"><i class="bx bx-filter me-1"></i> Filter</button></div>
        </form>
    </div></div>
    <div class="card"><div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead><tr><th>Kode</th><th>Nama</th><th class="text-end">Debit</th><th class="text-end">Kredit</th><th class="text-end">Saldo Debit</th><th class="text-end">Saldo Kredit</th></tr></thead>
                <tbody>
                    @forelse($this->rows as $row)
                        <tr>
                            <td class="small fw-semibold">{{ $row->code }}</td>
                            <td>{{ $row->name }}</td>
                            <td class="text-end">{{ $row->debit > 0 ? number_format($row->debit, 0, ',', '.') : '-' }}</td>
                            <td class="text-end">{{ $row->credit > 0 ? number_format($row->credit, 0, ',', '.') : '-' }}</td>
                            <td class="text-end">{{ $row->balDebit > 0 ? number_format($row->balDebit, 0, ',', '.') : '-' }}</td>
                            <td class="text-end">{{ $row->balCredit > 0 ? number_format($row->balCredit, 0, ',', '.') : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td colspan="2" class="text-end">TOTAL</td>
                        <td class="text-end">Rp {{ number_format($this->totals['debit'], 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($this->totals['credit'], 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($this->totals['balDebit'], 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($this->totals['balCredit'], 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div></div>
</div>
@livewireScripts
@endsection