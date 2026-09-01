@extends('layouts.sneat')

@section('title', 'Laporan POS & Servis')

@push('styles')
<style>
    .report-card {
        border: none;
        border-radius: 12px;
        background: var(--bs-card-bg);
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }
    .report-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.1);
    }
    .report-icon-container {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.25rem;
        background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.1) 0%, rgba(var(--bs-primary-rgb), 0.2) 100%);
        color: var(--bs-primary);
        font-size: 1.75rem;
        transition: all 0.3s ease;
    }
    .report-card:hover .report-icon-container {
        transform: scale(1.1);
        color: #fff;
    }
    /* Dynamic Hover Backgrounds for Icons */
    .card-sales:hover .report-icon-container { background: linear-gradient(135deg, #3f51b5 0%, #2196f3 100%); }
    .card-purchases:hover .report-icon-container { background: linear-gradient(135deg, #009688 0%, #4caf50 100%); }
    .card-stocks:hover .report-icon-container { background: linear-gradient(135deg, #ff9800 0%, #ffc107 100%); }
    .card-cash:hover .report-icon-container { background: linear-gradient(135deg, #e91e63 0%, #ff2d55 100%); }
    .card-receivables:hover .report-icon-container { background: linear-gradient(135deg, #9c27b0 0%, #e040fb 100%); }
    .card-services:hover .report-icon-container { background: linear-gradient(135deg, #00bcd4 0%, #80deea 100%); }
    .card-profit-loss:hover .report-icon-container { background: linear-gradient(135deg, #4caf50 0%, #8bc34a 100%); }

    .kpi-gradient-1 { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff; }
    .kpi-gradient-2 { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: #fff; }
    .kpi-gradient-3 { background: linear-gradient(135deg, #ff4b2b 0%, #ff416c 100%); color: #fff; }
    .kpi-gradient-4 { background: linear-gradient(135deg, #7f00ff 0%, #e100ff 100%); color: #fff; }

    .kpi-card {
        border: none;
        border-radius: 14px;
        padding: 1.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        position: relative;
        overflow: hidden;
    }
    .kpi-card::after {
        content: '';
        position: absolute;
        width: 150px;
        height: 150px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        top: -50px;
        right: -50px;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Pusat</span> Laporan Eksekutif</h4>
            <p class="text-muted mb-0">Analisis bisnis, mutasi kas, stok barang, piutang tempo, dan produktivitas servis.</p>
        </div>
    </div>

    <!-- KPI Row -->
    <div class="row g-3 mb-4">
        <!-- Today's Retail Sales -->
        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-gradient-1">
                <span class="d-block mb-1 text-white-50">Omset Retail Hari Ini</span>
                <h3 class="card-title text-white mb-2 fw-bold">Rp {{ number_format($todaySales, 0, ',', '.') }}</h3>
                <small class="text-white-50"><i class="bx bx-trending-up me-1"></i>Penjualan Toko/Cabang</small>
            </div>
        </div>

        <!-- Today's Service Revenue -->
        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-gradient-2">
                <span class="d-block mb-1 text-white-50">Omset Servis Hari Ini</span>
                <h3 class="card-title text-white mb-2 fw-bold">Rp {{ number_format($todayServices, 0, ',', '.') }}</h3>
                <small class="text-white-50"><i class="bx bx-wrench me-1"></i>Uang Jasa & Sparepart Servis</small>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-gradient-3">
                <span class="d-block mb-1 text-white-50">Produk Menipis</span>
                <h3 class="card-title text-white mb-2 fw-bold">{{ $lowStockCount }} Item</h3>
                <small class="text-white-50"><i class="bx bx-error-alt me-1"></i>Di bawah stok minimum</small>
            </div>
        </div>

        <!-- Total Outstanding Receivables (Piutang) -->
        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-gradient-4">
                <span class="d-block mb-1 text-white-50">Total Piutang Aktif</span>
                <h3 class="card-title text-white mb-2 fw-bold">Rp {{ number_format($totalReceivables, 0, ',', '.') }}</h3>
                <small class="text-white-50"><i class="bx bx-time-five me-1"></i>Invoice Tempo Belum Lunas</small>
            </div>
        </div>
    </div>

    <!-- Reports Main Menu Grid -->
    <h5 class="pb-1 mb-3">Modul Laporan Tersedia</h5>
    <div class="row g-3">
        <!-- Sales Report -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 report-card card-sales">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="report-icon-container">
                            <i class="bx bx-trending-up"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Laporan Penjualan</h5>
                        <p class="text-muted small mb-4">Ringkasan transaksi kasir per periode, filter channel penjualan (Toko, Cabang, Partai), dan metode pembayaran.</p>
                    </div>
                    <a href="{{ route('reports.sales') }}" class="btn btn-outline-primary w-100 mt-auto">Buka Laporan <i class="bx bx-chevron-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <!-- Purchases Report -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 report-card card-purchases">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="report-icon-container">
                            <i class="bx bx-shopping-bag"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Laporan Pembelian & PO</h5>
                        <p class="text-muted small mb-4">Pantau riwayat pemesanan barang (Purchase Orders) kepada supplier beserta nilai total pengeluaran belanja.</p>
                    </div>
                    <a href="{{ route('reports.purchases') }}" class="btn btn-outline-primary w-100 mt-auto">Buka Laporan <i class="bx bx-chevron-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <!-- Inventory Report -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 report-card card-stocks">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="report-icon-container">
                            <i class="bx bx-package"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Laporan Stok & Mutasi</h5>
                        <p class="text-muted small mb-4">Pantau level persediaan per gudang/etalase, warning minimum stok, dan telusuri log mutasi masuk-keluar ledger.</p>
                    </div>
                    <a href="{{ route('reports.stocks') }}" class="btn btn-outline-primary w-100 mt-auto">Buka Laporan <i class="bx bx-chevron-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <!-- Cash Ledger Report -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 report-card card-cash">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="report-icon-container">
                            <i class="bx bx-wallet"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Buku Kas & Kasbon</h5>
                        <p class="text-muted small mb-4">Pantau pemasukan operasional luar toko, biaya pengeluaran kantor, kasbon karyawan, dan mutasi saldo antar kas.</p>
                    </div>
                    <a href="{{ route('reports.cash') }}" class="btn btn-outline-primary w-100 mt-auto">Buka Laporan <i class="bx bx-chevron-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <!-- Outstanding Receivables Report -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 report-card card-receivables">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="report-icon-container">
                            <i class="bx bx-time-five"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Laporan Hutang Piutang</h5>
                        <p class="text-muted small mb-4">Pantau piutang aktif pelanggan dari penjualan tempo, melacak sisa tagihan, jatuh tempo nota, dan status pembayaran.</p>
                    </div>
                    <a href="{{ route('reports.receivables') }}" class="btn btn-outline-primary w-100 mt-auto">Buka Laporan <i class="bx bx-chevron-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <!-- Service Transactions Report -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 report-card card-services">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="report-icon-container">
                            <i class="bx bx-wrench"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Laporan Servis HP</h5>
                        <p class="text-muted small mb-4">Analisis transaksi jasa reparasi, melacak performa teknisi, status perbaikan gadget, dan pemakaian sparepart terkait.</p>
                    </div>
                    <a href="{{ route('reports.services') }}" class="btn btn-outline-primary w-100 mt-auto">Buka Laporan <i class="bx bx-chevron-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <!-- Profit & Loss Report -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 report-card card-profit-loss">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="report-icon-container">
                            <i class="bx bx-calculator"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Laporan Laba Rugi</h5>
                        <p class="text-muted small mb-4">Analisis performa finansial bersih per periode, membandingkan total pendapatan retail/servis dengan HPP (modal) & biaya operasional.</p>
                    </div>
                    <a href="{{ route('reports.profit_loss') }}" class="btn btn-outline-primary w-100 mt-auto">Buka Laporan <i class="bx bx-chevron-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
