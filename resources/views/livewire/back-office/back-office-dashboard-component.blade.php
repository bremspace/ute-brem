@extends('layouts.app')
@section('title', 'Back Office Dashboard')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles
    <div class="mb-4">
        <h4 class="mb-1">Back Office Dashboard</h4>
        <div class="text-muted">Ringkasan operasional back office.</div>
    </div>

    <!-- Cash Accounts Summary -->
    <div class="row g-3 mb-4">
        @foreach($this->cashAccounts as $account)
            <div class="col-md-3">
                <div class="card mb-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <div class="text-muted small">{{ $account->name }}</div>
                                <h4 class="mb-0">Rp {{ number_format($account->current_balance, 0, ',', '.') }}</h4>
                                <small class="text-muted">{{ strtoupper($account->type) }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Quick Links -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <a href="{{ route('back-office.cash-accounts.index') }}" class="card text-decoration-none">
                <div class="card-body text-center">
                    <i class="bx bx-wallet fs-1 text-primary"></i>
                    <h6 class="mt-2">Kas & Bank</h6>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('back-office.cash-transactions.index', 'income') }}" class="card text-decoration-none">
                <div class="card-body text-center">
                    <i class="bx bx-plus-circle fs-1 text-success"></i>
                    <h6 class="mt-2">Pemasukan</h6>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('back-office.cash-transactions.index', 'expense') }}" class="card text-decoration-none">
                <div class="card-body text-center">
                    <i class="bx bx-minus-circle fs-1 text-danger"></i>
                    <h6 class="mt-2">Pengeluaran</h6>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('back-office.stock-documents.index', 'correction') }}" class="card text-decoration-none">
                <div class="card-body text-center">
                    <i class="bx bx-package fs-1 text-warning"></i>
                    <h6 class="mt-2">Koreksi Stok</h6>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Transaksi Kas Terbaru</h5></div>
                <div class="card-body">
                    @if($this->recentCashTransactions->isEmpty())
                        <p class="text-muted">Belum ada transaksi.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead><tr><th>Kode</th><th>Tanggal</th><th>Jenis</th><th class="text-end">Jumlah</th></tr></thead>
                                <tbody>
                                    @foreach($this->recentCashTransactions as $trx)
                                        <tr>
                                            <td class="small">{{ $trx->transaction_code }}</td>
                                            <td class="small">{{ $trx->transaction_date->format('d/m/Y') }}</td>
                                            <td><span class="badge {{ $trx->transaction_type === 'income' ? 'bg-success' : ($trx->transaction_type === 'expense' ? 'bg-danger' : 'bg-info') }}">{{ ucfirst(str_replace('_', ' ', $trx->transaction_type)) }}</span></td>
                                            <td class="text-end small">Rp {{ number_format($trx->amount, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Dokumen Stok Terbaru</h5></div>
                <div class="card-body">
                    @if($this->recentStockDocuments->isEmpty())
                        <p class="text-muted">Belum ada dokumen stok.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead><tr><th>Kode</th><th>Tanggal</th><th>Jenis</th><th>Produk</th><th class="text-end">Qty</th></tr></thead>
                                <tbody>
                                    @foreach($this->recentStockDocuments as $doc)
                                        <tr>
                                            <td class="small">{{ $doc->document_code }}</td>
                                            <td class="small">{{ $doc->document_date->format('d/m/Y') }}</td>
                                            <td><span class="badge {{ $doc->document_type === 'correction' ? 'bg-warning' : 'bg-secondary' }}">{{ ucfirst($doc->document_type) }}</span></td>
                                            <td class="small">{{ $doc->product?->name ?? '-' }}</td>
                                            <td class="text-end small">{{ number_format($doc->quantity, 2, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@livewireScripts
@endsection