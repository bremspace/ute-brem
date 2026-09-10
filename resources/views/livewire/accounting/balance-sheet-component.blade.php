@extends('layouts.app')
@section('title', 'Neraca')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles
    <div class="mb-4"><h4 class="mb-1">Neraca (Balance Sheet)</h4><div class="text-muted">Posisi keuangan per tanggal tertentu.</div></div>
    <div class="card mb-4"><div class="card-body">
        <form wire:submit.prevent="$refresh" class="row g-3 align-items-end">
            <div class="col-md-4"><label class="form-label">Per Tanggal</label><input type="date" wire:model="asOf" class="form-control"></div>
            <div class="col-md-4"><button type="submit" class="btn btn-primary"><i class="bx bx-filter me-1"></i> Filter</button></div>
        </form>
    </div></div>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card h-100"><div class="card-header"><h5 class="mb-0">Aset (Assets)</h5></div><div class="card-body">
                @foreach($assetGroups['groups'] as $group)
                    <h6 class="text-muted mt-3">{{ $group->name }}</h6>
                    @foreach($group->rows as $r)
                        <div class="d-flex justify-content-between py-1"><span class="small">[{{ $r->code }}] {{ $r->name }}</span><span class="small fw-semibold">Rp {{ number_format($r->balance, 0, ',', '.') }}</span></div>
                    @endforeach
                    <div class="d-flex justify-content-between py-1 border-top"><strong>Subtotal</strong><strong>Rp {{ number_format($group->subtotal, 0, ',', '.') }}</strong></div>
                @endforeach
                <div class="d-flex justify-content-between py-2 border-top mt-2"><span class="fs-5 fw-bold">Total Aset</span><strong class="fs-5">Rp {{ number_format($totalAssets, 0, ',', '.') }}</strong></div>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4"><div class="card-header"><h5 class="mb-0">Liabilitas (Liabilities)</h5></div><div class="card-body">
                @foreach($liabilityGroups['groups'] as $group)
                    <h6 class="text-muted mt-3">{{ $group->name }}</h6>
                    @foreach($group->rows as $r)
                        <div class="d-flex justify-content-between py-1"><span class="small">[{{ $r->code }}] {{ $r->name }}</span><span class="small fw-semibold">Rp {{ number_format($r->balance, 0, ',', '.') }}</span></div>
                    @endforeach
                @endforeach
                <div class="d-flex justify-content-between py-2 border-top mt-2"><strong>Total Liabilitas</strong><strong>Rp {{ number_format($totalLiabilities, 0, ',', '.') }}</strong></div>
            </div></div>
            <div class="card h-100"><div class="card-header"><h5 class="mb-0">Ekuitas (Equity)</h5></div><div class="card-body">
                @foreach($equityGroups['groups'] as $group)
                    <h6 class="text-muted mt-3">{{ $group->name }}</h6>
                    @foreach($group->rows as $r)
                        <div class="d-flex justify-content-between py-1"><span class="small">[{{ $r->code }}] {{ $r->name }}</span><span class="small fw-semibold">Rp {{ number_format($r->balance, 0, ',', '.') }}</span></div>
                    @endforeach
                @endforeach
                <div class="d-flex justify-content-between py-2 border-top mt-2"><strong>Total Ekuitas</strong><strong>Rp {{ number_format($totalEquity, 0, ',', '.') }}</strong></div>
            </div></div>
        </div>
    </div>
    <div class="card mt-4 {{ $totalAssets == ($totalLiabilities + $totalEquity) ? 'border-success' : 'border-danger' }}"><div class="card-body d-flex justify-content-between">
        <span class="fs-5 fw-bold">Total Aset vs Liabilitas + Ekuitas</span>
        <span class="fs-5 fw-bold {{ $totalAssets == ($totalLiabilities + $totalEquity) ? 'text-success' : 'text-danger' }}">
            Rp {{ number_format($totalAssets, 0, ',', '.') }} {{ $totalAssets == ($totalLiabilities + $totalEquity) ? '=' : '!=' }} Rp {{ number_format($totalLiabilities + $totalEquity, 0, ',', '.') }}
        </span>
    </div></div>
</div>
@livewireScripts
@endsection