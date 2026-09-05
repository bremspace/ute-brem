@extends('layouts.sneat')

@section('title', 'Ringkasan Transaksi')

@push('styles')
    <style>
        .sale-mobile-items {
            display: none;
        }

        .sale-mobile-page {
            display: none;
        }

        @media (max-width: 767.98px) {
            #layout-menu,
            .layout-navbar,
            .dashboard-fab {
                display: none !important;
            }

            body.transactions-standalone,
            body.transactions-standalone .layout-wrapper,
            body.transactions-standalone .layout-container,
            body.transactions-standalone .layout-page,
            body.transactions-standalone .content-wrapper {
                width: 100% !important;
                max-width: 100% !important;
                min-width: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow-x: hidden !important;
            }

            body.transactions-standalone.sidebar-hidden .layout-page {
                padding-inline-start: 0 !important;
                -webkit-padding-start: 0 !important;
                padding-left: 0 !important;
                margin-left: 0 !important;
            }

            .layout-page {
                padding-inline-start: 0 !important;
                -webkit-padding-start: 0 !important;
                padding-left: 0 !important;
                margin-left: 0 !important;
            }

            .content-wrapper,
            .layout-container,
            .layout-page {
                width: 100% !important;
                max-width: 100% !important;
            }

            .container-xxl.container-p-y {
                width: 100% !important;
                max-width: 100% !important;
                padding: .7rem !important;
                margin: 0 !important;
            }

            .sale-desktop-page {
                display: none !important;
            }

            .sale-mobile-page {
                display: block;
                min-height: 100vh;
                background: #f5f6fb;
                padding: .75rem;
            }

            .sale-mobile-header {
                display: flex;
                justify-content: space-between;
                gap: .75rem;
                align-items: flex-start;
                margin-bottom: .75rem;
            }

            .sale-mobile-title {
                font-size: 19px;
                line-height: 1.15;
                font-weight: 900;
                color: #263238;
            }

            .sale-mobile-code {
                color: #697a8d;
                font-size: 11px;
                word-break: break-word;
                margin-top: .15rem;
            }

            .sale-mobile-actions-top {
                display: flex;
                gap: .4rem;
                flex: 0 0 auto;
            }

            .sale-mobile-total-card,
            .sale-mobile-info-card,
            .sale-mobile-items-card {
                background: #fff;
                border: 1px solid rgba(67, 89, 113, .12);
                border-radius: .65rem;
                padding: .7rem;
                margin-bottom: .7rem;
                box-shadow: 0 8px 18px rgba(32, 36, 44, .06);
            }

            .sale-mobile-grand {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: .75rem;
            }

            .sale-mobile-grand .k {
                font-size: 11px;
                color: #697a8d;
                font-weight: 900;
                text-transform: uppercase;
            }

            .sale-mobile-grand .v {
                font-size: 20px;
                font-weight: 900;
                color: #263238;
                white-space: nowrap;
            }

            .sale-mobile-breakdown,
            .sale-mobile-info-grid {
                display: grid;
                grid-template-columns: 1fr auto;
                gap: .25rem .75rem;
                margin-top: .55rem;
                padding-top: .55rem;
                border-top: 1px dashed rgba(67, 89, 113, .18);
                font-size: 11px;
            }

            .sale-mobile-breakdown .v,
            .sale-mobile-info-grid .v {
                font-weight: 800;
                text-align: right;
                color: #263238;
            }

            .sale-mobile-section-title {
                font-size: 12px;
                font-weight: 900;
                color: #0b4f7a;
                text-transform: uppercase;
                margin-bottom: .55rem;
            }

            .container-xxl > .d-flex.flex-column.flex-md-row {
                align-items: stretch !important;
                margin-bottom: .75rem !important;
            }

            .container-xxl h4 {
                font-size: 18px;
                line-height: 1.2;
            }

            .container-xxl .text-muted {
                word-break: break-word;
            }

            .sale-detail-actions {
                display: grid !important;
                grid-template-columns: 1fr 1fr;
                width: 100%;
                gap: .45rem !important;
            }

            .sale-detail-actions .btn {
                width: 100%;
                padding-inline: .45rem;
                font-size: 12px;
            }

            .row.g-4 {
                --bs-gutter-x: 0;
                --bs-gutter-y: .75rem;
            }

            .card {
                border-radius: .65rem;
            }

            .card-header {
                padding: .7rem .75rem;
                gap: .55rem;
                align-items: flex-start !important;
            }

            .card-header h5 {
                font-size: 15px;
                line-height: 1.2;
            }

            .card-body {
                padding: .75rem;
            }

            .card-body .row.g-3 {
                --bs-gutter-y: .55rem;
            }

            .sale-items-table-wrap {
                display: none !important;
            }

            .sale-mobile-items {
                display: grid;
                gap: .6rem;
            }

            .sale-mobile-item {
                border: 1px solid rgba(67, 89, 113, .16);
                border-radius: .55rem;
                padding: .65rem;
                background: #fff;
            }

            .sale-mobile-item-top {
                display: flex;
                justify-content: space-between;
                gap: .65rem;
                align-items: flex-start;
            }

            .sale-mobile-item-name {
                font-size: 13px;
                font-weight: 900;
                line-height: 1.2;
            }

            .sale-mobile-item-code {
                color: #697a8d;
                font-size: 11px;
                margin-top: .15rem;
            }

            .sale-mobile-item-total {
                font-size: 13px;
                font-weight: 900;
                white-space: nowrap;
            }

            .sale-mobile-item-meta {
                display: grid;
                grid-template-columns: 1fr auto;
                gap: .2rem .65rem;
                margin-top: .55rem;
                color: #697a8d;
                font-size: 11px;
            }

            .sale-mobile-item-meta .v {
                color: #263238;
                font-weight: 800;
                text-align: right;
            }

            .sale-mobile-serial {
                margin-top: .55rem;
            }

            .sale-mobile-serial label {
                font-size: 11px;
                font-weight: 800;
                margin-bottom: .2rem;
            }
        }
    </style>
