@extends('layouts.sneat')

@section('title', 'Transaksi')

@push('styles')
    <style>
        #transactions-table {
            width: 100% !important;
        }

        #transactions-table th,
        #transactions-table td {
            vertical-align: middle;
            white-space: nowrap;
        }

        #transactions-table td.trx-items-cell {
            min-width: 260px;
            max-width: 360px;
            white-space: normal;
        }

        .trx-item-summary {
            line-height: 1.25;
        }

        .transactions-table-wrapper .dt-buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: .35rem;
        }

        .transactions-table-wrapper .dataTables_filter,
        .transactions-table-wrapper .dataTables_length {
            margin-bottom: .75rem;
        }

        .transactions-table-wrapper .dataTables_filter label,
        .transactions-table-wrapper .dataTables_length label {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: 0;
        }

        .transactions-table-wrapper .dataTables_filter input,
        .transactions-table-wrapper .dataTables_length select {
            margin-left: 0;
        }

        @media (max-width: 767.98px) {
            .transactions-table-wrapper .dt-buttons {
                justify-content: flex-start;
            }

            .transactions-table-wrapper .dataTables_filter label,
            .transactions-table-wrapper .dataTables_length label {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        .trx-summary-card {
            border: 1px solid rgba(67, 89, 113, 0.16);
            border-radius: .5rem;
            padding: .6rem .75rem;
            background: #fff;
            height: 100%;
        }

        .trx-summary-card .k {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .02em;
            color: rgba(67, 89, 113, .75);
            text-transform: uppercase;
        }

        .trx-summary-card .v {
            font-size: 18px;
            font-weight: 900;
            color: #263238;
            line-height: 1.15;
            margin-top: .25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .transactions-mobile-list {
            display: none;
        }

        .transactions-mobile-page {
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

            .transactions-desktop-shell {
                display: none !important;
            }

            .transactions-mobile-page {
                display: block;
                min-height: 100vh;
                background: #f5f6fb;
                padding: .75rem;
            }

            .trx-mobile-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: .75rem;
                margin-bottom: .75rem;
            }

            .trx-mobile-title {
                font-size: 20px;
                font-weight: 900;
                color: #263238;
                line-height: 1.1;
            }

            .trx-mobile-subtitle {
                color: #697a8d;
                font-size: 12px;
                margin-top: .15rem;
            }

            .trx-mobile-header-actions {
                display: flex;
                gap: .4rem;
                flex: 0 0 auto;
            }

            .trx-mobile-summary {
                display: grid;
                grid-template-columns: 1.3fr 1fr;
                gap: .55rem;
                margin-bottom: .7rem;
            }

            .trx-mobile-summary-card {
                background: #fff;
                border: 1px solid rgba(67, 89, 113, .12);
                border-radius: .65rem;
                padding: .65rem;
                box-shadow: 0 8px 18px rgba(32, 36, 44, .06);
            }

            .trx-mobile-summary-card .k {
                color: #697a8d;
                font-size: 10px;
                font-weight: 900;
                text-transform: uppercase;
            }

            .trx-mobile-summary-card .v {
                color: #263238;
                font-size: 16px;
                font-weight: 900;
                line-height: 1.15;
                margin-top: .25rem;
            }

            .trx-mobile-filter {
                display: grid;
                grid-template-columns: 1fr auto;
                gap: .45rem;
                align-items: end;
                background: #fff;
                border: 1px solid rgba(67, 89, 113, .12);
                border-radius: .65rem;
                padding: .65rem;
                margin-bottom: .75rem;
            }

            .trx-mobile-filter label {
                font-size: 10px;
                font-weight: 900;
                color: #697a8d;
                text-transform: uppercase;
                margin-bottom: .2rem;
            }

            .trx-mobile-filter .form-control,
            .trx-mobile-filter .btn {
                min-height: 36px;
                font-size: 12px;
            }

            .transactions-table-wrapper > .table-responsive,
            .transactions-table-wrapper .dt-container,
            .transactions-table-wrapper .dataTables_wrapper {
                display: none !important;
            }

            .card {
                border-radius: .65rem;
            }

            .card-header {
                align-items: stretch !important;
                padding: .75rem !important;
            }

            .card-header h5 {
                font-size: 17px;
                line-height: 1.2;
            }

            .card-header .d-flex.gap-2 {
                display: grid !important;
                grid-template-columns: 1fr 1fr;
                width: 100%;
                gap: .45rem !important;
            }

            .card-header .d-flex.gap-2 .btn {
                width: 100%;
                padding: .45rem .5rem;
                font-size: 12px;
            }

            .transactions-table-wrapper {
                padding: .75rem !important;
            }

            .transactions-table-wrapper > .d-flex.flex-column.flex-md-row {
                align-items: stretch !important;
            }

            .transactions-table-wrapper form {
                display: grid !important;
                grid-template-columns: 1fr auto;
                align-items: end;
                width: 100%;
                gap: .45rem !important;
            }

            .transactions-table-wrapper form .btn {
                padding-inline: .65rem;
                white-space: nowrap;
            }

            .row.g-2.mb-3 {
                --bs-gutter-x: .45rem;
                --bs-gutter-y: .45rem;
            }

            .row.g-2.mb-3 > [class*="col-"] {
                width: 50%;
            }

            .transactions-mobile-list {
                display: grid;
                gap: .65rem;
            }

            .trx-mobile-card {
                border: 1px solid rgba(67, 89, 113, .16);
                border-radius: .55rem;
                background: #fff;
                padding: .7rem;
                box-shadow: 0 8px 20px rgba(32, 36, 44, .07);
            }

            .trx-mobile-top {
                display: flex;
                justify-content: space-between;
                gap: .65rem;
                align-items: flex-start;
            }

            .trx-mobile-code {
                font-size: 12px;
                font-weight: 900;
                color: #0b4f7a;
                word-break: break-word;
            }

            .trx-mobile-total {
                font-size: 15px;
                font-weight: 900;
                color: #263238;
                white-space: nowrap;
            }

            .trx-mobile-meta {
                display: grid;
                gap: .2rem;
                margin-top: .35rem;
                color: #697a8d;
                font-size: 11px;
            }

            .trx-mobile-item {
                margin-top: .55rem;
                padding-top: .5rem;
                border-top: 1px dashed rgba(67, 89, 113, .18);
                font-size: 11px;
            }

            .trx-mobile-actions {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: .45rem;
                margin-top: .65rem;
            }

            .trx-summary-card {
                padding: .5rem;
            }

            .trx-summary-card .v {
                font-size: 14px;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $saleChannel = $saleChannel ?? 'toko';
        $saleChannelLabel = $saleChannelLabel ?? 'Penjualan Toko';
        $saleIndexRoute = route('transactions.index.channel', $saleChannel);
        $saleCreateRoute = route('transactions.create.channel', $saleChannel);
        $saleDataRoute = route('transactions.data.channel', ['saleChannel' => $saleChannel, 'date' => $selectedDate]);
    @endphp
    <div class="container-xxl flex-grow-1 container-p-y">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
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

        <div class="transactions-mobile-page">
            <div class="trx-mobile-header">
                <div>
                    <div class="trx-mobile-title">{{ $saleChannelLabel }}</div>
                    <div class="trx-mobile-subtitle">{{ \Carbon\Carbon::parse($dailySummary['date'])->format('d/m/Y') }}</div>
                </div>
                <div class="trx-mobile-header-actions">
                    <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bx bx-home"></i>
                    </a>
                    @if (auth()->user()->hasPermission('transactions.create'))
                        <a href="{{ $saleCreateRoute }}" class="btn btn-sm btn-primary">
                            <i class="bx bx-plus me-1"></i> Jual
                        </a>
                    @endif
                </div>
            </div>

            <div class="trx-mobile-summary">
                <div class="trx-mobile-summary-card">
                    <div class="k">Omzet</div>
                    <div class="v">Rp {{ number_format((float) ($dailySummary['grand_total'] ?? 0), 0, ',', '.') }}</div>
                </div>
                <div class="trx-mobile-summary-card">
                    <div class="k">Transaksi</div>
                    <div class="v">{{ number_format((int) ($dailySummary['transactions_count'] ?? 0), 0, ',', '.') }}</div>
                </div>
                <div class="trx-mobile-summary-card">
                    <div class="k">Item</div>
                    <div class="v">{{ number_format((int) ($dailySummary['items_count'] ?? 0), 0, ',', '.') }}</div>
                </div>
                <div class="trx-mobile-summary-card">
                    <div class="k">Diskon</div>
                    <div class="v">Rp {{ number_format((float) ($dailySummary['discount_total'] ?? 0), 0, ',', '.') }}</div>
                </div>
            </div>

            <form method="GET" action="{{ $saleIndexRoute }}" class="trx-mobile-filter">
                <div>
                    <label>Pilih Tanggal</label>
                    <input type="date" name="date" class="form-control" value="{{ $selectedDate }}"
                        {{ $salesTableReady ? '' : 'disabled' }}>
                </div>
                <button class="btn btn-outline-secondary" {{ $salesTableReady ? '' : 'disabled' }}>
                    <i class="bx bx-search"></i>
                </button>
            </form>

            <div class="transactions-mobile-list">
                @forelse ($recentSales as $mobileSale)
                    @php
                        $firstItem = $mobileSale->items->first();
                        $remainingItems = max(0, $mobileSale->items->count() - 1);
                    @endphp
                    <div class="trx-mobile-card">
                        <div class="trx-mobile-top">
                            <div>
                                <a href="{{ route('transactions.show', $mobileSale) }}" class="trx-mobile-code text-decoration-none">
                                    {{ $mobileSale->sale_code }}
                                </a>
                                <div class="trx-mobile-meta">
                                    <span>{{ optional($mobileSale->sale_at)->format('d/m/Y H:i') ?: '-' }}</span>
                                    <span>{{ $mobileSale->customer?->name ?: 'Pelanggan umum' }} | {{ $mobileSale->location?->name ?: '-' }}</span>
                                    <span>Kasir: {{ $mobileSale->cashier?->name ?: '-' }}</span>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="trx-mobile-total">Rp {{ number_format((float) $mobileSale->grand_total, 0, ',', '.') }}</div>
                                <span class="badge bg-label-{{ $mobileSale->status === 'paid' ? 'success' : 'secondary' }}">
                                    {{ $mobileSale->status === 'paid' ? 'Paid' : 'Void' }}
                                </span>
                            </div>
                        </div>

                        <div class="trx-mobile-item">
                            @if ($firstItem)
                                <div class="fw-semibold">{{ $firstItem->product_name }}</div>
                                <div class="text-muted">
                                    {{ rtrim(rtrim(number_format((float) $firstItem->quantity, 2, ',', '.'), '0'), ',') }}
                                    {{ $firstItem->unit_name ?: 'PCS' }}
                                    @if ($remainingItems > 0)
                                        | +{{ $remainingItems }} item lain
                                    @endif
                                </div>
                            @else
                                <span class="text-muted">Tidak ada item.</span>
                            @endif
                        </div>

                        <div class="trx-mobile-actions">
                            <a href="{{ route('transactions.show', $mobileSale) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-show me-1"></i> Detail
                            </a>
                            <a href="{{ route('transactions.receipt', [$mobileSale, 'print' => 1]) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i class="bx bx-printer me-1"></i> Cetak
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted small py-4">Belum ada transaksi pada tanggal ini.</div>
                @endforelse
            </div>
        </div>

        <div class="card transactions-desktop-shell">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h5 class="mb-1">{{ $saleChannelLabel }}</h5>
                    <div class="text-muted small">Riwayat transaksi penjualan.</div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-x me-1"></i> Close
                    </a>
                    @if (auth()->user()->hasPermission('transactions.settings'))
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                            data-bs-target="#memberPointSettingModal">
                            <i class="bx bx-cog me-1"></i> Setting Poin Member
                        </button>
                    @endif
                    @if (auth()->user()->hasPermission('transactions.create'))
                        <a href="{{ $saleCreateRoute }}" class="btn btn-primary">
                            <i class="bx bx-plus me-1"></i> Jual
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body transactions-table-wrapper">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-2 mb-3">
                    <div>
                        <div class="fw-semibold">Ringkasan Penjualan Harian</div>
                        <div class="text-muted small">Tanggal: <span class="fw-semibold">{{ \Carbon\Carbon::parse($dailySummary['date'])->format('d/m/Y') }}</span></div>
                    </div>
                    <form method="GET" action="{{ $saleIndexRoute }}" class="d-flex align-items-end gap-2">
                        <div>
                            <label class="form-label mb-1">Pilih Tanggal</label>
                            <input type="date" name="date" class="form-control" value="{{ $selectedDate }}"
                                {{ $salesTableReady ? '' : 'disabled' }}
                                onchange="this.form.submit()">
                        </div>
                        <a href="{{ $saleIndexRoute }}"
                            class="btn btn-outline-secondary {{ $salesTableReady ? '' : 'disabled' }}" title="Hari ini"
                            aria-disabled="{{ $salesTableReady ? 'false' : 'true' }}">
                            <i class="bx bx-calendar me-1"></i> Hari ini
                        </a>
                    </form>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6 col-lg-3">
                        <div class="trx-summary-card">
                            <div class="k">Omzet</div>
                            <div class="v">Rp {{ number_format((float) ($dailySummary['grand_total'] ?? 0), 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="trx-summary-card">
                            <div class="k">Transaksi</div>
                            <div class="v">{{ number_format((int) ($dailySummary['transactions_count'] ?? 0), 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="trx-summary-card">
                            <div class="k">Item</div>
                            <div class="v">{{ number_format((int) ($dailySummary['items_count'] ?? 0), 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="trx-summary-card">
                            <div class="k">Diskon</div>
                            <div class="v">Rp {{ number_format((float) ($dailySummary['discount_total'] ?? 0), 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="transactions-table" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Tanggal</th>
                                <th>Customer</th>
                                <th>Lokasi</th>
                                <th>Item</th>
                                <th>Total</th>
                                <th>Poin</th>
                                <th>Kasir</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>

                <div class="transactions-mobile-list">
                    @forelse ($recentSales as $mobileSale)
                        @php
                            $firstItem = $mobileSale->items->first();
                            $remainingItems = max(0, $mobileSale->items->count() - 1);
                        @endphp
                        <div class="trx-mobile-card">
                            <div class="trx-mobile-top">
                                <div>
                                    <a href="{{ route('transactions.show', $mobileSale) }}" class="trx-mobile-code text-decoration-none">
                                        {{ $mobileSale->sale_code }}
                                    </a>
                                    <div class="trx-mobile-meta">
                                        <span>{{ optional($mobileSale->sale_at)->format('d/m/Y H:i') ?: '-' }}</span>
                                        <span>{{ $mobileSale->customer?->name ?: 'Pelanggan umum' }} | {{ $mobileSale->location?->name ?: '-' }}</span>
                                        <span>Kasir: {{ $mobileSale->cashier?->name ?: '-' }}</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="trx-mobile-total">Rp {{ number_format((float) $mobileSale->grand_total, 0, ',', '.') }}</div>
                                    <span class="badge bg-label-{{ $mobileSale->status === 'paid' ? 'success' : 'secondary' }}">
                                        {{ $mobileSale->status === 'paid' ? 'Paid' : 'Void' }}
                                    </span>
                                </div>
                            </div>

                            <div class="trx-mobile-item">
                                @if ($firstItem)
                                    <div class="fw-semibold">{{ $firstItem->product_name }}</div>
                                    <div class="text-muted">
                                        {{ rtrim(rtrim(number_format((float) $firstItem->quantity, 2, ',', '.'), '0'), ',') }}
                                        {{ $firstItem->unit_name ?: 'PCS' }}
                                        @if ($remainingItems > 0)
                                            | +{{ $remainingItems }} item lain
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">Tidak ada item.</span>
                                @endif
                            </div>

                            <div class="trx-mobile-actions">
                                <a href="{{ route('transactions.show', $mobileSale) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bx bx-show me-1"></i> Detail
                                </a>
                                <a href="{{ route('transactions.receipt', [$mobileSale, 'print' => 1]) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="bx bx-printer me-1"></i> Cetak
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted small py-4">Belum ada transaksi pada tanggal ini.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @if (auth()->user()->hasPermission('transactions.settings'))
        <div class="modal fade" id="memberPointSettingModal" tabindex="-1" aria-labelledby="memberPointSettingModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="memberPointSettingModalLabel">Setting Poin Member</h5>
                            <div class="text-muted small">Contoh: transaksi Rp 50.000 dapat 1 poin.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Rupiah per 1 poin</label>
                            <input type="number" min="1" step="1" class="form-control"
                                id="memberPointSpendAmount" value="{{ (int) $memberPointSpendAmount }}">
                            <div class="form-text">Contoh: transaksi Rp 50.000 dapat 1 poin.</div>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">Reset Poin</label>
                                <input type="number" min="0" max="120" step="1" class="form-control"
                                    id="memberPointResetEvery" value="{{ (int) ($memberPointResetEvery ?? 0) }}">
                                <div class="form-text">0 = tidak reset otomatis.</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Per</label>
                                <select class="form-select" id="memberPointResetUnit">
                                    <option value="month" {{ ($memberPointResetUnit ?? 'month') === 'month' ? 'selected' : '' }}>Bulan</option>
                                    <option value="year" {{ ($memberPointResetUnit ?? 'month') === 'year' ? 'selected' : '' }}>Tahun</option>
                                </select>
                                <div class="form-text">Contoh: reset setiap 12 bulan.</div>
                            </div>
                        </div>
                        @if (!$settingsTableReady)
                            <div class="alert alert-warning mb-0">
                                Pengaturan belum tersimpan karena tabel `pos_settings` belum ada.
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary" id="saveMemberPointSetting"
                            {{ $settingsTableReady ? '' : 'disabled' }}>
                            <i class="bx bx-save me-1"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        $(function() {
            $('#transactions-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: @json($saleDataRoute),
                order: [
                    [1, 'desc']
                ],
                autoWidth: false,
                scrollX: true,
                columns: [{
                        data: 'sale_code_link',
                        name: 'sale_code'
                    },
                    {
                        data: 'sale_at_label',
                        name: 'sale_at'
                    },
                    {
                        data: 'customer_label',
                        name: 'customer.name',
                        orderable: false
                    },
                    {
                        data: 'location_label',
                        name: 'location.name',
                        orderable: false
                    },
                    {
                        data: 'items_summary',
                        name: 'items_count',
                        className: 'trx-items-cell',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'grand_total_label',
                        name: 'grand_total',
                        searchable: false
                    },
                    {
                        data: 'points_earned',
                        name: 'points_earned',
                        searchable: false
                    },
                    {
                        data: 'cashier_label',
                        name: 'cashier.name',
                        orderable: false
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                dom: '<"row align-items-center mb-3 g-2"<"col-lg-4"l><"col-lg-8 text-lg-end"B>><"row mb-3"<"col-12"f>>rt<"row align-items-center mt-3 g-2"<"col-md-6"i><"col-md-6"p>>',
                buttons: ['copy', 'excel', 'pdf', 'print', 'colvis']
            });

            const settingsUrl = '{{ route('transactions.settings.member-points') }}';
            const csrf = $('meta[name="csrf-token"]').attr('content');

            $('#saveMemberPointSetting').on('click', function() {
                const value = parseInt($('#memberPointSpendAmount').val() || '0', 10);
                if (!value || value < 1) return;
                const resetEvery = parseInt($('#memberPointResetEvery').val() || '0', 10);
                const resetUnit = String($('#memberPointResetUnit').val() || 'month');

                const btn = $(this);
                btn.prop('disabled', true);

                $.ajax({
                    url: settingsUrl,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf
                    },
                    data: {
                        member_point_spend_amount: value,
                        member_point_reset_every: isNaN(resetEvery) ? 0 : resetEvery,
                        member_point_reset_unit: resetUnit
                    }
                }).done(function() {
                    const modalEl = document.getElementById('memberPointSettingModal');
                    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    window.location.reload();
                }).fail(function(xhr) {
                    const message = xhr.responseJSON?.message || 'Gagal menyimpan setting.';
                    alert(message);
                }).always(function() {
                    btn.prop('disabled', false);
                });
            });
        });
    </script>
@endpush
