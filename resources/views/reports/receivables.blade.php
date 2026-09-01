@extends('layouts.sneat')

@section('title', 'Laporan Hutang Piutang')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Laporan /</span> Piutang</h4>
            <p class="text-muted mb-0">Pemantauan piutang aktif dari invoice penjualan tempo, melacak sisa tagihan, dan tanggal jatuh tempo.</p>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary"><i class="bx bx-chevron-left me-1"></i>Kembali</a>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.receivables') }}" class="row g-3">
                <div class="col-12 col-md-9">
                    <label class="form-label">Customer Terdaftar</label>
                    <select name="customer_id" class="form-select">
                        <option value="">Semua Customer yang Memiliki Histori Tempo</option>
                        @foreach($customers as $cust)
                            <option value="{{ $cust->id }}" @selected((string) $selectedCustomerId === (string) $cust->id)>
                                {{ $cust->name }} ({{ $cust->member_code ?: 'Non-member' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Widgets -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4">
            <div class="card card-border-shadow-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-receipt text-primary"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">Rp {{ number_format($totalCredit, 0, ',', '.') }}</h4>
                    </div>
                    <p class="mb-0 text-muted">Total Penjualan Tempo</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card card-border-shadow-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-success"><i class="bx bx-check-double text-success"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">Rp {{ number_format($totalPaid, 0, ',', '.') }}</h4>
                    </div>
                    <p class="mb-0 text-muted">Total Cicilan Terbayar</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card card-border-shadow-danger h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-danger"><i class="bx bx-time text-danger"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">Rp {{ number_format($outstanding, 0, ',', '.') }}</h4>
                    </div>
                    <p class="mb-0 text-muted">Sisa Saldo Piutang Aktif</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Receivables Table -->
    <div class="card">
        <h5 class="card-header pb-2">Rincian Invoice Penjualan Tempo</h5>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Kode Invoice</th>
                        <th>Tanggal Nota</th>
                        <th>Jatuh Tempo</th>
                        <th>Customer</th>
                        <th>Lokasi Jual</th>
                        <th class="text-end">Total Invoice</th>
                        <th class="text-end">Terbayar</th>
                        <th class="text-end">Sisa Piutang</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($receivables as $row)
                        @php
                            $remaining = $row->grand_total - $row->paid_amount;
                            $statusColors = [
                                'unpaid' => 'danger',
                                'partial' => 'warning',
                                'paid' => 'success'
                            ];
                            $statusLabels = [
                                'unpaid' => 'Belum Bayar',
                                'partial' => 'Sebagian',
                                'paid' => 'Lunas'
                            ];
                            $statusColor = $statusColors[$row->credit_status] ?? 'secondary';
                            $statusLabel = $statusLabels[$row->credit_status] ?? $row->credit_status;
                            $isOverdue = $remaining > 0 && $row->credit_due_at && $row->credit_due_at->isPast();
                        @endphp
                        <tr class="{{ $isOverdue ? 'table-light-danger' : '' }}">
                            <td>
                                <a href="{{ route('transactions.show', $row->id) }}" class="fw-semibold text-primary">
                                    {{ $row->sale_code }}
                                </a>
                                @if($isOverdue)
                                    <span class="badge bg-danger ms-1 text-uppercase" style="font-size: 9px;">Overdue</span>
                                @endif
                            </td>
                            <td>{{ $row->sale_at ? $row->sale_at->format('d/m/Y') : '-' }}</td>
                            <td>
                                <span class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                                    {{ $row->credit_due_at ? $row->credit_due_at->format('d/m/Y') : '-' }}
                                </span>
                            </td>
                            <td>{{ $row->customer?->name ?: '-' }}</td>
                            <td>{{ $row->location?->name ?: '-' }}</td>
                            <td class="text-end">Rp {{ number_format($row->grand_total, 0, ',', '.') }}</td>
                            <td class="text-end text-success">Rp {{ number_format($row->paid_amount, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold text-danger">Rp {{ number_format($remaining, 0, ',', '.') }}</td>
                            <td><span class="badge bg-label-{{ $statusColor }}">{{ $statusLabel }}</span></td>
                            <td>
                                <a href="{{ route('transactions.show', $row->id) }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-show me-1"></i>Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">Tidak ada tagihan piutang tempo terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer py-3">
            {{ $receivables->links() }}
        </div>
    </div>
</div>
@endsection
