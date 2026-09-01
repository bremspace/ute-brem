@extends('layouts.sneat')

@section('title', 'Back Office')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Back Office</h4>
            <div class="text-muted">Operasional kas dan stok non-penjualan.</div>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="row g-3 mb-4">
        @foreach([
            ['Pemasukan', 'back-office.cash-transactions.index', ['type' => 'income'], 'bx-log-in'],
            ['Pengeluaran', 'back-office.cash-transactions.index', ['type' => 'expense'], 'bx-log-out'],
            ['Kasbon Karyawan', 'back-office.employee-advances.index', [], 'bx-user'],
            ['Mutasi Kas', 'back-office.cash-mutations.index', [], 'bx-transfer'],
            ['Koreksi Stok', 'back-office.stock-documents.index', ['type' => 'correction'], 'bx-adjust'],
            ['Pemakaian Barang', 'back-office.stock-documents.index', ['type' => 'usage'], 'bx-package'],
            ['Master Data Kas', 'back-office.cash-accounts.index', [], 'bx-wallet'],
            ['Master Data Biaya', 'back-office.cost-categories.index', [], 'bx-list-ul'],
            ['Master Jasa', 'services.index', [], 'bx-wrench'],
        ] as [$label, $route, $params, $icon])
            <div class="col-6 col-md-4 col-xl-3">
                <a href="{{ route($route, $params) }}" class="card h-100 text-decoration-none text-body">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="badge bg-label-primary p-2"><i class="bx {{ $icon }}"></i></span>
                        <div class="fw-semibold">{{ $label }}</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Saldo Kas</h5></div>
                <div class="card-body">
                    @forelse($cashAccounts as $account)
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <div>
                                <div class="fw-semibold">{{ $account->name }}</div>
                                <div class="text-muted small">{{ $account->code }} | {{ strtoupper($account->type) }}</div>
                            </div>
                            <div class="fw-bold">Rp {{ number_format((float) $account->current_balance, 0, ',', '.') }}</div>
                        </div>
                    @empty
                        <div class="text-muted">Belum ada data kas.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Aktivitas Terbaru</h5></div>
                <div class="card-body">
                    @forelse($recentCashTransactions as $row)
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <div>
                                <div class="fw-semibold">{{ $row->transaction_code }}</div>
                                <div class="text-muted small">{{ $row->cashAccount?->name ?: '-' }}{{ $row->targetCashAccount ? ' -> ' . $row->targetCashAccount->name : '' }}</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold">Rp {{ number_format((float) $row->amount, 0, ',', '.') }}</div>
                                <div class="text-muted small">{{ optional($row->transaction_date)->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">Belum ada aktivitas.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
