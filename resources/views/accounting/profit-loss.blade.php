@extends('layouts.sneat')

@section('title', 'Laba Rugi')

@section('content')
@php $acctTab = 'pl'; @endphp

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h4 class="fw-semibold mb-0">Laporan Laba Rugi</h4>
        <small class="text-muted">Periode {{ $from }} s/d {{ $to }}</small>
    </div>
</div>

@include('accounting._nav', compact('acctTab'))

<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-auto"><input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm"></div>
            <div class="col-auto"><input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm"></div>
            <div class="col-auto"><button class="btn btn-primary btn-sm">Filter</button></div>
        </form>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr><th colspan="2">Rincian</th><th class="text-end">Jumlah</th></tr>
                    </thead>
                    <tbody>
                        <tr class="table-info"><td colspan="3" class="fw-bold text-uppercase small">Pendapatan</td></tr>
                        @forelse ($revenue['rows'] as $r)
                            <tr><td class="ps-4 text-muted w-25">{{ $r->code }}</td><td>{{ $r->name }}</td><td class="text-end">{{ number_format($r->amount,0,',','.') }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="ps-4 text-muted">Belum ada pendapatan.</td></tr>
                        @endforelse
                        <tr><td colspan="2" class="text-end fw-semibold">Total Pendapatan</td><td class="text-end fw-bold text-success">{{ number_format($totalRevenue,0,',','.') }}</td></tr>

                        <tr class="table-warning"><td colspan="3" class="fw-bold text-uppercase small">Harga Pokok Penjualan (HPP)</td></tr>
                        @forelse ($cogs['rows'] as $r)
                            <tr><td class="ps-4 text-muted w-25">{{ $r->code }}</td><td>{{ $r->name }}</td><td class="text-end">({{ number_format(abs($r->amount),0,',','.') }})</td></tr>
                        @empty
                            <tr><td colspan="3" class="ps-4 text-muted">Belum ada HPP.</td></tr>
                        @endforelse
                        <tr><td colspan="2" class="text-end fw-semibold">Total HPP</td><td class="text-end fw-bold text-danger">({{ number_format($totalCogs,0,',','.') }})</td></tr>

                        <tr class="fw-bold">
                            <td colspan="2">LABA KOTOR</td>
                            <td class="text-end {{ $grossProfit >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($grossProfit,0,',','.') }}</td>
                        </tr>

                        <tr class="table-light"><td colspan="3" class="fw-bold text-uppercase small">Beban Operasional</td></tr>
                        @forelse ($expenses['rows'] as $r)
                            <tr><td class="ps-4 text-muted w-25">{{ $r->code }}</td><td>{{ $r->name }}</td><td class="text-end">({{ number_format(abs($r->amount),0,',','.') }})</td></tr>
                        @empty
                            <tr><td colspan="3" class="ps-4 text-muted">Belum ada beban.</td></tr>
                        @endforelse
                        <tr><td colspan="2" class="text-end fw-semibold">Total Beban</td><td class="text-end fw-bold text-danger">({{ number_format($totalExpense,0,',','.') }})</td></tr>

                        <tr class="table-dark fw-bold">
                            <td colspan="2">LABA BERSIH</td>
                            <td class="text-end {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($netProfit,0,',','.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-body text-center">
                <div class="text-muted small">Laba Bersih {{ $from }} s/d {{ $to }}</div>
                <div class="display-6 fw-bold {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($netProfit,0,',','.') }}
                </div>
                <small class="text-muted">Pendapatan {{ number_format($totalRevenue,0,',','.') }} · HPP {{ number_format($totalCogs,0,',','.') }} · Beban {{ number_format($totalExpense,0,',','.') }}</small>
            </div>
        </div>
    </div>
</div>
@endsection
