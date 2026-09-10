@extends('layouts.app')
@section('title', 'Laporan')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles
    <div class="mb-4"><h4 class="mb-1">Laporan (Reports)</h4><div class="text-muted">Ringkasan laporan operasional.</div></div>
    <div class="row g-4">
        <div class="col-md-3">
            <a href="{{ route('reports.sales') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bx bx-cart fs-1 text-primary"></i>
                    <h6 class="mt-2">Penjualan</h6>
                    <div class="text-muted small">Rp {{ number_format($this->todaySales, 0, ',', '.') }}</div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('reports.services') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bx bx-wrench fs-1 text-success"></i>
                    <h6 class="mt-2">Servis</h6>
                    <div class="text-muted small">Rp {{ number_format($this->todayServices, 0, ',', '.') }}</div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('reports.stocks') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bx bx-package fs-1 text-warning"></i>
                    <h6 class="mt-2">Stok Menipis</h6>
                    <div class="text-muted small">{{ $this->lowStockCount }} produk</div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('reports.receivables') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bx bx-dollar fs-1 text-danger"></i>
                    <h6 class="mt-2">Piutang</h6>
                    <div class="text-muted small">Rp {{ number_format($this->totalReceivables, 0, ',', '.') }}</div>
                </div>
            </a>
        </div>
    </div>
    <div class="row g-4 mt-2">
        <div class="col-md-3">
            <a href="{{ route('reports.purchases') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bx bx-shopping-bag fs-1 text-info"></i>
                    <h6 class="mt-2">Pembelian</h6>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('reports.cash') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bx bx-wallet fs-1 text-secondary"></i>
                    <h6 class="mt-2">Kas</h6>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('reports.profit-loss') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bx bx-line-chart fs-1 text-primary"></i>
                    <h6 class="mt-2">Laba Rugi</h6>
                </div>
            </a>
        </div>
    </div>
</div>
@livewireScripts
@endsection