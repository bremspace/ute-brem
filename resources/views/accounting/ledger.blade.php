@extends('layouts.sneat')

@section('title', 'Buku Besar')

@push('styles')
<style>
    .stat-mini { font-size: .85rem; }
    .bal-pos { color: var(--bs-success); font-weight: 700; }
    .bal-neg { color: var(--bs-danger); font-weight: 700; }
</style>
@endpush

@section('content')
@php $acctTab = 'ledger'; @endphp

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h4 class="fw-semibold mb-0">Buku Besar (General Ledger)</h4>
        <small class="text-muted">Rincian mutasi per akun pada periode terpilih.</small>
    </div>
</div>

@include('accounting._nav', compact('acctTab'))

<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="GET" action="{{ route('accounting.ledger') }}">
            <div class="col-auto">
                <select name="account" class="form-select form-select-sm" style="min-width:220px;">
                    <option value="">-- Pilih Akun --</option>
                    @foreach ($accounts as $acc)
                        <option value="{{ $acc->id }}" @selected((int) $accountId === $acc->id)>
                            {{ $acc->code }} · {{ $acc->name }} ({{ $acc->normal_balance }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
            </div>
            <div class="col-auto">
                <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
            </div>
            <div class="col-auto">
                <button class="btn btn-primary btn-sm">Tampilkan</button>
            </div>
        </form>
    </div>
</div>

@if ($selected)
    <div class="card">
        <div class="card-body p-0">
            <div class="d-flex flex-wrap gap-4 align-items-center justify-content-between px-4 py-3 border-bottom">
                <div>
                    <h5 class="mb-1 fw-bold">{{ $selected->code }} · {{ $selected->name }}</h5>
                    <small class="text-muted">Saldo Normal: {{ ucfirst($selected->normal_balance) }} · Kategori: {{ ucfirst($selected->category) }}</small>
                </div>
                <div class="text-end">
                    <div class="stat-mini text-muted">Saldo Awal</div>
                    <div class="fs-5 fw-bold">{{ number_format($opening, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th><th>No. Jurnal</th><th>Keterangan</th><th>Referensi</th>
                            <th class="text-end">Debit</th><th class="text-end">Kredit</th><th class="text-end">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $r)
                            <tr>
                                <td>{{ $r->date }}</td>
                                <td class="text-muted">{{ $r->code }}</td>
                                <td>{{ $r->description }}</td>
                                <td><span class="badge bg-light text-dark">{{ $r->reference }}</span></td>
                                <td class="text-end">{{ $r->debit ? number_format($r->debit,0,',','.') : '-' }}</td>
                                <td class="text-end">{{ $r->credit ? number_format($r->credit,0,',','.') : '-' }}</td>
                                <td class="text-end {{ $running >= 0 ? 'bal-pos' : 'bal-neg' }}">{{ number_format($r->balance,0,',','.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada transaksi pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="5" class="text-end fw-semibold">Saldo Akhir</td>
                            <td></td>
                            <td class="text-end fw-bold {{ $running >= 0 ? 'bal-pos' : 'bal-neg' }}">{{ number_format($running,0,',','.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@else
    <div class="alert alert-info">Pilih akun untuk melihat mutasinya.</div>
@endif
@endsection
