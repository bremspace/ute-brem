@extends('layouts.sneat')

@section('title', 'Neraca')

@section('content')
@php $acctTab = 'bs'; @endphp

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h4 class="fw-semibold mb-0">Neraca (Balance Sheet)</h4>
        <small class="text-muted">Posisi keuangan per tanggal {{ $asOf }}</small>
    </div>
</div>

@include('accounting._nav', compact('acctTab'))

<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-auto"><input type="date" name="as_of" value="{{ $asOf }}" class="form-control form-control-sm"></div>
            <div class="col-auto"><button class="btn btn-primary btn-sm">Tampilkan</button></div>
        </form>
    </div>
</div>

@php
    $balanced = abs($totalAssets - ($totalLiabilities + $totalEquity)) < 1;
@endphp

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-success-subtle fw-bold">ASET</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <tbody>
                        @foreach ($assetGroups['groups'] as $g)
                            <tr class="table-light"><td colspan="3" class="fw-semibold text-uppercase small">{{ $g->name }}</td></tr>
                            @forelse ($g->rows as $r)
                                <tr><td class="ps-4 text-muted w-25">{{ $r->code }}</td><td>{{ $r->name }}</td><td class="text-end">{{ number_format($r->balance,0,',','.') }}</td></tr>
                            @empty
                                <tr><td colspan="3" class="ps-4 text-muted">—</td></tr>
                            @endforelse
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold"><td colspan="2">TOTAL ASET</td><td class="text-end">{{ number_format($totalAssets,0,',','.') }}</td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-warning-subtle fw-bold">KEWAJIBAN & EKUITAS</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <tbody>
                        @foreach ($liabilityGroups['groups'] as $g)
                            <tr class="table-light"><td colspan="2" class="fw-semibold text-uppercase small">{{ $g->name }}</td></tr>
                            @forelse ($g->rows as $r)
                                <tr><td class="ps-4 text-muted w-25">{{ $r->code }}</td><td>{{ $r->name }}</td><td class="text-end">{{ number_format($r->balance,0,',','.') }}</td></tr>
                            @empty
                                <tr><td colspan="3" class="ps-4 text-muted">—</td></tr>
                            @endforelse
                        @endforeach
                        @foreach ($equityGroups['groups'] as $g)
                            <tr class="table-light"><td colspan="2" class="fw-semibold text-uppercase small">{{ $g->name }}</td></tr>
                            @forelse ($g->rows as $r)
                                <tr><td class="ps-4 text-muted w-25">{{ $r->code }}</td><td>{{ $r->name }}</td><td class="text-end">{{ number_format($r->balance,0,',','.') }}</td></tr>
                            @empty
                                <tr><td colspan="3" class="ps-4 text-muted">—</td></tr>
                            @endforelse
                        @endforeach
                        <tr><td class="ps-4 fw-semibold">Laba Berjalan (YTD)</td><td></td><td class="text-end fw-semibold">{{ number_format($retained,0,',','.') }}</td></tr>
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold"><td colspan="2">TOTAL KEWAJIBAN & EKUITAS</td><td class="text-end">{{ number_format($totalLiabilities + $totalEquity,0,',','.') }}</td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="alert {{ $balanced ? 'alert-success' : 'alert-warning' }} mt-3 mb-0">
    <b>Status Neraca:</b>
    @if ($balanced)
        Seimbang — Total Aset ({{ number_format($totalAssets,0,',','.') }}) = Kewajiban + Ekuitas ({{ number_format($totalLiabilities + $totalEquity,0,',','.') }}).
    @else
        Belum seimbang: Aset {{ number_format($totalAssets,0,',','.') }} vs Kewajiban+Ekuitas {{ number_format($totalLiabilities + $totalEquity,0,',','.') }}. Periksa pencatatan.
    @endif
</div>
@endsection
