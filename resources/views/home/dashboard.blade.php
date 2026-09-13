@extends('internal.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Welcome Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-surface-800 mb-0">Dashboard</h1>
            <p class="text-muted mb-0">Selamat datang, {{ Auth::user()->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('transactions.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Transaksi Baru
            </a>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">Penjualan Hari Ini</div>
                            <h3 class="fw-bold text-success mb-0">Rp 12,500,000</h3>
                        </div>
                        <div class="rounded bg-success bg-opacity-10 text-success p-2">
                            <i class="bi bi-cash-coin fs-5"></i>
                        </div>
                    </div>
                    <div class="small text-success mt-2">
                        <i class="bi bi-arrow-up"></i> +12% dari kemarin
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">Pelanggan Aktif</div>
                            <h3 class="fw-bold text-primary mb-0">847</h3>
                        </div>
                        <div class="rounded bg-primary bg-opacity-10 text-primary p-2">
                            <i class="bi bi-people fs-5"></i>
                        </div>
                    </div>
                    <div class="small text-muted mt-2">
                        <i class="bi bi-info-circle"></i> +24 hari ini
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">Stok Rendah</div>
                            <h3 class="fw-bold text-warning mb-0">23 item</h3>
                        </div>
                        <div class="rounded bg-warning bg-opacity-10 text-warning p-2">
                            <i class="bi bi-exclamation-triangle fs-5"></i>
                        </div>
                    </div>
                    <div class="small text-warning mt-2">
                        <i class="bi bi-arrow-right"></i> Perlu reorder
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">Persetujuan Tertunda</div>
                            <h3 class="fw-bold text-danger mb-0">5</h3>
                        </div>
                        <div class="rounded bg-danger bg-opacity-10 text-danger p-2">
                            <i class="bi bi-clock-history fs-5"></i>
                        </div>
                    </div>
                    <div class="small text-danger mt-2">
                        <i class="bi bi-arrow-right"></i> 2 PO perlu review
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="row g-4">
        <!-- Recent Transactions -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">Transaksi Terakhir</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode</th>
                                    <th>Pelanggan</th>
                                    <th>Item</th>
                                    <th>Total</th>
                                    <th>Metode</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-medium">TRX-2026-0001</td>
                                    <td>PT. Bintang Jaya</td>
                                    <td>5</td>
                                    <td class="fw-medium">Rp 1,250,000</td>
                                    <td><span class="badge bg-success">Cash</span></td>
                                    <td class="text-success">Selesai</td>
                                </tr>
                                <tr>
                                    <td class="fw-medium">TRX-2026-0002</td>
                                    <td>PT. Sejahtera Abadi</td>
                                    <td>3</td>
                                    <td class="fw-medium">Rp 850,000</td>
                                    <td><span class="badge bg-info">QRIS</span></td>
                                    <td class="text-success">Selesai</td>
                                </tr>
                                <tr>
                                    <td class="fw-medium">TRX-2026-0003</td>
                                    <td>Ahmad (Member)</td>
                                    <td>2</td>
                                    <td class="fw-medium">Rp 250,000</td>
                                    <td><span class="badge bg-warning text-dark">Tempo</span></td>
                                    <td class="text-warning">Menunggu</td>
                                </tr>
                                <tr>
                                    <td class="fw-medium">TRX-2026-0004</td>
                                    <td>Budi Santoso</td>
                                    <td>10</td>
                                    <td class="fw-medium">Rp 2,100,000</td>
                                    <td><span class="badge bg-success">Cash</span></td>
                                    <td class="text-success">Selesai</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 border-top text-center">
                        <a href="{{ route('transactions.index') }}" class="btn btn-outline-primary btn-sm">
                            Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">Alert Stok Rendah</h5>
                </div>
                <div class="card-body">
                    @foreach(['Injeksi AST 10L', 'Oil Filter 2041', 'Spark Plug NGK', 'Air Filter Mann', 'Brake Pads Kit'] as $item)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <div class="fw-medium small">{{ $item }}</div>
                            <div class="text-muted small">3 pcs tersisa</div>
                        </div>
                        <span class="badge bg-warning text-dark">Rendah</span>
                    </div>
                    @endforeach
                    <div class="pt-3 text-center">
                        <a href="{{ route('products.index') }}" class="btn btn-outline-warning btn-sm">
                            Lihat Semua Stok <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection