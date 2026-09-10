@extends('layouts.app')
@section('title', 'Jurnal Umum')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1">Jurnal Umum (General Journal)</h4><div class="text-muted">Lihat semua entri jurnal per periode.</div></div>
    </div>
    <div class="card mb-4"><div class="card-body">
        <form wire:submit.prevent="$refresh" class="row g-3 align-items-end">
            <div class="col-md-4"><label class="form-label">Dari</label><input type="date" wire:model="from" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Sampai</label><input type="date" wire:model="to" class="form-control"></div>
            <div class="col-md-4"><button type="submit" class="btn btn-primary"><i class="bx bx-filter me-1"></i> Filter</button></div>
        </form>
    </div></div>
    <div class="card mb-3"><div class="card-body d-flex gap-4">
        <span class="text-muted">Total Debit:</span> <strong>Rp {{ number_format($totalDebit, 0, ',', '.') }}</strong>
        <span class="text-muted">Total Kredit:</span> <strong>Rp {{ number_format($totalCredit, 0, ',', '.') }}</strong>
    </div></div>
    <div class="card"><div class="card-body">
        @forelse($entries as $date => $dayEntries)
            <h6 class="text-muted mb-3">{{ \Carbon\Carbon::parse($date)->format('d F Y') }}</h6>
            @foreach($dayEntries as $entry)
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between">
                        <span class="fw-semibold">{{ $entry->journal_code }}</span>
                        <span class="text-muted small">{{ $entry->description }}</span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tbody>
                                @foreach($entry->lines as $line)
                                    <tr>
                                        <td class="ps-4">[{{ $line->account->code ?? '-' }}] {{ $line->account->name ?? '-' }}</td>
                                        <td class="text-end" style="width:120px;">{{ $line->debit > 0 ? number_format($line->debit, 0, ',', '.') : '-' }}</td>
                                        <td class="text-end" style="width:120px;">{{ $line->credit > 0 ? number_format($line->credit, 0, ',', '.') : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        @empty
            <div class="text-center text-muted py-4">Tidak ada jurnal pada periode ini.</div>
        @endforelse
    </div></div>
</div>
@livewireScripts
@endsection