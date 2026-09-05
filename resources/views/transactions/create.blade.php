@extends('layouts.sneat')

@section('title', 'Jual')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .pos-sale-wrap {
            display: flex;
            flex-direction: column;
            height: 100vh;
            min-height: 0;
            overflow: hidden;
            padding-top: .35rem !important;
            padding-bottom: calc(var(--pos-footer-height, 76px) + 6px);
        }

        #saleForm {
            display: flex;
            flex: 1 1 auto;
            min-height: 0;
            flex-direction: column;
        }

        .pos-mode #layout-menu,
        .pos-mode .layout-navbar {
            display: none !important;
        }

        .pos-mode .layout-page {
            padding-inline-start: 0 !important;
            -webkit-padding-start: 0 !important;
            padding-left: 0 !important;
            margin-left: 0 !important;
        }

        .pos-mode .container-xxl,
        .pos-mode .container-fluid {
            max-width: none !important;
        }

        .pos-header {
            background: #d7ecff;
            border: 2px solid #0b4f7a;
            border-radius: 0.5rem;
            height: 100%;
        }

        .pos-header .form-control,
        .pos-header .form-select {
            background: #eaf5ff;
            border-color: #0b4f7a;
            min-height: 28px;
            font-size: 12px;
        }

        .pos-header .form-label {
            font-size: 9px;
            letter-spacing: .02em;
            margin-bottom: .1rem;
            font-weight: 800;
            color: #0b3551;
        }

        .pos-header .form-control,
        .pos-header .form-select {
            padding: .12rem .45rem;
        }

        .pos-total-big {
            background: #d7ecff;
            border: 2px solid #0b4f7a;
            border-radius: 0.5rem;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: .35rem;
            position: relative;
        }

        .pos-total-big .value {
            font-size: clamp(28px, 3.7vw, 54px);
            font-weight: 800;
            color: #d10b0b;
            letter-spacing: .02em;
            line-height: 1;
        }

        .pos-points-badge {
            position: absolute;
            left: 10px;
            bottom: 10px;
            font-size: 11px;
            font-weight: 800;
            color: #0b3551;
            background: rgba(255, 255, 255, 0.55);
            border: 1px solid rgba(11, 79, 122, 0.35);
            padding: 4px 8px;
            border-radius: 999px;
            line-height: 1;
            white-space: nowrap;
        }

        .pos-items-card {
            border: 2px solid #0b4f7a;
            display: flex;
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
        }

        .pos-items-card .card-body,
        .pos-items-card .table-responsive {
            flex: 1 1 auto;
            min-height: 0;
        }

        .pos-items-card .table-responsive {
            overflow-y: auto;
            overflow-x: auto;
        }

        .pos-items-card .table {
            table-layout: fixed;
            width: 100%;
            margin-bottom: 0;
        }

        .pos-items-card .table th:nth-child(1),
        .pos-items-card .table td:nth-child(1) {
            width: 34%;
        }

        .pos-items-card .table th:nth-child(2),
        .pos-items-card .table td:nth-child(2) {
            width: 10%;
        }

        .pos-items-card .table th:nth-child(3),
        .pos-items-card .table td:nth-child(3) {
            width: 9%;
        }

        .pos-items-card .table th:nth-child(4),
        .pos-items-card .table td:nth-child(4),
        .pos-items-card .table th:nth-child(5),
        .pos-items-card .table td:nth-child(5) {
            width: 13%;
        }

        .pos-items-card .table th:nth-child(6),
        .pos-items-card .table td:nth-child(6) {
            width: 15%;
        }

        .pos-items-card .table th:nth-child(7),
        .pos-items-card .table td:nth-child(7) {
            width: 6%;
        }

        .pos-items-card .table thead th {
            background: #9ed1ff;
            text-transform: uppercase;
            font-weight: 800;
            color: #0b3551;
            letter-spacing: .03em;
            border-bottom: 2px solid #0b4f7a;
            font-size: 11px;
            padding: .36rem .45rem;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .pos-items-card .table td {
            font-size: 11px;
            padding: .28rem .45rem;
            line-height: 1.2;
            height: 38px;
            vertical-align: middle;
        }

        .pos-items-card .table input.form-control-sm {
            min-height: 26px;
            padding: .08rem .35rem;
            font-size: 11px;
        }

        .pos-items-card .btn-sm {
            --bs-btn-padding-y: .18rem;
            --bs-btn-padding-x: .42rem;
            --bs-btn-font-size: 11px;
        }

        .pos-scan-row {
            background: #ffef4d;
        }

        .pos-scan-input {
            background: transparent;
            border: none;
            outline: none;
            width: 100%;
            font-weight: 700;
            font-size: .82rem;
            letter-spacing: .02em;
            min-height: 26px;
        }

        .pos-scan-row td {
            height: 34px !important;
            padding-top: .25rem !important;
            padding-bottom: .25rem !important;
        }

        .pos-status-strip {
            height: 18px;
            display: flex;
            border-radius: 4px;
            overflow: hidden;
        }

        .pos-status-strip .left {
            background: #f08a00;
            color: #fff;
            font-size: 12px;
            padding: 0 10px;
            display: flex;
            align-items: center;
            white-space: nowrap;
        }

        .pos-status-strip .right {
            flex: 1;
            background: #0a8f3a;
            color: #fff;
            font-size: 12px;
            padding: 0 10px;
            display: flex;
            align-items: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #pendingQtyBadge {
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .02em;
        }

        .pos-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1030;
            background: #d7ecff;
            border-top: 2px solid #0b4f7a;
            padding: .22rem .65rem;
        }

        .pos-footer .shortcuts {
            font-size: 12px;
            color: #0b3551;
            font-weight: 700;
            letter-spacing: .02em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pos-footer .totals {
            font-size: 12px;
            color: #0b3551;
        }

        .pos-footer .totals table {
            margin-bottom: 0;
        }

        .pos-footer .totals td {
            padding: 2px 6px;
            line-height: 1.1;
        }

        .pos-footer .totals .label {
            opacity: .9;
        }

        .pos-footer .totals .value {
            font-weight: 900;
            text-align: right;
            min-width: 110px;
        }

        .pos-footer .totals input.form-control-sm {
            height: 28px;
            padding: .25rem .5rem;
            text-align: right;
        }

        .pos-footer-inner {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: center;
        }

        .pos-shortcuts {
            font-size: 10px;
            color: #0b3551;
            font-weight: 700;
            letter-spacing: .02em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pos-shortcuts-row {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .pos-print-last {
            flex: 0 0 auto;
            white-space: nowrap;
        }

        .pos-totals {
            display: grid;
            grid-template-columns: auto auto;
            column-gap: 12px;
            row-gap: 1px;
            align-items: center;
            min-width: 280px;
        }

        .pos-totals .k {
            font-size: 10px;
            color: #0b3551;
            opacity: .9;
            white-space: nowrap;
        }

        .pos-totals .v {
            font-size: 10px;
            font-weight: 900;
            color: #0b3551;
            text-align: right;
            white-space: nowrap;
        }

        .pos-totals .v input {
            width: 140px;
            text-align: right;
        }

        .pos-items-card input[readonly] {
            background: #eef3f8;
            cursor: not-allowed;
        }

        .pos-top-grid {
            flex: 0 0 auto;
            margin-bottom: .45rem !important;
        }

        .pos-top-grid .row {
            --bs-gutter-y: .3rem;
        }

        .mobile-pos {
            display: none;
        }

        @media (max-width: 991.98px) {
            .pos-sale-wrap {
                height: auto;
                min-height: 100vh;
                overflow: visible;
            }

            .pos-footer {
                padding: .6rem .75rem;
            }

            .pos-footer-inner {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .pos-shortcuts {
                white-space: normal;
            }

            .pos-shortcuts-row {
                align-items: flex-start;
                flex-direction: column;
            }

            .pos-totals {
                min-width: 0;
            }
        }

        @media (max-width: 767.98px) {
            .pos-sale-wrap {
                padding: .45rem .55rem .75rem !important;
            }

            #saleForm {
                display: block;
            }

            .pos-top-grid,
            .pos-items-card {
                display: none !important;
            }

            .mobile-pos {
                display: grid;
                gap: .55rem;
            }

            .mobile-scan-panel {
                background: #d7ecff;
                border: 2px solid #0b4f7a;
                border-radius: .5rem;
                padding: .55rem;
            }

            .mobile-scan-panel .form-label {
                font-size: 10px;
                font-weight: 900;
                color: #0b3551;
                margin-bottom: .2rem;
            }

            .mobile-scan-panel .form-control,
            .mobile-scan-panel .form-select {
                min-height: 36px;
                font-size: 13px;
                padding: .3rem .55rem;
            }

            .mobile-total-bar {
                padding: .45rem .55rem;
                background: #fff;
                border: 1px solid rgba(11, 79, 122, .28);
                border-radius: .45rem;
            }

            .mobile-total-main {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: .75rem;
            }

            .mobile-total-bar .label {
                font-size: 10px;
                font-weight: 900;
                color: #0b3551;
                text-transform: uppercase;
            }

            .mobile-total-bar .value {
                color: #d10b0b;
                font-size: 22px;
                font-weight: 900;
                line-height: 1;
            }

            .mobile-total-breakdown {
                display: grid;
                grid-template-columns: 1fr auto;
                gap: .15rem .75rem;
                margin-top: .45rem;
                padding-top: .4rem;
                border-top: 1px dashed rgba(11, 79, 122, .24);
                font-size: 10px;
                color: #0b3551;
            }

            .mobile-total-breakdown .v {
                font-weight: 900;
                text-align: right;
            }

            .mobile-product-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: .5rem;
                max-height: 38vh;
                overflow-y: auto;
                padding-right: .1rem;
            }

            .mobile-product-card {
                border: 1px solid rgba(67, 89, 113, .16);
                border-radius: .5rem;
                background: #fff;
                text-align: left;
                padding: 0;
                box-shadow: 0 8px 18px rgba(32, 36, 44, .08);
                min-width: 0;
            }

            .mobile-product-thumb {
                width: 100%;
                height: 70px;
                background: #eef3f8;
                object-fit: cover;
                display: block;
            }

            .mobile-product-thumb-placeholder {
                display: grid;
                gap: .15rem;
                place-items: center;
                color: #6b7280;
                font-weight: 900;
                font-size: 11px;
                text-align: center;
                line-height: 1.15;
                padding: .5rem;
            }

            .mobile-product-thumb-placeholder i {
                font-size: 24px;
            }

            .mobile-product-info {
                padding: .4rem .45rem .45rem;
                background: #fff;
            }

            .mobile-product-name {
                color: #263238;
                font-size: 12px;
                font-weight: 800;
                line-height: 1.2;
                min-height: 2.4em;
                max-height: 2.4em;
                overflow: hidden;
            }

            .mobile-product-meta {
                display: grid;
                gap: .15rem;
                margin-top: .35rem;
                font-size: 10px;
                line-height: 1.15;
            }

            .mobile-cart {
                background: #fff;
                border: 1px solid rgba(67, 89, 113, .16);
                border-radius: .5rem;
                overflow: hidden;
            }

            .mobile-cart-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: .45rem .55rem;
                background: #9ed1ff;
                color: #0b3551;
                font-size: 11px;
                font-weight: 900;
                text-transform: uppercase;
            }

            .mobile-cart-list {
                max-height: 26vh;
                overflow-y: auto;
            }

            .mobile-cart-empty,
            .mobile-cart-row {
                padding: .55rem;
                border-top: 1px solid #edf0f4;
            }

            .mobile-cart-row {
                display: grid;
                grid-template-columns: 1fr auto;
                gap: .5rem;
                align-items: center;
            }

            .mobile-cart-name {
                font-size: 12px;
                font-weight: 800;
                line-height: 1.2;
            }

            .mobile-cart-sub {
                font-size: 11px;
                color: #697a8d;
                margin-top: .15rem;
            }

            .mobile-qty {
                display: flex;
                align-items: center;
                gap: .25rem;
            }

            .mobile-qty .btn {
                position: relative;
                z-index: 3;
                width: 34px;
                height: 34px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0;
                touch-action: manipulation;
            }

            .mobile-qty-value {
                min-width: 24px;
                text-align: center;
                font-size: 12px;
                font-weight: 900;
            }

            .pos-footer-inner {
                grid-template-columns: 1fr;
                gap: .35rem;
            }

            .pos-footer {
                display: none !important;
            }

            #togglePosModeBtn {
                display: none !important;
            }

            .pos-totals {
                width: 100%;
                min-width: 0;
            }
        }

        .select2-container .select2-selection--single {
            height: 38px;
            border: 1px solid #d9dee3;
            border-radius: .375rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            padding-left: 0.75rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
            right: .35rem;
        }
    </style>
