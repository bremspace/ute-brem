@extends('layouts.sneat')

@section('title', 'Detail Transfer Stok')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">Detail Transfer Stok</h4>
            <div class="text-muted">Kode Dokumen: <span class="fw-bold">{{ $branchTransfer->transfer_code }}</span></div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('branch-transfers.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
            <a href="{{ route('branch-transfers.print', $branchTransfer) }}" target="_blank" class="btn btn-outline-primary">
                <i class="bx bx-printer me-1"></i> Cetak Surat Jalan
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Document Information Card -->
        <div class="col-md-4">
            <div class="card mb-4 h-100">
                <div class="card-header">
                    <h5 class="mb-0">Informasi Pengiriman</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small d-block">Status</label>
                        @if($branchTransfer->status === 'draft')
                            <span class="badge bg-label-secondary fs-6">Draft</span>
                        @elseif($branchTransfer->status === 'in_transit')
                            <span class="badge bg-label-info fs-6">Dalam Perjalanan (In Transit)</span>
                        @elseif($branchTransfer->status === 'completed')
                            <span class="badge bg-label-success fs-6">Selesai (Completed)</span>
                        @elseif($branchTransfer->status === 'cancelled')
                            <span class="badge bg-label-danger fs-6">Dibatalkan (Cancelled)</span>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small d-block">Cabang Asal</label>
                        <div class="fw-bold">{{ $branchTransfer->sourceBranch?->name ?? '-' }}</div>
                        <small class="text-muted">{{ $branchTransfer->sourceLocation?->name }}</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small d-block">Cabang Tujuan</label>
                        <div class="fw-bold">{{ $branchTransfer->targetBranch?->name ?? '-' }}</div>
                        <small class="text-muted">{{ $branchTransfer->targetLocation?->name }}</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small d-block">Tanggal Pembuatan / Pengiriman</label>
                        <div>Pembuatan: {{ $branchTransfer->created_at->format('d/m/Y H:i') }}</div>
                        @if($branchTransfer->sent_at)
                            <div class="text-success">Pengiriman: {{ $branchTransfer->sent_at->format('d/m/Y H:i') }}</div>
                        @endif
                    </div>

                    @if($branchTransfer->received_at)
                        <div class="mb-3">
                            <label class="form-label text-muted small d-block">Tanggal Diterima</label>
                            <div class="fw-bold text-success">{{ $branchTransfer->received_at->format('d/m/Y H:i') }}</div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label text-muted small d-block">Penanggung Jawab</label>
                        <div>Pengirim: {{ $branchTransfer->creator?->name ?? '-' }}</div>
                        @if($branchTransfer->recipient)
                            <div>Penerima: {{ $branchTransfer->recipient?->name }}</div>
                        @endif
                    </div>

                    @if($branchTransfer->notes)
                        <div class="mb-3">
                            <label class="form-label text-muted small d-block">Catatan</label>
                            <div class="bg-light p-2 rounded small">{{ $branchTransfer->notes }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Document Details / Items List -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Daftar Produk</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Rak Asal</th>
                                    <th>Rak Tujuan</th>
                                    <th class="text-end">Kirim</th>
                                    @if(in_array($branchTransfer->status, ['completed']))
                                        <th class="text-end">Diterima</th>
                                        <th class="text-end">Selisih</th>
                                    @endif
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($branchTransfer->items as $item)
                                    @php
                                        $qtySent = (float) $item->quantity_sent;
                                        $qtyReceived = $item->quantity_received !== null ? (float) $item->quantity_received : null;
                                        $discrepancy = $qtyReceived !== null ? $qtySent - $qtyReceived : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">[{{ $item->product?->product_code }}]</span><br>
                                            <small class="text-muted">{{ $item->product?->name }}</small>
                                        </td>
                                        <td>{{ $item->sourceRack?->name ?: '-' }}</td>
                                        <td>{{ $item->targetRack?->name ?: '-' }}</td>
                                        <td class="text-end fw-semibold">{{ rtrim(rtrim(number_format($qtySent, 2, ',', '.'), '0'), ',') }}</td>
                                        @if(in_array($branchTransfer->status, ['completed']))
                                            <td class="text-end fw-semibold text-success">{{ rtrim(rtrim(number_format($qtyReceived, 2, ',', '.'), '0'), ',') }}</td>
                                            <td class="text-end fw-semibold text-danger">
                                                @if($discrepancy > 0)
                                                    -{{ rtrim(rtrim(number_format($discrepancy, 2, ',', '.'), '0'), ',') }}
                                                @else
                                                    0
                                                @endif
                                            </td>
                                        @endif
                                        <td><small>{{ $item->notes ?: '-' }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Action Form/Panel -->
            @if(in_array($branchTransfer->status, ['draft', 'in_transit']) && auth()->user()->hasPermission('master.product_stocks.edit'))
                <div class="card border-primary">
                    <div class="card-header bg-label-primary">
                        <h5 class="mb-0 text-primary">Aksi Dokumen</h5>
                    </div>
                    <div class="card-body pt-3">
                        <div class="d-flex flex-wrap gap-3 justify-content-end">
                            @if($branchTransfer->status === 'draft')
                                <!-- Cancel Draft Form -->
                                <form action="{{ route('branch-transfers.cancel', $branchTransfer) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengiriman ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bx bx-x me-1"></i> Batalkan Pengiriman
                                    </button>
                                </form>

                                <!-- Ship Draft Form -->
                                <form action="{{ route('branch-transfers.ship', $branchTransfer) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengirim barang? Stok lokasi asal akan langsung dipotong.')">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-send me-1"></i> Kirim Barang
                                    </button>
                                </form>
                            @endif

                            @if($branchTransfer->status === 'in_transit')
                                <!-- Cancel In-Transit Form -->
                                <form action="{{ route('branch-transfers.cancel', $branchTransfer) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengiriman ini? Stok yang telah dikurangi di lokasi asal akan dikembalikan.')">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bx bx-x me-1"></i> Batalkan & Kembalikan Stok
                                    </button>
                                </form>

                                <!-- Receive Form -->
                                <a href="{{ route('branch-transfers.receive.form', $branchTransfer) }}" class="btn btn-primary">
                                    <i class="bx bx-package me-1"></i> Konfirmasi Penerimaan Barang
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
