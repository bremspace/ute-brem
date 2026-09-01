@extends('layouts.sneat')

@section('title', 'Laporan Laba Rugi')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Laporan /</span> Laba Rugi</h4>
            <p class="text-muted mb-0">Analisis laba kotor dan laba bersih usaha berdasarkan pendapatan dan HPP riil per periode.</p>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary"><i class="bx bx-chevron-left me-1"></i>Kembali</a>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.profit_loss') }}" class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-12 col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Widgets -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card card-border-shadow-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-trending-up text-primary"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h4>
                    </div>
                    <p class="mb-0 text-muted">Total Pendapatan Bersih</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card card-border-shadow-danger h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-danger"><i class="bx bx-down-arrow-alt text-danger"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">Rp {{ number_format($totalHpp, 0, ',', '.') }}</h4>
                    </div>
                    <p class="mb-0 text-muted">Total HPP (Modal)</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card card-border-shadow-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-calculator text-warning"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">Rp {{ number_format($grossProfit, 0, ',', '.') }}</h4>
                    </div>
                    <p class="mb-0 text-muted">Laba Kotor</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card card-border-shadow-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-success"><i class="bx bx-money text-success"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">Rp {{ number_format($netProfit, 0, ',', '.') }}</h4>
                    </div>
                    <p class="mb-0 text-muted">Laba Bersih</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed PnL Statement Card -->
    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="mb-0">Laporan Laba Rugi Rinci</h5>
        </div>
        <div class="card-body pt-4">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr class="table-dark">
                            <th>Komponen Keuangan</th>
                            <th class="text-end">Rincian (Rp)</th>
                            <th class="text-end">Jumlah (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- 1. PENDAPATAN -->
                        <tr class="fw-bold bg-label-primary">
                            <td colspan="3"><i class="bx bx-receipt me-1"></i> I. PENDAPATAN OPERASIONAL</td>
                        </tr>
                        <tr>
                            <td class="ps-4">Pendapatan Penjualan Retail POS (Kotor)</td>
                            <td class="text-end text-muted">{{ number_format($salesRevenue, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="ps-4">Potongan / Diskon Penjualan POS (-)</td>
                            <td class="text-end text-danger">({{ number_format($salesDiscount, 0, ',', '.') }})</td>
                            <td></td>
                        </tr>
                        <tr class="table-light">
                            <td class="ps-4 fw-semibold text-secondary">Pendapatan Penjualan Retail Net</td>
                            <td></td>
                            <td class="text-end fw-semibold">{{ number_format($netSales, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4">Pendapatan Sparepart Servis</td>
                            <td class="text-end text-muted">{{ number_format($sparepartsRevenue, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="ps-4">Pendapatan Jasa Labor/Servis</td>
                            <td class="text-end text-muted">{{ number_format($laborRevenue, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                        <tr class="table-light">
                            <td class="ps-4 fw-semibold text-secondary">Pendapatan Servis & Spareparts Net</td>
                            <td></td>
                            <td class="text-end fw-semibold">{{ number_format($sparepartsRevenue + $laborRevenue, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="table-primary fw-bold">
                            <td>TOTAL PENDAPATAN OPERASIONAL NET (A)</td>
                            <td></td>
                            <td class="text-end">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                        </tr>

                        <!-- 2. HPP / BEBAN POKOK -->
                        <tr class="fw-bold bg-label-danger mt-3">
                            <td colspan="3"><i class="bx bx-shopping-bag me-1"></i> II. BEBAN POKOK PENJUALAN (HPP)</td>
                        </tr>
                        <tr>
                            <td class="ps-4">Beban Pokok Penjualan Retail POS (HPP)</td>
                            <td class="text-end text-muted">{{ number_format($salesHpp, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="ps-4">Beban Pokok Spareparts Servis (HPP)</td>
                            <td class="text-end text-muted">{{ number_format($sparepartsHpp, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                        <tr class="table-danger fw-bold">
                            <td>TOTAL BEBAN POKOK PENJUALAN / HPP (B)</td>
                            <td></td>
                            <td class="text-end text-danger">Rp ({{ number_format($totalHpp, 0, ',', '.') }})</td>
                        </tr>

                        <!-- 3. LABA KOTOR -->
                        <tr class="table-warning fw-bold fs-5">
                            <td>LABA KOTOR OPERASIONAL (A - B)</td>
                            <td></td>
                            <td class="text-end">Rp {{ number_format($grossProfit, 0, ',', '.') }}</td>
                        </tr>

                        <!-- 4. PENGELUARAN OPERASIONAL LAINNYA -->
                        <tr class="fw-bold bg-label-warning">
                            <td colspan="3"><i class="bx bx-calculator me-1"></i> III. BIAYA OPERASIONAL BACKOFFICE</td>
                        </tr>
                        <tr>
                            <td class="ps-4">Biaya Operasional Umum / Beban Lain-Lain</td>
                            <td class="text-end text-muted">{{ number_format($expenses, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                        <tr class="table-danger fw-bold">
                            <td>TOTAL BIAYA OPERASIONAL BACKOFFICE (C)</td>
                            <td></td>
                            <td class="text-end text-danger">Rp ({{ number_format($expenses, 0, ',', '.') }})</td>
                        </tr>

                        <!-- 5. LABA BERSIH -->
                        <tr class="table-success fw-bold fs-4">
                            <td>LABA BERSIH USAHA (LABA KOTOR - C)</td>
                            <td></td>
                            <td class="text-end text-success">Rp {{ number_format($netProfit, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
