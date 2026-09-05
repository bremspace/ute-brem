@extends('layouts.sneat')

@section('title', 'Dashboard')

@php
    $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
@endphp

@push('styles')
    <style>
        .dash-kpi .card {
            border: 0;
            box-shadow: 0 4px 20px rgba(17, 24, 39, 0.06);
        }

        .dash-kpi .kpi-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            color: #fff;
        }

        .kpi-icon.emerald {
            background: linear-gradient(135deg, #059669, #34d399);
        }

        .kpi-icon.primary {
            background: linear-gradient(135deg, var(--bs-primary), #818cf8);
        }

        .kpi-icon.amber {
            background: linear-gradient(135deg, #d97706, #fbbf24);
        }

        .kpi-icon.purple {
            background: linear-gradient(135deg, #7c3aed, #a78bfa);
        }

        .dash-quick a.btn {
            font-weight: 700;
        }

        .dash-table td,
        .dash-table th {
            vertical-align: middle;
        }

        .low-stock-pill {
            font-weight: 900;
            letter-spacing: .02em;
        }
    </style>
@endpush

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if (!($tables['sales'] ?? false))
            <div class="alert alert-warning">
                Dashboard menampilkan data terbatas karena tabel transaksi belum tersedia.
            </div>
        @endif

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h4 class="mb-1">Dashboard</h4>
                <div class="text-muted">Selamat datang, {{ auth()->user()->name }}.</div>
            </div>
            <div class="dash-quick d-flex gap-2 flex-wrap justify-content-md-end">
                @if (auth()->user()->hasPermission('transactions.create'))
                    <a href="{{ route('transactions.create') }}" class="btn btn-primary">
                        <i class="bx bx-cart-add me-1"></i> Jual
                    </a>
                @endif
                @if (auth()->user()->hasPermission('transactions.view'))
                    <a href="{{ route('transactions.index') }}" class="btn btn-outline-primary">
                        <i class="bx bx-receipt me-1"></i> Transaksi
                    </a>
                @endif
                @if (auth()->user()->hasPermission('master.products.view'))
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-package me-1"></i> Produk
                    </a>
                @endif
                @if (auth()->user()->hasPermission('master.product_stocks.view'))
                    <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-receipt me-1"></i> Purchase Order
                    </a>
                @endif
            </div>
        </div>

        <div class="row g-4 dash-kpi mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card card-hover h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small">Penjualan Hari Ini</div>
                                <div class="h4 mb-0">{{ $rp($todaySalesTotal) }}</div>
                                <div class="text-muted small mt-1">{{ (int) $todaySalesCount }} transaksi</div>
                            </div>
                            <div class="kpi-icon emerald">
                                <i class="bx bx-trending-up fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-hover h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small">Item Terjual Hari Ini</div>
                                <div class="h4 mb-0">{{ number_format((int) $todayItemsCount, 0, ',', '.') }}</div>
                                <div class="text-muted small mt-1">{{ $today }}</div>
                            </div>
                            <div class="kpi-icon primary">
                                <i class="bx bx-basket fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-hover h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small">Penjualan Bulan Ini</div>
                                <div class="h4 mb-0">{{ $rp($monthSalesTotal) }}</div>
                                <div class="text-muted small mt-1">{{ now()->format('F Y') }}</div>
                            </div>
                            <div class="kpi-icon purple">
                                <i class="bx bx-line-chart fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-hover h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small">Stok Minim</div>
                                <div class="h4 mb-0">{{ number_format((int) $lowStockCount, 0, ',', '.') }}</div>
                                <div class="text-muted small mt-1">Produk perlu restock</div>
                            </div>
                            <div class="kpi-icon amber">
                                <i class="bx bx-error-circle fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card card-hover h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Penjualan 14 Hari Terakhir</h6>
                            <div class="text-muted small">Total omzet per hari.</div>
                        </div>
                        <span class="badge bg-label-primary">Paid Only</span>
                    </div>
                    <div class="card-body">
                        <div id="posSalesChart" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card card-hover h-100">
                    <div class="card-header">
                        <h6 class="mb-1">Kas Awal Hari Ini</h6>
                        <div class="text-muted small">Session kas per lokasi (user login).</div>
                    </div>
                    <div class="card-body">
                        @if (!($tables['cash_sessions'] ?? false))
                            <div class="text-muted">Tabel kas belum tersedia.</div>
                        @elseif ($cashSessions->count() === 0)
                            <div class="text-muted">Belum ada kas awal hari ini.</div>
                            @if (auth()->user()->hasPermission('transactions.create'))
                                <div class="mt-3">
                                    <a href="{{ route('transactions.create') }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bx bx-plus me-1"></i> Buka Kasir
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="list-group list-group-flush">
                                @foreach ($cashSessions as $session)
                                    <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-semibold">{{ $session->location?->name ?: '-' }}</div>
                                            <div class="text-muted small">
                                                {{ optional($session->opened_at)->format('H:i') ?: '-' }}
                                                <span class="ms-1 badge bg-label-{{ $session->status === 'open' ? 'success' : 'secondary' }}">
                                                    {{ $session->status === 'open' ? 'Open' : 'Closed' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="fw-bold">{{ $rp($session->opening_cash) }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card card-hover h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Produk Stok Minim</h6>
                            <div class="text-muted small">Klik untuk masuk ke stok lokasi.</div>
                        </div>
                        @if (auth()->user()->hasPermission('master.products.view'))
                            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">Lihat Produk</a>
                        @endif
                    </div>
                    <div class="card-body">
                        @if ($lowStockProducts->count() === 0)
                            <div class="text-muted">Tidak ada produk stok minim.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-striped dash-table">
                                    <thead>
                                        <tr>
                                            <th>Kode</th>
                                            <th>Nama</th>
                                            <th class="text-end">Stok</th>
                                            <th class="text-end">Min</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($lowStockProducts as $p)
                                            <tr>
                                                <td class="fw-semibold">
                                                    <a class="text-decoration-none" href="{{ route('products.stocks.index', $p) }}">
                                                        {{ $p->product_code ?: '-' }}
                                                    </a>
                                                </td>
                                                <td>{{ $p->name }}</td>
                                                <td class="text-end">
                                                    <span class="badge bg-label-warning low-stock-pill">
                                                        {{ rtrim(rtrim(number_format((float) $p->stock_global, 2, ',', '.'), '0'), ',') }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    {{ $p->stock_min !== null ? rtrim(rtrim(number_format((float) $p->stock_min, 2, ',', '.'), '0'), ',') : '-' }}
                                                </td>
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
                <div class="card card-hover h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Transaksi Terakhir</h6>
                            <div class="text-muted small">Ringkas transaksi terbaru.</div>
                        </div>
                        @if (auth()->user()->hasPermission('transactions.view'))
                            <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary btn-sm">Lihat</a>
                        @endif
                    </div>
                    <div class="card-body">
                        @if (!($tables['sales'] ?? false))
                            <div class="text-muted">Tabel transaksi belum tersedia.</div>
                        @elseif ($recentSales->count() === 0)
                            <div class="text-muted">Belum ada transaksi.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-striped dash-table">
                                    <thead>
                                        <tr>
                                            <th>Kode</th>
                                            <th>Pelanggan</th>
                                            <th>Kasir</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentSales as $sale)
                                            <tr>
                                                <td class="fw-semibold">
                                                    <a class="text-decoration-none" href="{{ route('transactions.show', $sale) }}">
                                                        {{ $sale->sale_code }}
                                                    </a>
                                                    <div class="text-muted small">{{ optional($sale->sale_at)->format('d/m H:i') }}</div>
                                                </td>
                                                <td>{{ $sale->customer?->name ?: '-' }}</td>
                                                <td>{{ $sale->cashier?->name ?: '-' }}</td>
                                                <td class="text-end fw-bold">{{ $rp($sale->grand_total) }}</td>
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

        @if (($tables['purchase_orders'] ?? false) && auth()->user()->hasPermission('master.product_stocks.view'))
            <div class="row g-4 mt-1">
                <div class="col-12">
                    <div class="card card-hover">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Purchase Order Terakhir</h6>
                                <div class="text-muted small">Riwayat PO terbaru.</div>
                            </div>
                            <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary btn-sm">Lihat</a>
                        </div>
                        <div class="card-body">
                            @if ($recentPurchaseOrders->count() === 0)
                                <div class="text-muted">Belum ada PO.</div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped dash-table">
                                        <thead>
                                            <tr>
                                                <th>PO</th>
                                                <th>Produk</th>
                                                <th>Supplier</th>
                                                <th>Lokasi</th>
                                                <th class="text-end">Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($recentPurchaseOrders as $po)
                                                <tr>
                                                    <td class="fw-semibold">
                                                        <a class="text-decoration-none" href="{{ route('purchase-orders.show', $po) }}">
                                                            {{ $po->po_number }}
                                                        </a>
                                                        <div class="text-muted small">{{ optional($po->ordered_at)->format('d/m H:i') }}</div>
                                                    </td>
                                                    <td>{{ $po->product?->name ?: '-' }}</td>
                                                    <td>{{ $po->supplier?->name ?: '-' }}</td>
                                                    <td>{{ $po->location?->name ?: '-' }}</td>
                                                    <td class="text-end">{{ rtrim(rtrim(number_format((float) $po->quantity, 2, ',', '.'), '0'), ',') }}</td>
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
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const el = document.querySelector('#posSalesChart');
            if (!el || typeof ApexCharts === 'undefined') return;

            const labels = @json($chartLabels);
            const totals = @json($chartTotals);
            const counts = @json($chartCounts);

            const options = {
                chart: {
                    type: 'area',
                    height: 300,
                    toolbar: { show: false }
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        opacityFrom: 0.35,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                series: [{
                    name: 'Omzet',
                    data: totals.map(v => Math.round(Number(v || 0)))
                }],
                xaxis: {
                    categories: labels
                },
                yaxis: {
                    labels: {
                        formatter: function(val) {
                            const n = Math.round(Number(val || 0));
                            return 'Rp ' + n.toLocaleString('id-ID');
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val, opts) {
                            const idx = opts.dataPointIndex ?? 0;
                            const cnt = Number(counts[idx] || 0);
                            return 'Rp ' + Math.round(Number(val || 0)).toLocaleString('id-ID') + ' (' + cnt + ' trx)';
                        }
                    }
                },
                colors: ['#5c73f8']
            };

            new ApexCharts(el, options).render();
        })();
    </script>
@endpush
