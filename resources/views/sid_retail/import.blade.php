@extends('layouts.sneat')

@section('title', 'Migrasi SID Retail')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Migrasi Database SID Retail</h4>
            <div class="text-muted">Import dan sinkronisasi data dari backup SQL SID Retail (<code>latest.sql</code>).</div>
        </div>
        <a href="{{ route('sid-retail.config') }}" class="btn btn-outline-primary btn-sm">
            <i class="bx bx-cog me-1"></i> Konfigurasi DB
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            <i class="bx bx-error me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Jalankan Migrasi</h5>
            <p class="text-muted small">
                Data akan dibaca langsung dari file <span class="badge bg-label-info">latest.sql</span> di root project.
                Proses mencakup pemetaan produk, kategori, supplier, customer/member, mutasi stok, hingga akun pengguna.
            </p>

            <form action="{{ route('sid-retail.migrate') }}" method="POST" id="migrateForm">
                @csrf
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="confirm" value="1" id="confirmCheck" required>
                    <label class="form-check-label text-dark fw-semibold" for="confirmCheck">
                        Saya memahami dan menyetujui proses migrasi database ini ke sistem UTE POS.
                    </label>
                </div>
                <button type="submit" class="btn btn-primary" id="btnSubmit">
                    <i class="bx bx-play-circle me-1"></i> Mulai Migrasi Sekarang
                </button>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Riwayat & Log Migrasi</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tabel</th>
                        <th>Status</th>
                        <th>Total Baris</th>
                        <th>Berhasil</th>
                        <th>Gagal</th>
                        <th>Waktu Mulai</th>
                        <th>Waktu Selesai</th>
                        <th>Pesan Error</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse ($logs as $log)
                        <tr>
                            <td><span class="badge bg-label-primary font-monospace">{{ $log->table_name }}</span></td>
                            <td>
                                @if ($log->status === 'completed')
                                    <span class="badge bg-success">Selesai</span>
                                @elseif ($log->status === 'partial')
                                    <span class="badge bg-warning">Sebagian</span>
                                @elseif ($log->status === 'failed')
                                    <span class="badge bg-danger">Gagal</span>
                                @elseif ($log->status === 'running')
                                    <span class="badge bg-info">Sedang Berjalan</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($log->status) }}</span>
                                @endif
                            </td>
                            <td>{{ number_format($log->records_total) }}</td>
                            <td class="text-success fw-bold">{{ number_format($log->records_imported) }}</td>
                            <td class="text-danger fw-bold">{{ number_format($log->records_failed) }}</td>
                            <td>{{ optional($log->started_at)->format('d/m/Y H:i:s') ?: '-' }}</td>
                            <td>{{ optional($log->completed_at)->format('d/m/Y H:i:s') ?: '-' }}</td>
                            <td class="small text-danger text-truncate" style="max-width: 200px;">{{ $log->error_message ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                Belum ada riwayat migrasi. Klik tombol "Mulai Migrasi Sekarang" di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())
            <div class="card-footer">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Cakupan Tabel & Modul yang Didukung</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @php
                    $modules = [
                        ['barang', 'Produk & Barcode', 'bx-package', 'Produk, harga bertingkat, stok global, dsb.'],
                        ['kategori', 'Kategori', 'bx-category', 'Kategori dan sub kategori barang.'],
                        ['supplier', 'Supplier', 'bx-truck', 'Data vendor dan supplier sparepart.'],
                        ['pelanggan', 'Customer & Member', 'bx-user', 'Pelanggan umum dan member beserta poin.'],
                        ['penjualan', 'Transaksi Penjualan', 'bx-receipt', 'Faktur dan riwayat penjualan POS.'],
                        ['pembelian', 'Purchase Orders', 'bx-cart', 'Riwayat order pembelian ke supplier.'],
                        ['arus_stok', 'Ledger Mutasi Stok', 'bx-transfer', 'Arus masuk/keluar stok barang.'],
                        ['user', 'Pengguna Aplikasi', 'bx-shield', 'Akun kasir, manager, dan owner.'],
                    ];
                @endphp
                @foreach ($modules as $m)
                    <div class="col-md-3 col-sm-6">
                        <div class="border rounded p-3 h-100">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bx {{ $m[2] }} fs-4 text-primary"></i>
                                <div class="fw-bold">{{ $m[1] }}</div>
                            </div>
                            <span class="badge bg-label-secondary font-monospace mb-1">{{ $m[0] }}</span>
                            <div class="small text-muted">{{ $m[3] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('migrateForm')?.addEventListener('submit', function() {
        const btn = document.getElementById('btnSubmit');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Memproses Migrasi...';
        }
    });
</script>
@endsection
