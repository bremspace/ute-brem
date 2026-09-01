@extends('layouts.sneat')

@section('title', 'Laporan Transaksi Servis')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Laporan /</span> Servis HP</h4>
            <p class="text-muted mb-0">Analisis kinerja teknisi, histori reparasi gadget, dan rincian omset servis per periode.</p>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary"><i class="bx bx-chevron-left me-1"></i>Kembali</a>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.services') }}" class="row g-3">
                <div class="col-12 col-md-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Teknisi</label>
                    <select name="technician_id" class="form-select">
                        <option value="">Semua Teknisi</option>
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}" @selected((string) $selectedTechnicianId === (string) $tech->id)>
                                {{ $tech->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label">Status Servis</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="process" @selected($selectedStatus === 'process')>Proses</option>
                        <option value="done" @selected($selectedStatus === 'done')>Selesai</option>
                        <option value="taken" @selected($selectedStatus === 'taken')>Diambil</option>
                        <option value="cancelled" @selected($selectedStatus === 'cancelled')>Batal</option>
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
                            <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-wrench text-primary"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">{{ number_format($totalCount) }}</h4>
                    </div>
                    <p class="mb-0 text-muted">Jumlah Nota Servis</p>
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
                        <h4 class="ms-1 mb-0">Rp {{ number_format($totalAmount, 0, ',', '.') }}</h4>
                    </div>
                    <p class="mb-0 text-muted">Total Nilai / Omset Jasa & Sparepart</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Transactions Table -->
    <div class="card">
        <h5 class="card-header pb-2">Jurnal Transaksi Perbaikan HP</h5>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Kode Servis</th>
                        <th>Tanggal Masuk</th>
                        <th>Pelanggan</th>
                        <th>Perangkat (Device)</th>
                        <th>Kunci Pengaman</th>
                        <th>Teknisi</th>
                        <th>Kasir Penerima</th>
                        <th class="text-end">Total Biaya</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($services as $row)
                        @php
                            $colors = ['process' => 'warning', 'done' => 'info', 'taken' => 'success', 'cancelled' => 'secondary'];
                            $labels = ['process' => 'Proses', 'done' => 'Selesai', 'taken' => 'Diambil', 'cancelled' => 'Batal'];
                            $statusColor = $colors[$row->status] ?? 'secondary';
                            $statusLabel = $labels[$row->status] ?? $row->status;
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('service-transactions.show', $row->id) }}" class="fw-semibold text-primary">
                                    {{ $row->service_code }}
                                </a>
                            </td>
                            <td>{{ $row->service_at ? $row->service_at->format('d/m/Y H:i') : '-' }}</td>
                            <td>
                                <div class="fw-semibold">{{ $row->customer_name }}</div>
                                <small class="text-muted">{{ $row->customer_phone ?: '-' }}</small>
                            </td>
                            <td>{{ trim(($row->device_brand ?: '') . ' ' . ($row->device_type ?: '')) ?: '-' }}</td>
                            <td>
                                @if($row->device_lock_type)
                                    <span class="badge bg-label-dark">{{ strtoupper($row->device_lock_type) }}: {{ $row->device_lock_value }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $row->technician?->name ?: '-' }}</td>
                            <td>{{ $row->cashier?->name ?: '-' }}</td>
                            <td class="text-end fw-bold">Rp {{ number_format($row->grand_total, 0, ',', '.') }}</td>
                            <td><span class="badge bg-label-{{ $statusColor }}">{{ $statusLabel }}</span></td>
                            <td>
                                <a href="{{ route('service-transactions.show', $row->id) }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-show me-1"></i>Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">Tidak ada transaksi servis HP ditemukan pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer py-3">
            {{ $services->links() }}
        </div>
    </div>
</div>
@endsection
