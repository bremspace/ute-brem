@extends('layouts.sneat')

@section('title', 'Laporan Keuangan Kas')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Laporan /</span> Keuangan Kas</h4>
            <p class="text-muted mb-0">Analisis buku kas operasional, pemasukan lainnya, pengeluaran kantor, dan kasbon karyawan.</p>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary"><i class="bx bx-chevron-left me-1"></i>Kembali</a>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.cash') }}" class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Akun Kas</label>
                    <select name="cash_account_id" class="form-select">
                        <option value="">Semua Rekening Kas</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" @selected((string) $selectedAccountId === (string) $acc->id)>
                                {{ $acc->name }} ({{ strtoupper($acc->type) }})
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
        <div class="col-12 col-sm-4">
            <div class="card card-border-shadow-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-success"><i class="bx bx-trending-up text-success"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">Rp {{ number_format($inflow, 0, ',', '.') }}</h4>
                    </div>
                    <p class="mb-0 text-muted">Total Pemasukan Operasional</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card card-border-shadow-danger h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-danger"><i class="bx bx-trending-down text-danger"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">Rp {{ number_format($outflow, 0, ',', '.') }}</h4>
                    </div>
                    <p class="mb-0 text-muted">Total Pengeluaran / Biaya</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card card-border-shadow-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-wallet text-primary"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">Rp {{ number_format($inflow - $outflow, 0, ',', '.') }}</h4>
                    </div>
                    <p class="mb-0 text-muted">Selisih Kas Bersih (Netto)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="nav-align-top mb-4">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-transactions" aria-controls="navs-transactions" aria-selected="true">
                    <i class="bx bx-list-ol me-1"></i> Jurnal Pemasukan / Pengeluaran
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-advances" aria-controls="navs-advances" aria-selected="false">
                    <i class="bx bx-user me-1"></i> Pinjaman / Kasbon Karyawan
                </button>
            </li>
        </ul>
        <div class="tab-content px-0 py-3 shadow-none bg-transparent">
            <!-- Transactions Tab -->
            <div class="tab-pane fade show active" id="navs-transactions" role="tabpanel">
                <div class="card">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Kode Jurnal</th>
                                    <th>Tanggal</th>
                                    <th>Tipe</th>
                                    <th>Akun Kas</th>
                                    <th>Kategori Biaya</th>
                                    <th>Keterangan</th>
                                    <th>Referensi</th>
                                    <th class="text-end">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @forelse($transactions as $row)
                                    @php
                                        $isIncome = $row->transaction_type === 'income';
                                    @endphp
                                    <tr>
                                        <td><span class="fw-semibold">{{ $row->transaction_code }}</span></td>
                                        <td>{{ $row->transaction_date ? $row->transaction_date->format('d/m/Y') : '-' }}</td>
                                        <td>
                                            <span class="badge bg-label-{{ $isIncome ? 'success' : 'danger' }}">
                                                {{ $isIncome ? 'Masuk' : 'Keluar' }}
                                            </span>
                                        </td>
                                        <td>{{ $row->cashAccount?->name ?: '-' }}</td>
                                        <td><span class="badge bg-label-secondary">{{ $row->costCategory?->name ?: 'Non-kategori' }}</span></td>
                                        <td>{{ $row->description }}</td>
                                        <td><span class="text-muted">{{ $row->reference ?: '-' }}</span></td>
                                        <td class="text-end fw-bold text-{{ $isIncome ? 'success' : 'danger' }}">
                                            Rp {{ number_format($row->amount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">Tidak ada transaksi kas terdaftar pada periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer py-3">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>

            <!-- Employee Advances (Kasbon) Tab -->
            <div class="tab-pane fade" id="navs-advances" role="tabpanel">
                <div class="card">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nomor Kasbon</th>
                                    <th>Tanggal Ambil</th>
                                    <th>Karyawan / SPG</th>
                                    <th>Keperluan / Catatan</th>
                                    <th>Dibuat Oleh</th>
                                    <th class="text-end">Jumlah Kasbon</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @forelse($advances as $adv)
                                    <tr>
                                        <td><span class="fw-semibold">{{ $adv->advance_code }}</span></td>
                                        <td>{{ $adv->advance_date ? $adv->advance_date->format('d/m/Y') : '-' }}</td>
                                        <td>{{ $adv->employee?->name ?: '-' }}</td>
                                        <td>{{ $adv->notes }}</td>
                                        <td>{{ $adv->creator?->name ?: '-' }}</td>
                                        <td class="text-end fw-bold text-danger">Rp {{ number_format($adv->amount, 0, ',', '.') }}</td>
                                        <td><span class="badge bg-label-warning">Belum Lunas</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Tidak ada data kasbon karyawan pada periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