@endpush

@section('content')
    @php
        $saleChannel = $saleChannel ?? 'toko';
        $saleChannelLabel = $saleChannelLabel ?? 'Penjualan Toko';
        $saleIndexRoute = route('transactions.index.channel', $saleChannel);
    @endphp
    <div class="container-fluid flex-grow-1 py-3 pos-sale-wrap">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                @if (session('last_sale_code'))
                    <span class="ms-2 fw-semibold">{{ session('last_sale_code') }}</span>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (!$salesTableReady)
            <div class="alert alert-warning">
                Fitur transaksi belum siap karena tabel database belum tersedia.
            </div>
        @endif

        <form action="{{ route('transactions.store') }}" method="POST" id="saleForm" autocomplete="off">
            @csrf

            <input type="hidden" name="sale_code" value="{{ old('sale_code', $draftSaleCode) }}">
            <input type="hidden" name="sale_channel" id="saleChannel" value="{{ old('sale_channel', $saleChannel ?? 'toko') }}">
            <input type="hidden" name="sale_at" value="{{ old('sale_at', now()->format('Y-m-d H:i:s')) }}">
            <input type="hidden" name="paid_amount" id="paidAmount" value="{{ old('paid_amount', 0) }}">
            <input type="hidden" name="credit_term_days" id="creditTermDays" value="{{ old('credit_term_days') }}">

            <div class="d-flex justify-content-between align-items-center mb-2 flex-shrink-0">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ $saleIndexRoute }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bx bx-arrow-back"></i>
                    </a>
                    <div>
                        <div class="fw-semibold">{{ $saleChannelLabel ?? 'Penjualan Toko' }}</div>
                        <div class="text-muted small">Harga {{ ucfirst($saleChannel ?? 'toko') }}</div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-danger btn-sm" id="closeCashSessionBtn"
                        {{ $salesTableReady ? '' : 'disabled' }}>
                        <i class="bx bx-power-off me-1"></i> Tutup Kasir
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="togglePosModeBtn">
                        <i class="bx bx-fullscreen me-1"></i> Maximize
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm" id="submitSaleBtn"
                        {{ $salesTableReady ? '' : 'disabled' }}>
                        <i class="bx bx-save me-1"></i> Simpan (F8)
                    </button>
                </div>
            </div>

            <div class="row g-2 mb-2 pos-top-grid">
                <div class="col-lg-7">
                    <div class="p-2 pos-header">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label mb-1">REF NO</label>
                                <input type="text" class="form-control" value="{{ old('sale_code', $draftSaleCode) }}"
                                    readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-1">TANGGAL</label>
                                <input type="text" class="form-control" value="{{ now()->format('d/m/Y') }}" readonly>
                            </div>

                            <div class="col-md-7">
                                <label class="form-label mb-1">KODE | NAMA PELANGGAN</label>
                                <select class="form-select" name="customer_id" id="customerSelect"
                                    {{ $customersTableReady ? '' : 'disabled' }}>
                                    <option value="">-</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label mb-1">PEMBAYARAN</label>
                                <select class="form-select" name="payment_method" id="paymentMethod" required>
                                    <option value="cash">Cash</option>
                                    <option value="transfer">Transfer</option>
                                    <option value="qris">QRIS</option>
                                    <option value="tempo">Tempo</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label mb-1">KODE KAS</label>
                                <select class="form-select" name="location_id" id="locationId" required>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}"
                                            {{ (int) old('location_id', $defaultLocationId) === (int) $location->id ? 'selected' : '' }}>
                                            {{ $location->code ?? $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label mb-1">NO. REFERENSI (opsional)</label>
                                <input type="text" name="payment_reference" id="paymentReference" class="form-control"
                                    placeholder="No. referensi transfer/QRIS">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="pos-total-big">
                        <div class="value" id="grandTotalBig">0</div>
                        <div class="pos-points-badge">Poin: <span id="pointsBig">0</span></div>
                    </div>
                </div>
            </div>

            <div class="mobile-pos">
                <div class="mobile-scan-panel">
                    <div class="mobile-total-bar mb-2">
                        <div class="mobile-total-main">
                            <div>
                                <div class="label">Grand Total</div>
                                <div class="text-muted small">Tap produk untuk tambah item</div>
                            </div>
                            <div class="value" id="mobileGrandTotal">0</div>
                        </div>
                        <div class="mobile-total-breakdown">
                            <div>Subtotal</div>
                            <div class="v" id="mobileSubtotalLabel">Rp 0</div>
                            <div>Diskon</div>
                            <div class="v" id="mobileDiscountLabel">Rp 0</div>
                            <div>PPN</div>
                            <div class="v" id="mobileTaxLabel">Rp 0</div>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-7">
                            <label class="form-label">Scan Barcode</label>
                            <input type="text" id="mobileBarcodeInput" class="form-control"
                                placeholder="Scan / ketik barcode" autocomplete="off" inputmode="text">
                        </div>
                        <div class="col-5">
                            <label class="form-label">Kas</label>
                            <select class="form-select" id="mobileLocationMirror">
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}"
                                        {{ (int) old('location_id', $defaultLocationId) === (int) $location->id ? 'selected' : '' }}>
                                        {{ $location->code ?? $location->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-5">
                            <label class="form-label">Bayar</label>
                            <select class="form-select" id="mobilePaymentMirror">
                                <option value="cash">Cash</option>
                                <option value="transfer">Transfer</option>
                                <option value="qris">QRIS</option>
                                <option value="tempo">Tempo</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Pelanggan</label>
                            <select class="form-select" id="mobileCustomerMirror" {{ $customersTableReady ? '' : 'disabled' }}></select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Cari Produk</label>
                            <input type="search" id="mobileProductSearchInput" class="form-control"
                                placeholder="Nama barang, kode, atau barcode">
                        </div>
                    </div>
                </div>

                <div class="mobile-product-grid" id="mobileProductGrid">
                    <div class="text-muted small p-2">Memuat produk...</div>
                </div>

                <div class="mobile-cart">
                    <div class="mobile-cart-head">
                        <span>Keranjang</span>
                        <span id="mobileCartCount">0 item</span>
                    </div>
                    <div class="mobile-cart-list" id="mobileCartList">
                        <div class="mobile-cart-empty text-muted small">Belum ada item.</div>
                    </div>
                </div>
            </div>

            <div class="card pos-items-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0" id="saleItemsTable">
                            <thead>
                                <tr>
                                    <th>NAMA BARANG</th>
                                    <th>SATUAN</th>
                                    <th>QTY</th>
                                    <th>HARGA</th>
                                    <th>DSC.RP</th>
                                    <th>SUBTOTAL</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="saleItemsBody">
                                <tr class="pos-scan-row">
                                    <td colspan="7">
                                        <input type="text" id="barcodeInput" class="pos-scan-input"
                                            placeholder="Scan Barcode, atau ketik kode atau nama barang!"
                                            autocomplete="off" inputmode="none">
                                        <div class="mt-2 d-flex justify-content-end">
                                            <span id="pendingQtyBadge" class="badge bg-label-primary d-none">Qty: <span
                                                    id="pendingQtyLabel">1</span></span>
                                        </div>
                                    </td>
                                </tr>
                                <tr id="saleItemsEmptyRow">
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Belum ada item.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="saleItemsInputs"></div>

            <div class="pos-footer">
                <div class="pos-footer-inner">
                    <div class="pos-shortcuts-row">
                        <div class="pos-shortcuts">
                            CTRL+F: CARI BARANG | F3: MEMBER | CTRL+F7: ISI SN | F8: SIMPAN | F9: CETAK TERAKHIR | ESC: TUTUP MODAL
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary pos-print-last"
                            id="printLastReceiptBtn" {{ session('last_receipt_url') ? '' : 'disabled' }}>
                            <i class="bx bx-printer me-1"></i> Cetak Terakhir
                        </button>
                    </div>
                    <div class="pos-totals">
                        <div class="k">SUBTOTAL</div>
                        <div class="v" id="subtotalLabel">Rp 0</div>
                        <div class="k">DSC.</div>
                        <div class="v" id="discountLabel">Rp 0</div>
                        <div class="k">PPN</div>
                        <div class="v" id="taxLabel">Rp 0</div>
                        <div class="k">GRAND TOTAL</div>
                        <div class="v" id="grandTotalLabel">Rp 0</div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="paymentModalLabel">Pembayaran</h5>
                        <div class="text-muted small">Masukkan bayar dan cek kembali sebelum simpan.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="text-muted">Grand Total</div>
                        <div class="fw-bold fs-5" id="paymentGrandLabel">Rp 0</div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="text-muted">Poin Member</div>
                        <div class="fw-semibold"><span id="paymentPointsLabel">0</span> poin</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bayar <span class="text-danger">*</span></label>
                        <input type="number" min="0" step="1" class="form-control" id="paymentPaidInput"
                            value="0">
                        <div class="form-text" id="paymentPaidHint"></div>
                        <div class="text-danger small mt-2 d-none" id="paymentErrorText"></div>
                    </div>

                    <div class="mb-3 d-none" id="tempoTermWrap">
                        <label class="form-label">Lama Tempo <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" min="1" max="3650" step="1" class="form-control" id="tempoTermInput"
                                value="30">
                            <span class="input-group-text">hari</span>
                        </div>
                        <div class="form-text">Tempo hanya bisa untuk customer terdaftar.</div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted" id="paymentBalanceLabel">Kembali</div>
                        <div class="fw-bold" id="paymentChangeLabel">Rp 0</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="confirmPaymentBtn">
                        <i class="bx bx-save me-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="serialModal" tabindex="-1" aria-labelledby="serialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="serialModalLabel">Serial Number</h5>
                        <div class="text-muted small" id="serialModalSubtitle"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Masukkan serial number (1 baris 1 serial)</label>
                    <textarea class="form-control" id="serialTextarea" rows="6" placeholder="SN-001&#10;SN-002"></textarea>
                    <div class="form-text">Opsional. Bisa dikosongkan dan diisi dari detail transaksi setelah transaksi selesai.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" id="serialSaveBtn">
                        <i class="bx bx-save me-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="productSearchModal" tabindex="-1" aria-labelledby="productSearchModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="productSearchModalLabel">Silahkan Pilih Barang</h5>
                        <div class="text-muted small">Tekan ENTER untuk lanjut, ESC untuk batal</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 align-items-end mb-2">
                        <div class="col-md-8">
                            <label class="form-label mb-1">Cari</label>
                            <input type="text" class="form-control" id="productSearchInput"
                                placeholder="Ketik nama / kode / barcode...">
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-primary w-100" id="productSearchBtn">
                                <i class="bx bx-search me-1"></i> Cari
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped" id="productSearchTable">
                            <thead>
                                <tr>
                                    <th>Nama Barang</th>
                                    <th>Kode</th>
                                    <th>Supplier</th>
                                    <th class="text-end">Stok</th>
                                    <th class="text-end">Harga</th>
                                </tr>
                            </thead>
                            <tbody id="productSearchBody">
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">Ketik kata kunci lalu cari.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cashOpeningModal" tabindex="-1" aria-labelledby="cashOpeningModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="cashOpeningModalLabel">Kas Awal</h5>
                        <div class="text-muted small">Wajib diisi saat buka kasir (per hari).</div>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kas Awal (Rp)</label>
                        <input type="number" min="0" step="1" class="form-control" id="openingCashInput"
                            value="0">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Catatan (opsional)</label>
                        <input type="text" class="form-control" id="openingCashNotes"
                            placeholder="Catatan kas awal...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="saveOpeningCashBtn">
                        <i class="bx bx-save me-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cashClosingModal" tabindex="-1" aria-labelledby="cashClosingModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="cashClosingModalLabel">Tutup Kasir (Rekonsiliasi)</h5>
                        <div class="text-muted small">Cek kesesuaian fisik uang laci dengan laporan sistem.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="closeSessionLoading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <div class="mt-2 text-muted">Memuat ringkasan transaksi sesi...</div>
                    </div>
                    
                    <div id="closeSessionContent" class="d-none">
                        <div class="row">
                            <div class="col-md-6 border-end">
                                <h6 class="fw-semibold text-primary mb-3">Ringkasan Sistem</h6>
                                <table class="table table-sm table-borderless w-100">
                                    <tr>
                                        <td>Kas Awal:</td>
                                        <td class="text-end fw-semibold" id="sysOpeningCash">Rp 0</td>
                                    </tr>
                                    <tr>
                                        <td>Kas Penjualan POS:</td>
                                        <td class="text-end fw-semibold text-success" id="sysCashSales">Rp 0</td>
                                    </tr>
                                    <tr>
                                        <td>Kas Servis:</td>
                                        <td class="text-end fw-semibold text-success" id="sysCashServices">Rp 0</td>
                                    </tr>
                                    <tr>
                                        <td>Kas Pelunasan Tempo:</td>
                                        <td class="text-end fw-semibold text-success" id="sysCreditPayments">Rp 0</td>
                                    </tr>
                                    <tr class="border-top pt-2">
                                        <td class="fw-bold pt-2">Ekspektasi Uang Fisik:</td>
                                        <td class="text-end fw-bold text-primary pt-2" id="sysExpectedClosing">Rp 0</td>
                                    </tr>
                                </table>
                                
                                <div class="mt-3 small p-2 bg-light rounded text-muted">
                                    <div class="fw-bold mb-1">Non-Tunai (Informasi):</div>
                                    <div class="d-flex justify-content-between">
                                        <span>Transfer:</span>
                                        <span id="sysNonCashTransfer">Rp 0</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>QRIS:</span>
                                        <span id="sysNonCashQris">Rp 0</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 ps-md-4 mt-3 mt-md-0">
                                <h6 class="fw-semibold text-primary mb-3">Hitung Fisik Uang</h6>
                                <div class="mb-3">
                                    <label class="form-label">Total Uang Fisik di Laci (Rp)</label>
                                    <input type="number" min="0" step="1" class="form-control form-control-lg fw-bold" id="closingCashInput" value="0">
                                </div>
                                <div class="p-2 mb-3 rounded" id="discrepancyBox" style="background-color: #f8f9fa;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small fw-semibold text-muted">Selisih:</span>
                                        <span class="fw-bold fs-5" id="closingDiscrepancy">Rp 0</span>
                                    </div>
                                    <div class="small mt-1 text-muted" id="discrepancyNote">Pas (Tidak ada selisih)</div>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">Catatan Rekonsiliasi (Selisih/Kerusakan)</label>
                                    <textarea class="form-control" id="closingCashNotes" rows="2" placeholder="Catatan jika ada selisih..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="saveClosingCashBtn" disabled>
                        <i class="bx bx-power-off me-1"></i> Tutup Kasir & Setor
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        (function() {
            const lookupUrl = @json(route('transactions.lookup.product'));
            const searchProductsUrl = @json(route('transactions.search.products'));
            const cashStatusUrl = @json(route('transactions.cash.status'));
            const cashOpenUrl = @json(route('transactions.cash.open'));
            const lastReceiptUrl = @json(session('last_receipt_url'));
            const saleChannel = @json($saleChannel ?? 'toko');
            const memberPointSpend = {{ (int) $memberPointSpendAmount }};
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const saleForm = document.getElementById('saleForm');
            const barcodeInput = document.getElementById('barcodeInput');
            const mobileBarcodeInput = document.getElementById('mobileBarcodeInput');
            const mobileProductSearchInput = document.getElementById('mobileProductSearchInput');
            const mobileProductGrid = document.getElementById('mobileProductGrid');
            const mobileCartList = document.getElementById('mobileCartList');
            const mobileCartCount = document.getElementById('mobileCartCount');
            const mobileGrandTotal = document.getElementById('mobileGrandTotal');
            const mobileSubtotalLabel = document.getElementById('mobileSubtotalLabel');
            const mobileDiscountLabel = document.getElementById('mobileDiscountLabel');
            const mobileTaxLabel = document.getElementById('mobileTaxLabel');
            const mobileLocationMirror = document.getElementById('mobileLocationMirror');
            const mobilePaymentMirror = document.getElementById('mobilePaymentMirror');
            const mobileCustomerMirror = document.getElementById('mobileCustomerMirror');
            const locationIdEl = document.getElementById('locationId');
            const paidAmountHiddenEl = document.getElementById('paidAmount');
            const creditTermDaysEl = document.getElementById('creditTermDays');
            const itemsBody = document.getElementById('saleItemsBody');
            const emptyRow = document.getElementById('saleItemsEmptyRow');
            const inputsRoot = document.getElementById('saleItemsInputs');
            const paymentMethodEl = document.getElementById('paymentMethod');
            const grandTotalBig = document.getElementById('grandTotalBig');
            const pointsBig = document.getElementById('pointsBig');
            const pendingQtyBadge = document.getElementById('pendingQtyBadge');
            const pendingQtyLabel = document.getElementById('pendingQtyLabel');
            const printLastReceiptBtn = document.getElementById('printLastReceiptBtn');

            const subtotalLabel = document.getElementById('subtotalLabel');
            const discountLabel = document.getElementById('discountLabel');
            const taxLabel = document.getElementById('taxLabel');
            const grandTotalLabel = document.getElementById('grandTotalLabel');

            const paymentModalEl = document.getElementById('paymentModal');
            const paymentGrandLabel = document.getElementById('paymentGrandLabel');
            const paymentPointsLabel = document.getElementById('paymentPointsLabel');
            const paymentPaidInput = document.getElementById('paymentPaidInput');
            const paymentPaidHint = document.getElementById('paymentPaidHint');
            const paymentChangeLabel = document.getElementById('paymentChangeLabel');
            const paymentBalanceLabel = document.getElementById('paymentBalanceLabel');
            const paymentErrorText = document.getElementById('paymentErrorText');
            const confirmPaymentBtn = document.getElementById('confirmPaymentBtn');
            const tempoTermWrap = document.getElementById('tempoTermWrap');
            const tempoTermInput = document.getElementById('tempoTermInput');

            let items = [];
            let serialEditingIndex = null;
            let selectedIndex = null;
            let allowSubmit = false;
            let currentTotals = {
                subtotal: 0,
                discount: 0,
                tax: 0,
                grand: 0,
                points: 0
            };
            let pendingQty = null; // integer qty to apply on next item add
            let pendingDigitBuffer = ''; // for qty typing (max 3 digits)

            function setPendingQty(next) {
                const n = next === null ? null : Math.max(1, Math.min(999, parseInt(String(next), 10) || 1));
                pendingQty = n;
                pendingDigitBuffer = n === null ? '' : String(n);
                if (pendingQtyBadge && pendingQtyLabel) {
                    pendingQtyLabel.textContent = pendingQty ? String(pendingQty) : '1';
                    pendingQtyBadge.classList.toggle('d-none', !pendingQty);
                }
            }

            function rupiah(value) {
                const n = Math.max(0, Math.round(Number(value || 0)));
                return 'Rp ' + n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function rupiahPlain(value) {
                const n = Math.max(0, Math.round(Number(value || 0)));
                return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function toNumber(value) {
                const n = Number(value);
                return Number.isFinite(n) ? n : 0;
            }

            function printLastReceipt() {
                if (!lastReceiptUrl) return;

                const win = window.open(lastReceiptUrl, '_blank', 'noopener');
                if (!win) {
                    window.location.href = lastReceiptUrl;
                }
            }

            printLastReceiptBtn?.addEventListener('click', () => {
                printLastReceipt();
                focusScan();
            });

            function recalc() {
                let subtotal = 0;
                let discount = 0;
                let grand = 0;

                items.forEach(row => {
                    const qty = toNumber(row.quantity);
                    const price = toNumber(row.unit_price);
                    const disc = toNumber(row.discount_value);
                    const lineSubtotal = Math.max(0, (qty * price) - disc);
                    row.subtotal = lineSubtotal;
                    subtotal += (qty * price);
                    discount += disc;
                    grand += lineSubtotal;
                });

                const tax = 0; // Placeholder for PPN, can be made configurable later.
                grand = Math.max(0, grand + tax);

                subtotalLabel.textContent = rupiah(subtotal);
                discountLabel.textContent = rupiah(discount);
                taxLabel.textContent = rupiah(tax);
                grandTotalLabel.textContent = rupiah(grand);
                grandTotalBig.textContent = rupiahPlain(grand);
                if (mobileGrandTotal) mobileGrandTotal.textContent = rupiahPlain(grand);
                if (mobileSubtotalLabel) mobileSubtotalLabel.textContent = rupiah(subtotal);
                if (mobileDiscountLabel) mobileDiscountLabel.textContent = rupiah(discount);
                if (mobileTaxLabel) mobileTaxLabel.textContent = rupiah(tax);

                const pointsBadgeWrap = document.querySelector('.pos-points-badge');
                const customerSelect = document.getElementById('customerSelect');
                const selectedText = customerSelect?.selectedOptions?.[0]?.textContent || '';
                const isMemberSelected = /member/i.test(selectedText);

                const points = (isMemberSelected && memberPointSpend > 0) ? Math.floor(grand / memberPointSpend) : 0;
                if (pointsBig) pointsBig.textContent = points.toString();
                if (pointsBadgeWrap) pointsBadgeWrap.classList.toggle('d-none', !isMemberSelected);

                currentTotals = {
                    subtotal,
                    discount,
                    tax,
                    grand,
                    points
                };

                renderMobileCart();
            }

            function render() {
                inputsRoot.innerHTML = '';

                if (items.length === 0) {
                    emptyRow.classList.remove('d-none');
                } else {
                    emptyRow.classList.add('d-none');
                }

                itemsBody.querySelectorAll('tr[data-index]').forEach(el => el.remove());

                items.forEach((row, idx) => {
                    const tr = document.createElement('tr');
                    tr.dataset.index = String(idx);
                    tr.style.cursor = 'pointer';
                    tr.addEventListener('click', () => {
                        selectedIndex = idx;
                        itemsBody.querySelectorAll('tr[data-index]').forEach(r => r.classList.remove(
                            'table-active'));
                        tr.classList.add('table-active');
                        focusScan();
                    });

                    const productCell = document.createElement('td');
                    productCell.innerHTML = `<div class="fw-semibold">${escapeHtml(row.product_name)}</div>
                        <div class="text-muted small">${escapeHtml(row.product_code || '-')}</div>`;

                    const unitCell = document.createElement('td');
                    unitCell.innerHTML =
                        `<span class="fw-semibold">${escapeHtml(row.unit_name || 'PCS')}</span>` +
                        (row.is_open_price ? ` <span class="badge bg-label-warning">Open</span>` : ``);

                    const qtyCell = document.createElement('td');
                    qtyCell.innerHTML =
                        `<input type="number" min="1" step="1" class="form-control form-control-sm" value="${row.quantity}">`;
                    qtyCell.querySelector('input').addEventListener('input', (e) => {
                        row.quantity = toNumber(e.target.value);
                        recalc();
                        updateLineSubtotal(tr, row.subtotal);
                        hydrateInputs();
                    });

                    const priceCell = document.createElement('td');
                    if (row.is_open_price) {
                        priceCell.innerHTML =
                            `<input type="number" min="0" step="1" class="form-control form-control-sm" value="${row.unit_price}">`;
                        priceCell.querySelector('input').addEventListener('input', (e) => {
                            row.unit_price = toNumber(e.target.value);
                            recalc();
                            updateLineSubtotal(tr, row.subtotal);
                            hydrateInputs();
                        });
                    } else {
                        priceCell.innerHTML =
                            `<input type="number" min="0" step="1" class="form-control form-control-sm" value="${row.unit_price}" readonly>`;
                    }

                    const discCell = document.createElement('td');
                    discCell.innerHTML =
                        `<input type="number" min="0" step="1" class="form-control form-control-sm" value="${row.discount_value || 0}">`;
                    discCell.querySelector('input').addEventListener('input', (e) => {
                        row.discount_value = toNumber(e.target.value);
                        recalc();
                        updateLineSubtotal(tr, row.subtotal);
                        hydrateInputs();
                    });

                    const subtotalCell = document.createElement('td');
                    subtotalCell.className = 'fw-semibold';
                    subtotalCell.textContent = rupiah(row.subtotal);

                    const actionCell = document.createElement('td');
                    actionCell.className = 'text-end';

                    const btnGroup = document.createElement('div');
                    btnGroup.className = 'd-flex gap-1 justify-content-end';

                    if (row.has_serial_number) {
                        const snBtn = document.createElement('button');
                        snBtn.type = 'button';
                        snBtn.className = 'btn btn-sm btn-outline-warning';
                        snBtn.innerHTML = '<i class="bx bx-barcode-reader"></i>';
                        snBtn.title = 'Isi Serial Number';
                        snBtn.addEventListener('click', () => openSerialModal(idx));
                        btnGroup.appendChild(snBtn);
                    }

                    const delBtn = document.createElement('button');
                    delBtn.type = 'button';
                    delBtn.className = 'btn btn-sm btn-outline-danger';
                    delBtn.innerHTML = '<i class="bx bx-trash"></i>';
                    delBtn.addEventListener('click', () => {
                        items.splice(idx, 1);
                        render();
                        recalc();
                        hydrateInputs();
                        focusScan();
                    });

                    btnGroup.appendChild(delBtn);
                    actionCell.appendChild(btnGroup);

                    tr.appendChild(productCell);
                    tr.appendChild(unitCell);
                    tr.appendChild(qtyCell);
                    tr.appendChild(priceCell);
                    tr.appendChild(discCell);
                    tr.appendChild(subtotalCell);
                    tr.appendChild(actionCell);

                    itemsBody.appendChild(tr);
                });

                hydrateInputs();
                renderMobileCart();
            }

            function renderMobileCart() {
                if (!mobileCartList || !mobileCartCount) return;

                const totalQty = items.reduce((total, row) => total + toNumber(row.quantity), 0);
                mobileCartCount.textContent = `${totalQty || 0} item`;

                if (items.length === 0) {
                    mobileCartList.innerHTML = '<div class="mobile-cart-empty text-muted small">Belum ada item.</div>';
                    return;
                }

                mobileCartList.innerHTML = '';
                items.forEach((row, idx) => {
                    const itemEl = document.createElement('div');
                    itemEl.className = 'mobile-cart-row';
                    itemEl.innerHTML = `
                        <div>
                            <div class="mobile-cart-name">${escapeHtml(row.product_name)}</div>
                            <div class="mobile-cart-sub">${escapeHtml(row.unit_name || 'PCS')} | ${rupiah(row.subtotal || 0)}</div>
                        </div>
                        <div class="mobile-qty">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-action="minus" aria-label="Kurangi">-</button>
                            <span class="mobile-qty-value">${escapeHtml(row.quantity)}</span>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-action="plus" aria-label="Tambah">+</button>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-action="remove" aria-label="Hapus"><i class="bx bx-trash"></i></button>
                        </div>
                    `;

                    itemEl.querySelector('[data-action="minus"]').addEventListener('pointerup', (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        const nextQty = toNumber(row.quantity) - 1;
                        if (nextQty <= 0) {
                            items.splice(idx, 1);
                        } else {
                            row.quantity = nextQty;
                        }
                        render();
                        recalc();
                        hydrateInputs();
                    });
                    itemEl.querySelector('[data-action="plus"]').addEventListener('pointerup', (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        row.quantity = toNumber(row.quantity) + 1;
                        render();
                        recalc();
                        hydrateInputs();
                    });
                    itemEl.querySelector('[data-action="remove"]').addEventListener('pointerup', (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        items.splice(idx, 1);
                        render();
                        recalc();
                        hydrateInputs();
                    });

                    mobileCartList.appendChild(itemEl);
                });
            }

            function updateLineSubtotal(tr, value) {
                const subtotalCell = tr.querySelector('td:nth-child(6)');
                if (subtotalCell) subtotalCell.textContent = rupiah(value);
            }

            function hydrateInputs() {
                inputsRoot.innerHTML = '';

                items.forEach((row, idx) => {
                    const fields = [
                        ['product_id', row.product_id],
                        ['barcode', row.barcode || ''],
                        ['unit_level', row.unit_level || 1],
                        ['unit_name', row.unit_name || ''],
                        ['conversion_qty', row.conversion_qty || 1],
                        ['quantity', row.quantity || 1],
                        ['unit_price', row.unit_price || 0],
                        ['discount_value', row.discount_value || 0],
                    ];

                    fields.forEach(([key, value]) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `items[${idx}][${key}]`;
                        input.value = String(value);
                        inputsRoot.appendChild(input);
                    });

                    if (Array.isArray(row.serial_numbers)) {
                        row.serial_numbers.forEach((sn, snIdx) => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = `items[${idx}][serial_numbers][${snIdx}]`;
                            input.value = String(sn || '');
                            inputsRoot.appendChild(input);
                        });
                    }
                });
            }

            function focusScan() {
                setTimeout(() => {
                    if (window.matchMedia('(max-width: 767.98px)').matches && mobileBarcodeInput) {
                        mobileBarcodeInput.focus();
                        return;
                    }
                    barcodeInput.focus();
                }, 50);
            }

            async function updateCartPricesForCustomer(customerId) {
                if (items.length === 0) return;

                const locationId = locationIdEl.value;

                for (let i = 0; i < items.length; i++) {
                    const item = items[i];
                    if (item.is_open_price) continue;

                    try {
                        const url = new URL(lookupUrl, window.location.origin);
                        url.searchParams.set('q', item.barcode || item.product_code);
                        url.searchParams.set('location_id', locationId);
                        url.searchParams.set('sale_channel', saleChannel);
                        if (customerId) {
                            url.searchParams.set('customer_id', customerId);
                        }

                        const res = await fetch(url.toString(), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrf
                            }
                        });

                        if (res.ok) {
                            const data = await res.json();
                            if (data?.found && data.product) {
                                if (parseInt(data.product.unit_level) === parseInt(item.unit_level)) {
                                    item.unit_price = data.product.unit_price;
                                } else {
                                    item.unit_price = data.product.unit_price * (item.conversion_qty || 1);
                                }
                            }
                        }
                    } catch (e) {
                        console.error('Gagal memperbarui harga item:', e);
                    }
                }

                render();
                recalc();
            }

            async function lookupAndAdd(code) {
                const locationId = locationIdEl.value;
                const customerId = document.getElementById('customerSelect')?.value || '';

                const url = new URL(lookupUrl, window.location.origin);
                url.searchParams.set('q', code);
                url.searchParams.set('location_id', locationId);
                url.searchParams.set('sale_channel', saleChannel);
                if (customerId) {
                    url.searchParams.set('customer_id', customerId);
                }

                const res = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf
                    }
                });

                if (!res.ok) {
                    return null;
                }

                const data = await res.json();
                if (!data?.found) return null;
                return data.product;
            }

            function addProduct(product) {
                const qtyAdd = pendingQty ? pendingQty : 1;
                const key = `${product.id}:${product.unit_level || 1}:${product.barcode || ''}`;
                const existing = items.find(item =>
                    `${item.product_id}:${item.unit_level || 1}:${item.barcode || ''}` === key);

                if (existing) {
                    existing.quantity = toNumber(existing.quantity) + qtyAdd;
                } else {
                    items.push({
                        product_id: product.id,
                        product_code: product.product_code || '',
                        product_name: product.name,
                        barcode: product.barcode || '',
                        unit_level: product.unit_level || 1,
                        unit_name: product.unit_name || '',
                        conversion_qty: product.conversion_qty || 1,
                        unit_price: product.unit_price || 0,
                        discount_value: 0,
                        subtotal: 0,
                        has_serial_number: !!product.has_serial_number,
                        is_open_price: !!product.is_open_price,
                        serial_numbers: [],
                        quantity: qtyAdd
                    });
                }

                setPendingQty(null);

                render();
                recalc();
                focusScan();
            }

            function openSerialModal(index) {
                serialEditingIndex = index;
                const item = items[index];
                if (!item) return;

                document.getElementById('serialModalSubtitle').textContent = item.product_name;
                const textarea = document.getElementById('serialTextarea');
                textarea.value = (item.serial_numbers || []).join('\n');

                const modal = new bootstrap.Modal(document.getElementById('serialModal'));
                modal.show();
                setTimeout(() => textarea.focus(), 150);
            }

            document.getElementById('serialSaveBtn').addEventListener('click', () => {
                if (serialEditingIndex === null) return;
                const item = items[serialEditingIndex];
                if (!item) return;

                const lines = document.getElementById('serialTextarea').value
                    .split('\n')
                    .map(s => s.trim())
                    .filter(Boolean);

                item.serial_numbers = lines;
                hydrateInputs();

                const modalEl = document.getElementById('serialModal');
                bootstrap.Modal.getInstance(modalEl)?.hide();
                focusScan();
            });

            barcodeInput.addEventListener('keydown', async (e) => {
                // Qty typing mode: type digits while input empty, applied to next item added.
                // Heuristic to avoid breaking barcode scans: we only buffer max 3 digits.
                // If user types 4th digit while still empty, we treat it as barcode input instead.
                if (barcodeInput.value === '') {
                    const isDigit = e.key.length === 1 && e.key >= '0' && e.key <= '9';
                    if (isDigit) {
                        // If pending qty already set, allow barcode digits to be typed/scanned normally.
                        if (pendingQty !== null) {
                            // allow default
                        } else {
                            const nextBuffer = pendingDigitBuffer + e.key;
                            if (nextBuffer.length <= 3) {
                                e.preventDefault();
                                pendingDigitBuffer = nextBuffer;
                                setPendingQty(parseInt(pendingDigitBuffer, 10));
                                return;
                            }

                            // 4th digit: switch to barcode entry, not qty.
                            // Put the buffered digits into the input so barcode typing/scanning continues naturally.
                            e.preventDefault();
                            barcodeInput.value = nextBuffer;
                            pendingDigitBuffer = '';
                            pendingQty = null;
                            if (pendingQtyBadge) pendingQtyBadge.classList.add('d-none');
                            return;
                        }
                    }

                    // Backspace clears pending qty when input is empty.
                    if (e.key === 'Backspace' && pendingQty !== null) {
                        e.preventDefault();
                        setPendingQty(null);
                        return;
                    }
                }

                if (e.key !== 'Enter') return;
                e.preventDefault();

                const code = barcodeInput.value.trim();
                if (!code) return;

                barcodeInput.value = '';

                try {
                    const product = await lookupAndAdd(code);
                    if (!product) {
                        openProductSearch(code);
                        focusScan();
                        return;
                    }
                    addProduct(product);
                } catch (err) {
                    alert('Gagal lookup produk.');
                    focusScan();
                }
            });

            paymentMethodEl.addEventListener('change', () => {
                if (mobilePaymentMirror && mobilePaymentMirror.value !== paymentMethodEl.value) {
                    mobilePaymentMirror.value = paymentMethodEl.value;
                }
                recalc();
                focusScan();
            });

            mobilePaymentMirror?.addEventListener('change', () => {
                paymentMethodEl.value = mobilePaymentMirror.value;
                paymentMethodEl.dispatchEvent(new Event('change'));
            });

            function openPaymentModal() {
                if (!paymentModalEl || !paymentPaidInput || !confirmPaymentBtn) {
                    // Fallback: submit directly using grand total as paid (best effort).
                    paidAmountHiddenEl.value = String(Math.round(currentTotals.grand || 0));
                    allowSubmit = true;
                    saleForm.submit();
                    return;
                }

                const method = paymentMethodEl.value || 'cash';
                const grand = Math.round(Number(currentTotals.grand || 0));
                const points = Number(currentTotals.points || 0);
                const isTempo = method === 'tempo';

                if (isTempo && !String(document.getElementById('customerSelect')?.value || '').trim()) {
                    alert('Pembayaran tempo hanya bisa untuk customer terdaftar.');
                    if (typeof $ !== 'undefined') {
                        $('#customerSelect').select2('open');
                    }
                    return;
                }

                paymentGrandLabel.textContent = rupiah(grand);
                paymentPointsLabel.textContent = String(points);
                tempoTermWrap?.classList.toggle('d-none', !isTempo);
                paymentBalanceLabel.textContent = isTempo ? 'Sisa Tempo' : 'Kembali';

                paymentErrorText.classList.add('d-none');
                paymentErrorText.textContent = '';

                if (isTempo) {
                    paymentPaidInput.readOnly = false;
                    paymentPaidInput.value = '0';
                    paymentPaidHint.textContent = 'Tempo: isi bayar awal jika ada, boleh 0.';
                    if (tempoTermInput && !tempoTermInput.value) tempoTermInput.value = '30';
                } else if (method !== 'cash') {
                    paymentPaidInput.value = String(grand);
                    paymentPaidInput.readOnly = true;
                    paymentPaidHint.textContent = 'Non-cash: bayar otomatis sama dengan grand total.';
                } else {
                    paymentPaidInput.readOnly = false;
                    paymentPaidInput.value = String(grand);
                    paymentPaidHint.textContent = 'Cash: isi jumlah yang dibayar customer.';
                }

                syncPaymentPreview();

                const modal = bootstrap.Modal.getOrCreateInstance(paymentModalEl);
                modal.show();
                setTimeout(() => {
                    if (!paymentPaidInput.readOnly) paymentPaidInput.focus();
                }, 150);
            }

            function syncPaymentPreview() {
                const grand = Math.round(Number(currentTotals.grand || 0));
                const paid = Math.round(Number(paymentPaidInput?.value || 0));
                const method = paymentMethodEl.value || 'cash';
                const isTempo = method === 'tempo';
                const balance = isTempo ? Math.max(0, grand - paid) : Math.max(0, paid - grand);
                paymentChangeLabel.textContent = rupiah(balance);

                if (isTempo) {
                    const term = parseInt(String(tempoTermInput?.value || '0'), 10);
                    if (!term || term < 1) {
                        paymentErrorText.textContent = 'Lama tempo wajib diisi.';
                        paymentErrorText.classList.remove('d-none');
                        return false;
                    }
                    if (paid > grand) {
                        paymentErrorText.textContent = 'Bayar awal tempo tidak boleh lebih besar dari grand total.';
                        paymentErrorText.classList.remove('d-none');
                        return false;
                    }
                } else if (paid < grand) {
                    paymentErrorText.textContent = 'Jumlah bayar kurang dari grand total.';
                    paymentErrorText.classList.remove('d-none');
                    return false;
                }

                paymentErrorText.classList.add('d-none');
                paymentErrorText.textContent = '';
                return true;
            }

            paymentPaidInput?.addEventListener('input', () => {
                syncPaymentPreview();
            });

            // Intercept submit to ask for payment (bayar/kembali) first.
            saleForm?.addEventListener('submit', (e) => {
                if (allowSubmit) return;
                e.preventDefault();

                if (items.length === 0) {
                    alert('Belum ada item.');
                    focusScan();
                    return;
                }

                recalc();
                openPaymentModal();
            });

            confirmPaymentBtn?.addEventListener('click', () => {
                if (!syncPaymentPreview()) {
                    paymentPaidInput?.focus();
                    return;
                }

                const grand = Math.round(Number(currentTotals.grand || 0));
                const paid = Math.round(Number(paymentPaidInput?.value || 0));
                const method = paymentMethodEl.value || 'cash';

                if (method === 'tempo') {
                    paidAmountHiddenEl.value = String(paid);
                    creditTermDaysEl.value = String(parseInt(String(tempoTermInput?.value || '30'), 10) || 30);
                } else if (method !== 'cash') {
                    paidAmountHiddenEl.value = String(grand);
                    creditTermDaysEl.value = '';
                } else {
                    paidAmountHiddenEl.value = String(paid);
                    creditTermDaysEl.value = '';
                }

                allowSubmit = true;
                saleForm.submit();
            });

            // Init select2 customer search
            const customersUrl = @json(route('transactions.search.customers'));
            if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                $('#customerSelect').select2({
                    placeholder: '-',
                    allowClear: true,
                    width: '100%',
                    ajax: {
                        url: customersUrl,
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term
                            };
                        },
                        processResults: function(data) {
                            return data;
                        },
                        cache: true
                    }
                }).on('change', function() {
                    const cid = $(this).val() || '';
                    updateCartPricesForCustomer(cid);
                    recalc();
                    focusScan();
                });

                if (mobileCustomerMirror) {
                    $('#mobileCustomerMirror').select2({
                        placeholder: 'Pilih customer tempo',
                        allowClear: true,
                        width: '100%',
                        ajax: {
                            url: customersUrl,
                            dataType: 'json',
                            delay: 250,
                            data: function(params) {
                                return {
                                    q: params.term
                                };
                            },
                            processResults: function(data) {
                                return data;
                            },
                            cache: true
                        }
                    }).on('select2:select', function(e) {
                        const data = e.params.data;
                        const option = new Option(data.text, data.id, true, true);
                        $('#customerSelect').append(option).trigger('change');
                    }).on('select2:clear', function() {
                        $('#customerSelect').val(null).trigger('change');
                    });
                }
            }

            function escapeHtml(str) {
                return String(str || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/\"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            // Product search modal
            const productSearchModalEl = document.getElementById('productSearchModal');
            const productSearchInput = document.getElementById('productSearchInput');
            const productSearchBtn = document.getElementById('productSearchBtn');
            const productSearchBody = document.getElementById('productSearchBody');
            let productSearchResults = [];

            function openProductSearch(initialQuery = '') {
                const modal = bootstrap.Modal.getOrCreateInstance(productSearchModalEl);
                modal.show();
                productSearchInput.value = initialQuery || '';
                setTimeout(() => productSearchInput.focus(), 120);
                if (initialQuery) runProductSearch();
            }

            async function runProductSearch() {
                const q = productSearchInput.value.trim();
                const locationId = locationIdEl.value;
                const customerId = document.getElementById('customerSelect')?.value || '';

                productSearchBody.innerHTML =
                    `<tr><td colspan="5" class="text-center text-muted py-3">Memuat...</td></tr>`;

                const url = new URL(searchProductsUrl, window.location.origin);
                url.searchParams.set('q', q);
                url.searchParams.set('location_id', locationId);
                url.searchParams.set('limit', '30');
                url.searchParams.set('sale_channel', saleChannel);
                if (customerId) {
                    url.searchParams.set('customer_id', customerId);
                }

                const res = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf
                    }
                });
                const data = res.ok ? await res.json() : null;
                productSearchResults = data?.results || [];

                if (productSearchResults.length === 0) {
                    productSearchBody.innerHTML =
                        `<tr><td colspan="5" class="text-center text-muted py-3">Tidak ada data.</td></tr>`;
                    return;
                }

                productSearchBody.innerHTML = '';
                productSearchResults.forEach((row, idx) => {
                    const tr = document.createElement('tr');
                    tr.style.cursor = 'pointer';
                    tr.addEventListener('dblclick', () => pickProductFromSearch(idx));
                    tr.addEventListener('click', () => {
                        productSearchBody.querySelectorAll('tr').forEach(r => r.classList.remove(
                            'table-active'));
                        tr.classList.add('table-active');
                    });
                    tr.innerHTML = `
                        <td class="fw-semibold">${escapeHtml(row.name)}</td>
                        <td>${escapeHtml(row.product_code || '-')}</td>
                        <td>${escapeHtml(row.supplier_name || '-')}</td>
                        <td class="text-end">${row.stock_at_location === null ? '-' : escapeHtml(row.stock_at_location)}</td>
                        <td class="text-end">${rupiah(row.selling_price || 0)}</td>
                    `;
                    productSearchBody.appendChild(tr);
                    if (idx === 0) tr.classList.add('table-active');
                });
            }

            function pickProductFromSearch(index) {
                const row = productSearchResults[index];
                if (!row) return;

                bootstrap.Modal.getOrCreateInstance(productSearchModalEl).hide();
                addProduct({
                    id: row.id,
                    product_code: row.product_code,
                    name: row.name,
                    has_serial_number: row.has_serial_number,
                    is_open_price: row.is_open_price,
                    barcode: row.product_code,
                    unit_level: 1,
                    unit_name: row.unit_name || 'PCS',
                    conversion_qty: 1,
                    unit_price: row.selling_price,
                    stock_at_location: row.stock_at_location
                });
            }

            productSearchBtn.addEventListener('click', () => runProductSearch());
            productSearchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (productSearchResults.length > 0) pickProductFromSearch(0);
                }
            });
            productSearchModalEl.addEventListener('shown.bs.modal', () => setTimeout(() => productSearchInput.focus(),
                50));

            let mobileProductSearchTimer = null;

            function mobileProductPayload(row) {
                return {
                    id: row.id,
                    product_code: row.product_code,
                    name: row.name,
                    has_serial_number: row.has_serial_number,
                    is_open_price: row.is_open_price,
                    barcode: row.product_code,
                    unit_level: 1,
                    unit_name: row.unit_name || 'PCS',
                    conversion_qty: 1,
                    unit_price: row.selling_price,
                    stock_at_location: row.stock_at_location
                };
            }

            async function loadMobileProducts(query = '') {
                if (!mobileProductGrid) return;

                mobileProductGrid.innerHTML = '<div class="text-muted small p-2">Memuat produk...</div>';

                const customerId = document.getElementById('customerSelect')?.value || '';
                const url = new URL(searchProductsUrl, window.location.origin);
                url.searchParams.set('q', query || '');
                url.searchParams.set('location_id', locationIdEl.value);
                url.searchParams.set('limit', '30');
                url.searchParams.set('sale_channel', saleChannel);
                if (customerId) {
                    url.searchParams.set('customer_id', customerId);
                }

                const res = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf
                    }
                });
                const data = res.ok ? await res.json() : null;
                const rows = data?.results || [];

                if (rows.length === 0) {
                    mobileProductGrid.innerHTML = '<div class="text-muted small p-2">Produk tidak ditemukan.</div>';
                    return;
                }

                mobileProductGrid.innerHTML = '';
                rows.forEach(row => {
                    const card = document.createElement('button');
                    card.type = 'button';
                    card.className = 'mobile-product-card';
                    const imageHtml = row.image_url
                        ? `<img src="${escapeHtml(row.image_url)}" alt="${escapeHtml(row.name)}" class="mobile-product-thumb">`
                        : `<div class="mobile-product-thumb mobile-product-thumb-placeholder"><i class="bx bx-camera"></i><span>Foto belum ada</span></div>`;

                    card.innerHTML = `
                        ${imageHtml}
                        <div class="mobile-product-info">
                            <div class="mobile-product-name">${escapeHtml(row.name)}</div>
                            <div class="mobile-product-meta">
                                <span class="text-muted">${escapeHtml(row.product_code || '-')}</span>
                                <span class="fw-bold text-primary">${rupiah(row.selling_price || 0)}</span>
                            </div>
                        </div>
                    `;
                    card.addEventListener('click', () => addProduct(mobileProductPayload(row)));
                    mobileProductGrid.appendChild(card);
                });
            }

            mobileProductSearchInput?.addEventListener('input', () => {
                clearTimeout(mobileProductSearchTimer);
                mobileProductSearchTimer = setTimeout(() => {
                    loadMobileProducts(mobileProductSearchInput.value.trim());
                }, 220);
            });

            mobileBarcodeInput?.addEventListener('keydown', async (e) => {
                if (e.key !== 'Enter') return;
                e.preventDefault();

                const code = mobileBarcodeInput.value.trim();
                if (!code) return;
                mobileBarcodeInput.value = '';

                try {
                    const product = await lookupAndAdd(code);
                    if (!product) {
                        mobileProductSearchInput.value = code;
                        await loadMobileProducts(code);
                        mobileProductSearchInput.focus();
                        return;
                    }
                    addProduct(product);
                } catch (err) {
                    alert('Gagal lookup produk.');
                    focusScan();
                }
            });

            mobileLocationMirror?.addEventListener('change', () => {
                locationIdEl.value = mobileLocationMirror.value;
                locationIdEl.dispatchEvent(new Event('change'));
                loadMobileProducts(mobileProductSearchInput?.value?.trim() || '');
            });

            // Cash opening modal
            const cashModalEl = document.getElementById('cashOpeningModal');
            const openingCashInput = document.getElementById('openingCashInput');
            const openingCashNotes = document.getElementById('openingCashNotes');
            const saveOpeningCashBtn = document.getElementById('saveOpeningCashBtn');

            async function checkCashSession() {
                const locationId = locationIdEl.value;
                const url = new URL(cashStatusUrl, window.location.origin);
                url.searchParams.set('location_id', locationId);
                const res = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf
                    }
                });
                const data = res.ok ? await res.json() : null;
                if (!data || data.enabled === false) return;
                if (data.has_session) return;

                const modal = bootstrap.Modal.getOrCreateInstance(cashModalEl, {
                    backdrop: 'static',
                    keyboard: false
                });
                modal.show();
                setTimeout(() => openingCashInput.focus(), 120);
            }

            saveOpeningCashBtn.addEventListener('click', async () => {
                const locationId = locationIdEl.value;
                const openingCash = toNumber(openingCashInput.value);
                // Local date (avoid UTC shift from toISOString)
                const sessionDate = new Date().toLocaleDateString('en-CA');

                saveOpeningCashBtn.disabled = true;
                try {
                    const res = await fetch(cashOpenUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            location_id: locationId,
                            session_date: sessionDate,
                            opening_cash: openingCash,
                            notes: openingCashNotes.value || null
                        })
                    });

                    const data = res.ok ? await res.json() : null;
                    if (!res.ok) {
                        alert(data?.message || 'Gagal menyimpan kas awal.');
                        return;
                    }

                    bootstrap.Modal.getOrCreateInstance(cashModalEl).hide();
                    focusScan();
                } finally {
                    saveOpeningCashBtn.disabled = false;
                }
            });

            // Cash closing session
            const closeCashSessionBtn = document.getElementById('closeCashSessionBtn');
            const cashClosingModalEl = document.getElementById('cashClosingModal');
            const closeSessionLoading = document.getElementById('closeSessionLoading');
            const closeSessionContent = document.getElementById('closeSessionContent');
            
            const sysOpeningCash = document.getElementById('sysOpeningCash');
            const sysCashSales = document.getElementById('sysCashSales');
            const sysCashServices = document.getElementById('sysCashServices');
            const sysCreditPayments = document.getElementById('sysCreditPayments');
            const sysExpectedClosing = document.getElementById('sysExpectedClosing');
            
            const sysNonCashTransfer = document.getElementById('sysNonCashTransfer');
            const sysNonCashQris = document.getElementById('sysNonCashQris');
            
            const closingCashInput = document.getElementById('closingCashInput');
            const closingDiscrepancy = document.getElementById('closingDiscrepancy');
            const discrepancyNote = document.getElementById('discrepancyNote');
            const closingCashNotes = document.getElementById('closingCashNotes');
            const saveClosingCashBtn = document.getElementById('saveClosingCashBtn');
            const discrepancyBox = document.getElementById('discrepancyBox');

            let expectedClosingCashAmount = 0;

            function updateDiscrepancy() {
                const physicalCash = toNumber(closingCashInput.value);
                const diff = physicalCash - expectedClosingCashAmount;

                if (diff === 0) {
                    closingDiscrepancy.textContent = 'Rp 0';
                    closingDiscrepancy.className = 'fw-bold fs-5 text-dark';
                    discrepancyNote.textContent = 'Pas (Tidak ada selisih)';
                    discrepancyBox.style.backgroundColor = '#f8f9fa';
                } else if (diff > 0) {
                    closingDiscrepancy.textContent = '+' + rupiah(diff);
                    closingDiscrepancy.className = 'fw-bold fs-5 text-success';
                    discrepancyNote.textContent = 'Surplus (Uang fisik lebih banyak)';
                    discrepancyBox.style.backgroundColor = '#e8f5e9';
                } else {
                    closingDiscrepancy.textContent = '-' + rupiah(Math.abs(diff));
                    closingDiscrepancy.className = 'fw-bold fs-5 text-danger';
                    discrepancyNote.textContent = 'Defisit / Minus (Uang fisik kurang)';
                    discrepancyBox.style.backgroundColor = '#ffebee';
                }
            }

            closingCashInput.addEventListener('input', updateDiscrepancy);

            if (closeCashSessionBtn) {
                closeCashSessionBtn.addEventListener('click', async () => {
                    const modal = bootstrap.Modal.getOrCreateInstance(cashClosingModalEl);
                    modal.show();

                    closeSessionLoading.classList.remove('d-none');
                    closeSessionContent.classList.add('d-none');
                    saveClosingCashBtn.disabled = true;

                    try {
                        const url = new URL('/transactions/cash/summary', window.location.origin);
                        url.searchParams.set('location_id', locationIdEl.value);

                        const res = await fetch(url.toString(), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrf
                            }
                        });

                        if (res.ok) {
                            const data = await res.json();
                            if (data && data.has_session) {
                                expectedClosingCashAmount = data.expected_closing_cash;

                                sysOpeningCash.textContent = rupiah(data.opening_cash);
                                sysCashSales.textContent = rupiah(data.sales.cash + data.sales.tempo_cash);
                                sysCashServices.textContent = rupiah(data.services.cash + data.services.tempo_cash);
                                sysCreditPayments.textContent = rupiah(data.credit_payments.cash);
                                sysExpectedClosing.textContent = rupiah(data.expected_closing_cash);

                                sysNonCashTransfer.textContent = rupiah(data.aggregates.total_transfer);
                                sysNonCashQris.textContent = rupiah(data.aggregates.total_qris);

                                closingCashInput.value = data.expected_closing_cash;
                                updateDiscrepancy();

                                closeSessionLoading.classList.add('d-none');
                                closeSessionContent.classList.remove('d-none');
                                saveClosingCashBtn.disabled = false;
                            }
                        } else {
                            alert('Sesi kas tidak aktif atau tidak ditemukan.');
                            modal.hide();
                        }
                    } catch (e) {
                        alert('Gagal mengambil ringkasan kas sesi ini.');
                        console.error(e);
                        modal.hide();
                    }
                });
            }

            saveClosingCashBtn.addEventListener('click', async () => {
                saveClosingCashBtn.disabled = true;
                const locationId = locationIdEl.value;
                const closingCash = toNumber(closingCashInput.value);
                const notes = closingCashNotes.value.trim();

                try {
                    const res = await fetch('/transactions/cash/close', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify({
                            location_id: locationId,
                            closing_cash: closingCash,
                            notes: notes
                        })
                    });

                    const data = await res.json();
                    if (res.ok) {
                        alert(data.message || 'Kasir berhasil ditutup!');
                        window.location.reload();
                    } else {
                        alert(data.message || 'Gagal menutup kasir.');
                        saveClosingCashBtn.disabled = false;
                    }
                } catch (e) {
                    alert('Terjadi kesalahan koneksi saat menutup kasir.');
                    console.error(e);
                    saveClosingCashBtn.disabled = false;
                }
            });

            locationIdEl.addEventListener('change', () => {
                if (mobileLocationMirror && mobileLocationMirror.value !== locationIdEl.value) {
                    mobileLocationMirror.value = locationIdEl.value;
                }
                checkCashSession();
                loadMobileProducts(mobileProductSearchInput?.value?.trim() || '');
                focusScan();
            });

            // POS mode toggle (maximize)
            const toggleBtn = document.getElementById('togglePosModeBtn');

            function setPosMode(enabled) {
                document.body.classList.toggle('pos-mode', !!enabled);
                localStorage.setItem('uteparts-pos-mode', enabled ? '1' : '0');
                toggleBtn.innerHTML = enabled ? '<i class="bx bx-exit-fullscreen me-1"></i> Restore' :
                    '<i class="bx bx-fullscreen me-1"></i> Maximize';
            }

            toggleBtn.addEventListener('click', async () => {
                const next = !document.body.classList.contains('pos-mode');
                setPosMode(next);
                if (next && document.documentElement.requestFullscreen) {
                    try {
                        await document.documentElement.requestFullscreen();
                    } catch (e) {}
                } else if (!next && document.exitFullscreen) {
                    try {
                        await document.exitFullscreen();
                    } catch (e) {}
                }
                focusScan();
            });

            // Keyboard shortcuts
            document.addEventListener('keydown', (e) => {
                if (e.key === 'F8') {
                    e.preventDefault();
                    document.getElementById('submitSaleBtn')?.click();
                    return;
                }
                if (e.key === 'F9') {
                    e.preventDefault();
                    printLastReceipt();
                    return;
                }
                if (e.key === 'F3') {
                    e.preventDefault();
                    try {
                        if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                            $('#customerSelect').select2('open');
                        }
                    } catch (err) {}
                    return;
                }
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'f') {
                    e.preventDefault();
                    openProductSearch(barcodeInput.value || '');
                    return;
                }
                if ((e.ctrlKey || e.metaKey) && e.key === 'F7') {
                    e.preventDefault();
                    if (selectedIndex !== null && items[selectedIndex]?.has_serial_number) {
                        openSerialModal(selectedIndex);
                    }
                    return;
                }
            });

            // Ensure scan input stays focused when clicking around.
            document.addEventListener('click', (e) => {
                const target = e.target;
                if (!target) return;
                if (target.closest('#serialModal')) return;
                if (target.closest('#productSearchModal')) return;
                if (target.closest('#cashOpeningModal')) return;
                if (target.closest('.select2-container')) return;
                if (target.closest('input, textarea, select, button')) return;
                focusScan();
            });

            // Restore POS mode on reload
            setPosMode(true);

            // Ensure pending qty badge starts hidden.
            setPendingQty(null);

            // Keep bottom padding in sync with footer height to avoid overlap.
            const footerEl = document.querySelector('.pos-footer');

            function syncFooterHeight() {
                if (!footerEl) return;
                const h = footerEl.getBoundingClientRect().height;
                document.documentElement.style.setProperty('--pos-footer-height', Math.ceil(h) + 'px');
            }
            window.addEventListener('resize', syncFooterHeight);
            syncFooterHeight();

            recalc();
            renderMobileCart();
            loadMobileProducts();
            focusScan();
            checkCashSession();

            if (lastReceiptUrl && window.matchMedia('(max-width: 767.98px)').matches) {
                const receiptUrl = new URL(lastReceiptUrl, window.location.origin);
                receiptUrl.searchParams.set('embed', '1');
                receiptUrl.searchParams.set('print', '1');

                const frame = document.createElement('iframe');
                frame.title = 'receipt-auto-print';
                frame.style.width = '0';
                frame.style.height = '0';
                frame.style.border = '0';
                frame.style.position = 'fixed';
                frame.style.left = '-9999px';
                frame.style.top = '-9999px';
                frame.src = receiptUrl.toString();
                document.body.appendChild(frame);
            }
        })();
    </script>
@endpush
