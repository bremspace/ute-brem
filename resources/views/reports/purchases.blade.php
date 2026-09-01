@extends('layouts.sneat')

@section('title', 'Laporan Pembelian & PO')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Laporan /</span> Pembelian</h4>
            <p class="text-muted mb-0">Pantau riwayat pemesanan barang (Purchase Orders) kepada supplier.</p>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary"><i class="bx bx-chevron-left me-1"></i>Kembali</a>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.purchases') }}" class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">Semua Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected((string) $selectedSupplierId === (string) $supplier->id)>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt"></i></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Widgets -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6">
            <div class="card card-border-shadow-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-shopping-bag text-primary"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">{{ number_format($summary->total_pos ?? 0) }}</h4>
                    </div>
                    <p class="mb-0 text-muted">Jumlah PO Dibuat</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6">
            <div class="card card-border-shadow-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-success"><i class="bx bx-money text-success"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">Rp {{ number_format($summary->total_purchase_amount ?? 0, 0, ',', '.') }}</h4>
                    </div>
                    <p class="mb-0 text-muted">Total Nilai Pembelian</p>
                </div>
            </div>
        </div>
    </div>

    <!-- PO Table -->
    <div class="card">
        <h5 class="card-header pb-2">Daftar Purchase Orders</h5>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nomor PO</th>
                        <th>Tanggal PO</th>
                        <th>Supplier</th>
                        <th>Lokasi Masuk</th>
                        <th>Dibuat Oleh</th>
                        <th>Catatan</th>
                        <th class="text-end">Total Nilai</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($purchases as $row)
                        <tr>
                            <td>
                                <a href="{{ route('purchase-orders.show', $row->id) }}" class="fw-semibold text-primary">
                                    {{ $row->po_number }}
                                </a>
                            </td>
                            <td>{{ $row->order_date ? $row->order_date->format('d/m/Y') : '-' }}</td>
                            <td>{{ $row->supplier?->name ?: '-' }}</td>
                            <td>{{ $row->location?->name ?: '-' }}</td>
                            <td>{{ $row->creator?->name ?: '-' }}</td>
                            <td><span class="text-muted small">{{ \Str::limit($row->notes, 30) }}</span></td>
                            <td class="text-end fw-bold">Rp {{ number_format($row->total_amount, 0, ',', '.') }}</td>
                            <td><span class="badge bg-label-success">Selesai (Masuk)</span></td>
                            <td>
                                <a href="{{ route('purchase-orders.show', $row->id) }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-show me-1"></i>Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">Tidak ada Purchase Order ditemukan pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer py-3">
            {{ $purchases->links() }}
        </div>
    </div>
</div>
@endsection