@endpush

@php
    $subtotal = 'Rp ' . number_format((float) $sale->subtotal, 0, ',', '.');
    $discount = 'Rp ' . number_format((float) $sale->discount_total, 0, ',', '.');
    $grand = 'Rp ' . number_format((float) $sale->grand_total, 0, ',', '.');
    $paid = 'Rp ' . number_format((float) $sale->paid_amount, 0, ',', '.');
    $change = 'Rp ' . number_format((float) $sale->change_amount, 0, ',', '.');
    $outstandingAmount = max(0, (float) $sale->grand_total - (float) $sale->paid_amount);
    $outstanding = 'Rp ' . number_format($outstandingAmount, 0, ',', '.');
    $isTempo = ($sale->payment_method ?? '') === 'tempo' || in_array($sale->credit_status ?? 'paid', ['unpaid', 'partial'], true);
    $saleChannel = in_array($sale->sale_channel ?? 'toko', ['toko', 'cabang', 'partai'], true) ? $sale->sale_channel : 'toko';
    $saleIndexRoute = route('transactions.index.channel', $saleChannel);
    $saleCreateRoute = route('transactions.create.channel', $saleChannel);
@endphp

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('success') && $sale->status === 'paid')
        <div class="modal fade" id="printReceiptPromptModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cetak Struk?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-muted">
                            Transaksi berhasil disimpan. Ingin langsung cetak struk tanpa buka tab baru?
                        </div>
                        <iframe id="receiptPrintFrame" title="receipt-print" style="width:0;height:0;border:0;position:absolute;left:-9999px;top:-9999px;"></iframe>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tidak</button>
                        <button type="button" class="btn btn-primary" id="confirmPrintReceiptBtn">
                            <i class="bx bx-printer me-1"></i> Cetak
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="sale-mobile-page">
        <div class="sale-mobile-header">
            <div>
                <div class="sale-mobile-title">Detail Transaksi</div>
                <div class="sale-mobile-code">{{ $sale->sale_code }}</div>
            </div>
            <div class="sale-mobile-actions-top">
                <a href="{{ $saleIndexRoute }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-arrow-back"></i>
                </a>
                <a href="{{ route('transactions.receipt', [$sale, 'print' => 1]) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="bx bx-printer"></i>
                </a>
            </div>
        </div>

        <div class="sale-mobile-total-card">
            <div class="sale-mobile-grand">
                <div>
                    <div class="k">Grand Total</div>
                    <span class="badge bg-label-{{ $sale->status === 'paid' ? 'success' : 'secondary' }}">
                        {{ $sale->status === 'paid' ? 'Paid' : 'Void' }}
                    </span>
                </div>
                <div class="v">{{ $grand }}</div>
            </div>
            <div class="sale-mobile-breakdown">
                <div>Subtotal</div><div class="v">{{ $subtotal }}</div>
                <div>Diskon</div><div class="v">{{ $discount }}</div>
                <div>Bayar</div><div class="v">{{ $paid }}</div>
                <div>{{ $isTempo ? 'Sisa Tempo' : 'Kembali' }}</div><div class="v">{{ $isTempo ? $outstanding : $change }}</div>
                @if($isTempo)
                    <div>Jatuh Tempo</div><div class="v">{{ optional($sale->credit_due_at)->format('d/m/Y') ?: '-' }}</div>
                @endif
            </div>
        </div>

        <div class="sale-mobile-info-card">
            <div class="sale-mobile-section-title">Informasi</div>
            <div class="sale-mobile-info-grid">
                <div>Tanggal</div><div class="v">{{ optional($sale->sale_at)->format('d/m/Y H:i') ?: '-' }}</div>
                <div>Lokasi</div><div class="v">{{ $sale->location?->name ?: '-' }}</div>
                <div>Customer</div><div class="v">{{ $sale->customer?->name ?: '-' }}</div>
                <div>Kasir</div><div class="v">{{ $sale->cashier?->name ?: '-' }}</div>
            </div>
        </div>

        <div class="sale-mobile-items-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="sale-mobile-section-title mb-0">Item</div>
                @if($sale->status !== 'void')
                    <button type="submit" form="serialNumbersForm" class="btn btn-sm btn-outline-primary">
                        <i class="bx bx-save me-1"></i> Serial
                    </button>
                @endif
            </div>
            <div class="sale-mobile-items">
                @foreach($sale->items as $item)
                    <div class="sale-mobile-item">
                        <div class="sale-mobile-item-top">
                            <div>
                                <div class="sale-mobile-item-name">{{ $item->product_name }}</div>
                                <div class="sale-mobile-item-code">{{ $item->product_code ?: '-' }}</div>
                            </div>
                            <div class="sale-mobile-item-total">
                                Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="sale-mobile-item-meta">
                            <div>Qty</div>
                            <div class="v">{{ rtrim(rtrim(number_format((float) $item->quantity, 2, ',', '.'), '0'), ',') }} {{ $item->unit_name ?: '-' }}</div>
                            <div>Harga</div>
                            <div class="v">Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</div>
                            <div>Diskon</div>
                            <div class="v">Rp {{ number_format((float) $item->discount_value, 0, ',', '.') }}</div>
                        </div>
                        @if($item->product?->has_serial_number)
                            <div class="sale-mobile-serial">
                                <label class="form-label">Serial Number</label>
                                <textarea name="serial_numbers[{{ $item->id }}]" form="serialNumbersForm"
                                    class="form-control form-control-sm serial-input-mobile" rows="2"
                                    placeholder="Opsional, 1 serial per baris"
                                    {{ $sale->status === 'void' ? 'readonly' : '' }}>{{ implode("\n", is_array($item->serial_numbers) ? $item->serial_numbers : []) }}</textarea>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="d-grid gap-2">
            @if($sale->status !== 'void' && auth()->user()->hasPermission('transactions.view'))
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#voidTransactionModal">
                    <i class="bx bx-block me-1"></i> Void Transaksi
                </button>
            @endif
            @if($isTempo && $outstandingAmount > 0 && $sale->customer_id)
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#salePaymentModal">
                    <i class="bx bx-wallet me-1"></i> Input Pelunasan
                </button>
            @endif
            @if(auth()->user()->hasPermission('transactions.create'))
                <a href="{{ $saleCreateRoute }}" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Jual Lagi
                </a>
            @endif
        </div>
    </div>

    <div class="sale-desktop-page">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">Ringkasan Transaksi</h4>
            <div class="text-muted">{{ $sale->sale_code }}</div>
        </div>
        <div class="d-flex gap-2 sale-detail-actions">
            <a href="{{ $saleIndexRoute }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Daftar Transaksi
            </a>
            <a href="{{ route('transactions.receipt', $sale) }}" target="_blank" class="btn btn-outline-primary">
                <i class="bx bx-printer me-1"></i> Cetak Struk
            </a>
            @if($sale->status !== 'void' && auth()->user()->hasPermission('transactions.view'))
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#voidTransactionModal">
                    <i class="bx bx-block me-1"></i> Void
                </button>
            @endif
            @if($isTempo && $outstandingAmount > 0 && $sale->customer_id)
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#salePaymentModal">
                    <i class="bx bx-wallet me-1"></i> Input Pelunasan
                </button>
            @endif
            @if(auth()->user()->hasPermission('transactions.create'))
                <a href="{{ $saleCreateRoute }}" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Jual Lagi
                </a>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Informasi</h5>
                    <span class="badge bg-label-{{ $sale->status === 'paid' ? 'success' : 'secondary' }}">
                        {{ $sale->status === 'paid' ? 'Paid' : 'Void' }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Tanggal</div>
                            <div class="fw-semibold">{{ optional($sale->sale_at)->format('d M Y H:i') ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Lokasi</div>
                            <div class="fw-semibold">{{ $sale->location?->name ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Customer</div>
                            <div class="fw-semibold">{{ $sale->customer?->name ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Pembayaran</div>
                            <div class="fw-semibold">{{ strtoupper($sale->payment_method ?: '-') }}</div>
                        </div>
                        @if($isTempo)
                            <div class="col-md-6">
                                <div class="text-muted small">Tempo</div>
                                <div class="fw-semibold">{{ $sale->credit_term_days ?: '-' }} hari, jatuh tempo {{ optional($sale->credit_due_at)->format('d M Y') ?: '-' }}</div>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <div class="text-muted small">Kasir</div>
                            <div class="fw-semibold">{{ $sale->cashier?->name ?: '-' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">Catatan</div>
                            <div class="fw-semibold">{{ $sale->notes ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Item</h5>
                        <div class="text-muted small">Serial number opsional, bisa diisi setelah transaksi.</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-label-secondary">{{ $sale->items->count() }} item</span>
                        @if($sale->status !== 'void')
                            <button type="submit" form="serialNumbersForm" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-save me-1"></i> Simpan Serial
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('transactions.serial-numbers.update', $sale) }}" method="POST" id="serialNumbersForm">
                        @csrf
                        <div class="table-responsive sale-items-table-wrap">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Qty</th>
                                        <th>Harga</th>
                                        <th>Diskon</th>
                                        <th>Subtotal</th>
                                        <th style="min-width: 220px;">Serial</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sale->items as $item)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $item->product_name }}</div>
                                                <div class="text-muted small">{{ $item->product_code ?: '-' }}</div>
                                            </td>
                                            <td>{{ rtrim(rtrim(number_format((float) $item->quantity, 2, ',', '.'), '0'), ',') }} {{ $item->unit_name ?: '-' }}</td>
                                            <td>Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                                            <td>Rp {{ number_format((float) $item->discount_value, 0, ',', '.') }}</td>
                                            <td class="fw-semibold">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                                            <td>
                                                @if($item->product?->has_serial_number)
                                                    <textarea name="serial_numbers[{{ $item->id }}]" class="form-control form-control-sm serial-input-desktop" rows="2"
                                                        placeholder="Opsional, 1 serial per baris"
                                                        {{ $sale->status === 'void' ? 'readonly' : '' }}>{{ implode("\n", is_array($item->serial_numbers) ? $item->serial_numbers : []) }}</textarea>
                                                @elseif(is_array($item->serial_numbers) && count($item->serial_numbers) > 0)
                                                    <span class="badge bg-label-warning">{{ count($item->serial_numbers) }} SN</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="sale-mobile-items">
                            @foreach($sale->items as $item)
                                <div class="sale-mobile-item">
                                    <div class="sale-mobile-item-top">
                                        <div>
                                            <div class="sale-mobile-item-name">{{ $item->product_name }}</div>
                                            <div class="sale-mobile-item-code">{{ $item->product_code ?: '-' }}</div>
                                        </div>
                                        <div class="sale-mobile-item-total">
                                            Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}
                                        </div>
                                    </div>
                                    <div class="sale-mobile-item-meta">
                                        <div>Qty</div>
                                        <div class="v">{{ rtrim(rtrim(number_format((float) $item->quantity, 2, ',', '.'), '0'), ',') }} {{ $item->unit_name ?: '-' }}</div>
                                        <div>Harga</div>
                                        <div class="v">Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</div>
                                        <div>Diskon</div>
                                        <div class="v">Rp {{ number_format((float) $item->discount_value, 0, ',', '.') }}</div>
                                    </div>
                                    @if($item->product?->has_serial_number)
                                        <div class="sale-mobile-serial">
                                            <label class="form-label">Serial Number</label>
                                            <textarea name="serial_numbers[{{ $item->id }}]" class="form-control form-control-sm serial-input-mobile" rows="2"
                                                placeholder="Opsional, 1 serial per baris"
                                                {{ $sale->status === 'void' ? 'readonly' : '' }}>{{ implode("\n", is_array($item->serial_numbers) ? $item->serial_numbers : []) }}</textarea>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Total</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <div class="text-muted">Subtotal</div>
                        <div class="fw-semibold">{{ $subtotal }}</div>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <div class="text-muted">Diskon</div>
                        <div class="fw-semibold">{{ $discount }}</div>
                    </div>
                    <hr class="my-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="text-muted">Grand Total</div>
                        <div class="fw-bold">{{ $grand }}</div>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <div class="text-muted">Bayar</div>
                        <div class="fw-semibold">{{ $paid }}</div>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <div class="text-muted">{{ $isTempo ? 'Sisa Tempo' : 'Kembali' }}</div>
                        <div class="fw-semibold">{{ $isTempo ? $outstanding : $change }}</div>
                    </div>
                    @if($isTempo)
                        <div class="d-flex justify-content-between mb-3">
                            <div class="text-muted">Status Tempo</div>
                            <span class="badge bg-label-{{ ($sale->credit_status ?? 'paid') === 'paid' ? 'success' : 'warning' }}">
                                {{ ($sale->credit_status ?? 'paid') === 'paid' ? 'Lunas' : 'Belum Lunas' }}
                            </span>
                        </div>
                        @if($outstandingAmount > 0 && $sale->customer_id)
                            <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#salePaymentModal">
                                <i class="bx bx-wallet me-1"></i> Input Pelunasan
                            </button>
                        @endif
                    @endif
                </div>
            </div>

            @if($isTempo)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Riwayat Pelunasan</h5>
                    </div>
                    <div class="card-body">
                        @forelse($sale->payments as $payment)
                            <div class="d-flex justify-content-between align-items-start border-bottom pb-2 mb-2">
                                <div>
                                    <div class="fw-semibold">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</div>
                                    <div class="text-muted small">{{ optional($payment->payment_at)->format('d M Y H:i') }} | {{ strtoupper($payment->payment_method) }}</div>
                                </div>
                                <div class="text-muted small text-end">{{ $payment->reference ?: '-' }}</div>
                            </div>
                        @empty
                            <div class="text-muted">Belum ada pelunasan.</div>
                        @endforelse
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Poin Member</h5>
                </div>
                <div class="card-body">
                    <div class="text-muted small mb-2">Per Rp {{ number_format((int) $memberPointSpendAmount, 0, ',', '.') }} dapat 1 poin.</div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">Poin Didapat</div>
                        <span class="badge bg-label-primary">{{ (int) $sale->points_earned }} poin</span>
                    </div>
                    <div class="mt-3">
                        <div class="text-muted small">Saldo poin customer</div>
                        <div class="fw-semibold">{{ $sale->customer?->points_balance !== null ? number_format((int) $sale->customer->points_balance, 0, ',', '.') . ' poin' : '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

@if($sale->status !== 'void' && auth()->user()->hasPermission('transactions.view'))
    <div class="modal fade" id="voidTransactionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('transactions.void', $sale) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Void Transaksi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning mb-3">
                            Transaksi akan menjadi void dan stok item akan dikembalikan ke lokasi transaksi.
                        </div>
                        <label class="form-label">Alasan Void</label>
                        <textarea name="void_reason" class="form-control" rows="3" maxlength="500" placeholder="Opsional"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bx bx-block me-1"></i> Void Transaksi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@if($isTempo && $outstandingAmount > 0 && $sale->customer_id)
    <div class="modal fade" id="salePaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('transactions.payments.store', $sale) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Input Pelunasan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">Sisa tempo: <strong>{{ $outstanding }}</strong></div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Bayar</label>
                            <input type="datetime-local" name="payment_at" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nominal</label>
                            <input type="number" min="1" max="{{ (int) $outstandingAmount }}" step="1" name="amount" class="form-control" value="{{ (int) $outstandingAmount }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Metode</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="transfer">Transfer</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Referensi</label>
                            <input type="text" name="reference" class="form-control" placeholder="Opsional">
                        </div>
                        <div>
                            <label class="form-label">Catatan</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Opsional"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Simpan Pelunasan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@push('scripts')
    <script>
        (function() {
            const form = document.getElementById('serialNumbersForm');
            if (!form) return;

            function syncSerialInputs() {
                const mobile = window.matchMedia('(max-width: 767.98px)').matches;
                document.querySelectorAll('.serial-input-desktop').forEach(input => input.disabled = mobile);
                document.querySelectorAll('.serial-input-mobile').forEach(input => input.disabled = !mobile);
            }

            syncSerialInputs();
            window.addEventListener('resize', syncSerialInputs);
            form.addEventListener('submit', syncSerialInputs);
        })();
    </script>
@endpush
@endsection

@if(session('success') && $sale->status === 'paid')
    @push('scripts')
        <script>
            (function() {
                const modalEl = document.getElementById('printReceiptPromptModal');
                const btn = document.getElementById('confirmPrintReceiptBtn');
                const frame = document.getElementById('receiptPrintFrame');
                if (!modalEl || !btn || !frame || typeof bootstrap === 'undefined') return;

                const receiptUrl = @json(route('transactions.receipt', $sale) . '?embed=1');
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl, {backdrop: 'static'});

                // show modal automatically after save
                try { modal.show(); } catch (e) {}

                let loadedOnce = false;
                let isPrinting = false;

                function loadFrameOnce() {
                    if (loadedOnce) return;
                    loadedOnce = true;
                    frame.src = receiptUrl + '&ts=' + Date.now();
                }

                // Preload in background so print is instant.
                loadFrameOnce();

                btn.addEventListener('click', function() {
                    if (isPrinting) return;
                    isPrinting = true;
                    btn.disabled = true;

                    const doPrint = () => {
                        try {
                            frame.contentWindow?.focus();
                            frame.contentWindow?.print();
                        } catch (e) {
                            // fallback: open in new tab if browser blocks iframe print
                            window.open(receiptUrl.replace('embed=1', ''), '_blank');
                        } finally {
                            setTimeout(() => {
                                try { modal.hide(); } catch (e) {}
                                btn.disabled = false;
                                isPrinting = false;
                            }, 400);
                        }
                    };

                    // If not loaded yet, wait for load.
                    if (!frame.contentWindow || frame.contentDocument?.readyState !== 'complete') {
                        const onLoad = () => {
                            frame.removeEventListener('load', onLoad);
                            doPrint();
                        };
                        frame.addEventListener('load', onLoad);
                        loadFrameOnce();
                        return;
                    }

                    doPrint();
                });
            })();
        </script>
    @endpush
@endif
