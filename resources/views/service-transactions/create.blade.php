@extends('layouts.sneat')

@section('title', 'Transaksi Service')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .service-page {
            min-height: calc(100vh - 3rem);
        }

        .service-panel {
            background: #d7ecff;
            border: 2px solid #0b4f7a;
            border-radius: .5rem;
        }

        .service-panel .form-label {
            font-size: 11px;
            font-weight: 800;
            color: #0b3551;
            margin-bottom: .15rem;
        }

        .service-panel .form-control,
        .service-panel .form-select {
            border-color: #0b4f7a;
            background: #eef7ff;
        }

        .service-total {
            color: #d10b0b;
            font-size: clamp(28px, 3vw, 46px);
            font-weight: 900;
            line-height: 1;
            max-width: 100%;
            overflow: hidden;
            text-align: center;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .service-items {
            border: 2px solid #0b4f7a;
        }

        .service-items th { background: #9ed1ff; color: #0b3551; text-transform: uppercase; font-weight: 800; }
        .service-items .table-responsive { max-height: 330px; overflow: auto; }
        .service-items tbody td { vertical-align: middle; }
        .service-right-sticky { position: sticky; top: 1rem; }
        .service-shortcut-card {
            background: #ffffff;
            border: 1px solid rgba(11, 79, 122, .18);
            border-radius: .5rem;
        }

        .service-shortcut-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: .5rem;
        }

        .service-shortcut {
            display: flex;
            align-items: center;
            gap: .45rem;
            min-height: 36px;
            padding: .45rem .55rem;
            border-radius: .45rem;
            background: #eef7ff;
            color: #0b4f7a;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }

        .service-shortcut kbd {
            background: #0b4f7a;
            color: #fff;
            border-radius: .35rem;
            padding: .15rem .35rem;
            font-size: 11px;
            line-height: 1.2;
        }

        .pattern-lock-wrap {
            display: none;
            margin-top: .5rem;
            background: rgba(255, 255, 255, .35);
            border: 1px solid rgba(11, 79, 122, .22);
            border-radius: .5rem;
            padding: .75rem;
        }

        .pattern-lock-wrap.active {
            display: block;
        }

        .pattern-lock-board {
            position: relative;
            width: min(210px, 100%);
            aspect-ratio: 1 / 1;
            margin: 0 auto;
            touch-action: none;
            user-select: none;
        }

        .pattern-lock-lines {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .pattern-lock-grid {
            position: absolute;
            inset: 0;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(3, 1fr);
            gap: 18px;
            padding: 18px;
        }

        .pattern-dot {
            width: 34px;
            height: 34px;
            border: 2px solid #0b4f7a;
            border-radius: 50%;
            background: #eef7ff;
            color: #0b4f7a;
            font-size: 12px;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
            justify-self: center;
            align-self: center;
            cursor: pointer;
            position: relative;
            z-index: 2;
        }

        .pattern-dot.active {
            background: #0b4f7a;
            color: #fff;
            box-shadow: 0 0 0 7px rgba(11, 79, 122, .14);
        }

        .pattern-lock-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            margin-top: .65rem;
        }

        .pattern-lock-value {
            font-size: 12px;
            font-weight: 800;
            color: #0b3551;
            word-break: break-word;
        }

        .select2-container { width: 100% !important; }

        @media (max-width: 991.98px) {
            .service-right-sticky { position: static; }
            .service-items .table-responsive { max-height: none; }
            .service-shortcut-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        .service-mobile-items {
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

            .service-page {
                min-height: 100vh;
                background: #f5f6fb;
                padding: .7rem !important;
            }

            .service-page > form > .d-flex.justify-content-between {
                align-items: stretch !important;
                gap: .6rem;
                margin-bottom: .7rem !important;
            }

            .service-page h5 {
                font-size: 18px;
                font-weight: 900;
            }

            .service-page > form > .row {
                --bs-gutter-y: .7rem;
            }

            .service-panel {
                border-width: 1px;
                border-radius: .65rem;
                background: #fff;
            }

            .service-panel.p-3 {
                padding: .75rem !important;
            }

            .service-panel h6 {
                font-size: 13px;
                font-weight: 900;
                color: #0b4f7a;
            }

            .service-panel .form-label {
                font-size: 10px;
                text-transform: uppercase;
            }

            .service-panel .form-control,
            .service-panel .form-select {
                min-height: 36px;
                font-size: 13px;
            }

            .service-total {
                font-size: 28px;
            }

            .service-items .table-responsive {
                display: none;
            }

            .service-mobile-items {
                display: grid;
                gap: .55rem;
                padding: .55rem;
                background: #fff;
            }

            .service-mobile-empty,
            .service-mobile-item {
                border: 1px solid rgba(67, 89, 113, .14);
                border-radius: .55rem;
                padding: .6rem;
                background: #fff;
            }

            .service-mobile-item-top {
                display: flex;
                justify-content: space-between;
                gap: .6rem;
                align-items: flex-start;
            }

            .service-mobile-item-name {
                font-size: 12px;
                font-weight: 900;
                line-height: 1.2;
            }

            .service-mobile-item-code {
                font-size: 11px;
                color: #697a8d;
                margin-top: .15rem;
            }

            .service-mobile-item-total {
                font-size: 13px;
                font-weight: 900;
                white-space: nowrap;
            }

            .service-mobile-controls {
                display: grid;
                grid-template-columns: repeat(3, 1fr) auto;
                gap: .4rem;
                align-items: end;
                margin-top: .55rem;
            }

            .service-mobile-controls label {
                font-size: 9px;
                font-weight: 900;
                color: #697a8d;
                text-transform: uppercase;
                margin-bottom: .15rem;
            }

            .service-mobile-controls .form-control {
                min-height: 32px;
                font-size: 12px;
                padding: .2rem .35rem;
            }

            .service-shortcut-card {
                display: none;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid flex-grow-1 container-p-y service-page">
        @if (session('error'))<div class="alert alert-danger alert-dismissible" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
        @if (!$tableReady)<div class="alert alert-warning">Fitur transaksi service belum siap. Jalankan migration terlebih dahulu.</div>@endif

        <form action="{{ route('service-transactions.store') }}" method="POST" id="serviceForm">
            @csrf
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('service-transactions.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back"></i></a>
                    <h5 class="mb-0">Transaksi Service</h5>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Simpan</button>
            </div>

            <div class="row g-3 align-items-start">
                <div class="col-xl-5 col-lg-6">
                    <div class="service-panel p-3 mb-3">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label">Kode</label>
                                <input type="text" name="service_code" class="form-control" value="{{ old('service_code', $draftServiceCode) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tanggal</label>
                                <input type="date" name="service_at" class="form-control" value="{{ old('service_at', now()->toDateString()) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tgl Kembali</label>
                                <input type="date" name="return_date" class="form-control" value="{{ old('return_date') }}">
                            </div>
                        </div>
                    </div>

                    <div class="service-panel p-3 mb-3">
                        <h6 class="mb-2">Identitas Pelanggan</h6>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label">Customer Terdaftar</label>
                                <select id="customerSelect" name="customer_id" class="form-select"></select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Kode Pelanggan</label>
                                <input type="text" name="customer_code" id="customerCode" class="form-control" value="{{ old('customer_code') }}">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Nama Pelanggan</label>
                                <input type="text" name="customer_name" id="customerName" class="form-control" value="{{ old('customer_name') }}" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Alamat Pelanggan</label>
                                <input type="text" name="customer_address" id="customerAddress" class="form-control" value="{{ old('customer_address') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Telepon Pelanggan</label>
                                <input type="text" name="customer_phone" id="customerPhone" class="form-control" value="{{ old('customer_phone') }}">
                            </div>
                        </div>
                    </div>

                    <div class="service-panel p-3 mb-3">
                        <h6 class="mb-2">Identitas Barang Servis</h6>
                        <div class="row g-2">
                            <div class="col-md-4"><label class="form-label">Merek</label><input type="text" name="device_brand" class="form-control" value="{{ old('device_brand') }}"></div>
                            <div class="col-md-4"><label class="form-label">Type</label><input type="text" name="device_type" class="form-control" value="{{ old('device_type') }}"></div>
                            <div class="col-md-4"><label class="form-label">Nomor Seri</label><input type="text" name="serial_number" class="form-control" value="{{ old('serial_number') }}"></div>
                            <div class="col-md-7"><label class="form-label">Nama Teknisi</label><select name="technician_id" class="form-select"><option value="">-</option>@foreach($technicians as $tech)<option value="{{ $tech->id }}">{{ $tech->name }}</option>@endforeach</select></div>
                            <div class="col-md-5"><label class="form-label">Komisi</label><input type="number" min="0" step="1" name="technician_commission" class="form-control" value="{{ old('technician_commission', 0) }}"></div>
                        </div>
                    </div>

                    <div class="service-panel p-3">
                        <h6 class="mb-2">Kerusakan dan Kelengkapan</h6>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label">Kunci HP</label>
                                <select name="device_lock_type" class="form-select">
                                    <option value="">Tidak ada</option>
                                    <option value="pin" @selected(old('device_lock_type') === 'pin')>PIN</option>
                                    <option value="pattern" @selected(old('device_lock_type') === 'pattern')>Pola</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">PIN / Pola HP</label>
                                <input type="text" name="device_lock_value" id="deviceLockValue" class="form-control" value="{{ old('device_lock_value') }}" placeholder="Contoh PIN: 123456">
                                <div class="pattern-lock-wrap" id="patternLockWrap">
                                    <div class="pattern-lock-board" id="patternLockBoard">
                                        <svg class="pattern-lock-lines" id="patternLockLines" viewBox="0 0 210 210" preserveAspectRatio="none"></svg>
                                        <div class="pattern-lock-grid">
                                            @for($i = 1; $i <= 9; $i++)
                                                <button type="button" class="pattern-dot" data-dot="{{ $i }}">{{ $i }}</button>
                                            @endfor
                                        </div>
                                    </div>
                                    <div class="pattern-lock-actions">
                                        <div class="pattern-lock-value">Pola: <span id="patternLockLabel">-</span></div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clearPatternLockBtn">Reset Pola</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12"><label class="form-label">Keterangan Cek</label><textarea name="check_notes" class="form-control" rows="2">{{ old('check_notes') }}</textarea></div>
                            <div class="col-md-6"><label class="form-label">Keluhan Service</label><textarea name="complaint" class="form-control" rows="3">{{ old('complaint') }}</textarea></div>
                            <div class="col-md-6"><label class="form-label">Kelengkapan</label><textarea name="accessories" class="form-control" rows="3">{{ old('accessories') }}</textarea></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-7 col-lg-6">
                    <div class="service-right-sticky">
                        <div class="row g-3 mb-3">
                            <div class="col-xl-8">
                                <div class="service-panel p-3 h-100">
                                    <h6 class="mb-2">Biaya Jasa dan Sparepart</h6>
                                    <div class="row g-2">
                                        <div class="col-md-6"><label class="form-label">Jasa</label><select id="serviceSelect" class="form-select"></select></div>
                                        <div class="col-md-6"><label class="form-label">Spare Part</label><select id="productSelect" class="form-select"></select></div>
                                        <div class="col-md-4"><label class="form-label">Lokasi Stok</label><select name="location_id" class="form-select">@foreach($locations as $location)<option value="{{ $location->id }}" @selected((int) old('location_id', $defaultLocationId) === $location->id)>{{ $location->name }}</option>@endforeach</select></div>
                                        <div class="col-md-4"><label class="form-label">Pembayaran</label><select name="payment_method" id="paymentMethod" class="form-select"><option value="cash">Cash</option><option value="transfer">Transfer</option><option value="qris">QRIS</option><option value="tempo">Tempo</option></select></div>
                                        <div class="col-md-4"><label class="form-label">No. Referensi</label><input type="text" name="payment_reference" class="form-control"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4">
                                <div class="service-panel p-3 h-100 d-flex align-items-center justify-content-center">
                                    <div class="service-total" id="grandTotalBig">0</div>
                                </div>
                            </div>
                        </div>

                        <div class="card service-items mb-3">
                            <div class="table-responsive">
                                <table class="table mb-0" id="itemsTable">
                                    <thead><tr><th>Item</th><th style="width:95px;">Qty</th><th style="width:135px;">Harga</th><th style="width:120px;">Disc</th><th style="width:135px;">Subtotal</th><th style="width:52px;"></th></tr></thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <div class="service-mobile-items" id="serviceMobileItems">
                                <div class="service-mobile-empty text-muted small">Belum ada jasa atau sparepart.</div>
                            </div>
                        </div>

                        <div class="service-panel p-3">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="process">Proses</option><option value="done">Selesai</option><option value="taken">Sudah Diambil</option><option value="cancelled">Dibatalkan</option></select></div>
                                <div class="col-md-3"><label class="form-label">Bayar</label><input type="number" min="0" step="1" name="paid_amount" id="paidAmount" class="form-control" value="{{ old('paid_amount', 0) }}" required></div>
                                <div class="col-md-3 d-none" id="tempoTermWrap"><label class="form-label">Tempo</label><div class="input-group"><input type="number" min="1" max="3650" step="1" name="credit_term_days" id="tempoTermInput" class="form-control" value="{{ old('credit_term_days', 30) }}"><span class="input-group-text">hari</span></div></div>
                                <div class="col-md-3"><label class="form-label">Format Faktur</label><input type="text" name="invoice_format" class="form-control" value="{{ old('invoice_format', 'STANDART') }}"></div>
                                <div class="col-md-3">
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="is_checked" value="1" checked><label class="form-check-label">Sudah dicek</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="print_detail_price" value="1" checked><label class="form-check-label">Detail harga</label></div>
                                </div>
                            </div>
                            <div class="row g-2 mt-2">
                                <div class="col-6 col-md-3 d-flex justify-content-between"><span>SUBTOTAL</span><strong id="subtotalLabel">Rp 0</strong></div>
                                <div class="col-6 col-md-3 d-flex justify-content-between"><span>DISC</span><strong id="discountLabel">Rp 0</strong></div>
                                <div class="col-6 col-md-3 d-flex justify-content-between"><span>GRAND</span><strong id="grandTotalLabel">Rp 0</strong></div>
                                <div class="col-6 col-md-3 d-flex justify-content-between"><span id="serviceBalanceLabel">KEMBALI</span><strong id="changeLabel">Rp 0</strong></div>
                            </div>
                        </div>

                        <div class="service-shortcut-card p-2 mt-3">
                            <div class="service-shortcut-grid">
                                <div class="service-shortcut"><kbd>F5</kbd><span>Jasa</span></div>
                                <div class="service-shortcut"><kbd>F6</kbd><span>Sparepart</span></div>
                                <div class="service-shortcut"><kbd>F3</kbd><span>Customer</span></div>
                                <div class="service-shortcut"><kbd>F8</kbd><span>Simpan</span></div>
                                <div class="service-shortcut"><kbd>Esc</kbd><span>Tutup</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="modal fade" id="missingServiceDataModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Data Service Belum Lengkap</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning mb-3">
                            Beberapa data penting belum dicatat. Pastikan memang tidak diperlukan sebelum menyimpan.
                        </div>
                        <ul class="mb-0" id="missingServiceDataList"></ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Lengkapi Dulu</button>
                        <button type="button" class="btn btn-primary" id="confirmSaveServiceBtn">
                            <i class="bx bx-save me-1"></i> Tetap Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        const serviceSearchUrl = @json(route('services.search'));
        const productSearchUrl = @json(route('transactions.search.products'));
        const customerSearchUrl = @json(route('transactions.search.customers'));
        let items = [];
        let allowSubmitWithMissingData = false;

        function money(value) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(Number(value || 0))); }
        function numberInput(value, name, index, field, disabled = false) {
            return `<input type="number" min="0" step="1" class="form-control form-control-sm js-item-input" data-index="${index}" data-field="${field}" name="${name}" value="${Number(value || 0)}" ${disabled ? 'readonly' : ''}>`;
        }

        function renderItems() {
            const tbody = document.querySelector('#itemsTable tbody');
            tbody.innerHTML = items.map((item, index) => {
                const prefix = item.type === 'service' ? 'services' : 'products';
                const idKey = item.type === 'service' ? 'service_id' : 'product_id';
                const priceDisabled = !item.is_open_price;
                const discountDisabled = !item.allow_discount_override;
                return `<tr>
                    <td><div class="fw-semibold">${item.name}</div><div class="text-muted small">${item.code}</div><input type="hidden" name="${prefix}[${index}][${idKey}]" value="${item.id}"></td>
                    <td>${numberInput(item.quantity, `${prefix}[${index}][quantity]`, index, 'quantity')}</td>
                    <td>${numberInput(item.unit_price, `${prefix}[${index}][unit_price]`, index, 'unit_price', priceDisabled)}</td>
                    <td>${numberInput(item.discount_value, `${prefix}[${index}][discount_value]`, index, 'discount_value', discountDisabled)}</td>
                    <td class="text-end fw-bold js-line-subtotal">${money(Math.max(0, (item.quantity * item.unit_price) - item.discount_value))}</td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(${index})"><i class="bx bx-trash"></i></button></td>
                </tr>`;
            }).join('');
            tbody.querySelectorAll('.js-item-input').forEach(input => input.addEventListener('input', updateItemFromInput));
            renderMobileItems();
            recalc();
        }

        function renderMobileItems() {
            const wrap = document.getElementById('serviceMobileItems');
            if (!wrap) return;

            if (items.length === 0) {
                wrap.innerHTML = '<div class="service-mobile-empty text-muted small">Belum ada jasa atau sparepart.</div>';
                return;
            }

            wrap.innerHTML = items.map((item, index) => {
                const line = Math.max(0, (Number(item.quantity) * Number(item.unit_price)) - Number(item.discount_value || 0));
                return `<div class="service-mobile-item">
                    <div class="service-mobile-item-top">
                        <div>
                            <div class="service-mobile-item-name">${escapeHtml(item.name)}</div>
                            <div class="service-mobile-item-code">${item.type === 'service' ? 'Jasa' : 'Sparepart'} | ${escapeHtml(item.code)}</div>
                        </div>
                        <div class="service-mobile-item-total">${money(line)}</div>
                    </div>
                    <div class="service-mobile-controls">
                        <div>
                            <label>Qty</label>
                            <input type="number" min="0" step="1" class="form-control js-mobile-item-input" data-index="${index}" data-field="quantity" value="${Number(item.quantity || 0)}">
                        </div>
                        <div>
                            <label>Harga</label>
                            <input type="number" min="0" step="1" class="form-control js-mobile-item-input" data-index="${index}" data-field="unit_price" value="${Number(item.unit_price || 0)}" ${!item.is_open_price ? 'readonly' : ''}>
                        </div>
                        <div>
                            <label>Disc</label>
                            <input type="number" min="0" step="1" class="form-control js-mobile-item-input" data-index="${index}" data-field="discount_value" value="${Number(item.discount_value || 0)}" ${!item.allow_discount_override ? 'readonly' : ''}>
                        </div>
                        <button type="button" class="btn btn-outline-danger" data-remove="${index}"><i class="bx bx-trash"></i></button>
                    </div>
                </div>`;
            }).join('');

            wrap.querySelectorAll('.js-mobile-item-input').forEach(input => input.addEventListener('input', updateMobileItemFromInput));
            wrap.querySelectorAll('[data-remove]').forEach(button => {
                button.addEventListener('click', () => removeItem(Number(button.dataset.remove)));
            });
        }

        function escapeHtml(str) {
            return String(str || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function updateMobileItemFromInput(event) {
            const input = event.currentTarget;
            const index = Number(input.dataset.index);
            const field = input.dataset.field;
            if (!items[index] || !field) return;
            items[index][field] = Number(input.value || 0);
            renderItems();
        }

        function updateItemFromInput(event) {
            const input = event.currentTarget;
            const index = Number(input.dataset.index);
            const field = input.dataset.field;
            if (!items[index] || !field) return;
            items[index][field] = Number(input.value || 0);
            const row = input.closest('tr');
            const lineSubtotal = row?.querySelector('.js-line-subtotal');
            if (lineSubtotal) {
                lineSubtotal.textContent = money(Math.max(0, (items[index].quantity * items[index].unit_price) - items[index].discount_value));
            }
            recalc();
        }

        function recalc() {
            const subtotal = items.reduce((sum, item) => sum + (Number(item.quantity) * Number(item.unit_price)), 0);
            const discount = items.reduce((sum, item) => sum + Number(item.discount_value || 0), 0);
            const grand = Math.max(0, subtotal - discount);
            const paid = Number(document.getElementById('paidAmount').value || 0);
            const isTempo = document.getElementById('paymentMethod')?.value === 'tempo';
            document.getElementById('subtotalLabel').textContent = money(subtotal);
            document.getElementById('discountLabel').textContent = money(discount);
            document.getElementById('grandTotalLabel').textContent = money(grand);
            document.getElementById('serviceBalanceLabel').textContent = isTempo ? 'SISA TEMPO' : 'KEMBALI';
            document.getElementById('changeLabel').textContent = money(isTempo ? Math.max(0, grand - paid) : Math.max(0, paid - grand));
            document.getElementById('grandTotalBig').textContent = new Intl.NumberFormat('id-ID').format(grand);
        }
        function addItem(item) {
            items.push(Object.assign({ quantity: 1, discount_value: 0 }, item));
            renderItems();
        }
        function removeItem(index) {
            items.splice(index, 1);
            renderItems();
        }

        $(function() {
            $('#serviceSelect').select2({
                placeholder: 'Cari jasa',
                ajax: { url: serviceSearchUrl, dataType: 'json', delay: 250, data: params => ({ q: params.term || '' }), processResults: data => ({ results: data.results.map(row => ({ id: row.id, text: `${row.service_code} - ${row.name}`, row })) }) }
            }).on('select2:select', e => {
                const row = e.params.data.row;
                addItem({ type: 'service', id: row.id, code: row.service_code, name: row.name, unit_price: row.unit_price, is_open_price: row.is_open_price, allow_discount_override: row.allow_discount_override });
                $('#serviceSelect').val(null).trigger('change');
            });

            $('#productSelect').select2({
                placeholder: 'Cari sparepart',
                ajax: { url: productSearchUrl, dataType: 'json', delay: 250, data: params => ({ q: params.term || '', location_id: $('[name="location_id"]').val() }), processResults: data => ({ results: data.results.map(row => ({ id: row.id, text: `${row.product_code} - ${row.name}`, row })) }) }
            }).on('select2:select', e => {
                const row = e.params.data.row;
                addItem({ type: 'product', id: row.id, code: row.product_code, name: row.name, unit_price: row.selling_price, is_open_price: row.is_open_price, allow_discount_override: false });
                $('#productSelect').val(null).trigger('change');
            });

            $('#customerSelect').select2({
                placeholder: 'Cari pelanggan',
                allowClear: true,
                ajax: { url: customerSearchUrl, dataType: 'json', delay: 250, data: params => ({ q: params.term || '' }), processResults: data => ({ results: data.results }) }
            }).on('select2:select', e => {
                document.getElementById('customerName').value = e.params.data.text.split(' (')[0] || '';
                document.getElementById('customerCode').value = e.params.data.id || '';
            });

            document.getElementById('paidAmount').addEventListener('input', recalc);
            document.getElementById('paymentMethod').addEventListener('change', function() {
                const isTempo = this.value === 'tempo';
                document.getElementById('tempoTermWrap')?.classList.toggle('d-none', !isTempo);
                if (!isTempo) document.getElementById('tempoTermInput').value = '';
                if (isTempo && !document.getElementById('tempoTermInput').value) document.getElementById('tempoTermInput').value = '30';
                recalc();
            });
            document.getElementById('serviceForm').addEventListener('submit', handleServiceSubmit);
            document.getElementById('confirmSaveServiceBtn').addEventListener('click', function() {
                allowSubmitWithMissingData = true;
                document.getElementById('serviceForm').submit();
            });
            document.addEventListener('keydown', handleServiceShortcuts);
            initPatternLock();
            renderItems();
        });

        function initPatternLock() {
            const lockType = document.querySelector('[name="device_lock_type"]');
            const lockValue = document.getElementById('deviceLockValue');
            const wrap = document.getElementById('patternLockWrap');
            const board = document.getElementById('patternLockBoard');
            const lines = document.getElementById('patternLockLines');
            const label = document.getElementById('patternLockLabel');
            const clearBtn = document.getElementById('clearPatternLockBtn');
            const dots = Array.from(document.querySelectorAll('.pattern-dot'));
            let pattern = [];
            let drawing = false;

            if (!lockType || !lockValue || !wrap || !board || !lines || !label || dots.length === 0) return;

            function syncMode() {
                const isPattern = lockType.value === 'pattern';
                wrap.classList.toggle('active', isPattern);
                lockValue.classList.toggle('d-none', isPattern);
                lockValue.placeholder = isPattern ? 'Gambar pola di bawah' : 'Contoh PIN: 123456';

                if (!isPattern) {
                    resetPattern(false);
                } else if (lockValue.value.trim() !== '') {
                    pattern = lockValue.value.split('-').map(Number).filter((num) => num >= 1 && num <= 9);
                    renderPattern();
                }
            }

            function dotCenter(dot) {
                const boardRect = board.getBoundingClientRect();
                const dotRect = dot.getBoundingClientRect();
                return {
                    x: ((dotRect.left + dotRect.width / 2) - boardRect.left) / boardRect.width * 210,
                    y: ((dotRect.top + dotRect.height / 2) - boardRect.top) / boardRect.height * 210,
                };
            }

            function renderPattern() {
                dots.forEach((dot) => dot.classList.toggle('active', pattern.includes(Number(dot.dataset.dot))));
                lines.innerHTML = '';

                for (let i = 0; i < pattern.length - 1; i++) {
                    const from = dots.find((dot) => Number(dot.dataset.dot) === pattern[i]);
                    const to = dots.find((dot) => Number(dot.dataset.dot) === pattern[i + 1]);
                    if (!from || !to) continue;
                    const a = dotCenter(from);
                    const b = dotCenter(to);
                    const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                    line.setAttribute('x1', a.x);
                    line.setAttribute('y1', a.y);
                    line.setAttribute('x2', b.x);
                    line.setAttribute('y2', b.y);
                    line.setAttribute('stroke', '#0b4f7a');
                    line.setAttribute('stroke-width', '5');
                    line.setAttribute('stroke-linecap', 'round');
                    lines.appendChild(line);
                }

                const value = pattern.join('-');
                lockValue.value = value;
                label.textContent = value || '-';
            }

            function addDot(dot) {
                const number = Number(dot?.dataset?.dot || 0);
                if (!number || pattern.includes(number)) return;
                pattern.push(number);
                renderPattern();
            }

            function dotFromPoint(event) {
                const point = event.touches?.[0] || event;
                const boardRect = board.getBoundingClientRect();
                const pointerX = ((point.clientX - boardRect.left) / boardRect.width) * 210;
                const pointerY = ((point.clientY - boardRect.top) / boardRect.height) * 210;
                let nearest = null;
                let nearestDistance = Number.POSITIVE_INFINITY;

                dots.forEach((dot) => {
                    const center = dotCenter(dot);
                    const distance = Math.hypot(pointerX - center.x, pointerY - center.y);
                    if (distance < nearestDistance) {
                        nearest = dot;
                        nearestDistance = distance;
                    }
                });

                return nearestDistance <= 24 ? nearest : null;
            }

            function resetPattern(clearValue = true) {
                pattern = [];
                if (clearValue) lockValue.value = '';
                label.textContent = '-';
                dots.forEach((dot) => dot.classList.remove('active'));
                lines.innerHTML = '';
            }

            board.addEventListener('pointerdown', (event) => {
                if (lockType.value !== 'pattern') return;
                event.preventDefault();
                drawing = true;
                board.setPointerCapture?.(event.pointerId);
                addDot(dotFromPoint(event));
            });

            board.addEventListener('pointermove', (event) => {
                if (!drawing || lockType.value !== 'pattern') return;
                event.preventDefault();
                addDot(dotFromPoint(event));
            });

            ['pointerup', 'pointercancel', 'pointerleave'].forEach((eventName) => {
                board.addEventListener(eventName, () => {
                    drawing = false;
                });
            });

            dots.forEach((dot) => {
                dot.addEventListener('click', () => {
                    if (lockType.value === 'pattern') addDot(dot);
                });
            });

            clearBtn?.addEventListener('click', () => resetPattern(true));
            lockType.addEventListener('change', syncMode);
            window.addEventListener('resize', () => {
                if (lockType.value === 'pattern') renderPattern();
            });
            syncMode();
        }

        function closeOpenUi() {
            if (typeof $ !== 'undefined') {
                $('#serviceSelect, #productSelect, #customerSelect').select2('close');
            }

            document.querySelectorAll('.modal.show').forEach((modalEl) => {
                bootstrap.Modal.getInstance(modalEl)?.hide();
            });
        }

        function handleServiceShortcuts(event) {
            if (event.key === 'Escape') {
                closeOpenUi();
                return;
            }

            const key = String(event.key || '').toUpperCase();
            const handledKeys = ['F3', 'F5', 'F6', 'F8'];
            if (!handledKeys.includes(key)) return;

            event.preventDefault();

            if (key === 'F3') {
                $('#customerSelect').select2('open');
                return;
            }

            if (key === 'F5') {
                $('#serviceSelect').select2('open');
                return;
            }

            if (key === 'F6') {
                $('#productSelect').select2('open');
                return;
            }

            if (key === 'F8') {
                document.getElementById('serviceForm').requestSubmit();
            }
        }

        function fieldValue(name) {
            return String(document.querySelector(`[name="${name}"]`)?.value || '').trim();
        }

        function collectMissingImportantData() {
            const missing = [];
            const lockType = fieldValue('device_lock_type');
            const lockValue = fieldValue('device_lock_value');

            if (!fieldValue('customer_phone')) missing.push('Telepon pelanggan belum diisi');
            if (!fieldValue('customer_address')) missing.push('Alamat pelanggan belum diisi');
            if (!fieldValue('device_brand')) missing.push('Merek HP belum diisi');
            if (!fieldValue('device_type')) missing.push('Tipe HP belum diisi');
            if (!fieldValue('serial_number')) missing.push('Nomor seri/IMEI belum diisi');
            if (!lockType) missing.push('Jenis kunci HP belum dipilih (PIN/Pola/Tidak ada)');
            if (lockType && !lockValue) missing.push(`${lockType === 'pin' ? 'PIN' : 'Pola'} HP belum diisi`);
            if (!fieldValue('complaint')) missing.push('Keluhan service belum diisi');
            if (!fieldValue('accessories')) missing.push('Kelengkapan HP belum diisi');
            if (!fieldValue('check_notes')) missing.push('Keterangan cek belum diisi');

            return missing;
        }

        function handleServiceSubmit(event) {
            const method = document.getElementById('paymentMethod')?.value || 'cash';
            const grand = items.reduce((sum, item) => sum + Math.max(0, (Number(item.quantity) * Number(item.unit_price)) - Number(item.discount_value || 0)), 0);
            const paid = Number(document.getElementById('paidAmount').value || 0);

            if (method === 'tempo') {
                if (!String(document.getElementById('customerSelect')?.value || '').trim()) {
                    event.preventDefault();
                    alert('Pembayaran tempo hanya bisa untuk customer terdaftar.');
                    $('#customerSelect').select2('open');
                    return;
                }
                if (!Number(document.getElementById('tempoTermInput')?.value || 0)) {
                    event.preventDefault();
                    alert('Lama tempo wajib diisi.');
                    document.getElementById('tempoTermInput')?.focus();
                    return;
                }
                if (paid > grand) {
                    event.preventDefault();
                    alert('Bayar awal tempo tidak boleh lebih besar dari grand total.');
                    document.getElementById('paidAmount')?.focus();
                    return;
                }
            } else if (paid < grand) {
                event.preventDefault();
                alert('Jumlah bayar kurang dari total service.');
                document.getElementById('paidAmount')?.focus();
                return;
            }

            if (allowSubmitWithMissingData) return;

            const missing = collectMissingImportantData();
            if (missing.length === 0) return;

            event.preventDefault();
            const list = document.getElementById('missingServiceDataList');
            list.innerHTML = missing.map(item => `<li>${item}</li>`).join('');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('missingServiceDataModal')).show();
        }
    </script>
@endpush
