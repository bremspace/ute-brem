@extends('layouts.sneat')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Laporan /</span> Penjualan</h4>
            <p class="text-muted mb-0">Analisis histori penjualan retail per periode, channel, dan metode pembayaran.</p>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary"><i class="bx bx-chevron-left me-1"></i>Kembali</a>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.sales') }}" class="row g-3">
                <div class="col-12 col-md-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label">Channel</label>
                    <select name="sale_channel" class="form-select">
                        <option value="">Semua Channel</option>
                        <option value="toko" @selected($selectedChannel === 'toko')>Toko</option>
                        <option value="cabang" @selected($selectedChannel === 'cabang')>Cabang</option>
                        <option value="partai" @selected($selectedChannel === 'partai')>Partai</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label">Pembayaran</label>
                    <select name="payment_method" class="form-select">
                        <option value="">Semua Metode</option>
                        <option value="cash" @selected($selectedPaymentMethod === 'cash')>Tunai (Cash)</option>
                        <option value="transfer" @selected($selectedPaymentMethod === 'transfer')>Transfer</option>
                        <option value="qris" @selected($selectedPaymentMethod === 'qris')>QRIS</option>
                        <option value="tempo" @selected($selectedPaymentMethod === 'tempo')>Tempo (Credit)</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex align-items-end">
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
                            <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-receipt text-primary"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">{{ number_format($summary->total_transactions ?? 0) }}</h4>
                    </div>
                    <p class="mb-0 text-muted">Total Transaksi</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card card-border-shadow-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-success"><i class="bx bx-trending-up text-success"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">Rp {{ number_format($summary->total_grand ?? 0, 0, ',', '.') }}</h4>
                    </div>
                    <p class="mb-0 text-muted">Total Omset Bersih</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card card-border-shadow-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-gift text-warning"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">Rp {{ number_format($summary->total_discount ?? 0, 0, ',', '.') }}</h4>
                    </div>
                    <p class="mb-0 text-muted">Total Diskon Diberikan</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card card-border-shadow-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-info"><i class="bx bx-money text-info"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">Rp {{ number_format($summary->total_paid ?? 0, 0, ',', '.') }}</h4>
                    </div>
                    <p class="mb-0 text-muted">Uang Diterima</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="card">
        <h5 class="card-header pb-2">Rincian Transaksi Penjualan</h5>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Kode Struk</th>
                        <th>Tanggal</th>
                        <th>Channel</th>
                        <th>Pelanggan</th>
                        <th>Lokasi</th>
                        <th>Pembayaran</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">Diskon</th>
                        <th class="text-end">Grand Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($sales as $row)
                        <tr>
                            <td>
                                <a href="{{ route('transactions.show', $row->id) }}" class="fw-semibold text-primary">
                                    {{ $row->sale_code }}
                                </a>
                            </td>
                            <td>{{ $row->sale_at ? $row->sale_at->format('d/m/Y H:i') : '-' }}</td>
                            <td><span class="badge bg-label-secondary">{{ ucfirst($row->sale_channel) }}</span></td>
                            <td>{{ $row->customer?->name ?: 'Umum (Walk-in)' }}</td>
                            <td>{{ $row->location?->name ?: '-' }}</td>
                            <td>
                                <span class="badge bg-label-primary">{{ strtoupper($row->payment_method) }}</span>
                                @if($row->payment_method === 'tempo')
                                    <div class="text-muted small" style="font-size: 11px;">Jatuh Tempo: {{ $row->credit_due_at ? $row->credit_due_at->format('d/m/Y') : '-' }}</div>
                                @endif
                            </td>
                            <td class="text-end">Rp {{ number_format($row->subtotal, 0, ',', '.') }}</td>
                            <td class="text-end text-danger">-Rp {{ number_format($row->discount_total, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold">Rp {{ number_format($row->grand_total, 0, ',', '.') }}</td>
                            <td><span class="badge bg-label-success">Lunas</span></td>
                            <td>
                                <a href="{{ route('transactions.show', $row->id) }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-show me-1"></i>Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted">Tidak ada transaksi ditemukan pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer py-3">
            {{ $sales->links() }}
        </div>
    </div>
</div>
@endsection
